<?php

declare(strict_types=1);

namespace TmiFaq\Controller\Factory;

use TmiFaq\Controller\FaqBackController;
use Laminas\I18n\Translator\Translator;
use TmiFaq\Form\FaqForm;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FaqBackControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FaqController
    {
        return new FaqController(
            $container->get(EntityManager::class),
            $container->get(Translator::class),
            $container->get('FormElementManager')->get(FaqForm::class)
        );
    }
}
