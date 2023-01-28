<?php

declare(strict_types=1);

namespace TmiFaq\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TmiFaq\Controller\FaqCategoryBackController as Controller;
use TmiFaq\Form\FaqCategoryForm;

class FaqCategoryBackControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Controller
    {
        return new Controller(
            $container->get(EntityManager::class),
            $container->get('FormElementManager')->get(FaqCategoryForm::class)
        );
    }
}
