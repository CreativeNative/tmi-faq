<?php

declare(strict_types=1);

namespace TmiFaq\Form\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use TmiFaq\Form\FaqCategoryForm;

class FaqCategoryFormFactory
{
    public function __invoke(ContainerInterface $container): FaqCategoryForm
    {
        return new FaqCategoryForm($container->get(EntityManager::class));
    }
}
