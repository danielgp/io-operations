<?php

/*
 * The MIT License
 *
 * Copyright 2018 - 2024 Daniel Popiniuc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace danielgp\io_operations;

trait InputOutputOperations
{

    public function checkClientCalling()
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('By design this script will only work from PHP command line '
                . 'and this ain`t it...');
        }
    }

    public function checkInputParameters($arrayParameters)
    {
        $aAllParameters = getopt('--', array_keys($arrayParameters));
        foreach ($arrayParameters as $strParameterLabel => $strParameterName) {
            if (substr($strParameterLabel, -2) !== '::') {
                $this->checkInputSingleParameter($aAllParameters, str_replace(':', '', $strParameterLabel)
                    . '', $strParameterName);
            }
        }
        return $aAllParameters;
    }

    private function checkInputSingleParameter($arrayParameters, $strParameterLabel, $strParameterName)
    {
        if (!array_key_exists($strParameterLabel, $arrayParameters)) {
            $feedbackMessage = sprintf('Mandatory input parameter "%s" for %s has not been seen, '
                . 'will quit then!', $strParameterLabel, $strParameterName);
            echo PHP_EOL . $feedbackMessage;
            throw new \RuntimeException($feedbackMessage);
        }
    }
}
