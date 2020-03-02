<?php

declare(strict_types=1);

namespace Mitra\Authentication;

use Doctrine\ORM\EntityRepository;
use Firebase\JWT\JWT;
use Mitra\Entity\User;
use Mitra\Repository\UserRepository;

final class TokenProvider
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var string
     */
    private $secretKey;

    public function __construct(UserRepository $userRepository, string $secretKey)
    {
        $this->userRepository = $userRepository;
        $this->secretKey = $secretKey;
    }

    public function generate(string $username, string $plaintextPassword): string
    {
        $user = $this->userRepository->findOneByPreferredUsername($username);

        if (null === $user) {
            throw new \RuntimeException(sprintf('Could not find user with preferredUsername `%s`', $username));
        }

        if (false === password_verify($plaintextPassword, $user->getHashedPassword())) {
            throw new \RuntimeException('The provided password is invalid');
        }

        $payload = ['userId' => $user->getId()];

        return JWT::encode($payload, $this->secretKey);
    }
}
