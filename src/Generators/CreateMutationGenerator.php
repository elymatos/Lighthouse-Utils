<?php

namespace DeInternetJongens\LighthouseUtils\Generators;

use DeInternetJongens\LighthouseUtils\Generators\Classes\MutationWithInput;
use GraphQL\Type\Definition\Type;

class CreateMutationGenerator
{
    /**
     * Generates a GraphQL Mutation to create a record
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return MutationWithInput
     */
    public static function generate(string $typeName, array $typeFields): MutationWithInput
    {
        $mutation = '    create' . $typeName;

        $arguments = RelationArgumentGenerator::generate($typeFields);
        $inputTypeName = sprintf('create%sInput', ucfirst($typeName));
        $arguments[] = sprintf('input: %s!', $inputTypeName);
        $inputType = InputTypeArgumentGenerator::generate($inputTypeName, $typeFields);

        if (count($arguments) < 1) {
            return new MutationWithInput('', '');
        }

        $mutation .= sprintf('(%s)', implode(', ', $arguments));
        $mutation .= sprintf(': %1$s @create(model: "%1$s")', $typeName);

        return new MutationWithInput($mutation, $inputType);
    }
}
