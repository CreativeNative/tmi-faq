<?php

declare(strict_types=1);

namespace TmiFaq\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Validator\NoObjectExists;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
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

    private FaqForm $faqForm;

    private string $locale;

    public function __construct(
        EntityManager $entityManager,
        FaqForm $faqForm,
    ) {
        $this->entityManager = $entityManager;
        $this->faqForm       = $faqForm;
        $this->locale        = locale_get_default();
    }

    /**
     * @throws NotSupported
     */
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

            $form->getInputFilter()->get('question')->getValidatorChain()->attach($validator);

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
                'locale'   => $locale,
                'form'     => $form,
                'entity'   => $entity,
                'message'  => $message,
            ]
        );
    }

    /**
     * @throws NonUniqueResultException
     * @return array{question: string}
     */
    private function getNameAndLinks(FaqEntity $guide, FaqRepository $repository): array
    {
        $translations           = [];
        $translationsCollection = $guide->getTranslations();

        if (! $translationsCollection->isEmpty()) {
            foreach ($translationsCollection as $row) {
                $translations[$row->getLocale()][$row->getField()] = $row->getContent();
            }
        }

        $germanNameAndSlug                 = $repository->getGermanNameAndSlugById($guide->getId());
        $translations['de_DE']['question'] = $germanNameAndSlug['question'];
        $translations['de_DE']['slug']     = $germanNameAndSlug['slug'];

        return [
            'question' => $translations[$this->locale]['question'],
        ];
    }
}
