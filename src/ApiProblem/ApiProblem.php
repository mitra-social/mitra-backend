<?php

declare(strict_types=1);

namespace Mitra\ApiProblem;

use Mitra\Serialization\Encode\ArrayNormalizable;

/**
 * @link https://tools.ietf.org/html/rfc7807
 */
class ApiProblem implements ApiProblemInterface, ArrayNormalizable
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var integer
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $detail;

    /**
     * @var string|null
     */
    protected $instance;

    public function __construct(string $type, string $title, int $status)
    {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     * @return static
     */
    public function withDetail(string $detail): self
    {
        $clone = clone $this;
        $clone->detail = $detail;

        return $clone;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    /**
     * @param string $instance
     * @return static
     */
    public function withInstance(string $instance): self
    {
        $clone = clone $this;
        $clone->instance = $instance;

        return $clone;
    }

    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'title' => $this->title,
        ];

        if (null !== $this->detail) {
            $data['detail'] = $this->detail;
        }

        if (null !== $this->instance) {
            $data['instance'] = $this->instance;
        }

        return $data;
    }
}
