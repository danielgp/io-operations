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
