<?php

/*
 * The MIT License
 *
 * Copyright 2018 Daniel Popiniuc
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

trait InputOutputArrays
{

    /**
     * Replace space with break line for each key element
     *
     * @param array $aElements
     * @return array
     */
    protected function setArrayToArrayKbr(array $aElements)
    {
        $aReturn = [];
        foreach ($aElements as $key => $value) {
            $aReturn[str_replace(' ', '<br/>', $key)] = $value;
        }
        return $aReturn;
    }

    /**
     * Converts a single-child array into an parent-child one
     *
     * @param array $inArray
     * @return array
     */
    public function setArrayValuesAsKey(array $inArray)
    {
        $outArray = array_combine($inArray, $inArray);
        ksort($outArray);
        return $outArray;
    }

    protected function setArrayToJsonSafely($inputParameters, $preety = false)
    {
        $encodingFlags = (JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($preety) {
            $encodingFlags = (JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $contentToWrite = json_encode($inputParameters, $encodingFlags);
        if ($contentToWrite === false) {
            throw new \RuntimeException(sprintf('Unable to encode string into JSON (code %s with message %s)', ''
                    . json_last_error(), json_last_error_msg()));
        }
        return utf8_encode($contentToWrite);
    }
}
