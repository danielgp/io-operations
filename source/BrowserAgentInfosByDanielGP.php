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
        $aReturn = [];
        switch ($targetToAnalyze) {
            case 'browser':
                $aReturn = $this->getArchitectureFromUserAgentBrowser($userAgent);
                break;
            case 'os':
                $aReturn = $this->getArchitectureFromUserAgentOperatingSystem($userAgent);
                break;
            default:
                $aReturn = [
                    'name' => '---'
                ];
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
        $browserClass       = new \DeviceDetector\Parser\Client\Browser();
        $browserFamily      = $browserClass->getBrowserFamily($deviceDetectorClass->getClient('short_name'));
        $browserInfoArray   = [
            'architecture' => $this->getArchitectureFromUserAgent($userAgent, 'browser'),
            'connection'   => (isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : ''),
            'family'       => ($browserFamily !== false ? $browserFamily : '---'),
            'host'         => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'referrer'     => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
            'user_agent'   => $this->getUserAgentByCommonLib(),
        ];
        $browserInformation = array_merge($browserInfoArray, $this->getClientBrowserAccepted());
        $clientDetails      = $deviceDetectorClass->getClient();
        if (is_array($clientDetails)) {
            $browserInformation                  = array_merge($browserInformation, $clientDetails);
            // more digestable details about version
            $browserInformation['version_major'] = explode('.', $browserInformation['version'])[0];
            $browserInformation['version_minor'] = explode('.', $browserInformation['version'])[1];
        }
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
            'accept_encoding' => $this->brServerGlobals->server->get('HTTP_ACCEPT_ENCODING'),
        ];
        if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
            $sReturn['accept_charset'] = $_SERVER['HTTP_ACCEPT_CHARSET'];
        }
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
            $aReturn = [
                'Bot' => $devDetectClass->getBot(), // handle bots,spiders,crawlers,...
            ];
        } else {
            $aReturn = [];
            foreach ($returnType as $value) {
                switch ($value) {
                    case 'Browser':
                        $aReturn[$value] = $this->getClientBrowser($devDetectClass, $userAgent);
                        break;
                    case 'Device':
                        $aReturn[$value] = $this->getClientBrowserDevice($devDetectClass);
                        break;
                    case 'OS':
                        $aReturn[$value] = $this->getClientBrowserOperatingSystem($devDetectClass, $userAgent);
                        break;
                }
            }
        }
        return $aReturn;
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
            'ip direct' => filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP),
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

    /**
     * Captures the user agent
     *
     * @return string
     */
    protected function getUserAgentByCommonLib()
    {
        $crtUserAgent = '';
        if (isset($_GET['ua'])) {
            $crtUserAgent = $_GET['ua'];
        } else {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $crtUserAgent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
            } elseif (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {  // command line
                $crtUserAgent = 'PHP/' . PHP_VERSION . ' comand-line';
            }
        }
        return $crtUserAgent;
    }
}
