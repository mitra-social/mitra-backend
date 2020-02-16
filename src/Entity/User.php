<?php

declare(strict_types=1);

namespace Mitra\Entity;

final class User
{

    /**
     * @var string
     */
    private $preferredUsername;

    /**
     * @var string
     */
    private $email;

    /**
     * @param string $preferredUsername
     * @param string $email
     */
    public function __construct(string $preferredUsername, string $email)
    {
        $this->preferredUsername = $preferredUsername;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPreferredUsername(): string
    {
        return $this->preferredUsername;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
