<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UrlAddSlashesTest extends TestCase 
{
    public function testUrlAddSlashes() 
    {
        $input = "www.example.com";
        $result = url_add_slashes($input, true, true);
        $expectedResult = "/www.example.com/";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUrlAddSlashesNoSlashes() 
    {
        $input = "www.example.com";
        $result = url_add_slashes($input, false, false);
        $expectedResult = "www.example.com";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUrlAddSlashesEmpty() 
    {
        $this->assertEquals("/", url_add_slashes("", true, false));
        $this->assertEquals("/", url_add_slashes("", false, true));
    }

    public function testUrlAddSlashesExisting() 
    {
        $this->assertEquals("/a/", url_add_slashes("/a/", true, true));
    }
}