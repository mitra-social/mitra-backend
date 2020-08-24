<?php

declare(strict_types=1);

namespace Mitra\Security;

final class PasswordVerifier implements PasswordVerifierInterface
{
    public function verify(string $plaintextPassword, string $hashedPassword): bool
    {
        return password_verify($plaintextPassword, $hashedPassword);
    }
}
