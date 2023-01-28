<?php

declare(strict_types=1);

namespace TmiFaq\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TmiFaq\Controller\FaqBackController;
use TmiFaq\Form\FaqForm;

class FaqBackControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FaqBackController
    {
        return new FaqBackController(
            $container->get(EntityManager::class),
            $container->get(Translator::class),
            $container->get('FormElementManager')->get(FaqForm::class)
        );
    }
}
