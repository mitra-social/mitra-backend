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

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
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
