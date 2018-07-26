<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;

class InputTypeArgumentGenerator
{
    /** @var array */
    private static $supportedGraphQLTypes = [
        StringType::class,
        IntType::class,
        FloatType::class,
    ];

    /**
     * Generates a GraphQL Input Type
     * More information:
     * https://lighthouse-php.netlify.com/docs/schema.html#input-types
     *
     * @param Type[] $typeFields
     * @return array
     */
    public static function generate(array $typeFields): array
    {
        $arguments = [];
        foreach ($typeFields as $fieldName => $field) {
            $className = get_class($field);
            if (! in_array($className, self::$supportedGraphQLTypes)
                || str_contains($fieldName, '_at')
            ) {
                continue;
            };

            $required = isset($field->config['generator-required']) && $field->config['generator-required'] === true ? '!' : '';
            $arguments[] = sprintf('%s: %s%s', $fieldName, $field->name, $required);
        }

        return $arguments;
    }
}
