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

    public function testReplaceEnclosedFunctionOffset()
    {
        $string = "ab (x) (y)";
        $offsets = [];
        $result = \replace_enclosed_function("(", ")", $string, function($match, $offset) use (&$offsets)
        {
            $offsets[] = $offset;
            return "$match$match";
        });
        $this->assertEquals("ab (xx) (yy)", $result);
        $this->assertEquals([3, 8], $offsets);
    }

    public function testReplaceEnclosedFunctionMultiByteSafe()
    {
        $string = "éé(a) (a)";
        $offsets = [];
        $result = \replace_enclosed_function("(", ")", $string, function($match, $offset) use (&$offsets)
        {
            $offsets[] = $offset;
            return "ü";
        }, false, true);
        $this->assertEquals("éé(ü) (ü)", $result);
        $this->assertEquals([2, 6], $offsets);
    }

    public function testReplaceEnclosedFunctionNoMatch()
    {
        $string = "nothing enclosed";
        $result = \replace_enclosed_function("(", ")", $string, fn($match) => "x");
        $this->assertEquals($string, $result);
    }

    public function testReplaceEnclosedFunctionManyMatches()
    {
        $string = str_repeat("(a) ", 100000);
        $result = \replace_enclosed_function("(", ")", $string, fn($match) => "bb");
        $this->assertEquals(str_repeat("(bb) ", 100000), $result);
    }
}