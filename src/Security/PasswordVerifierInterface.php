<?php

declare(strict_types=1);

namespace Mitra\Security;

interface PasswordVerifierInterface
{
    public function verify(string $plaintextPassword, string $hashedPassword): bool;
}
