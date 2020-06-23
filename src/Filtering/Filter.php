<?php

declare(strict_types=1);

namespace Mitra\Filtering;

final class Filter
{
    /**
     * @var array<array<int, string>>
     */
    private $tokens;

    /**
     * @var array<string, string|int>
     */
    private $filterQueryStr;

    /**
     * @param array<array<int, string>> $tokens
     * @param string $filterQueryStr
     */
    public function __construct(string $filterQueryStr, array $tokens)
    {
        $this->tokens = $tokens;
        $this->filterQueryStr = $filterQueryStr;
    }

    /**
     * @return array<array<int, string>> $tokenStream
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return string
     */
    public function getFilterQueryStr(): string
    {
        return $this->filterQueryStr;
    }
}
