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
        // A single delimiter satisfies both start and end, e.g. a root path prefix
        $this->assertEquals("/", str_must_start_end_with("/", "/"));
        $this->assertEquals("/", str_must_start_end_with("", "/"));
    }
}
