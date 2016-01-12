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

namespace danielgp\browser_agent_info;

/**
 * Trait to expose various information from user browser
 *
 * @author Daniel Popiniuc
 */
trait BrowserAgentInfosByDanielGP
{

    use \danielgp\network_components\NetworkComponentsByDanielGP,
        ArchiecturesCpu;

    private $brServerGlobals = null;

    private function autoPopulateSuperGlobals()
    {
        if (is_null($this->brServerGlobals)) {
            $rqst                  = new \Symfony\Component\HttpFoundation\Request;
            $this->brServerGlobals = $rqst->createFromGlobals();
        }
    }

    /**
     * Return CPU architecture details from given user agent
     *
     * @param string $userAgent
     * @param string $targetToAnalyze
     * @return array
     */
    protected function getArchitectureFromUserAgent($userAgent, $targetToAnalyze = 'os')
    {
        switch ($targetToAnalyze) {
            case 'browser':
                $aReturn = $this->getArchitectureFromUserAgentBrowser($userAgent);
                break;
            case 'os':
                $aReturn = $this->getArchitectureFromUserAgentOperatingSystem($userAgent);
                break;
            default:
                $aReturn = ['name' => '---'];
                break;
        }
        return $aReturn;
    }

    /**
     * Provides details about browser
     *
     * @param \DeviceDetector\DeviceDetector $deviceDetectorClass
     * @param string $userAgent
     * @return array
     */
    private function getClientBrowser(\DeviceDetector\DeviceDetector $deviceDetectorClass, $userAgent)
    {
        $this->autoPopulateSuperGlobals();
        $browserInfoArray   = [
            'architecture' => $this->getArchitectureFromUserAgent($userAgent, 'browser'),
            'connection'   => $this->brServerGlobals->server->get('HTTP_CONNECTION'),
            'family'       => $this->getClientBrowserFamily($deviceDetectorClass),
            'host'         => $this->brServerGlobals->server->get('HTTP_HOST'),
            'referrer'     => $this->brServerGlobals->headers->get('referer'),
            'user_agent'   => $this->getUserAgentByCommonLib(),
        ];
        $vrs                = $this->getClientBrowserVersion($deviceDetectorClass);
        $browserInformation = array_merge($browserInfoArray, $this->getClientBrowserAccepted(), $vrs);
        ksort($browserInformation);
        return $browserInformation;
    }

    /**
     * Returns accepted things setting from the client browser
     *
     * @return array
     */
    private function getClientBrowserAccepted()
    {
        $this->autoPopulateSuperGlobals();
        $sReturn = [
            'accept'          => $this->brServerGlobals->server->get('HTTP_ACCEPT'),
            'accept_charset'  => $this->brServerGlobals->headers->get('Accept-Charset'),
            'accept_encoding' => $this->brServerGlobals->server->get('HTTP_ACCEPT_ENCODING'),
        ];
        if (!is_null($this->brServerGlobals->server->get('HTTP_ACCEPT_LANGUAGE'))) {
            $sReturn['accept_language']     = $this->brServerGlobals->server->get('HTTP_ACCEPT_LANGUAGE');
            $prfd                           = null;
            preg_match_all('/([a-z]{2})(?:-[a-zA-Z]{2})?/', $sReturn['accept_language'], $prfd);
            $sReturn['preferred locale']    = $prfd[0];
            $sReturn['preferred languages'] = array_values(array_unique(array_values($prfd[1])));
        }
        return $sReturn;
    }

    /**
     * Provides various details about browser based on user agent
     *
     * @param array $returnType
     * @return array
     */
    protected function getClientBrowserDetails($returnType = ['Browser', 'Device', 'OS'], $tmpFolder = null)
    {
        $userAgent      = $this->getUserAgentByCommonLib();
        $devDetectClass = new \DeviceDetector\DeviceDetector($userAgent);
        if (is_null($tmpFolder)) {
            $tmpFolder = '../../tmp/DoctrineCache/';
        }
        $devDetectClass->setCache(new \Doctrine\Common\Cache\PhpFileCache($tmpFolder));
        $devDetectClass->discardBotInformation();
        $devDetectClass->parse();
        if ($devDetectClass->isBot()) {
            return [
                'Bot' => $devDetectClass->getBot(),
            ];
        }
        return $this->getClientBrowserDetailsNonBot($devDetectClass, $userAgent, $returnType);
    }

    private function getClientBrowserDetailsNonBot(\DeviceDetector\DeviceDetector $devDetectClass, $uAg, $rtrnTypAry)
    {
        $aReturn = [];
        foreach ($rtrnTypAry as $value) {
            $aReturn[$value] = $this->getClientBrowserDetailsSingle($devDetectClass, $uAg, $value);
        }
        return $aReturn;
    }

    private function getClientBrowserDetailsSingle(\DeviceDetector\DeviceDetector $devDetectClass, $uAg, $value)
    {
        switch ($value) {
            case 'Browser':
                return $this->getClientBrowser($devDetectClass, $uAg);
            // intentionally left blank
            case 'Device':
                return $this->getClientBrowserDevice($devDetectClass);
            // intentionally left blank
            case 'OS':
                return $this->getClientBrowserOperatingSystem($devDetectClass, $uAg);
            // intentionally left blank
        }
    }

    /**
     * Returns client device details from client browser
     *
     * @param class $deviceDetectorClass
     * @return array
     */
    private function getClientBrowserDevice(\DeviceDetector\DeviceDetector $deviceDetectorClass)
    {
        $this->autoPopulateSuperGlobals();
        $clientIp = $this->brServerGlobals->getClientIp();
        return [
            'brand'     => $deviceDetectorClass->getDeviceName(),
            'ip'        => $clientIp,
            'ip direct' => $this->brServerGlobals->server->get('REMOTE_ADDR'),
            'ip type'   => $this->checkIpIsPrivate($clientIp),
            'ip v4/v6'  => $this->checkIpIsV4OrV6($clientIp),
            'model'     => $deviceDetectorClass->getModel(),
            'name'      => $deviceDetectorClass->getBrandName(),
        ];
    }

    private function getClientBrowserFamily(\DeviceDetector\DeviceDetector $deviceDetectorClass)
    {
        $browserClass  = new \DeviceDetector\Parser\Client\Browser();
        $browserFamily = $browserClass->getBrowserFamily($deviceDetectorClass->getClient('short_name'));
        return ($browserFamily !== false ? $browserFamily : '---');
    }

    /**
     * Returns client operating system details from client browser
     *
     * @param class $deviceDetectorClass
     * @param string $userAgent
     * @return array
     */
    private function getClientBrowserOperatingSystem(\DeviceDetector\DeviceDetector $deviceDetectorClass, $userAgent)
    {
        $aReturn                 = $deviceDetectorClass->getOs();
        $aReturn['architecture'] = $this->getArchitectureFromUserAgent($userAgent, 'os');
        $operatingSystem         = new \DeviceDetector\Parser\OperatingSystem();
        $osFamily                = $operatingSystem->getOsFamily($deviceDetectorClass->getOs('short_name'));
        $aReturn['family']       = ($osFamily !== false ? $osFamily : 'Unknown');
        ksort($aReturn);
        return $aReturn;
    }

    private function getClientBrowserVersion(\DeviceDetector\DeviceDetector $deviceDetectorClass)
    {
        $clientDetails = $deviceDetectorClass->getClient();
        if (isset($clientDetails['version']) && (strpos($clientDetails['version'], '.') !== false)) {
            $vrs                            = explode('.', $clientDetails['version']);
            $clientDetails['version_major'] = $vrs[0];
            $clientDetails['version_minor'] = $vrs[1];
        }
        return $clientDetails;
    }

    /**
     * Captures the user agent
     *
     * @return string
     */
    protected function getUserAgentByCommonLib()
    {
        $this->autoPopulateSuperGlobals();
        if (!is_null($this->brServerGlobals->get('ua'))) {
            return $this->brServerGlobals->get('ua');
        }
        return $this->getUserAgentByCommonLibDetection();
    }

    private function getUserAgentByCommonLibDetection()
    {
        if (!is_null($this->brServerGlobals->server->get('HTTP_USER_AGENT'))) {
            return $this->brServerGlobals->server->get('HTTP_USER_AGENT');
        } elseif (PHP_SAPI === 'cli' || empty($this->brServerGlobals->server->get('REMOTE_ADDR'))) { // command line
            return 'PHP/' . PHP_VERSION . ' comand-line';
        }
    }
}
