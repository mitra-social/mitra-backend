<?php

namespace spec\Mitra\Dto;

use Mitra\Dto\DataToDtoPopulator;
use Mitra\Dto\NestedDto;
use Mitra\Dto\UserDto;
use PhpSpec\ObjectBehavior;

final class DataToDtoPopulatorSpec extends ObjectBehavior
{

    public function let(): void
    {
        $this->beConstructedWith(UserDto::class);
    }

    public function it_creates_dto_from_data(): void
    {
        $returnValue = $this->map('nested', new DataToDtoPopulator(NestedDto::class));
        $returnValue->shouldBe($this);

        $this->populate([
            "preferredUsername" => "TiMESPLiNTER",
            "email" => "pascal.muenst@hsr.ch",
            "nested" => [
                'something' => 'hello',
            ]
        ]);
    }
}
