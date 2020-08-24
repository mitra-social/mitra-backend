<?php

declare(strict_types=1);

namespace Mitra\Security;

interface PasswordHasherInterface
{
    public function hash(string $plaintextPassword): string;
}
