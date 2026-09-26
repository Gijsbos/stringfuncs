<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReplaceEnclosedQuotesTest extends TestCase 
{
    public function testReplaceEnclosedQuotesMultiByteSafe()
    {
        $string = '" this is a test" `a` using quotes';
        $result = \replace_enclosed_quotes($string, "a", "the", true);
        $expectedResult = '" this is the test" `the` using quotes';
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes()
    {
        $string = '" this is a test" `a` using quotes';
        $result = \replace_enclosed_quotes($string, "a", "the");
        $expectedResult = '" this is the test" `the` using quotes';
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes2()
    {
        $string = "this string uses 'quote 1 `' as an example with 'quote 2 `' to illustrate the problem";
        $result = \replace_enclosed_quotes($string, "quote", "");
        $expectedResult = "this string uses ' 1 `' as an example with ' 2 `' to illustrate the problem";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes3()
    {
        $string = "'te,st1','test2'";
        $result = \replace_enclosed_quotes($string, ",", "U+002C");
        $expectedResult = "'teU+002Cst1','test2'";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes4()
    {
        $string = '" a test is \" is " a test is';
        $result = \replace_enclosed_quotes($string, "is", "was");
        $expectedResult = '" a test was \" was " a test is';
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes5()
    {
        $string = "'a test is \' is ' a test is";
        $result = \replace_enclosed_quotes($string, "is", "was");
        $expectedResult = "'a test was \' was ' a test is";
        $this->assertEquals($expectedResult, $result);
    }

    # @BugFix: double quote not getting priority over single quote, single quote start was not checking if it was smaller than start
    public function testReplaceEnclosedQuotes6()
    {
        $string = <<< EOD
\$command = #"#substr('#string'#, 0, 2)";
EOD;
        $result = \replace_enclosed_quotes($string, "#", "hashtag");
        $expectedResult = <<< EOD
\$command = #"hashtagsubstr('hashtagstring'hashtag, 0, 2)";
EOD;
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotes7()
    {
        $string = <<<EOD
\$className = \$this->getEntity()->namespace . "\\" . \$placeholder;
EOD;
        $result = \replace_enclosed_quotes($string, "/", "U+002F");
        $expectedResult = <<<EOD
\$className = \$this->getEntity()->namespace . "\\" . \$placeholder;
EOD;
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceEnclosedQuotesMarkerTextIsPreserved()
    {
        $string = '"a \" b" 0x5C0x22';
        $result = \replace_enclosed_quotes($string, "x", "y");
        $this->assertEquals($string, $result);
    }

    public function testReplaceEnclosedQuotesSearchDoesNotCorruptEscapes()
    {
        $string = '"a \" x"';
        $result = \replace_enclosed_quotes($string, "x", "X");
        $this->assertEquals('"a \" X"', $result);
    }

    public function testReplaceEnclosedQuotesMultiByteContent()
    {
        $string = '"éé" "a"';
        $result = \replace_enclosed_quotes($string, "a", "b", true);
        $this->assertEquals('"éé" "b"', $result);
    }

    public function testReplaceEnclosedQuotesUnclosedQuoteIsSkipped()
    {
        $string = "it's \"a\" day";
        $result = \replace_enclosed_quotes($string, "a", "the");
        $this->assertEquals("it's \"the\" day", $result);
    }

    public function testReplaceEnclosedQuotesEscapedBackslashBeforeQuote()
    {
        $string = "'a\\\\' a 'a'";
        $result = \replace_enclosed_quotes($string, "a", "b");
        $this->assertEquals("'b\\\\' a 'b'", $result);
    }

    public function testReplaceEnclosedQuotesTrailingBackslash()
    {
        $string = "'a' \\";
        $result = \replace_enclosed_quotes($string, "a", "b");
        $this->assertEquals("'b' \\", $result);
    }

    public function testReplaceEnclosedQuotesNoQuotes()
    {
        $string = "no quotes here";
        $result = \replace_enclosed_quotes($string, "no", "yes");
        $this->assertEquals($string, $result);
    }
}