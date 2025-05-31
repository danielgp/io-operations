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
