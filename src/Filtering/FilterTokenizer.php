<?php

declare(strict_types=1);

namespace Mitra\Filtering;

final class FilterTokenizer
{
    public const TOKEN_TEXT = 'TEXT';

    public const TOKEN_GROUP_START = 'GROUP_START';
    public const TOKEN_GROUP_END = 'GROUP_END';
    public const TOKEN_COMPARATOR_EQ = 'COMPARATOR_EQ';
    public const TOKEN_COMPARATOR_NEQ = 'COMPARATOR_NEQ';
    public const TOKEN_OPERATOR_AND = 'OPERATOR_AND';
    public const TOKEN_OPERATOR_OR = 'OPERATOR_OR';

    private const TOKEN_MAP = [
        '(' => self::TOKEN_GROUP_START,
        ')' => self::TOKEN_GROUP_END,
        '=' => self::TOKEN_COMPARATOR_EQ,
        '!=' => self::TOKEN_COMPARATOR_NEQ,
        ';' => self::TOKEN_OPERATOR_AND,
        ',' => self::TOKEN_OPERATOR_OR,
    ];

    /**
     * @var iterable<array<string>>
     */
    private $tokenStream;

    /**
     * @var string
     */
    private $filterInputStr;

    /**
     * @param string $filterInputStr
     * @param iterable<array<string>> $tokenStream
     */
    private function __construct(string $filterInputStr, iterable $tokenStream)
    {
        $this->filterInputStr = $filterInputStr;
        $this->tokenStream = $tokenStream;
    }

    public static function create(string $filter): self
    {
        return new self($filter, self::tokenize($filter));
    }

    /**
     * @param string $filter
     * @return iterable<array<string>>
     */
    private static function tokenize(string $filter): iterable
    {
        $currentOffset = 0;
        $currentToken = '';

        while (1 === preg_match('/[();,=!]/', $filter, $matches, PREG_OFFSET_CAPTURE, $currentOffset)) {
            $matchedOffset = (int) $matches[0][1];
            $matchedStr = $matches[0][0];

            if (0 === $currentOffset && 0 !== $matchedOffset) {
                yield [
                    self::TOKEN_TEXT,
                    substr($filter, $currentOffset, $matchedOffset),
                ];
            }

            if (!array_key_exists($currentToken . $matchedStr, self::TOKEN_MAP)) {
                yield [
                    self::TOKEN_MAP[$currentToken],
                ];
                $currentToken = '';
            }

            $restLength = $matchedOffset - $currentOffset;

            if ($currentOffset > 0 && $restLength > 0) {
                yield [
                    self::TOKEN_TEXT,
                    substr($filter, $currentOffset, $restLength)
                ];
            }

            $currentOffset = $matchedOffset + strlen($matchedStr);
            $currentToken .= $matchedStr;
        }

        yield [
            self::TOKEN_MAP[$currentToken],
        ];

        if ('' !== $filterEnd = substr($filter, $currentOffset)) {
            yield [
                self::TOKEN_TEXT,
                $filterEnd,
            ];
        }
    }

    /**
     * @return iterable<array<string>>
     */
    public function getTokenStream(): iterable
    {
        return $this->tokenStream;
    }

    public function getFilterInputStr(): string
    {
        return $this->filterInputStr;
    }
}
