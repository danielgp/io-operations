<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 - 2024 Daniel Popiniuc
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
