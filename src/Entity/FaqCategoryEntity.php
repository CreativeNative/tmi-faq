<?php

declare(strict_types=1);

namespace TmiFaq\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use TmiFaq\Repository\FaqCategoryRepository;
use TmiTranslation\Entity\TranslationEntity;

/**
 * @ORM\Entity(repositoryClass=FaqCategoryRepository::class)
 * @ORM\Table(
 *      name="faq_category",
 *      options={
 *         "charset":"utf8mb4",
 *         "collate":"utf8mb4_unicode_520_ci",
 *         "engine":"InnoDB"
 *      },
 *     indexes={
 *          @ORM\Index(columns={"position"})
 *     },
 * )
 *
 * @Gedmo\TranslationEntity(class=FaqCategoryTranslationEntity::class)
 */
class FaqCategoryEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int|null $id = null;

    /** @Gedmo\Locale */
    private string|null $locale = null;

    /** @ORM\Column(type="smallint", options={"default":0}) */
    private int $position = 0;

    /**
     * @ORM\ManyToMany(
     *      targetEntity=FaqEntity::class,
     *      mappedBy="categories",
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(
     *      name="faq_categories",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="faq_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    private Collection $faqs;

    /**
     * @ORM\OneToMany(
     *     targetEntity=FaqCategoryTranslationEntity::class,
     *     mappedBy="object",
     *     cascade={"persist","remove"}
     * )
     *
     * @var ArrayCollection
     */
    private Collection $translations;

    /**
     * @ORM\OneToOne(targetEntity=TranslationEntity::class, fetch="EAGER")
     * @ORM\JoinColumn(name="name", referencedColumnName="id")
     */
    private TranslationEntity|null $name = null;

    /**
     * @ORM\OneToOne(targetEntity=TranslationEntity::class, fetch="EAGER")
     * @ORM\JoinColumn(name="slug", referencedColumnName="id")
     */
    private TranslationEntity|null $slug = null;

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
    private string|null $headline = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    private string|null $teaser = null;

    public function __construct()
    {
        $this->faqs         = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    final public function setId(int $id): void
    {
        $this->id = $id;
    }

    final public function getId(): int|null
    {
        return $this->id;
    }

    final public function setTranslatableLocale(string|null $locale): void
    {
        $this->locale = $locale;
    }

    final public function getTranslatableLocale(): string|null
    {
        return $this->locale;
    }

    final public function getPosition(): int
    {
        return $this->position;
    }

    final public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    final public function getFaqs(): Collection
    {
        return $this->faqs;
    }

    final public function addFaqs(Collection $faqs): void
    {
        foreach ($faqs as $faq) {
            $this->addFaq($faq);
        }
    }

    final public function addFaq(FaqEntity $faq): void
    {
        if ($this->faqs->contains($faq)) {
            return;
        }

        $this->faqs->add($faq);
        $faq->addCategory($this);
    }

    final public function removeFaqs(Collection $faqs): void
    {
        foreach ($faqs as $faq) {
            $this->removeFaq($faq);
        }
    }

    final public function removeFaq(FaqEntity $faq): void
    {
        if (! $this->faqs->contains($faq)) {
            return;
        }

        $this->faqs->removeElement($faq);
        $faq->removeCategory($this);
    }

    final public function getTranslations(): Collection
    {
        return $this->translations;
    }

    final public function addTranslation(FaqCategoryTranslationEntity $translation): void
    {
        if (! $this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }
    }

    final public function removeName(): void
    {
        $this->name = null;
    }

    final public function getName(): TranslationEntity|null
    {
        return $this->name;
    }

    final public function getTranslationKeyForName(): string|null
    {
        if ($this->name instanceof TranslationEntity) {
            return $this->name->getTranslationKey();
        }

        return null;
    }

    final public function setName(TranslationEntity $translation): void
    {
        $this->name = $translation;
    }

    final public function removeSlug(): void
    {
        $this->slug = null;
    }

    final public function getSlug(): TranslationEntity|null
    {
        return $this->slug;
    }

    final public function getTranslationKeyForSlug(): string|null
    {
        if ($this->slug instanceof TranslationEntity) {
            return $this->slug->getTranslationKey();
        }

        return null;
    }

    final public function setSlug(TranslationEntity $translation): void
    {
        $this->slug = $translation;
    }

    final public function getTitle(): string|null
    {
        return $this->title;
    }

    final public function setTitle(string|null $title): void
    {
        $this->title = $title;
    }

    final public function getDescription(): string|null
    {
        return $this->description;
    }

    final public function setDescription(string|null $description): void
    {
        $this->description = $description;
    }

    final public function getHeadline(): string|null
    {
        return $this->headline;
    }

    final public function setHeadline(string|null $headline): void
    {
        $this->headline = $headline;
    }

    final public function getTeaser(): string|null
    {
        return $this->teaser;
    }

    final public function setTeaser(string|null $teaser): void
    {
        $this->teaser = $teaser;
    }
}
