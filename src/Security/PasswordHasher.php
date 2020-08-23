<?php

declare(strict_types=1);

namespace Mitra\Security;

final class PasswordHasher implements PasswordHasherInterface
{
    /**
     * @var int|string
     */
    private $algorithm;

    /**
     * @param int|string $algorithm
     */
    public function __construct($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function hash(string $plaintextPassword): string
    {
        $hashedPassword = password_hash($plaintextPassword, $this->algorithm);

        if (false === $hashedPassword) {
            throw new \RuntimeException('Hashing the plaintext password failed');
        }

        return $hashedPassword;
    }
}
