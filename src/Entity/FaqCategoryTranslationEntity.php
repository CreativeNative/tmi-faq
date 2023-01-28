<?php

declare(strict_types=1);

namespace TmiFaq\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="faq_category_translations",
 *     options={
 *         "charset":"utf8mb4",
 *         "collate":"utf8mb4_unicode_520_ci",
 *         "engine":"InnoDB"
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="lookup_unique_idx",
 *              columns={
 *                  "locale", "object_id", "field"
 *              }
 *          )
 *     }
 * )
 */
class FaqCategoryTranslationEntity extends AbstractPersonalTranslation
{
    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    /**
     * @ORM\ManyToOne(targetEntity="TmiFaq\Entity\FaqCategoryEntity", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     *
     * @var object
     */
    protected $object;
}
