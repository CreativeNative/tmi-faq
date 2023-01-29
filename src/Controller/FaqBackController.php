<?php

declare(strict_types=1);

namespace TmiFaq\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Validator\NoObjectExists;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use TmiFaq\Entity\FaqEntity;
use TmiFaq\Form\FaqForm;
use TmiFaq\Repository\FaqRepository;

use function array_merge;
use function locale_get_default;

class FaqBackController extends AbstractActionController
{
    private EntityManager $entityManager;

    private Translator $translator;

    private FaqForm $faqForm;

    private string $locale;

    public function __construct(
        EntityManager $entityManager,
        Translator $translator,
        FaqForm $faqForm,
    ) {
        $this->entityManager = $entityManager;
        $this->translator    = $translator;
        $this->faqForm       = $faqForm;
        $this->locale        = locale_get_default();
    }

    public function indexAction(): ViewModel
    {
        /** @var FaqRepository $repository */
        $repository = $this->entityManager->getRepository(FaqEntity::class);
        $faqs       = $repository->getQuestionsAsArray();

        return new ViewModel(
            [
                'faqs' => $faqs,
            ]
        );
    }

    /**
     * @throws OptimisticLockException|ORMException
     */
    public function createAction(): Response|ViewModel
    {
        $form = $this->faqForm;

        $faq = new FaqEntity();

        /** Bind entity to form */
        $form->bind($faq);

        /** @var Request $request */
        $request = $this->getRequest();

        $message = [];

        if ($request->isPost()) {
            $data = array_merge(
                $request->getPost()->toArray()
            );

            $form->setData($data);

            $validator = new NoObjectExists(
                [
                    'object_repository' => $this->entityManager->getRepository(
                        FaqEntity::class
                    ),
                    'fields'            => 'question',
                    'messages'          => [
                        NoObjectExists::ERROR_OBJECT_FOUND => "This input already exists.",
                    ],
                ]
            );

            if ($form->getInputFilter() !== null) {
                $form->getInputFilter()->get('question')->getValidatorChain()->attach($validator);
            }

            if ($form->isValid()) {
                /** @var FaqEntity $validatedEntity */
                $validatedEntity = $form->getData();

                $this->entityManager->persist($validatedEntity);
                $this->entityManager->flush();

                return $this->redirect()->toRoute('faq-back/edit', ['id' => $validatedEntity->getId()]);
            }

            $message = [
                'message' => 'message_persistens_incomplete',
                'class'   => 'alert-danger',
            ];
        }

        return new ViewModel(
            [
                'faq'     => $faq,
                'form'    => $form,
                'message' => $message,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     * @throws OptimisticLockException|ORMException
     */
    public function editAction(): Response|ViewModel
    {
        $entityId = (int) $this->params()->fromRoute('id');
        $locale   = (string) $this->params()->fromRoute('locale', 'de_DE');

        if ($entityId <= 0) {
            return $this->redirect()->toRoute('faq-back');
        }

        /** @var FaqRepository $repository */
        $repository = $this->entityManager->getRepository(FaqEntity::class);

        $entity = $repository->findByIdForUpdate($entityId);

        if ($entity === null) {
            return $this->redirect()->toRoute('faq-back');
        }

        $entity->setTranslatableLocale($locale);
        $this->entityManager->refresh($entity);

        $result = $this->getNameAndLinks($entity, $repository);

        $form = $this->faqForm;

        $form->bind($entity);

        /** @var Request $request */
        $request = $this->getRequest();

        $message = [];

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $form->setData($data);
            $message = [
                'message' => 'message_persistens_incomplete',
                'class'   => 'alert-danger',
            ];
            if ($form->isValid()) {
                /** @var FaqEntity $validatedEntity */
                $validatedEntity = $form->getObject();

                $this->entityManager->persist($validatedEntity);
                $this->entityManager->flush();

                $message = [
                    'message' => 'message_persistens_complete',
                    'class'   => 'alert-success',
                ];
            }
        }

        return new ViewModel(
            [
                'question' => $result['question'],
                'urlDe'    => $result['urlDe'],
                'urlEn'    => $result['urlEn'],
                'urlIt'    => $result['urlIt'],
                'locale'   => $locale,
                'form'     => $form,
                'entity'   => $entity,
                'message'  => $message,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     * @return array{question: string, urlDe: string, urlEn: string, urlIt: string}
     */
    private function getNameAndLinks(FaqEntity $guide, FaqRepository $repository): array
    {
        $translations = [];
        if ($guide->getTranslations() !== null) {
            foreach ($guide->getTranslations() as $row) {
                $translations[$row->getLocale()][$row->getField()] = $row->getContent();
            }
        }

        $germanNameAndSlug                 = $repository->getGermanNameAndSlugById($guide->getId());
        $translations['de_DE']['question'] = $germanNameAndSlug['question'];
        $translations['de_DE']['slug']     = $germanNameAndSlug['slug'];

        $domainDe = $this->translator->translate('terra-mia-domain', 'default', 'de_DE');
        $domainEn = $this->translator->translate('terra-mia-domain', 'default', 'en_US');
        $domainIt = $this->translator->translate('terra-mia-domain', 'default', 'it_IT');

        $urlDe = '';
        if (! empty($translations['de_DE']['slug'])) {
            $urlDe = $domainDe . $this->url()->fromRoute(
                'faq-front/category/question',
                [
                    'category' => $this->translator->translate(
                        $guide->getCategory()->getSlug()->getTranslationKey(),
                        'default',
                        'de_DE'
                    ),
                    'slug'     => $translations['de_DE']['slug'],
                ],
                ['locale' => 'de_DE']
            );
        }
        $urlEn = '';
        if (! empty($translations['en_US']['slug'])) {
            $urlEn = $domainEn . $this->url()->fromRoute(
                'faq-front/category/question',
                [
                    'category' => $this->translator->translate(
                        $guide->getCategory()->getSlug()->getTranslationKey(),
                        'default',
                        'en_US'
                    ),
                    'slug'     => $translations['en_US']['slug'],
                ],
                ['locale' => 'en_US']
            );
        }
        $urlIt = '';
        if (! empty($translations['it_IT']['slug'])) {
            $urlIt = $domainIt . $this->url()->fromRoute(
                'faq-front/category/question',
                [
                    'category' => $this->translator->translate(
                        $guide->getCategory()->getSlug()->getTranslationKey(),
                        'default',
                        'it_IT'
                    ),
                    'slug'     => $translations['it_IT']['slug'],
                ],
                ['locale' => 'it_IT']
            );
        }
        return [
            'question' => $translations[$this->locale]['question'],
            'urlDe'    => $urlDe,
            'urlEn'    => $urlEn,
            'urlIt'    => $urlIt,
        ];
    }
}
