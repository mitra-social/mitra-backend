<?php

declare(strict_types=1);

namespace Mitra\Authentication;

use Firebase\JWT\JWT;
use Mitra\Repository\InternalUserRepository;
use Mitra\Security\PasswordVerifierInterface;

final class TokenProvider
{

    /**
     * @var InternalUserRepository
     */
    private $userRepository;

    /**
     * @var PasswordVerifierInterface
     */
    private $passwordVerifier;

    /**
     * @var string
     */
    private $secretKey;

    public function __construct(
        InternalUserRepository $userRepository,
        PasswordVerifierInterface $passwordVerifier,
        string $secretKey
    ) {
        $this->userRepository = $userRepository;
        $this->passwordVerifier = $passwordVerifier;
        $this->secretKey = $secretKey;
    }

    /**
     * @param string $username
     * @param string $plaintextPassword
     * @return string
     * @throws TokenIssueException
     */
    public function generate(string $username, string $plaintextPassword): string
    {
        $user = $this->userRepository->findByUsername($username);

        if (null === $user) {
            throw new TokenIssueException(sprintf('Could not find user with preferredUsername `%s`', $username));
        }

        if (false === $this->passwordVerifier->verify($plaintextPassword, $user->getHashedPassword())) {
            throw new TokenIssueException('The provided password is invalid');
        }

        $payload = ['userId' => $user->getId()];

        return JWT::encode($payload, $this->secretKey);
    }
}
