<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CliColorTest extends TestCase 
{
    public function testCliColor() 
    {
        $this->assertEquals("\033[0;31mhi\033[0m", cli_color("hi", "red"));
    }

    public function testCliColorBackground() 
    {
        $this->assertEquals("\033[0;31m\033[44mhi\033[0m", cli_color("hi", "red", "blue"));
    }

    public function testCliColorUnknown() 
    {
        $this->assertEquals("hi\033[0m", cli_color("hi", "orange", "orange"));
    }

    public function testCliColorPadded() 
    {
        $this->expectOutputString(cli_color("    ", "red") . cli_color("hi", "red") . cli_color("    ", "red") . "\n");
        $this->assertEquals(10, cli_color_padded("hi", "red", null, 10, 2));
    }
}
