<?php

namespace spec\Mitra\Dto;

use PhpSpec\ObjectBehavior;

final class DtoToDataPopulatorSpec extends ObjectBehavior
{
    public function it_converts_object_to_array(): void
    {
        $dto2 = new \stdClass();
        $dto2->hello = 'world';

        $dto = new \stdClass();
        $dto->foo = 'foo';
        $dto->bar = 'bar';
        $dto->baz = $dto2;

        $this->populate($dto)->shouldReturn([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => [
                'hello' => 'world',
            ],
        ]);
    }
}
