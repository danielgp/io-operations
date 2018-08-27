<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 - 2018 Daniel Popiniuc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace danielgp\browser_agent_info;

class BrowserAgentInfosByDanielGPTest extends \PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass()
    {
        require_once str_replace(implode(DIRECTORY_SEPARATOR, [
                'tests',
                'php-unit',
            ]), 'source', __DIR__) . DIRECTORY_SEPARATOR . 'ArchitecturesCpu.php';
        require_once str_replace(implode(DIRECTORY_SEPARATOR, [
                'tests',
                'php-unit',
            ]), 'source', __DIR__) . DIRECTORY_SEPARATOR . 'BrowserAgentInfosByDanielGP.php';
    }

    public function testArchitectureBrowserAMD64()
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:39.0) Gecko/20100101 Firefox/39.0';
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a  = $mock->getArchitectureFromUserAgent($ua, 'browser');
        $this->assertContains('AMD/Intel x64', $a['name']);
    }

    public function testArchitectureOperatingSystemAMD64()
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:39.0) Gecko/20100101 Firefox/39.0';
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a  = $mock->getArchitectureFromUserAgent($ua, 'os');
        $this->assertContains('AMD/Intel x64', $a['name']);
    }

    public function testArchitectureOperatingSystemARM()
    {
        $ua = 'Mozilla/5.0 (Android; Mobile; rv:37.0) Gecko/37.0 Firefox/37.0';
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a  = $mock->getArchitectureFromUserAgent($ua, 'os');
        $this->assertContains('Advanced RISC Machine', $a['name']);
    }

    public function testArchitectureOperatingSystemIA32()
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/42.0.2311.90 Safari/537.36 OPR/29.0.1795.47';
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a  = $mock->getArchitectureFromUserAgent($ua, 'browser');
        $this->assertContains('Intel x86', $a['name']);
    }

    public function testArchitectureOperatingSystemUnknown()
    {
        $ua = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/42.0.2311.90 Safari/537.36 OPR/29.0.1795.47';
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a  = $mock->getArchitectureFromUserAgent($ua, 'dummy');
        $this->assertContains('---', $a['name']);
    }

    public function testClientDetails()
    {
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a = $mock->getClientBrowserDetails([
            'Browser',
            'Device',
            'OS',
                ], 'd:\\www\\other\\temp\\PHP\\PHP56\\');
        $this->assertNotEmpty($a);
    }

    public function testClientDetailsNoCacheSpecified()
    {
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $a = $mock->getClientBrowserDetails([
            'Browser',
            'Device',
            'OS',
        ]);
        $this->assertNotEmpty($a);
    }

    public function testUserAgent()
    {
        $mock = $this->getMockForTrait(BrowserAgentInfosByDanielGP::class);
        $actual = $mock->getUserAgentByCommonLib();
        $this->assertEquals('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:39.0) Gecko/20100101 Firefox/39.0', $actual);
    }
}
