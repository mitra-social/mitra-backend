<?php

namespace Mitra\Dto;

final class UserDto
{

    /**
     * @var string|null
     */
    public $id;

    /**
     * @var string
     */
    public $preferredUsername;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var NestedDto;
     */
    public $nested;
}
