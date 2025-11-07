<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ArrayGetKeyValueTest extends TestCase 
{
    public function testArrayGetKeyValue() 
    {
        $path = "app.base-url";
        $array = [
            "app" =>
            [
                "base-url" => "www.example.com"
            ]
        ];
        $delimiter = ".";
        $result = array_get_key_value($path, $array, $delimiter);
        $expectedResult = "www.example.com";
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeyValueNested() 
    {
        $path = "app.nested";
        $array = [
            "app" =>
            [
                "base-url" => "www.example.com",
                "nested" => "base-url",
            ]
        ];
        $delimiter = ".";
        $result = array_get_key_value($path, $array, $delimiter);
        $expectedResult = "base-url";
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeyValueNotFound() 
    {
        $this->expectExceptionMessage("Key 'not-found' not found");
        
        $path = "app.not-found";
        $array = [
            "app" =>
            [
                "base-url" => "www.example.com",
                "nested" => "base-url",
            ]
        ];
        $delimiter = ".";
        $result = array_get_key_value($path, $array, $delimiter, true);
    }
}