<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ExplodeEnclosedTest extends TestCase
{
    public function testExplodeEnclosed()
    {
        $string = "this is (a (par) test) with ( (more) ) enclosed";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array(
            "a (par) test",
            " (more) "
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedOpenNotFound()
    {
        $string = "open parentheses are missing )";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array();
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedCloseNotFound()
    {
        $string = "(close parentheses are missing";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array();
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedNoBalance()
    {
        $string = "(close) (parentheses are missing";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array(
            "close"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedNoBalance1()
    {
        $string = "(close) (parentheses are missing";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array(
            "close"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedNoBalance2()
    {
        $string = "((close) (parentheses are) missing";
        $result = explode_enclosed("(", ")", $string);
        $expectedResult = array();
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedIncludeOpenClose()
    {
        $string = "this is (a (par) test) with ( (more) ) enclosed";
        $result = explode_enclosed("(", ")", $string, 0, false, true);
        $expectedResult = array(
            0 => "(a (par) test)",
            1 => "( (more) )"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedIndexAsStartPos()
    {
        $string = "this is (a (par) test) with ( (more) ) enclosed";
        $result = explode_enclosed("(", ")", $string, 0, true);
        $expectedResult = array(
            8 => "a (par) test",
            28 => " (more) "
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedIncludeOpenCloseIndexAsStartPos()
    {
        $string = "this is (a (par) test) with ( (more) ) enclosed";
        $result = explode_enclosed("(", ")", $string, 0, true, true);
        $expectedResult = array(
            7 => "(a (par) test)",
            27 => "( (more) )"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedQuotes()
    {
        $string = "enclose 'this is a string' as a result";
        $result = explode_enclosed("'", "'", $string);
        $expectedResult = array(
            "this is a string",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedQuotesEmpty()
    {
        $string = "enclose '' as a result";
        $result = explode_enclosed("'", "'", $string);
        $expectedResult = array(
            "",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedMultiCharacterOpenClose()
    {
        $string = "enclose {{here is a value}} as a result";
        $result = explode_enclosed("{{", "}}", $string);
        $expectedResult = array(
            "here is a value",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedMultiByteSafeIndexAsStartPos()
    {
        $string = "é(b)é(d)";
        $result = explode_enclosed("(", ")", $string, 0, true, false, true);
        $expectedResult = array(
            1 => "b",
            5 => "d"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedMultiByteSafeOffset()
    {
        $string = "é(b)é(d)";
        $result = explode_enclosed("(", ")", $string, 3, true, false, true);
        $expectedResult = array(
            5 => "d"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedOffset()
    {
        $string = "(a) (b)";
        $result = explode_enclosed("(", ")", $string, 1);
        $expectedResult = array(
            "b"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testExplodeEnclosedManyMatches()
    {
        $string = str_repeat("(a) ", 100000);
        $result = explode_enclosed("(", ")", $string);
        $this->assertCount(100000, $result);
    }

    public function testExplodeEnclosedEmptyDelimiter()
    {
        $this->expectException(\ValueError::class);
        explode_enclosed("", ")", "(a)");
    }
}