<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StrMustNotStartWithTest extends TestCase 
{
    public function testStrMustNotStartWith1()
    {
        $result = str_must_not_start_with("test", "te");
        $this->assertEquals("st", $result);
    }

    public function testStrMustNotStartWith2()
    {
        $result = str_must_not_start_with("st", "te");
        $this->assertEquals("st", $result);
    }
}