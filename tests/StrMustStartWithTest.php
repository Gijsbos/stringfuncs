<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustStartWithTest extends TestCase 
{
    public function testStrMustStartWith1()
    {
        $result = str_must_start_with("test", "te");
        $this->assertEquals("test", $result);
    }

    public function testStrMustStartWith2()
    {
        $result = str_must_start_with("st", "te");
        $this->assertEquals("test", $result);
    }
}