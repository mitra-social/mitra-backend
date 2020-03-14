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
        if ('' === $mimeType = $request->getHeaderLine('Content-Type')) {
            throw new \RuntimeException('Missing `Content-Type` header');
        }

        $decodedBody = $this->decoder->decode((string) $request->getBody(), $mimeType);

        return $this->dataToDtoManager->populate($dtoClass, $decodedBody);
    }
}
