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
}