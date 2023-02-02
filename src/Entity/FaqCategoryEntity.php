<?php

declare(strict_types=1);

namespace TmiFaq\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use TmiTranslation\Entity\TranslationEntity;

/**
 * @ORM\Entity(repositoryClass="TmiFaq\Repository\FaqCategoryRepository")
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
 * @Gedmo\TranslationEntity(class="TmiFaq\Entity\FaqCategoryTranslationEntity")
 */
class FaqCategoryEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** @Gedmo\Locale */
    private ?string $locale = null;

    /** @ORM\Column(type="smallint", options={"default":0}) */
    private int $position = 0;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="TmiFaq\Entity\FaqEntity",
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
     *     targetEntity="TmiFaq\Entity\FaqCategoryTranslationEntity",
     *     mappedBy="object",
     *     cascade={"persist","remove"}
     * )
     *
     * @var ArrayCollection
     */
    private Collection $translations;

    /**
     * @ORM\OneToOne(targetEntity="TmiTranslation\Entity\TranslationEntity", fetch="EAGER")
     * @ORM\JoinColumn(name="name", referencedColumnName="id")
     */
    private ?TranslationEntity $name = null;

    /**
     * @ORM\OneToOne(targetEntity="TmiTranslation\Entity\TranslationEntity", fetch="EAGER")
     * @ORM\JoinColumn(name="slug", referencedColumnName="id")
     */
    private ?TranslationEntity $slug = null;

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
    private ?string $headline = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $teaser = null;

    public function __construct()
    {
        $this->faqs         = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTranslatableLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTranslatableLocale(): ?string
    {
        return $this->locale;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getFaqs(): ?Collection
    {
        return $this->faqs;
    }

    public function addFaqs(Collection $faqs): void
    {
        foreach ($faqs as $faq) {
            $this->addFaq($faq);
        }
    }

    public function addFaq(FaqEntity $faq): void
    {
        if ($this->faqs->contains($faq)) {
            return;
        }

        $this->faqs->add($faq);
        $faq->addCategory($this);
    }

    public function removeFaqs(Collection $faqs): void
    {
        foreach ($faqs as $faq) {
            $this->removeFaq($faq);
        }
    }

    public function removeFaq(FaqEntity $faq): void
    {
        if (! $this->faqs->contains($faq)) {
            return;
        }

        $this->faqs->removeElement($faq);
        $faq->removeCategory($this);
    }

    public function getTranslations(): ?Collection
    {
        return $this->translations;
    }

    public function addTranslation(FaqCategoryTranslationEntity $translation): void
    {
        if (! $this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }
    }

    public function removeName(): void
    {
        $this->name = null;
    }

    public function getName(): ?TranslationEntity
    {
        return $this->name;
    }

    public function getTranslationKeyForName(): ?string
    {
        if ($this->name instanceof TranslationEntity) {
            return $this->name->getTranslationKey();
        }

        return null;
    }

    public function setName(TranslationEntity $translation): void
    {
        $this->name = $translation;
    }

    public function removeSlug(): void
    {
        $this->slug = null;
    }

    public function getSlug(): ?TranslationEntity
    {
        return $this->slug;
    }

    public function getTranslationKeyForSlug(): ?string
    {
        if ($this->slug instanceof TranslationEntity) {
            return $this->slug->getTranslationKey();
        }

        return null;
    }

    public function setSlug(TranslationEntity $translation): void
    {
        $this->slug = $translation;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): void
    {
        $this->headline = $headline;
    }

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }
}
