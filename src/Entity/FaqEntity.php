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
    private int|null $id = null;

    /** @Gedmo\Locale */
    private string|null $locale = null;

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
    private FaqCategoryEntity|null $category = null;

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
    private string|null $title = null;

    /**
     * @ORM\Column(type="string", length=160, nullable=true)
     *
     * @Gedmo\Translatable
     */
    private string|null $description = null;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     *
     * @Gedmo\Translatable
     */
    private string|null $question = null;

    /**
     * @ORM\Column(type="string", length=70, unique=true, nullable=true)
     *
     * @Gedmo\Translatable
     * @Gedmo\Slug(fields={"question"})
     */
    private string|null $slug = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    private string|null $answer = null;

    /** @ORM\Column(type="string", length=20, nullable=true) */
    private string|null $partial = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->categories   = new ArrayCollection();
    }

    final public function getId(): int|null
    {
        return $this->id;
    }

    final public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    final public function getTranslatableLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return ArrayCollection
     */
    final public function getTranslations(): iterable
    {
        return $this->translations;
    }

    final public function addTranslation(FaqTranslationEntity $translation): void
    {
        if (! $this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }
    }

    /**
     * Get main category
     */
    final public function getCategory(): FaqCategoryEntity|null
    {
        return $this->category;
    }

    final public function setCategory(FaqCategoryEntity $category): void
    {
        $this->category = $category;
    }

    final public function getCategories(): Collection
    {
        return $this->categories;
    }

    final public function addCategories(ArrayCollection $categories): void
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    final public function addCategory(FaqCategoryEntity $category): void
    {
        if ($this->categories->contains($category)) {
            return;
        }

        $this->categories->add($category);
        $category->addFaq($this);
    }

    final public function removeCategories(ArrayCollection $categories): void
    {
        foreach ($categories as $category) {
            $this->removeCategory($category);
        }
    }

    final public function removeCategory(FaqCategoryEntity $category): void
    {
        if (! $this->categories->contains($category)) {
            return;
        }

        $this->categories->removeElement($category);
        $category->removeFaq($this);
    }

    final public function getPosition(): int
    {
        return $this->position;
    }

    final public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    final public function getTitle(): string|null
    {
        return $this->title;
    }

    final public function setTitle(string|null $title): void
    {
        $this->title = $title;
    }

    final public function getSlug(): string|null
    {
        return $this->slug;
    }

    final public function setSlug(string|null $slug): void
    {
        $this->slug = $slug;
    }

    final public function getDescription(): string|null
    {
        return $this->description;
    }

    final public function setDescription(string|null $description): void
    {
        $this->description = $description;
    }

    final public function getQuestion(): string|null
    {
        return $this->question;
    }

    final public function setQuestion(string|null $question): void
    {
        $this->question = $question;
    }

    final public function getAnswer(): string|null
    {
        return $this->answer;
    }

    final public function setAnswer(string|null $answer): void
    {
        $this->answer = $answer;
    }

    final public function getPartial(): string|null
    {
        return $this->partial;
    }

    final public function setPartial(string|null $partial): void
    {
        $this->partial = $partial;
    }
}
