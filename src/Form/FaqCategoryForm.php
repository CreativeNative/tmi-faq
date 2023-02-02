<?php

declare(strict_types=1);

namespace TmiFaq\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use TmiFaq\Entity\FaqCategoryEntity;
use TmiTranslation\Entity\TranslationEntity;

class FaqCategoryForm extends Form
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct('category');

        $this->entityManager = $entityManager;

        $this->setHydrator(new DoctrineHydrator($entityManager))
            ->setObject(new FaqCategoryEntity());

        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements(): void
    {
        $this->add(
            [
                'type'       => Element\Hidden::class,
                'name'       => 'id',
                'attributes' => ['id' => 'categoryId'],
            ]
        );

        $this->add(
            [
                'name'       => 'name',
                'type'       => ObjectSelect::class,
                'options'    => [
                    'label'            => 'Name (key)',
                    'label_attributes' => [
                        'class' => 'required',
                    ],
                    'empty_option'     => 'Please select',
                    'object_manager'   => $this->entityManager,
                    'target_class'     => TranslationEntity::class,
                    'label_generator'  => function ($entity) {
                        /** @var  TranslationEntity $entity */
                        return ' ' . $entity->getTranslationKey();
                    },
                    'find_method'      => [
                        'name' => 'forFaqCategoryName',
                    ],
                ],
                'attributes' => [
                    'id'       => 'name',
                    'required' => 'required',
                    'style'    => 'width:100%',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'slug',
                'type'       => ObjectSelect::class,
                'options'    => [
                    'label'            => 'Slug (key)',
                    'label_attributes' => [
                        'class' => 'required',
                    ],
                    'empty_option'     => 'Please select',
                    'object_manager'   => $this->entityManager,
                    'target_class'     => TranslationEntity::class,
                    'label_generator'  => function ($entity) {
                        /** @var TranslationEntity $entity */
                        return ' ' . $entity->getTranslationKey();
                    },
                    'find_method'      => [
                        'name' => 'forFaqCategorySlug',
                    ],
                ],
                'attributes' => [
                    'id'       => 'slug',
                    'required' => 'required',
                    'style'    => 'width:100%',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Text::class,
                'name'       => 'title',
                'options'    => [
                    'label' => 'Title',
                ],
                'attributes' => [
                    'id'    => 'title',
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Text::class,
                'name'       => 'description',
                'options'    => [
                    'label' => 'Description',
                ],
                'attributes' => [
                    'id'    => 'description',
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Text::class,
                'name'       => 'headline',
                'options'    => [
                    'label' => 'Headline',
                ],
                'attributes' => [
                    'id'    => 'headline',
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Textarea::class,
                'name'       => 'teaser',
                'options'    => [
                    'label' => 'Teaser',
                ],
                'attributes' => [
                    'id'    => 'teaser',
                    'class' => 'form-control ckeditor',
                ],
            ]
        );

        $this->add(
            [
                'type'    => Element\Csrf::class,
                'name'    => 'csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => 600,
                    ],
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'form-submit',
                'attributes' => [
                    'value' => 'Save',
                    'id'    => 'submit',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }

    public function addInputFilter(): void
    {
        $inputFilter = new InputFilter();

        $inputFilter->add(
            [
                'name'       => 'name',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'slug',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\ToInt::class],
                ],
                'validators' => [
                    ['name' => Validator\Digits::class],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'title',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\ToNull::class],
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StripNewlines::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 40,
                            'max' => 70,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'description',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\ToNull::class],
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StripNewlines::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 130,
                            'max' => 160,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'headline',
                'required'   => false,
                'filters'    => [
                    ['name' => Filter\ToNull::class],
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StripNewlines::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 40,
                            'max' => 70,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'     => 'teaser',
                'required' => false,
                'filters'  => [
                    ['name' => Filter\ToNull::class],
                    ['name' => Filter\StringTrim::class],
                ],
            ]
        );

        $this->setInputFilter($inputFilter);
    }
}
