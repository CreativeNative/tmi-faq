<?php

declare(strict_types=1);

namespace TmiFaq\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="TmiFaq\Repository\FaqRepository")
 * @ORM\Table(
 *     name="faq",
 *     options={
 *         "charset":"utf8mb4",
 *         "collate":"utf8mb4_unicode_520_ci",
 *         "engine":"InnoDB"
 *     },
 *     indexes={
 *          @ORM\Index(columns={"position"})
 *     }
 * )
 *
 * @Gedmo\TranslationEntity(class="TmiFaq\Entity\FaqTranslationEntity")
 */
class FaqEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /** @Gedmo\Locale */
    private ?string $locale = null;

    /**
     * One-To-Many, Bidirectional, One Faq has Many Translations.
     *
     * @ORM\OneToMany(
     *     targetEntity="FaqTranslationEntity",
     *     mappedBy="object",
     *     cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    private Collection $translations;

    /** @ORM\ManyToOne(targetEntity="TmiFaq\Entity\FaqCategoryEntity") */
    private ?FaqCategoryEntity $category = null;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="TmiFaq\Entity\FaqCategoryEntity",
     *      inversedBy="faqs",
     *      cascade={"persist", "remove"}
     * )
     * @ORM\JoinTable(
     *      name="faq_categories",
     *      joinColumns={@ORM\JoinColumn(name="faq_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     * Many-To-Many, Unidirectional, Many Rentals have Many Categories.
     */
    private Collection $categories;

    /** @ORM\Column(type="integer") */
    private int $position = 0;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=160, nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $question = null;

    /**
     * @ORM\Column(type="string", length=70, unique=true, nullable=true)
     *
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"question"})
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $answer = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->categories   = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTranslatableLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations(): iterable
    {
        return $this->translations;
    }

    public function addTranslation(FaqTranslationEntity $translation): void
    {
        if (! $this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }
    }

    /**
     * Get main category
     */
    public function getCategory(): ?FaqCategoryEntity
    {
        return $this->category;
    }

    public function setCategory(FaqCategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }

    public function addCategories(ArrayCollection $categories): void
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    public function addCategory(FaqCategoryEntity $category): void
    {
        if ($this->categories->contains($category)) {
            return;
        }

        $this->categories->add($category);
        $category->addFaq($this);
    }

    public function removeCategories(ArrayCollection $categories): void
    {
        foreach ($categories as $category) {
            $this->removeCategory($category);
        }
    }

    public function removeCategory(FaqCategoryEntity $category): void
    {
        if (! $this->categories->contains($category)) {
            return;
        }

        $this->categories->removeElement($category);
        $category->removeFaq($this);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }
}
