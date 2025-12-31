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

trait InputOutputCurl
{

    public function getContentFromUrlThroughCurl(string $fullURL, array $features = [])
    {
        $chanel     = curl_init();
        $inScopeUrl = $this->validateUrl($fullURL);
        $this->handleCurlOptions($chanel, $inScopeUrl, $features);
        $aReturn    = [
            'response' => curl_exec($chanel),
            'errNo'    => curl_errno($chanel),
            'errMsg'   => curl_error($chanel),
        ];
        if (array_key_exists('includeStatistics', $features)) {
            $aReturn['info'] = curl_getinfo($chanel);
        }
        if (array_key_exists('NoBody', $features)) {
            unset($aReturn['response']);
        }
        if (PHP_VERSION_ID < 80500) {
            curl_close($chanel);
        }
        return $aReturn;
    }

    private function handleCurlOptions(\CurlHandle|false $chanel, string $inScopeUrl, array $features): void
    {
        $this->setUserAgent($chanel, $features);
        $this->handleSecureConnection($chanel, $inScopeUrl, $features);
        $this->setForceIpv4($chanel, $features);
        $this->setNoBody($chanel, $features);
        curl_setopt($chanel, CURLOPT_FAILONERROR, true);
        curl_setopt($chanel, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($chanel, CURLOPT_FRESH_CONNECT, true); //avoid a cached response
        curl_setopt($chanel, CURLOPT_HEADER, false);
        curl_setopt($chanel, CURLOPT_MAXREDIRS, 10);
        curl_setopt($chanel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chanel, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($chanel, CURLOPT_URL, $inScopeUrl);
        $this->setPostingDetails($chanel, $features);
        if (array_key_exists('HttpHeader', $features) && !array_key_exists('PostFields', $features)) {
            curl_setopt($chanel, CURLOPT_HTTPHEADER, $features['HttpHeader']);
        }
    }

    private function handleSecureConnection(\CurlHandle|false $chanel, string $fullURL, array $features): void
    {
        if ((substr(strtolower($fullURL), 0, 5) === 'https')) {
            $chk = false;
            if (array_key_exists('forceSSLverification', $features)) {
                $chk = true;
            }
            curl_setopt($chanel, CURLOPT_SSL_VERIFYHOST, $chk);
            curl_setopt($chanel, CURLOPT_SSL_VERIFYPEER, $chk);
        }
    }

    private function setForceIpv4(\CurlHandle|false $chanel, array $features): void
    {
        if (array_key_exists('forceIpV4', $features)) {
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($chanel, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
        }
    }

    private function setNoBody(\CurlHandle|false $chanel, array $features): void
    {
        if (array_key_exists('NoBody', $features)) {
            curl_setopt($chanel, CURLOPT_NOBODY, true);
        }
    }

    private function setPostingDetails(\CurlHandle|false $chanel, array $features): void
    {
        if (array_key_exists('PostFields', $features)) {
            curl_setopt($chanel, CURLOPT_POST, true);
            curl_setopt($chanel, CURLOPT_POSTFIELDS, $features['PostFields']);
            curl_setopt($chanel, CURLOPT_HTTPHEADER, $features['HttpHeader']);
        }
    }

    private function setUserAgent(\CurlHandle|false $chanel, array $features): void
    {
        if (array_key_exists('setUserAgent', $features)) {
            curl_setopt($chanel, CURLOPT_USERAGENT, $features['setUserAgent']);
        }
    }

    private function validateUrl(string $strUrl): string
    {
        $inScopeUrl = filter_var($strUrl, FILTER_VALIDATE_URL);
        if ($inScopeUrl === false) {
            throw new \Exception('Invalid URL provided...');
        }
        return $inScopeUrl;
    }
}
