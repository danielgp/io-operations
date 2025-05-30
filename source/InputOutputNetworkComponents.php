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

trait InputOutputNetworkComponents
{
    /**
     * Determines if a given IP is with a defined range
     *
     * @param $ipGiven
     * @param $ipStart
     * @param $ipEnd
     * @return string
     */
    public function checkIpIsInRange($ipGiven, $ipStart, $ipEnd)
    {
        $sReturn     = 'out';
        $startNo     = $this->convertIpToNumber($ipStart);
        $endNo       = $this->convertIpToNumber($ipEnd);
        $evaluatedNo = $this->convertIpToNumber($ipGiven);
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
     * @param $ipGiven
     * @return string
     */
    public function checkIpIsPrivate($ipGiven)
    {
        if (filter_var($ipGiven, FILTER_VALIDATE_IP)) {
            if (!filter_var($ipGiven, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE)) {
                return 'private';
            }
            return 'public';
        }
        return 'invalid IP';
    }

    /**
     * Checks if given IP is a V4 or V6
     *
     * @param $ipGiven
     * @return string
     */
    public function checkIpIsV4OrV6($ipGiven)
    {
        if (filter_var($ipGiven, FILTER_VALIDATE_IP)) {
            if (filter_var($ipGiven, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return 'V4';
            } elseif (filter_var($ipGiven, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return 'V6';
            }
        }
        return 'invalid IP';
    }

    /**
     * Converts IP to a number
     *
     * @param $ipGiven
     * @return string|int
     */
    public function convertIpToNumber($ipGiven)
    {
        if (filter_var($ipGiven, FILTER_VALIDATE_IP)) {
            if (filter_var($ipGiven, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ips = explode('.', $ipGiven);
                return $ips[3] + $ips[2] * 256 + $ips[1] * 65536 + $ips[0] * 16777216;
            } elseif (filter_var($ipGiven, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $this->convertIpV6ToNumber($ipGiven);
            }
        }
        return 'invalid IP';
    }

    private function convertIpV6ToNumber($ipGiven)
    {
        $binNum = '';
        foreach (unpack('C*', inet_pton($ipGiven)) as $byte) {
            $binNum .= str_pad(decbin($byte), 8, "0", STR_PAD_LEFT);
        }
        return base_convert(ltrim($binNum, '0'), 2, 10);
    }

    /**
     * Returns the IP of the client
     *
     * @return string
     */
    public function getClientRealIpAddress()
    {
        $rqst = new \Symfony\Component\HttpFoundation\Request();
        return $rqst->createFromGlobals()->getClientIp();
    }
}
