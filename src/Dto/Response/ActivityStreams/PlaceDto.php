<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

final class PlaceDto extends ObjectDto
{
    /**
     * @var string
     */
    public $type = 'Place';

    /**
     * Indicates the accuracy of position coordinates on a Place objects. Expressed in properties of percentage.
     * e.g. "94.0" means "94.0% accurate".
     * Range: xsd:float [>= 0.0f, <= 100.0f]
     * @var null|float
     */
    public $accuracy;

    /**
     * Indicates the altitude of a place. The measurement units is indicated using the units property. If units is not
     * specified, the default is assumed to be "m" indicating meters.
     * @var null|float
     */
    public $altitude;

    /**
     * The latitude of a place
     * @var null|float
     */
    public $latitude;

    /**
     * The longitude of a place
     * @var null|float
     */
    public $longitude;

    /**
     * The radius from the given latitude and longitude for a Place. The units is expressed by the units property.
     * If units is not specified, the default is assumed to be "m" indicating "meters".
     * Range: xsd:float [>= 0.0f]
     * @var null|float
     */
    public $radius;

    /**
     * Specifies the measurement units for the radius and altitude properties on a Place object.
     * If not specified, the default is assumed to be "m" for "meters".
     * Range: "cm" | "feet" | "inches" | "km" | "m" | "miles" | xsd:anyURI
     * @var null|string
     */
    public $units;
}
