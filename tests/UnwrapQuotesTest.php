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
}