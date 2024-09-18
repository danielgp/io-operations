<?php

/*
 * The MIT License
 *
 * Copyright 2024 E303778.
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
