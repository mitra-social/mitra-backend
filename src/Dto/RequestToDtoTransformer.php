<?php

declare(strict_types=1);

namespace Mitra\Dto;

use Mitra\Serialization\Decode\DecoderInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestToDtoTransformer
{

    /**
     * @var DataToDtoTransformer
     */
    private $dataToDtoTransformer;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(DataToDtoTransformer $dataToDtoTransformer, DecoderInterface $decoder)
    {
        $this->dataToDtoTransformer = $dataToDtoTransformer;
        $this->decoder = $decoder;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $dtoClass
     * @return object
     * @throws RequestToDtoTransformerException
     */
    public function fromRequest(ServerRequestInterface $request, string $dtoClass): object
    {
        $decodedBody = $this->decoder->decode((string) $request->getBody(), $request->getAttribute('contentType'));

        if (!is_array($decodedBody)) {
            throw new RequestToDtoTransformerException(sprintf(
                'Decoded body must be of type array, `%s` given',
                gettype($decodedBody)
            ));
        }

        return $this->dataToDtoTransformer->populate($dtoClass, $decodedBody);
    }
}
