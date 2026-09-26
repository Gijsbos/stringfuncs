<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PlaceholderRestoreTest extends TestCase 
{
    public function testPlaceholderRestore()
    {
        $result = placeholder_restore("f({0}) g({1})", [0 => "a", 1 => "b"]);
        $this->assertEquals("f(a) g(b)", $result);
    }

    public function testPlaceholderRestoreUnknownIndex()
    {
        $result = placeholder_restore("f({0}) g({7})", [0 => "a"]);
        $this->assertEquals("f(a) g({7})", $result);
    }

    public function testPlaceholderRestoreNoPlaceholders()
    {
        $result = placeholder_restore("plain text", [0 => "a"]);
        $this->assertEquals("plain text", $result);
    }
}
