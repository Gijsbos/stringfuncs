<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustNotStartEndWithTest extends TestCase 
{
    public function testStrMustNotStartEndWith() 
    {
        $this->assertEquals("a", str_must_not_start_end_with("/a/", "/"));
        $this->assertEquals("a", str_must_not_start_end_with("a", "/"));
    }

    public function testStrMustNotStartEndWithDifferentEnd() 
    {
        $this->assertEquals("a", str_must_not_start_end_with("[a]", "[", "]"));
    }
}
