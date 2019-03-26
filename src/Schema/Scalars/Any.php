<?php

namespace DeInternetJongens\LighthouseUtils\Schema\Scalars;

use GraphQL\Error\Error;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use function PHPSTORM_META\type;

/**
 */
class Any extends ScalarType
{
    /** @var string */
    public $name = 'Any';

    /** @var string */
    public $description = 'Any.';

    /**
     * @inheritDoc
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseValue($value)
    {
        $s = is_string($value) ? 'string' : '';
        $s .= is_int($value) ? ' int ' : '';
        $s .= is_float($value) ? ' float ' : '';
        if ((!is_string($value)) && (!is_int($value)) && (!is_float($value)) && (!($value instanceof NodeList))) {
            throw new Error(sprintf('Input error: Expected valid Any, got: [%s]', $value));
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        $kind = $valueNode->kind;
        if (($kind == 'StringValue')
            || ($kind == 'IntValue')
            || ($kind == 'FloatValue')
            || ($kind == 'ListValue')) {
        } else {
            throw new Error('Query error: Can only parse Any got: ' . $valueNode->kind, [$valueNode]);
        }
        if ($kind == 'ListValue') {
            return $this->parseValue($valueNode->values);
        } else {
            return $this->parseValue($valueNode->value);
        }
    }
}
