<?php

declare(strict_types=1);

namespace TmiFaq\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Validator\NoObjectExists;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use TmiFaq\Entity\FaqCategoryEntity;
use TmiFaq\Form\FaqCategoryForm;
use TmiFaq\Repository\FaqCategoryRepository;

class FaqCategoryBackController extends AbstractActionController
{
    private EntityManager $entityManager;

    private FaqCategoryForm $categoryForm;

    public function __construct(
        EntityManager $entityManager,
        FaqCategoryForm $categoryForm
    ) {
        $this->entityManager = $entityManager;
        $this->categoryForm  = $categoryForm;
    }

    public function indexAction(): ViewModel
    {
        /** @var FaqCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(FaqCategoryEntity::class);
        $entity     = $repository->findAllCategories();

        return new ViewModel(['category' => $entity]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(): Response|ViewModel
    {
        $form = $this->categoryForm;

        $entity = new FaqCategoryEntity();

        /** Bind entity to form */
        $form->bind($entity);

        /** @var Request $request */
        $request = $this->getRequest();

        $message = [];

        if ($request->isPost()) {
            $form->setData($request->getPost()->toArray());

            $validator = new NoObjectExists(
                [
                    'object_repository' => $this->entityManager->getRepository(
                        FaqCategoryEntity::class
                    ),
                    'fields'            => ['name'],
                    'messages'          => [
                        NoObjectExists::ERROR_OBJECT_FOUND => 'This input already exists.',
                    ],
                ]
            );
            $form->getInputFilter()->get('name')->getValidatorChain()->attach($validator);

            if ($form->isValid()) {
                /**
                 * Get validated entity
                 *
                 * @var FaqCategoryEntity $validatedEntity
                 */
                $validatedEntity = $form->getData();

                $this->entityManager->persist($validatedEntity);
                $this->entityManager->flush();

                return $this->redirect()->toRoute('faq-back/category/edit', [
                    'id'     => $validatedEntity->getId(),
                    'locale' => 'de_DE',
                ]);
            }

            $message = [
                'message' => 'message_persistens_incomplete',
                'class'   => 'alert-danger',
            ];
        }

        return new ViewModel(
            [
                'form'    => $form,
                'message' => $message,
            ]
        );
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(): Response|ViewModel
    {
        $entityId = (int) $this->params()->fromRoute('id');
        $locale   = (string) $this->params()->fromRoute('locale', 'de_DE');

        if ($entityId <= 0) {
            return $this->redirect()->toRoute('faq-back/category');
        }

        /** @var FaqCategoryRepository $repository */
        $repository = $this->entityManager->getRepository(FaqCategoryEntity::class);

        $entity = $repository->findByIdandLocale($entityId, $locale);

        if ($entity === null) {
            return $this->redirect()->toRoute('faq-back/category');
        }

        $entity->setTranslatableLocale($locale);
        $this->entityManager->refresh($entity);

        $form = $this->categoryForm;

        $form->bind($entity);

        /** @var Request $request */
        $request = $this->getRequest();

        $message = [];

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $form->setData($data);
            $message = [
                'message' => 'message_persistens_incomplete',
                'class'   => 'alert-danger',
            ];
            if ($form->isValid()) {
                /** @var FaqCategoryEntity $validatedEntity */
                $validatedEntity = $form->getData();

                $this->entityManager->persist($validatedEntity);
                $this->entityManager->flush();

                $message = [
                    'message' => 'message_persistens_complete',
                    'class'   => 'alert-success',
                ];
            }
        }

        return new ViewModel(
            [
                'locale'  => $locale,
                'form'    => $form,
                'entity'  => $entity,
                'message' => $message,
            ]
        );
    }
}
