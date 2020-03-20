<?php

declare(strict_types=1);

namespace Mitra\Dto;

use Mitra\Serialization\Decode\DecoderInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestToDtoManager
{

    /**
     * @var DataToDtoManager
     */
    private $dataToDtoManager;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(DataToDtoManager $dataToDtoManager, DecoderInterface $decoder)
    {
        $this->dataToDtoManager = $dataToDtoManager;
        $this->decoder = $decoder;
    }

    public function fromRequest(ServerRequestInterface $request, string $dtoClass): object
    {
        $decodedBody = $this->decoder->decode((string) $request->getBody(), $request->getAttribute('contentType'));

        return $this->dataToDtoManager->populate($dtoClass, $decodedBody);
    }
}
