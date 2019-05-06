<?php


namespace xobotyi\beansclient\Serializer;


use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    const TEST_DATA = [
        true,
        false,
        null,
        123,
        "Hello world",
        [1, 2, 3],
        [
            'foo' => 'bar',
            'baz' => 'bux',
        ],
    ];

    public
    function testSerialize() {
        $serializer = new JsonSerializer();

        foreach (self::TEST_DATA as $data) {
            $this->assertEquals(json_encode($data), $serializer->serialize($data));
        }
    }

    public
    function testUnserialize() {
        $serializer = new JsonSerializer();

        foreach (self::TEST_DATA as $data) {
            $this->assertEquals($data, $serializer->unserialize(json_encode($data)));
        }
    }
}