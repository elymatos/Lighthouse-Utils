<?php

namespace DeInternetJongens\LighthouseUtils\Generators\Queries;

use DeInternetJongens\LighthouseUtils\Models\GraphQLSchema;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Date;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\DateTimeTz;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Email;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\FullTextSearch;
use DeInternetJongens\LighthouseUtils\Schema\Scalars\Any;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateTime;

class PaginateAllQueryGenerator
{
    /** @var array */
    private static $supportedGraphQLTypes = [
        IDType::class,
        StringType::class,
        IntType::class,
        FloatType::class,
        Date::class,
        DateTime::class,
        DateTimeTz::class,
        //EnumType::class,
        Email::class,
        FullTextSearch::class,
        Any::class,
    ];

    /** @var array */
    private static $builtinScalarType = [
        IDType::class,
        StringType::class,
        IntType::class,
        FloatType::class,
    ];
    /**
     * Generates GraphQL queries with arguments for each field
     * Returns a query for 'all' and 'paginated', depending on what kind of result you want
     *
     * @param string $typeName
     * @param Type[] $typeFields
     * @return string
     */
    public static function generate(string $typeName, array $typeFields): string
    {
        $arguments = [];
        $inputTypeNames = self::getInputTypeNamesKeyedByDataType();

        foreach ($typeFields as $columnName => $field) {
            $className = get_class($field);

            // We can generate queries for all but Object types, as Object types are relations
            if (!in_array($className, self::$supportedGraphQLTypes)) {
                continue;
            }

            $columnDataType = $field->name;

            if (in_array($className, self::$builtinScalarType)) {
                $inputTypeName = $inputTypeNames['Any'] ?: false;
            } else {
                $inputTypeName = $inputTypeNames[$columnDataType] ?: false;
            }

            if (!$inputTypeName) {
                continue;
            }

            if ($field instanceof FullTextSearch) {
                $arguments[] = sprintf('%s: %s @fulltext', $columnName, $columnDataType);
                continue;
            }

            // Add all our custom directives
            //$arguments[] = sprintf('%s: %s @eq', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_not: %s @not', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_in: [%s] @in', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_not_in: [%s] @not_in', $fieldName, $field->name);

            $arguments[] = sprintf('%s: %s @queryable', $columnName, $inputTypeName);

            if ($field instanceof EnumType) {
                continue;
            }

            //if ($field instanceof StringType) {
            //    $arguments[] = sprintf('%s_contains: %s @contains', $fieldName, $field->name);
            //    $arguments[] = sprintf('%s_not_contains: %s @not_contains', $fieldName, $field->name);
            //    $arguments[] = sprintf('%s_starts_with: %s @starts_with', $fieldName, $field->name);
            //    $arguments[] = sprintf('%s_not_starts_with: %s @not_starts_with', $fieldName, $field->name);
            //    $arguments[] = sprintf('%s_ends_with: %s @not_ends_with', $fieldName, $field->name);
            //
            //    continue;
            //}

            //$arguments[] = sprintf('%s_lt: %s @lt', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_lte: %s @lte', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_gt: %s @gt', $fieldName, $field->name);
            //$arguments[] = sprintf('%s_gte: %s @gte', $fieldName, $field->name);
        }

        if (count($arguments) < 1) {
            return '';
        }

        $queryArguments = sprintf('(%s)', implode(', ', $arguments));
        $allQuery = '';
        if (config('lighthouse-utils.generate_all_queries')) {
            $allQueryName = str_plural(lcfirst($typeName));
            $allQuery = sprintf('    %1$s%2$s: [%3$s]! @all(model: "%3$s")', $allQueryName, $queryArguments, $typeName);
        }

        $paginatedQueryName = str_plural(lcfirst($typeName)) . 'Paginated';
        $paginatedQuery = sprintf('    %1$s%2$s: [%3$s]! @paginate(model: "%3$s")', $paginatedQueryName, $queryArguments, $typeName);

        if (config('lighthouse-utils.authorization')) {
            if (config('lighthouse-utils.generate_all_queries')) {
                $allPermission = sprintf('All%1$s', str_plural($typeName));
                $allQuery .= sprintf(' @can(if: "%1$s", model: "User")', $allPermission);
                GraphQLSchema::register($allQueryName, $typeName, 'query', $allPermission ?? null);
            }

            $paginatePermission = sprintf('paginate%1$s', str_plural($typeName));
            $paginatedQuery .= sprintf(' @can(if: "%1$s", model: "User")', $paginatePermission);
            GraphQLSchema::register($paginatedQueryName, $typeName, 'query', $paginatePermission ?? null);
        }

        return $allQuery . "\r\n" . $paginatedQuery;
    }

    /**
     * @return array
     */
    public static function getInputTypes(): array
    {
        $inputTypeNames = self::getInputTypeNamesKeyedByDataType();
        $scalarNames = self::getScalarNamesByDataType();
        /*
        $inputTypes = [
            'enum Operator {
                MORETHAN @enum(value: ">")
                LESSTHAN @enum(value: "<")
                EQUALS @enum(value: "=")
                NOTEQUALS @enum(value: "!=")
            }',
        ];
        */
        $inputTypes = [
            'enum Operator {
                EQ @enum(value: "eq")
                NEQ @enum(value: "neq")
                GT @enum(value: "gt")
                GTE @enum(value: "gte")
                LT @enum(value: "lt")
                LTE @enum(value: "lte")
                IN @enum(value: "in")
                NOT_IN @enum(value: "notIn")
                CONTAINS @enum(value: "contains")
                NOT_CONTAINS @enum(value: "not_contains")
                STARTS_WITH @enum(value: "starts_with")
                NOT_STARTS_WITH @enum(value: "not_starts_with")
                ENDS_WITH @enum(value: "ends_with")
                NOT_ENDS_WITH @enum(value: "not_ends_with")
                FULLTEXT @enum(value: "fulltext")
            }',
        ];

        foreach ($scalarNames as $dataType => $scalarClassName) {
            $inputTypes[] = sprintf('scalar %s @scalar(class: "%s")', $dataType, $scalarClassName);
        }

        foreach ($inputTypeNames as $dataType => $inputTypeName) {
            $inputTypes[] = sprintf('input %s {operator: Operator! value: %s!}', $inputTypeName, $dataType);
        }

        return $inputTypes;
    }

    /**
     * @return array
     */
    private static function getInputTypeNamesKeyedByDataType(): array
    {
        $names = [];
        foreach (self::$supportedGraphQLTypes as $supportedGraphQLType) {
            $columnDataType = (new $supportedGraphQLType())->name;
            $names[$columnDataType] = sprintf('where%sInput', $columnDataType);;
        }

        return $names;
    }

    /**
     * @return array
     */
    private static function getScalarNamesByDataType(): array
    {
        $names = [];
        foreach (self::$supportedGraphQLTypes as $supportedGraphQLType) {
            $columnDataType = (new $supportedGraphQLType())->name;
            if (!in_array($supportedGraphQLType, self::$builtinScalarType)) {
                $names[$columnDataType] = str_replace('\\','\\\\',$supportedGraphQLType);
            }
        }

        return $names;
    }
}
