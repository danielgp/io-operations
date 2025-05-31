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

trait InputOutputHardware
{
    use \danielgp\io_operations\InputOutputFiles;

    private function getCommandsForCurrentOperatingSystem(): array
    {
        $arrayWhatMapping = $this->getArrayFromJsonFile(__DIR__ . '/json', 'Hardware.json');
        if (!array_key_exists(PHP_OS_FAMILY, $arrayWhatMapping)) {
            \PHPUnit\Framework\throwException(PHP_OS_FAMILY . ' not covered yet...');
        }
        return $arrayWhatMapping[PHP_OS_FAMILY];
    }

    private function getHardwareInfo(string $strWhat): array
    {
        $arrayWhatMapping = $this->getCommandsForCurrentOperatingSystem();
        $arrayOutput      = [];
        if (function_exists('shell_exec')) {
            $strFeedback = trim(shell_exec($arrayWhatMapping[$strWhat]));
            $arrayPieces = explode("\r\n", $strFeedback);
            $intCounter  = 0;
            $intLine     = 0;
            foreach ($arrayPieces as $strPiece) {
                if ($strPiece == '') {
                    $intLine++;
                } else {
                    $intLine = 0;
                }
                if ($intLine == 2) {
                    $intCounter++;
                }
                if ($strPiece !== '') {
                    $arrayP                               = explode('=', $strPiece);
                    $arrayOutput[$intCounter][$arrayP[0]] = $arrayP[1];
                }
            }
        } else {
            $arrayOutput[] = 'Command execution not possible.';
        }
        return $arrayOutput;
    }
}
