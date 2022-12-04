<?php

/*
 * The MIT License
 *
 * Copyright 2022 Daniel Popiniuc.
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

trait InputOutputQueries
{

    use \danielgp\io_operations\InputOutputFiles;

    /**
     * Returns the Query language type by scanning the 1st keyword from a given query
     *
     * @param string $sQuery
     */
    protected function getMySQLqueryType($sQuery)
    {
        $queryPieces    = explode(' ', strtoupper($sQuery));
        $arrayReturn    = [
            '1st Keyword Within Query' => $queryPieces[0],
            'Description'              => 'unknown',
            'Type'                     => 'unknown',
            'Type Description'         => 'unknown',
        ];
        $statementTypes = $this->listOfMySQLqueryStatementType($queryPieces[0]);
        if (in_array($queryPieces[0], $statementTypes['keys'])) {
            $ar1         = ['1st Keyword Within Query' => $queryPieces[0]];
            $lnT         = $this->listOfMySQLqueryLanguageType($statementTypes['value']['Type']);
            $arrayReturn = array_merge($ar1, $lnT, $statementTypes['value']);
            ksort($arrayReturn);
        }
        return $arrayReturn;
    }

    /**
     * Just to keep a list of type of language as array
     *
     * @return array
     */
    private function listOfMySQLqueryLanguageType($qType)
    {
        $keyForReturn = 'Type Description';
        $vMap         = ['DCL', 'DDL', 'DML', 'DQL', 'DTL'];
        $arrayReturn  = [$keyForReturn => 'unknown'];
        if (in_array($qType, $vMap)) {
            $valForReturn = $this->getArrayFromJsonFile(__DIR__ . '/json', 'MySQLiLanguageTypes.json')[$qType];
            $arrayReturn  = [$keyForReturn => $valForReturn[0] . ' (' . $valForReturn[1] . ')'];
        }
        return $arrayReturn;
    }

    /**
     * Just to keep a list of statement types as array
     *
     * @param string $firstKwordWQuery
     * @return array
     */
    private function listOfMySQLqueryStatementType($firstKwordWQuery)
    {
        $statmentsArray = $this->getArrayFromJsonFile(__DIR__ . '/json', 'MySQLiStatementTypes.json');
        return [
            'keys'  => array_keys($statmentsArray),
            'value' => [
                'Description' => $statmentsArray[$firstKwordWQuery][1],
                'Type'        => $statmentsArray[$firstKwordWQuery][0],
            ],
        ];
    }
}
