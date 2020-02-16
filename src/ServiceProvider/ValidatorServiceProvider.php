<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use Mitra\Mapping\Validation\NestedDtoValidationMapping;
use Mitra\Mapping\Validation\UserDtoValidationMapping;
use Mitra\Validator\Symfony\ClassMapLoader;
use Mitra\Validator\SymfonyValidator;
use Mitra\Validator\ValidatorInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\ValidatorBuilder;
use Pimple\Psr11\Container as PsrContainer;

final class ValidatorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[ValidatorInterface::class] = function () {

            $metadataFactory = new LazyLoadingMetadataFactory(
                new ClassMapLoader([
                    UserDto::class => new UserDtoValidationMapping(),
                    NestedDto::class => new NestedDtoValidationMapping(),
                ])
            );

            $symfonyValidator = (new ValidatorBuilder())->setMetadataFactory($metadataFactory)->getValidator();

            return new SymfonyValidator($symfonyValidator);
        };
    }
}
