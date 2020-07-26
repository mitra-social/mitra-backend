<?php

declare(strict_types=1);

namespace Mitra\Filtering;

use Doctrine\ORM\QueryBuilder;

final class SqlFilterRenderer implements FilterRendererInterface
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var \Closure
     */
    private $propertyResolver;

    public function __construct(QueryBuilder $queryBuilder, \Closure $propertyResolver)
    {
        $this->queryBuilder = $queryBuilder;
        $this->propertyResolver = $propertyResolver;
    }

    public function apply(Filter $filter): void
    {
        $str = '';
        $paramCount = 0;
        $comparatorRight = false;
        $filterId = uniqid();

        foreach ($filter->getTokens() as $token) {
            $tokenType = $token[0];

            if (FilterTokenizer::TOKEN_GROUP_START === $tokenType) {
                $str .= '(';
            } elseif (FilterTokenizer::TOKEN_GROUP_END === $tokenType) {
                $str .= ')';
            } elseif (FilterTokenizer::TOKEN_TEXT === $tokenType) {
                if (true === $comparatorRight) {
                    $paramName = sprintf("filter_%s_param_%d", $filterId, $paramCount++);
                    $str .= sprintf(':%s', $paramName);
                    $this->queryBuilder->setParameter($paramName, $token[1]);
                    $comparatorRight = false;
                } else {
                    $propertyName = $token[1];

                    $str .= ($this->propertyResolver)($propertyName);
                }
            } elseif (FilterTokenizer::TOKEN_OPERATOR_AND === $tokenType) {
                $str .= ' AND ';
            } elseif (FilterTokenizer::TOKEN_OPERATOR_OR === $tokenType) {
                $str .= ' OR ';
            } elseif (FilterTokenizer::TOKEN_COMPARATOR_EQ === $tokenType) {
                $str .= ' = ';
                $comparatorRight = true;
            } elseif (FilterTokenizer::TOKEN_COMPARATOR_NEQ === $tokenType) {
                $str .= ' != ';
                $comparatorRight = true;
            }
        }

        $this->queryBuilder->andWhere($str);
    }
}
