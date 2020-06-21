<?php

declare(strict_types=1);

namespace Mitra\Filtering;

final class SqlFilterFactoryInterface implements FilterFactoryInterface
{
    public function create(FilterTokenizer $filterTokenizer): Filter
    {
        $str = '';
        $params = [];
        $properties = [];
        $comparatorRight = false;
        $filterId = uniqid();

        foreach ($filterTokenizer->getTokenStream() as $token) {
            $tokenType = $token[0];

            if (FilterTokenizer::TOKEN_GROUP_START === $tokenType) {
                $str .= '(';
            } elseif (FilterTokenizer::TOKEN_GROUP_END === $tokenType) {
                $str .= ')';
            } elseif (FilterTokenizer::TOKEN_TEXT === $tokenType) {
                if (true === $comparatorRight) {
                    $paramName = sprintf("filter_%s_param_%d", $filterId, count($params));
                    $str .= sprintf(':%s', $paramName);
                    $params[$paramName] = $token[1];
                    $comparatorRight = false;
                } else {
                    $propertyName = $token[1];
                    $str .= sprintf('{{ %s }}', $propertyName);
                    $properties[$propertyName] = $propertyName;
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

        return new Filter($str, $properties, $params);
    }
}
