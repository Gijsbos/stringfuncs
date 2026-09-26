<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PlaceholderReplaceTest extends TestCase 
{
    public function testPlaceholderReplace()
    {
        $content = "f(a, (b)) g(c)";
        $index = 0;
        $result = placeholder_replace("(", ")", $content, $index);
        $this->assertEquals("f({0}) g({1})", $content);
        $this->assertEquals([0 => "a, (b)", 1 => "c"], $result);
        $this->assertEquals(2, $index);
    }

    public function testPlaceholderReplaceStartIndex()
    {
        $content = "f(a)";
        $index = 5;
        $result = placeholder_replace("(", ")", $content, $index);
        $this->assertEquals("f({5})", $content);
        $this->assertEquals([5 => "a"], $result);
        $this->assertEquals(6, $index);
    }

    public function testPlaceholderReplaceMultiCharacterOpenClose()
    {
        $content = "x {{a}} y";
        $index = 0;
        $result = placeholder_replace("{{", "}}", $content, $index);
        $this->assertEquals("x {{{0}}} y", $content);
        $this->assertEquals([0 => "a"], $result);
    }

    public function testPlaceholderReplaceMultiByteSafe()
    {
        $content = "é(ü) é(ö)";
        $index = 0;
        $result = placeholder_replace("(", ")", $content, $index, true);
        $this->assertEquals("é({0}) é({1})", $content);
        $this->assertEquals([0 => "ü", 1 => "ö"], $result);
    }

    public function testPlaceholderReplaceRoundTrip()
    {
        $original = "f(a, (b)) g(c)";
        $content = $original;
        $index = 0;
        $placeholders = placeholder_replace("(", ")", $content, $index);
        $this->assertEquals($original, placeholder_restore($content, $placeholders));
    }
}
