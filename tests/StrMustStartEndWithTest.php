<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustStartEndWithTest extends TestCase 
{
    public function testStrMustStartEndWith() 
    {
        $this->assertEquals("/a/", str_must_start_end_with("a", "/"));
        $this->assertEquals("/a/", str_must_start_end_with("/a/", "/"));
    }

    public function testStrMustStartEndWithDifferentEnd() 
    {
        $this->assertEquals("[a]", str_must_start_end_with("a", "[", "]"));
    }

    public function testStrMustStartEndWithInputEqualsDelimiter() 
    {
        $this->assertEquals("//", str_must_start_end_with("/", "/"));
    }
}
