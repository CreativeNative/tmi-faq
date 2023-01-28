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
use TmiFaq\Entity\FaqEntity;

class FaqForm extends Form
{
    private EntityManager $entityManager;

    public function __construct(
        EntityManager $entityManager
    ) {
        // Define form name
        parent::__construct('faq-form');

        $this->entityManager = $entityManager;

        $this->setHydrator(new DoctrineHydrator($entityManager))
            ->setObject(new FaqEntity());

        // Set POST method for this form
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
                'attributes' => [
                    'id' => 'faq-id',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'category',
                'type'       => ObjectSelect::class,
                'options'    => [
                    'label'            => 'Category',
                    'empty_option'     => 'Please select',
                    'object_manager'   => $this->entityManager,
                    'target_class'     => FaqCategoryEntity::class,
                    'label_attributes' => ['class' => 'required'],
                    'label_generator'  => function ($entity) {
                        /** @var FaqCategoryEntity $entity */
                        return $entity->getName()->getTranslationKey() ?? '';
                    },
                    'find_method'      => [
                        'name' => 'findAllCategoriesForSelect',
                    ],
                ],
                'attributes' => [
                    'class'    => 'form-control',
                    'style'    => 'width:100%',
                    'required' => 'required',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'categories',
                'type'       => ObjectSelect::class,
                'options'    => [
                    'label'           => 'Categories',
                    'empty_option'    => 'Please select',
                    'object_manager'  => $this->entityManager,
                    'target_class'    => FaqCategoryEntity::class,
                    'label_generator' => function ($entity) {
                        /** @var FaqCategoryEntity $entity */
                        return $entity->getName()->getTranslationKey() ?? '';
                    },
                    'find_method'     => [
                        'name' => 'findAllCategoriesForSelect',
                    ],
                ],
                'attributes' => [
                    'id'       => 'categories',
                    'multiple' => 'multiple',
                    'class'    => 'multiple form-control',
                    'style'    => 'width:100%',
                ],
            ]
        );


        $this->add(
            [
                'type'       => Element\Text::class,
                'name'       => 'question',
                'options'    => [
                    'label'            => 'Question',
                    'label_attributes' => ['class' => 'required'],
                ],
                'attributes' => [
                    'id'       => 'question',
                    'class'    => 'form-control',
                    'required' => 'required',
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Text::class,
                'name'       => 'answer',
                'options'    => [
                    'label' => 'Answer',
                ],
                'attributes' => [
                    'id'    => 'answer',
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add([
            'type'    => Element\Csrf::class,
            'name'    => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600,
                ],
            ],
        ]);

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

    private function addInputFilter(): void
    {
        $inputFilter = new InputFilter();

        $inputFilter->add(
            [
                'name'       => 'question',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StringTrim::class],
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StripNewlines::class],
                ],
                'validators' => [
                    ['name' => Validator\NotEmpty::class],
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'min' => 3,
                            'max' => 150,
                        ],
                    ],
                ],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'answer',
                'required'   => true,
                'filters'  => [
                    ['name' => Filter\StringTrim::class],
                ],
            ]
        );

        $this->setInputFilter($inputFilter);
    }
}
