<?php


namespace xobotyi\beansclient;

use Exception;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public
    function testYamlParse(): void {
        self::assertEquals(null, Response::YamlParse(''));
        self::assertEquals(null, Response::YamlParse('         '));

        $str = "---";

        self::assertEquals([], Response::YamlParse($str));
        self::assertEquals([], Response::YamlParse($str, true));

        $str = "---\r\n- a:b\r\na:b";

        self::assertEquals(['a:b', 'a:b'], Response::YamlParse($str));
        self::assertEquals(['a:b', 'a' => 'b'], Response::YamlParse($str, true));
    }

    public
    function testYamlParseException(): void {
        $str = "---\r\n-  ";

        $this->expectException(Exception::class);
        Response::YamlParse($str, true);
    }
}