<?php

declare(strict_types=1);

namespace TmiFaq\Controller;

use TmiFaq\Entity\FaqEntity;
use TmiFaq\Entity\FaqCategoryEntity;
use TmiFaq\Repository\FaqRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class FaqFrontController extends AbstractActionController
{
    private EntityManager $entityManager;

    private Translator $translator;

    private PhpRenderer $renderer;

    public function __construct(
        EntityManager $entityManager,
        Translator $translator,
        PhpRenderer $renderer
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->renderer = $renderer;
    }

    /**
     * @throws Exception
     */
    public function indexAction(): ViewModel
    {
        /** @var \TmiFaq\Repository\FaqCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(FaqCategoryEntity::class);

        /** @var array $categories */
        $categories = $repository->getCategoriesWithFaqs();

        $title = $this->translator->translate('terra-mia-title');
        $description = $this->translator->translate('terra-mia-description');

        $domain = $this->translator->translate('terra-mia-domain');
        $domainDe = $this->translator->translate('terra-mia-domain', 'default', 'de_DE');
        $domainEn = $this->translator->translate('terra-mia-domain', 'default', 'en_US');
        $domainIt = $this->translator->translate('terra-mia-domain', 'default', 'it_IT');

        $this->renderer->headLink(['rel' => 'canonical', 'href' => $domain], 'APPEND');
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'de', 'href' => $domainDe]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'en', 'href' => $domainEn]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'it', 'href' => $domainIt]);

        $defaultImage = $domain . $this->translator->translate('default-image');

        $this->renderer->headTitle($title);
        $this->renderer->headMeta()
            ->appendName('description', $description)
            ->setProperty('og:url', $domain)
            ->setProperty('og:title', $title)
            ->setProperty('og:description', $description)
            ->setProperty('og:image', $defaultImage)
            ->setProperty('og:image:type', 'image/jpeg')
            ->setProperty('og:image:width', '1024')
            ->setProperty('og:image:height', '1024')
            ->setProperty('og:image:alt', $title)
            ->setProperty('og:type', 'website');

        return new ViewModel(
            [
                'categories'   => $categories,
                'title'        => $title,
                'description'  => $description,
                'defaultImage' => $defaultImage,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function categoryAction(): ViewModel
    {
        $category = $this->params()->fromRoute('category');

        if (empty($category)) {
            $this->getResponse()->setStatusCode(404);
            return new ViewModel();
        }

        /** @var \TmiFaq\Repository\FaqCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(FaqCategoryEntity::class);

        /** @var array $categoryEntity */
        $categoryEntity = $repository->getCategory($category);

        if ($categoryEntity === null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        $title = $this->translator->translate('terra-mia-title');
        $description = $this->translator->translate('terra-mia-description');

        $domain = $this->translator->translate('terra-mia-domain');
        $domainDe = $this->translator->translate('terra-mia-domain', 'default', 'de_DE');
        $domainEn = $this->translator->translate('terra-mia-domain', 'default', 'en_US');
        $domainIt = $this->translator->translate('terra-mia-domain', 'default', 'it_IT');

        $this->renderer->headLink(['rel' => 'canonical', 'href' => $domain], 'APPEND');
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'de', 'href' => $domainDe]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'en', 'href' => $domainEn]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'it', 'href' => $domainIt]);

        $defaultImage = $domain . $this->translator->translate('default-image');

        $this->renderer->headTitle($title);
        $this->renderer->headMeta()
            ->appendName('description', $description)
            ->setProperty('og:url', $domain)
            ->setProperty('og:title', $title)
            ->setProperty('og:description', $description)
            ->setProperty('og:image', $defaultImage)
            ->setProperty('og:image:type', 'image/jpeg')
            ->setProperty('og:image:width', '1024')
            ->setProperty('og:image:height', '1024')
            ->setProperty('og:image:alt', $title)
            ->setProperty('og:type', 'website');

        return new ViewModel(
            [
                'category'     => $categoryEntity,
                'title'        => $title,
                'teaser'       => $description,
                'defaultImage' => $defaultImage,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function questionAction(): ViewModel
    {
        $category = $this->params()->fromRoute('category');
        $slug = $this->params()->fromRoute('slug');


        if (empty($category)) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }

        /** @var FaqRepository $repository */
        $repository = $this->entityManager->getRepository(FaqEntity::class);

        /** @var array $questionEntity */
        $questionEntity = $repository->findBySlug($slug);

        if ($questionEntity === null) {
            $this->getResponse()->setStatusCode(404);

            return new ViewModel();
        }
//        echo '<pre>' . print_r($questionEntity, true) . '</pre>';
//        exit;

        $result = $this->getNameAndLinks($questionEntity, $repository);



        $title = $this->translator->translate($questionEntity['category']['name']['translation_key']);
        $description = $this->translator->translate('terra-mia-description');

        $domain = $this->translator->translate('terra-mia-domain');

        $this->renderer->headLink(['rel' => 'canonical', 'href' => $result['canonicalUrl']], 'APPEND');
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'de', 'href' => $result['urlDe']]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'en', 'href' => $result['urlEn']]);
        $this->renderer->headLink(['rel' => 'alternate', 'hreflang' => 'it', 'href' => $result['urlIt']]);

        $defaultImage = $domain . $this->translator->translate('default-image');

        $this->renderer->headTitle($title);
        $this->renderer->headMeta()
            ->appendName('description', $description)
            ->setProperty('og:url', $domain)
            ->setProperty('og:title', $title)
            ->setProperty('og:description', $description)
            ->setProperty('og:image', $defaultImage)
            ->setProperty('og:image:type', 'image/jpeg')
            ->setProperty('og:image:width', '1024')
            ->setProperty('og:image:height', '1024')
            ->setProperty('og:image:alt', $title)
            ->setProperty('og:type', 'website');

        return new ViewModel(
            [
                'question'     => $questionEntity,
                'title'        => $title,
                'teaser'       => $description,
                'defaultImage' => $defaultImage,
            ]
        );
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getNameAndLinks(array $faq, FaqRepository $repository): array
    {
        $translations = [];
        if (isset($faq['translations'])) {
            foreach ($faq['translations'] as $row) {
                $translations[$row['locale']][$row['field']] = $row['content'];
            }
        }

        $germanNameAndSlug = $repository->getGermanNameAndSlugById($faq['id']);
        $translations['de_DE']['question'] = $germanNameAndSlug['question'];
        $translations['de_DE']['slug'] = $germanNameAndSlug['slug'];

        $domain = $this->translator->translate('terra-mia-domain');
        $domainDe = $this->translator->translate('terra-mia-domain', 'default', 'de_DE');
        $domainEn = $this->translator->translate('terra-mia-domain', 'default', 'en_US');
        $domainIt = $this->translator->translate('terra-mia-domain', 'default', 'it_IT');

        $canonicalUrl = $domain . $this->url()->fromRoute(
                'faq-front/category/question',
                [
                    'category' => $this->translator->translate($faq['category']['slug']['translation_key']),
                    'slug'     => $faq['slug'],
                ]
            );

        $urlDe = '';
        if (!empty($translations['de_DE']['slug'])) {
            $urlDe = $domainDe . $this->url()->fromRoute(
                    'faq-front/category/question',
                    [
                        'category' => $this->translator->translate(
                            $faq['category']['slug']['translation_key'],
                            'default',
                            'de_DE'
                        ),
                        'slug'     => $translations['de_DE']['slug'],
                    ],
                    ['locale' => 'de_DE']
                );
        }
        $urlEn = '';
        if (!empty($translations['en_US']['slug'])) {
            $urlEn = $domainEn . $this->url()->fromRoute(
                    'faq-front/category/question',
                    [
                        'category' => $this->translator->translate(
                            $faq['category']['slug']['translation_key'],
                            'default',
                            'en_US'
                        ),
                        'slug'     => $translations['en_US']['slug'],
                    ],
                    ['locale' => 'en_US']
                );
        }
        $urlIt = '';
        if (!empty($translations['it_IT']['slug'])) {
            $urlIt = $domainIt . $this->url()->fromRoute(
                    'faq-front/category/question',
                    [
                        'category' => $this->translator->translate(
                            $faq['category']['slug']['translation_key'],
                            'default',
                            'it_IT'
                        ),
                        'slug'     => $translations['it_IT']['slug'],
                    ],
                    ['locale' => 'it_IT']
                );
        }
        return [
            'questionDe'   => $translations['de_DE']['question'],
            'questionEn'   => $translations['en_US']['question'],
            'questionIt'   => $translations['it_IT']['question'],
            'canonicalUrl' => $canonicalUrl,
            'urlDe'        => $urlDe,
            'urlEn'        => $urlEn,
            'urlIt'        => $urlIt,
        ];
    }
}
