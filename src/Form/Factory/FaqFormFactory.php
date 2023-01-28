<?php

declare(strict_types=1);

namespace TmiFaq\Form\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TmiFaq\Form\FaqForm;

class FaqFormFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FaqForm
    {
        return new FaqForm($container->get(EntityManager::class));
    }
}
