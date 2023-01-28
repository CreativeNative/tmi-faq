<?php

declare(strict_types=1);

namespace TmiFaq\Controller\Factory;

use TmiFaq\Controller\FaqCategoryBackController;
use TmiFaq\Form\FaqCategoryForm;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FaqCategoryBackControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):
    FaqCategoryBackController
    {
        return new FaqCategoryBackController(
            $container->get(EntityManager::class),
            $container->get('FormElementManager')->get(FaqCategoryForm::class)
        );
    }
}
