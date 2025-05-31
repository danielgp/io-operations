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
    public static function setUpBeforeClass()
    {
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'InputOutputNetworkComponents.php';
    }

    public function testCheckIpIsInRangeIn()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsInRange('160.221.78.69', '160.221.78.1', '160.221.79.254');
        $this->assertEquals('in', $a);
    }

    public function testCheckIpIsInRangeOut()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsInRange('160.221.78.69', '160.221.79.1', '160.221.79.254');
        $this->assertEquals('out', $a);
    }

    public function testCheckIpIsPrivateEqualInvalid()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsPrivate('192.168');
        $this->assertEquals('invalid IP', $a);
    }

    public function testCheckIpIsPrivateEqualPrivate()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsPrivate('127.0.0.1');
        $this->assertEquals('private', $a);
    }

    public function testCheckIpIsPrivateEqualPublic()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsPrivate('216.58.211.4');
        $this->assertEquals('public', $a);
    }

    public function testCheckIpIsV4OrV6EqualInvalid()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsV4OrV6('192.168');
        $this->assertEquals('invalid IP', $a);
    }

    public function testCheckIpIsV4OrV6EqualV4()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsV4OrV6('192.168.1.1');
        $this->assertEquals('V4', $a);
    }

    public function testCheckIpIsV4OrV6EqualV6()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->checkIpIsV4OrV6('::1');
        $this->assertEquals('V6', $a);
    }

    public function testConvertIpToNumber()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->convertIpToNumber('10.0.5.9');
        $this->assertEquals(167773449, $a);
    }

    public function testConvertIpToNumberInvlidIP()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->convertIpToNumber('10.99');
        $this->assertEquals('invalid IP', $a);
    }

    public function testConvertIpToNumberOfIpV6()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->convertIpToNumber('::FFFF:FFFF');
        $this->assertEquals(4294967295, $a);
    }

    public function testGetClientRealIpAddress()
    {
        $mock = $this->getMockForTrait(InputOutputNetworkComponents::class);
        $a    = $mock->getClientRealIpAddress();
        $this->assertEquals('127.0.0.1', $a);
    }
}
