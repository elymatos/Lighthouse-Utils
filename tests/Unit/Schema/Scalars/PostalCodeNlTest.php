<?php

namespace DeInternetJongens\LighthouseUtils\Tests\Unit\Schema\Scalars;

use DeInternetJongens\LighthouseUtils\Schema\Scalars\PostalCodeNl;
use GraphQL\Error\Error;
use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;

class PostalCodeNlTest extends TestCase
{
    public function parseValueDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => '8111BS'
            ],
            'Postalcode wrong pattern with space' => [
                'input' => '8111 BS',
                'expected exception' => Error::class,
            ],
            'Postalcode wrong pattern no numbers' => [
                'input' => 'AAAABS',
                'expected exception' => Error::class,
            ],
            'Postalcode wrong pattern no letters' => [
                'input' => '123412',
                'expected exception' => Error::class,
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $expectedException
     * @return void
     * @throws Error
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider parseValueDataProvider
     */
    public function testParseValue(string $input, string $expectedException = '')
    {
        if ($expectedException !== '') {
            $this->expectException($expectedException);
        }

        $result = $this->getScalar()->parseValue($input);

        $this->assertEquals($input, $result);
    }

    /**
     * @return array
     */
    public function serializeDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => '8111BS',
                'expected result' => '8111BS'
            ],
        ];
    }

    /**
     * @return void
     * @throws Error
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @dataProvider serializeDataProvider
     */
    public function testSerialize($input, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->getScalar()->serialize($input));
    }

    /**
     * @return array
     */
    public function parseLiteralDataProvider(): array
    {
        return [
            'Happy flow' => [
                'input' => '8111BS',
                'node class' => StringValueNode::class,
            ],
            'Invalid format' => [
                'input' => '8111 BS',
                'node class' => StringValueNode::class,
                'exception' => Error::class,
            ],
            'Invalid node type' => [
                'input' => '8111BS',
                'node class' => BooleanValueNode::class,
                'exception' => Error::class,
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $nodeClass
     * @param string $exception
     * @return void
     * @throws Error
     * @throws \InvalidArgumentException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @dataProvider parseLiteralDataProvider
     */
    public function testParseLiteral(string $input, string $nodeClass, string $exception = ''): void
    {
        if ($exception !== '') {
            $this->expectException($exception);
        }

        $result = $this->getScalar()->parseLiteral(
            new $nodeClass(
                [
                    'value' => $input,
                    'kind' => 'String!',
                ]
            )
        );

        $expectedResult = $input;

        $this->assertEquals($result, $expectedResult);
    }

    private function getScalar(): PostalCodeNl
    {
        return new PostalCodeNl();
    }
}
