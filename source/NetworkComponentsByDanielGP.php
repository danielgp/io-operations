<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Popiniuc
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

namespace danielgp\network_components;

trait NetworkComponentsByDanielGP
{

    /**
     * Determines if a given IP is with a defined range
     *
     * @param ipv4 $ip
     * @param ipv4 $ipStart
     * @param ipv4 $ipEnd
     * @return string
     */
    protected function checkIpIsInRange($ip, $ipStart, $ipEnd)
    {
        $sReturn     = 'out';
        $startNo     = $this->convertIpToNumber($ipStart);
        $endNo       = $this->convertIpToNumber($ipEnd);
        $evaluatedNo = $this->convertIpToNumber($ip);
        if ($sReturn == 'out') {
            if (($evaluatedNo >= $startNo) && ($evaluatedNo <= $endNo)) {
                $sReturn = 'in';
            }
        }
        return $sReturn;
    }

    /**
     * Checks if given IP is a private or public one
     *
     * @param ipv4 $ip
     * @return string
     */
    protected function checkIpIsPrivate($ip)
    {
        $ipType = 'unkown';
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE)) {
                $ipType = 'private';
            } else {
                $ipType = 'public';
            }
        } else {
            $ipType = 'invalid';
        }
        return $ipType;
    }

    /**
     * Checks if given IP is a V4 or V6
     *
     * @param ipv4 $ip
     * @return string
     */
    protected function checkIpIsV4OrV6($ip)
    {
        $ipType = 'unkown';
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ipType = 'V4';
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ipType = 'V6';
            }
        } else {
            $ipType = 'invalid';
        }
        return $ipType;
    }

    /**
     * Converts IP to a number
     *
     * @param type $ip
     * @return string|int
     */
    protected function convertIpToNumber($ip)
    {
        $sReturn = '';
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ips     = explode('.', $ip);
                $sReturn = $ips[3] + $ips[2] * 256 + $ips[1] * 65536 + $ips[0] * 16777216;
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $binNum = '';
                foreach (unpack('C*', inet_pton($ip)) as $byte) {
                    $binNum .= str_pad(decbin($byte), 8, "0", STR_PAD_LEFT);
                }
                $sReturn = base_convert(ltrim($binNum, '0'), 2, 10);
            }
        } else {
            $sReturn = 'invalid IP';
        }
        return $sReturn;
    }

    /**
     * Returns the IP of the client
     *
     * @return string
     */
    protected function getClientRealIpAddress()
    {
        $aPatterns = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];
        $finalIP   = null;
        foreach ($aPatterns as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $IPaddress) {
                    $IPaddress = trim($IPaddress); // Just to be safe
                    if (filter_var($IPaddress, FILTER_VALIDATE_IP) !== false) {
                        $finalIP = $IPaddress;
                    }
                }
            }
        }
        return $finalIP;
    }
}
