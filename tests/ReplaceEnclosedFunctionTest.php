<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplaceEnclosedFunctionTest extends TestCase 
{
    public function testReplaceEnclosedFunction()
    {
        $string = "this is (is a) test";
        $result = \replace_enclosed_function("(", ")", $string, function($match){
            return str_replace("is", "was", $match);
        });
        $expectedResult = "this is (was a) test";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedFunction2()
    {
        $string = "this is (is a) test (and more is) to be tested";
        $result = \replace_enclosed_function("(", ")", $string, function($match){
            return str_replace("is", "was", $match);
        });
        $expectedResult = "this is (was a) test (and more was) to be tested";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedFunctionIncludeOpenClose()
    {
        $string = "this is (is a) test (and more is) to be tested";
        $result = \replace_enclosed_function("(", ")", $string, function($match)
        {
            return str_replace("(is a)", "(was a)", $match);
        }, true);
        $expectedResult = "this is (was a) test (and more is) to be tested";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedFunctionMultiCharacterOpenClose()
    {
        $string = "enclose {{here is a value}} as a result";
        $result = \replace_enclosed_function("{{", "}}", $string, function($match)
        {
            return str_replace("is a", "was a", $match);
        });
        $expectedResult = "enclose {{here was a value}} as a result";
        $this->assertEquals($expectedResult, $result);
    }
}