<?php
namespace Mrubiosan\Navigator\Tests;

use Mrubiosan\LooseSchemaNavigator\Navigator;
use PHPUnit\Framework\TestCase;

class NavigatorTest extends TestCase
{
    public function testItTraverses()
    {
        $data = [
            'foo' => [
                'bar' => (object) [
                    'baz' => 'hello'
                ]
            ]
        ];

        $testSubject = new Navigator($data);
        $this->assertEquals('hello', $testSubject->foo->bar->baz->string());
    }

    public function testItJsonDecodesNodesWhenTraversing()
    {
        $data = [
            'foo' => [
                'bar' => json_encode(['baz' => 'hello'])
            ]
        ];

        $testSubject = new Navigator($data);
        $this->assertEquals('hello', $testSubject->foo->bar->baz->string());

        $badData = [
            'foo' => [
                'bar' => 'notjson'
            ]
        ];

        $testSubject = new Navigator($badData);
        $this->assertEquals('', $testSubject->foo->bar->baz->string());
    }

    public function testObjectGivenDefault()
    {
        $testSubject = new Navigator([]);
        $defaultObject = new \stdClass();
        $this->assertSame($defaultObject, $testSubject->foo->object($defaultObject));
    }

    /**
     * @dataProvider typeProvider
     */
    public function testTypes($method, $value, $expected)
    {
        $data = [
            'foo' => $value
        ];

        $testSubject = new Navigator($data);
        $result = $testSubject->foo->$method();
        if (is_object($expected)) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public function typeProvider()
    {
        return [
            ['string', 'hello', 'hello'],
            ['string', 123, '123'],
            ['string', ['not_a_string','but_an_array'], ''],
            ['string', null, ''],
            ['int', '123', 123],
            ['int', 'notnumeric', 0],
            ['int', new \stdClass(), 0],
            ['int', true, 1],
            ['float', '456.123', 456.123],
            ['float', 456, 456.0],
            ['float', 'oops', 0.0],
            ['float', [], 0.0],
            ['bool', '1', true],
            ['bool', 'true', true],
            ['bool', true, true],
            ['bool', '0', false],
            ['bool', '123', false],
            ['bool', null, false],
            ['dateTime', 123, new \DateTime("@123")],
            ['dateTime', '1985-12-24', new \DateTime("1985-12-24")],
            ['dateTime', 'blergh', null],
            ['dateTime', [], null],
            ['array', [1, 2, 3], [1, 2, 3]],
            ['array', ['a' => 1], ['a' => 1]],
            ['array', (object) [1, 2, 3], [1, 2, 3]],
            ['array', 'a string', []],
            ['array', '["a json array string"]', ['a json array string']],
            ['array', '{"foo":"a json object string"}', ['foo' => 'a json object string']],
            ['object', (object) [1, 2, 3], (object) [1, 2, 3]],
            ['object', ['a' => 1], (object) ['a' => 1]],
            ['object', 'a string', new \stdClass()],
            ['object', '{"foo":"a json object string"}', (object) ['foo' => 'a json object string']],
            ['object', '["a json array string"]', (object) ['a json array string']],
        ];
    }

    public function testDefaultIfEmpty()
    {
        $data = [
            'foo' => [
                'bar' => '0'
            ]
        ];

        $testSubject = new Navigator($data);
        $this->assertEquals('myDefault', $testSubject->foo->bar->defaultIfEmpty()->string('myDefault'));
    }

    public function testNonExistingNodes()
    {
        $data = [
            'foo' => 123,
            'bar' => 456
        ];
        $testSubject = new Navigator($data);

        $this->assertEquals(0, $testSubject->baz->bar->int());
    }
}

