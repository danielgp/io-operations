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

class InputOutputNetworkComponentsTest extends \PHPUnit\Framework\TestCase
{
    
    use InputOutputNetworkComponents;
    
    #[\Override]
    public static function setUpBeforeClass():void
    {
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'InputOutputNetworkComponents.php';
    }

    public function testCheckIpIsInRangeIn()
    {
        $a    = $this->checkIpIsInRange('160.221.78.69', '160.221.78.1', '160.221.79.254');
        $this->assertEquals('in', $a);
    }

    public function testCheckIpIsInRangeOut()
    {
        $a    = $this->checkIpIsInRange('160.221.78.69', '160.221.79.1', '160.221.79.254');
        $this->assertEquals('out', $a);
    }

    public function testCheckIpIsPrivateEqualInvalid()
    {
        $a    = $this->checkIpIsPrivate('192.168');
        $this->assertEquals('invalid IP', $a);
    }

    public function testCheckIpIsPrivateEqualPrivate()
    {
        $a    = $this->checkIpIsPrivate('127.0.0.1');
        $this->assertEquals('private', $a);
    }

    public function testCheckIpIsPrivateEqualPublic()
    {
        $a    = $this->checkIpIsPrivate('216.58.211.4');
        $this->assertEquals('public', $a);
    }

    public function testCheckIpIsV4OrV6EqualInvalid()
    {
        $a    = $this->checkIpIsV4OrV6('192.168');
        $this->assertEquals('invalid IP', $a);
    }

    public function testCheckIpIsV4OrV6EqualV4()
    {
        $a    = $this->checkIpIsV4OrV6('192.168.1.1');
        $this->assertEquals('V4', $a);
    }

    public function testCheckIpIsV4OrV6EqualV6()
    {
        $a    = $this->checkIpIsV4OrV6('::1');
        $this->assertEquals('V6', $a);
    }

    public function testConvertIpToNumber()
    {
        $a    = $this->convertIpToNumber('10.0.5.9');
        $this->assertEquals(167773449, $a);
    }

    public function testConvertIpToNumberInvlidIP()
    {
        $a    = $this->convertIpToNumber('10.99');
        $this->assertEquals('invalid IP', $a);
    }

    public function testConvertIpToNumberOfIpV6()
    {
        $a    = $this->convertIpToNumber('::FFFF:FFFF');
        $this->assertEquals(4294967295, $a);
    }

    public function testGetClientRealIpAddress()
    {
        $a    = $this->getClientRealIpAddress();
        $this->assertEquals('127.0.0.1', $a);
    }
}
