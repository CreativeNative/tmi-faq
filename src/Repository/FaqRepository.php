<?php

declare(strict_types=1);

namespace TmiFaq\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use TmiFaq\Entity\FaqEntity;
use TmiTranslation\Repository\TranslationEntityRepository;

use function locale_get_default;

class FaqRepository extends TranslationEntityRepository
{
    private string $locale;

    public function __construct(EntityManagerInterface $entityManager, ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->locale = locale_get_default();
    }

    /**
     * Query for faq question view
     *
     * @throws NonUniqueResultException
     */
    final public function findBySlug(string $slug): array|null
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'Faq',
            'partial FaqTranslation.{id, content, field, locale}',
            'partial Categories.{id}',
            'partial Slugs.{id, translationKey}',
            'partial Names.{id, translationKey}',
            'partial Category.{id}',
            'partial Slug.{id, translationKey}',
            'partial Name.{id, translationKey}'
        )
            ->from(FaqEntity::class, 'Faq')
            ->leftJoin('Faq.translations', 'FaqTranslation')
            ->leftJoin('Faq.categories', 'Categories')
            ->leftJoin('Categories.slug', 'Slugs')
            ->leftJoin('Categories.name', 'Names')
            ->leftJoin('Faq.category', 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->leftJoin('Category.name', 'Name')
            ->where($queryBuilder->expr()->eq('Faq.slug', ':slug'))
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('FaqTranslation.field', ':questionField'),
                    $queryBuilder->expr()->eq('FaqTranslation.field', ':slugField')
                )
            )
            ->setParameter('slug', $slug, Types::STRING)
            ->setParameter('questionField', 'question', Types::STRING)
            ->setParameter('slugField', 'slug', Types::STRING);

        return $this->getOneOrNullResult(
            $queryBuilder,
            $this->locale,
            'faq-' . $slug . '-' . $this->locale,
            AbstractQuery::HYDRATE_ARRAY
        );
    }

    /**
     * @throws NonUniqueResultException
     */
    final public function getGermanNameAndSlugById(int $entityId): array|null
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('Faq.question, Faq.slug')
            ->from(FaqEntity::class, 'Faq')
            ->where($queryBuilder->expr()->eq('Faq.id', ':id'))
            ->setParameter('id', $entityId);

        return $this->getOneOrNullResult(
            $queryBuilder,
            'de_DE',
            'getGermanNameAndSlugById-' . $entityId,
            AbstractQuery::HYDRATE_ARRAY
        );
    }

    final public function getSitemap(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'partial Faq.{id, slug}',
            'partial FaqTranslation.{id, content, field, locale}',
            'partial Category.{id}',
            'partial Slug.{id, translationKey}'
        )
            ->from(FaqEntity::class, 'Faq')
            ->leftJoin('Faq.translations', 'FaqTranslation')
            ->leftJoin('Faq.category', 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->isNotNull('FaqTranslation.field'),
                    $queryBuilder->expr()->eq('FaqTranslation.field', ':slugField')
                )
            )
            ->setParameter('slugField', 'slug', Types::STRING);

        return $this->getArrayResult($queryBuilder, $this->locale, 'getFAQSitemap');
    }

    /**
     * - NOT CACHED -
     *
     * Query for faq index
     */
    final public function getQuestionsAsArray(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'Faq',
            'partial Category.{id}',
            'partial Slug.{id, translationKey}',
            'FaqTranslation'
        )
            ->from(FaqEntity::class, 'Faq')
            ->leftJoin('Faq.category', 'Category')
            ->leftJoin('Category.slug', 'Slug')
            ->leftJoin('Faq.translations', 'FaqTranslation')
            ->orderBy('Faq.position', 'ASC');

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * - NOT CACHED -
     *
     * Return faq for updating
     *
     * @throws NonUniqueResultException
     */
    final public function findByIdForUpdate(int $entityId): FaqEntity|null
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(
            'Faq',
            'Category',
            'CategorySlug',
            'CategoryName',
            'Categories',
            'CategoriesSlug',
            'CategoriesName',
        )
            ->from(FaqEntity::class, 'Faq')
            ->leftJoin('Faq.category', 'Category')
            ->leftJoin('Category.slug', 'CategorySlug')
            ->leftJoin('Category.name', 'CategoryName')
            ->leftJoin('Faq.categories', 'Categories')
            ->leftJoin('Categories.slug', 'CategoriesSlug')
            ->leftJoin('Categories.name', 'CategoriesName')
            ->where($queryBuilder->expr()->eq('Faq.id', ':id'))
            ->setParameter('id', $entityId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
