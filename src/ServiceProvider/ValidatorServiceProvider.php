<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Validator\Symfony\ClassMapLoader;
use Mitra\Validator\SymfonyValidator;
use Mitra\Validator\ValidatorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\ValidatorBuilder;

final class ValidatorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[ValidatorInterface::class] = function ($container): ValidatorInterface {
            $metadataFactory = new LazyLoadingMetadataFactory(
                new ClassMapLoader($container['mappings']['validation'])
            );

            $symfonyValidator = (new ValidatorBuilder())->setMetadataFactory($metadataFactory)->getValidator();

            return new SymfonyValidator($symfonyValidator);
        };
    }
}
