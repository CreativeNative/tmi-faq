<?php

declare(strict_types=1);

namespace TmiFaq\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;
use TmiFaq\Entity\FaqCategoryEntity;
use TmiTranslation\Repository\TranslationEntityRepository;

use function locale_get_default;

class FaqCategoryRepository extends TranslationEntityRepository
{
    /**
     * Query for getting category by slug
     *
     * @throws NonUniqueResultException
     */
    final public function getCategory(string $slug): array|null
    {
        $locale = locale_get_default();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'Category',
            'partial CategoryTranslation.{id, content, field, locale}',
            'partial Slug.{id, translationKey}',
            'partial Name.{id, translationKey}',
            'partial Faqs.{id, question, slug}'
        )
            ->from(FaqCategoryEntity::class, 'Category')
            ->leftJoin('Category.translations', 'CategoryTranslation')
            ->leftJoin('Category.slug', 'Slug')
            ->leftJoin('Category.name', 'Name')
            ->leftJoin('Category.faqs', 'Faqs')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->isNull('CategoryTranslation.field'),
                    $queryBuilder->expr()->eq('CategoryTranslation.field', ':title')
                )
            )
            ->setParameter('title', 'title', Types::STRING)
            ->orderBy('Faqs.position', 'ASC');

        switch ($locale) {
            case 'de_DE':
                $queryBuilder->andWhere($queryBuilder->expr()->eq('Slug.german', ':slug'));
                break;
            case 'en_US':
                $queryBuilder->andWhere($queryBuilder->expr()->eq('Slug.english', ':slug'));
                break;
            case 'it_IT':
                $queryBuilder->andWhere($queryBuilder->expr()->eq('Slug.italian', ':slug'));
                break;
            default:
                throw new RuntimeException('Unexpected value');
        }

        $queryBuilder->setParameter('slug', $slug, Types::STRING);

        return $this->getOneOrNullResult(
            $queryBuilder,
            locale_get_default(),
            'findByCategorySlug-' . $slug,
            AbstractQuery::HYDRATE_ARRAY
        );
    }

    /**
     * Query for getting all categories in index page
     */
    final public function getCategoriesWithFaqs(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'partial Category.{id, description}',
            'partial Slug.{id, translationKey}',
            'partial Name.{id, translationKey}',
            'partial Faqs.{id, question, slug}'
        )
            ->from(FaqCategoryEntity::class, 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->leftJoin('Category.name', 'Name')
            ->leftJoin('Category.faqs', 'Faqs')
            ->orderBy('Category.position', 'ASC')
            ->orderBy('Faqs.position', 'ASC');

        return $this->getArrayResult(
            $queryBuilder,
            locale_get_default(),
            'getCategories'
        );
    }

    /**
     * @throws NonUniqueResultException
     */
    final public function getGermanFaqCategoryTitleById(int $entityId): array|null
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'partial Category.{id, title}'
        )
            ->from(FaqCategoryEntity::class, 'Category')
            ->where($queryBuilder->expr()->eq('Category.id', ':id'))
            ->setParameter('id', $entityId);

        return $this->getOneOrNullResult(
            $queryBuilder,
            'de_DE',
            'getGermanFaqCategoryTitleById-' . $entityId,
            AbstractQuery::HYDRATE_ARRAY
        );
    }

    final public function getCategoriesForSitemap(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'partial Category.{id}',
            'partial Slug.{id, translationKey}'
        )
            ->from(FaqCategoryEntity::class, 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->orderBy('Category.position', 'ASC');

        return $this->getArrayResult($queryBuilder, locale_get_default(), 'getFAQCategoriesForSitemap');
    }

    /**
     * - NOT CACHED -
     */
    final public function findAllCategories(string $locale = ''): array
    {
        if (empty($locale)) {
            $locale = locale_get_default();
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'partial Category.{id}',
            'partial Slug.{id, translationKey}',
            'partial Name.{id, translationKey}'
        )
            ->from(FaqCategoryEntity::class, 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->leftJoin('Category.name', 'Name')
            ->orderBy('Category.position', 'ASC');

        return $this->getArrayResult($queryBuilder, $locale);
    }

    /**
     * - NOT CACHED -
     * Query for select element
     */
    final public function findAllCategoriesForSelect(): array|null
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('Category', 'Translation')
            ->from(FaqCategoryEntity::class, 'Category')
            ->leftJoin('Category.name', 'Translation');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * - NOT CACHED
     *
     * @throws NonUniqueResultException
     */
    final public function findByIdandLocale(int $entityId, string $locale): FaqCategoryEntity|null
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('partial Category.{id, title, description, headline, teaser}')
            ->from(FaqCategoryEntity::class, 'Category')
            ->where($qb->expr()->eq('Category.id', ':id'))
            ->setParameter('id', $entityId);

        return $this->getOneOrNullResult($qb, $locale);
    }
}
