<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UrlFormatSlashesTest extends TestCase 
{
    public function testUrlFormatSlashes() 
    {
        $input = "www.example.com";
        $result = url_format_slashes($input, true, true);
        $expectedResult = "/www.example.com/";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUrlFormatSlashesExcludeSlashes() 
    {
        $input = "www.example.com";
        $result = url_format_slashes($input, false, false);
        $expectedResult = "www.example.com";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUrlFormatSlashesRemovesSlashes() 
    {
        $input = "/www.example.com/";
        $result = url_format_slashes($input, false, false);
        $expectedResult = "www.example.com";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUrlFormatSlashesEmpty() 
    {
        $this->assertEquals("", url_format_slashes("", false, false));
        $this->assertEquals("//", url_format_slashes("", true, true));
    }
}