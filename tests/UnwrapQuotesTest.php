<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UnwrapQuotesTest extends TestCase 
{
    public function testUnwrapQuotesSingle() 
    {
        $input = "'hi'";
        $result = unwrap_quotes($input);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnwrapQuotesDouble() 
    {
        $input = '"hi"';
        $result = unwrap_quotes($input);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnwrapSingleAlt() 
    {
        $input = '`hi`';
        $result = unwrap_quotes($input);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnwrapQuotesNotQuoted() 
    {
        $this->assertEquals("hello", unwrap_quotes("hello"));
        $this->assertEquals("'hi\"", unwrap_quotes("'hi\""));
    }

    public function testUnwrapQuotesTrims() 
    {
        $this->assertEquals("hi", unwrap_quotes("  'hi' "));
    }
}