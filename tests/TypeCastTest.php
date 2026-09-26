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

    public function testTypeCastIntegralFloatString() 
    {
        $this->assertSame(1, typecast("1.0"));
        $this->assertSame(1000, typecast("1e3"));
        $this->assertSame(-5, typecast("-5"));
    }

    public function testTypeCastLargeInteger() 
    {
        $this->assertSame(PHP_INT_MAX, typecast((string) PHP_INT_MAX));
        $this->assertSame(9223372036854775808.0, typecast("9223372036854775808"));
    }

    public function testTypeCastNumericTypes() 
    {
        $this->assertSame(5, typecast(5));
        $this->assertSame(1.5, typecast(1.5));
        $this->assertSame(null, typecast(null));
    }
}