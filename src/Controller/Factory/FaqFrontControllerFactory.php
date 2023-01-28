<?php

declare(strict_types=1);

namespace TmiFaq\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TmiFaq\Controller\FaqFrontController;

class FaqFrontControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FaqFrontController
    {
        return new FaqFrontController(
            $container->get(EntityManager::class),
            $container->get(Translator::class),
            $container->get(PhpRenderer::class)
        );
    }
}
