<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ViolationListDto;
use Mitra\Serialization\Encode\EncoderInterface;
use Mitra\Validator\ViolationListInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ResponseFactory implements ResponseFactoryInterface, PsrResponseFactoryInterface
{

    /**
     * @var PsrResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var EntityToDtoMapper
     */
    private $entityToDtoMapper;

    /**
     * @param PsrResponseFactoryInterface $responseFactory
     * @param EncoderInterface $encoder
     * @param EntityToDtoMapper $entityToDtoMapper
     */
    public function __construct(
        PsrResponseFactoryInterface $responseFactory,
        EncoderInterface $encoder,
        EntityToDtoMapper $entityToDtoMapper
    ) {
        $this->responseFactory = $responseFactory;
        $this->encoder = $encoder;
        $this->entityToDtoMapper = $entityToDtoMapper;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * @param ViolationListInterface $violationList
     * @param ServerRequestInterface $request
     * @param string $mimeType
     * @return ResponseInterface
     */
    public function createResponseFromViolationList(
        ViolationListInterface $violationList,
        ServerRequestInterface $request,
        string $mimeType
    ): ResponseInterface {
        return $this->createResponseFromEntity($violationList, ViolationListDto::class, $request, $mimeType, 400);
    }

    public function createResponseFromEntity(
        object $entity,
        string $dtoClass,
        ServerRequestInterface $request,
        string $mimeType,
        int $code = 200
    ): ResponseInterface {
        $dto = $this->entityToDtoMapper->map($entity, $dtoClass);
        $response = $this->responseFactory->createResponse($code)
            ->withHeader('Content-Type', $mimeType);

        $response->getBody()->write($this->encoder->encode($dto, $mimeType));

        return $response;
    }
}
