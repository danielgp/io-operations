<?php

/*
 * Copyright (c) 2018 - 2025 Daniel Popiniuc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Daniel Popiniuc
 */

namespace danielgp\io_operations;

class InputOutputBrowserAgentTest extends \PHPUnit\Framework\TestCase
{
    
    use InputOutputBrowserAgent;

    #[\Override]
    protected function setUp(): void
    {
        require_once str_replace(implode(DIRECTORY_SEPARATOR, ['tests', 'php-unit']), 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'ArchitecturesCpu.php';
        require_once str_replace(implode(DIRECTORY_SEPARATOR, ['tests', 'php-unit']), 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'InputOutputBrowserAgent.php';
    }

    public function testArchitectureBrowserAMD64()
    {
        $ua   = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:39.0) Gecko/20100101 Firefox/39.0';
        $a    = $this->getArchitectureFromUserAgent($ua, 'browser');
        $this->assertContains('AMD/Intel x64', $a);
    }

    public function testArchitectureOperatingSystemAMD64()
    {
        $ua   = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:39.0) Gecko/20100101 Firefox/39.0';
        $a    = $this->getArchitectureFromUserAgent($ua, 'os');
        $this->assertContains('AMD/Intel x64', $a);
    }

    public function testArchitectureOperatingSystemARM()
    {
        $ua   = 'Mozilla/5.0 (Android; Mobile; rv:37.0) Gecko/37.0 Firefox/37.0';
        $a    = $this->getArchitectureFromUserAgent($ua, 'os');
        $this->assertContains('Advanced RISC Machine', $a);
    }

    public function testArchitectureOperatingSystemIA32()
    {
        $ua   = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/42.0.2311.90 Safari/537.36 OPR/29.0.1795.47';
        $a    = $this->getArchitectureFromUserAgent($ua, 'browser');
        $this->assertContains('Intel x86', $a);
    }

    public function testArchitectureOperatingSystemUnknown()
    {
        $ua   = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/42.0.2311.90 Safari/537.36 OPR/29.0.1795.47';
        $a    = $this->getArchitectureFromUserAgent($ua, 'dummy');
        $this->assertContains('---', $a);
    }

    public function testClientDetails()
    {
        $a    = $this->getClientBrowserDetails([
            'Browser',
            'Device',
            'OS',
            ], 'd:\\www\\other\\temp\\PHP\\PHP56\\');
        $this->assertNotEmpty($a);
    }

    public function testClientDetailsNoCacheSpecified()
    {
        $a    = $this->getClientBrowserDetails([
            'Browser',
            'Device',
            'OS',
        ]);
        $this->assertNotEmpty($a);
    }

    public function testUserAgent()
    {
        $actual = $this->getUserAgentByCommonLib();
        $this->assertEquals('Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', $actual);
    }
}
