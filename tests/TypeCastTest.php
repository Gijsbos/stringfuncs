<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TypeCastTest extends TestCase 
{
    public function testTypeCastString() 
    {
        $input = "string";
        $result = typecast($input);
        $expectedResult = "string";
        $this->assertEquals($expectedResult, $result);
    }

    public function testTypeCastInteger() 
    {
        $input = "1";
        $result = typecast($input);
        $expectedResult = 1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testTypeCastFloat() 
    {
        $input = "1.1";
        $result = typecast($input);
        $expectedResult = 1.1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testTypeCastBool() 
    {
        $input = true;
        $result = typecast($input);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }
}