<?php

declare(strict_types=1);

namespace Mitra\Filtering;

final class Filter
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var array<string, string|int>
     */
    private $parameters;

    /**
     * @var array<string, string>
     */
    private $properties;

    /**
     * @param string $template
     * @param array<string, string> $properties
     * @param array<string, string|int> $parameters
     */
    public function __construct(string $template, array $properties, array $parameters)
    {
        $this->template = $template;
        $this->parameters = $parameters;
        $this->properties = $properties;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array<string, string|int>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string, string>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array<string, string> $resolvedProperties
     * @return string
     */
    public function render(array $resolvedProperties): string
    {
        return preg_replace_callback('/\{\{ (.+?) \}\}/', function ($m) use ($resolvedProperties): string {
            $propertyName = $m[1];

            if (!array_key_exists($propertyName, $resolvedProperties)) {
                throw new \InvalidArgumentException(sprintf('Property `%s` has not been resolved', $propertyName));
            }

            return $resolvedProperties[$propertyName];
        }, $this->template);
    }
}
