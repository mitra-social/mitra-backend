<?php

declare(strict_types=1);

namespace Mitra\ServiceProvider;

use Mitra\Authentication\TokenProvider;
use Mitra\Repository\InternalUserRepository;
use Mitra\Security\PasswordHasher;
use Mitra\Security\PasswordHasherInterface;
use Mitra\Security\PasswordVerifier;
use Mitra\Security\PasswordVerifierInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class AuthenticationServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $container[TokenProvider::class] = static function (Container $container): TokenProvider {
            return new TokenProvider(
                $container[InternalUserRepository::class],
                $container[PasswordVerifierInterface::class],
                $container['jwt.secret']
            );
        };

        $container[PasswordVerifierInterface::class] = static function (): PasswordVerifierInterface {
            return new PasswordVerifier();
        };

        $container[PasswordHasherInterface::class] = static function (): PasswordHasherInterface {
            return new PasswordHasher(PASSWORD_DEFAULT);
        };
    }
}
