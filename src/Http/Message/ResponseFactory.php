<?php

declare(strict_types=1);

namespace Mitra\Http\Message;

use Mitra\ApiProblem\ApiProblemInterface;
use Mitra\Dto\EntityToDtoMapper;
use Mitra\Dto\Response\ViolationListDto;
use Mitra\Dto\Response\ApiProblemDto;
use Mitra\Normalization\NormalizerInterface;
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
     * @var NormalizerInterface
     */
    private $normalizer;

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
     * @param NormalizerInterface $normalizer
     * @param EncoderInterface $encoder
     * @param EntityToDtoMapper $entityToDtoMapper
     */
    public function __construct(
        PsrResponseFactoryInterface $responseFactory,
        NormalizerInterface $normalizer,
        EncoderInterface $encoder,
        EntityToDtoMapper $entityToDtoMapper
    ) {
        $this->responseFactory = $responseFactory;
        $this->normalizer = $normalizer;
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

        return $this->createResponseFromDto($dto, $request, $mimeType, $code);
    }

    public function createResponseFromDto(
        object $dto,
        ServerRequestInterface $request,
        string $mimeType,
        int $code = 200
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse($code)
            ->withHeader('Content-Type', $mimeType);

        $response->getBody()->write($this->encoder->encode($this->normalizer->normalize($dto), $mimeType));

        return $response;
    }

    public function createResponseFromApiProblem(
        ApiProblemInterface $apiProblem,
        ServerRequestInterface $request,
        string $mimeType
    ): ResponseInterface {
        return $this->createResponseFromEntity(
            $apiProblem,
            ApiProblemDto::class,
            $request,
            $mimeType,
            $apiProblem->getStatus()
        );
    }
}
