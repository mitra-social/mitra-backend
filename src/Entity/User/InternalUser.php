<?php

declare(strict_types=1);

namespace Mitra\Entity\User;

use Mitra\Entity\TimestampableInterface;
use Mitra\Entity\TimestampableTrait;

class InternalUser extends AbstractUser implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $hashedPassword;

    /**
     * @var string|null
     */
    private $plaintextPassword;

    /**
     * @var string|null
     */
    private $privateKey;

    public function __construct(string $id, string $username, string $email)
    {
        parent::__construct($id);

        $this->email = $email;
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getHashedPassword(): ?string
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function getPlaintextPassword(): ?string
    {
        return $this->plaintextPassword;
    }

    public function setPlaintextPassword(?string $plaintextPassword): void
    {
        $this->plaintextPassword = $plaintextPassword;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function setKeyPair(string $publicKey, string $privateKey): void
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function clearKeyPair(): void
    {
        $this->publicKey = null;
        $this->privateKey = null;
    }

    public function __toString()
    {
        return sprintf(
            'id:%s, username:%s, email:%s',
            $this->id,
            $this->username,
            $this->email,
        );
    }
}
