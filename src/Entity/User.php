<?php

declare(strict_types=1);

namespace Mitra\Entity;

final class User implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $preferredUsername;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $hashedPassword;

    /**
     * @var string|null
     */
    private $plaintextPassword;

    public function __construct(string $id, string $preferredUsername, string $email)
    {
        $this->id = $id;
        $this->preferredUsername = $preferredUsername;
        $this->email = $email;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPreferredUsername(): string
    {
        return $this->preferredUsername;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getHashedPassword(): ?string
    {
        return $this->hashedPassword;
    }

    /**
     * @param string $hashedPassword
     */
    public function setHashedPassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    /**
     * @return string|null
     */
    public function getPlaintextPassword(): ?string
    {
        return $this->plaintextPassword;
    }

    public function setPlaintextPassword(?string $plaintextPassword): void
    {
        $this->plaintextPassword = $plaintextPassword;
    }
}
