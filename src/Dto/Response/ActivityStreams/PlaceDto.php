<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class PlaceDto extends ObjectDto
{
    public $type = 'Place';

    public $accuracy;

    public $altitude;

    public $latitude;

    public $longitude;

    public $radius;

    public $units;
}
