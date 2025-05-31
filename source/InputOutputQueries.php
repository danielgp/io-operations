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
