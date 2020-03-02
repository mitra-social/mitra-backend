<?php

namespace spec\Mitra\Dto;

use Mitra\Dto\Request\CreateUserRequestDto;
use PhpSpec\ObjectBehavior;

final class DataToDtoPopulatorSpec extends ObjectBehavior
{

    public function let(): void
    {
        $this->beConstructedWith(CreateUserRequestDto::class);
    }

    public function it_creates_dto_from_data(): void
    {
        $this->populate([
            "preferredUsername" => "TiMESPLiNTER",
            "email" => "pascal.muenst@hsr.ch",
        ]);
    }
}
