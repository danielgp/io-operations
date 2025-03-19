<?php

/*
 * The MIT License
 *
 * Copyright 2022 - 2024 Daniel Popiniuc.
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

trait InputOutputDatabases
{

    public $strErrorText            = null;
    private $arrayConnectionDetails = [];
    private $bolDebugText           = false;
    private $objConnection          = null;

    public function establishDatabaseConnectionFlexible(string $strDatabaseType, array $arrayConnectionParameters)
    {
        if (http_response_code() != 200) {
            return null;
        }
        $arrayAuth = [
            'U' => '',
            'P' => '',
        ];
        if (array_key_exists('Authentication', $arrayConnectionParameters)) {
            $arrayAuth = [
                'U' => $arrayConnectionParameters['Authentication']['Username'],
                'P' => $arrayConnectionParameters['Authentication']['Password'],
            ];
        }
        switch ($strDatabaseType) {
            case 'MySQL':
                $strDsn             = 'mysql:'
                    . implode(';', [
                        'host=' . $arrayConnectionParameters['Host'],
                        'dbname=' . $arrayConnectionParameters['Database'],
                        'port=' . $arrayConnectionParameters['Port'],
                        'charset=' . $arrayConnectionParameters['Charset'],
                        'collation=' . $arrayConnectionParameters['Collation'],
                ]);
                $aConnectionOptions = [
                    \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_FOUND_ROWS => TRUE,
                ];
                break;
            case 'SQLite':
                $strDsn             = 'sqlite:' . $arrayConnectionParameters['FileName'];
                $aConnectionOptions = [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION,
                ];
                break;
        }
        $this->arrayConnectionDetails['DatabaseType'] = $strDatabaseType;
        $this->exposeDebugText('Database connection DSN: ' . $strDsn);
        if ($arrayAuth['U'] != '') {
            $this->exposeDebugText('Database connection username: ' . $arrayAuth['U']);
        }
        try {
            $this->objConnection = new \PDO($strDsn, $arrayAuth['U'], $arrayAuth['P'], $aConnectionOptions);
            $this->exposeDebugText('Database connection established: '
                . $this->getResultsAsJson($this->objConnection));
        } catch (\RuntimeException $e) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            $this->strErrorText = vsprintf('Conectarea la [%s] a întâmpinat o eroare'
                . ', detaliile sunt după cum urmează: %s', [
                $strDatabaseType,
                $e->getMessage(),
            ]);
            $this->exposeDebugText('Database connection: ' . $this->strErrorText);
        }
    }

    public function exposeDebugText(string $strText)
    {
        if ($this->bolDebugText) {
            error_log($strText);
        }
    }

    public function getQueryFromFile(array $arrayInputs)
    {
        $this->exposeDebugText('Query file is: ' . $arrayInputs['queryFileName']);
        $fileContent = $this->getFileEntireContent($arrayInputs['queryFileName']);
        $strRawQuery = $fileContent;
        if (is_array($fileContent)) {
            $strRawQueryAsIs = implode(' ', $fileContent);
            $this->exposeDebugText('Query as read from file: ' . $strRawQueryAsIs);
            $arrayToClean    = [
                str_repeat(' ', 12),
                str_repeat(' ', 8),
                str_repeat(' ', 4),
                str_repeat(' ', 2),
            ];
            $strRawQuery     = str_replace($arrayToClean, ' ', $strRawQueryAsIs);
        }
        $sReturn = $strRawQuery;
        if (array_key_exists('queryParameterValues', $arrayInputs) && ($arrayInputs['queryParameterValues'] !== [])) {
            $sReturn = vsprintf($strRawQuery, $arrayInputs['queryParameterValues']);
        }
        return $sReturn;
    }

    public function getResultsAsJson(array|\PDO $arrayResults)
    {
        $encodingFlags = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return json_encode($arrayResults, $encodingFlags);
    }

    public function getResultsThroughVerification(array $arrayResult)
    {
        $strReturn = $arrayResult;
        if (is_null($arrayResult)) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            $this->strErrorText = 'NU există date pe server cu valorile introduse!';
            $this->exposeDebugText('No data (NULL): ' . $this->strErrorText);
        } elseif (is_array($arrayResult)) {
            if ($arrayResult == []) {
                if (!headers_sent()) {
                    http_response_code(403);
                }
                $this->strErrorText = 'NU există date pe server cu valorile introduse!';
                $this->exposeDebugText('Empty results: ' . $this->strErrorText);
            }
        }
        return $strReturn;
    }

    public function getResultsUsingQuery(string $strQuery, string $strFetchingType = \PDO::FETCH_ASSOC)
    {
        if (is_null($this->objConnection)) {
            return null;
        }
        $result = [];
        try {
            $stmt = $this->objConnection->prepare($strQuery);
            $this->exposeDebugText('Before Query execution: ' . $strQuery);
            $stmt->execute();
            if (substr($strQuery, 0, 7) === 'INSERT ') {
                $this->exposeDebugText('INSERT detected');
                $intLastInsertId = $this->objConnection->lastInsertId();
                $this->exposeDebugText('Last insert ID: ' . $intLastInsertId);
                switch ($intLastInsertId) {
                    case '0':
                        $intRowsAffected = 0;
                        break;
                    default:
                        $intRowsAffected = $stmt->rowCount();
                        break;
                }
                $result = [
                    'lastInsertId' => $intLastInsertId,
                    'rowsAffected' => $intRowsAffected,
                ];
                $this->exposeDebugText('Rows affected: ' . $this->getResultsAsJson($result));
            } elseif ((substr($strQuery, 0, 7) === 'UPDATE ') || (substr($strQuery, 0, 5) === 'CALL ')) {
                $this->exposeDebugText('UPDATE/CALL detected');
                $result = [
                    'rowsAffected' => $stmt->rowCount(),
                ];
                $this->exposeDebugText('Rows affected: ' . $this->getResultsAsJson($result));
            } elseif (($stmt->rowCount() == 0) && ($this->arrayConnectionDetails['DatabaseType'] != 'SQLite')) {
                $this->exposeDebugText('0 RowCount seen');
                $result = [
                    'rowsAffected' => 0,
                ];
                $this->exposeDebugText('Rows affected: ' . $this->getResultsAsJson($result));
            } else {
                $result = $stmt->fetchAll($strFetchingType);
            }
            $objReturn = $this->getResultsThroughVerification($result);
            $this->exposeDebugText('Return after verification:' . $this->getResultsAsJson($objReturn));
            return $objReturn;
        } catch (\PDOException $e) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            $this->strErrorText = vsprintf('Eroare întâlnită, mesajul de la serverul de date este [%s]', [
                $e->getMessage(),
            ]);
            $this->exposeDebugText('After Query execution: ' . $this->strErrorText);
        }
    }

    public function getStoredQuery($objClass, $label, $given_parameters = null): string
    {
        $sReturn = call_user_func_array([$objClass, 'setRightQuery'], [$label, $given_parameters]);
        if ($sReturn === false) {
            $this->exposeDebug('<b>' . $label . '</b> was not defined!');
        }
        return $sReturn;
    }

    public function setBatchDataIntoDatabase(string $strQuery, array $arrayDataToWrite, string $strWhich)
    {
        if (is_null($this->objConnection)) {
            return null;
        }
        $arrayParameterValues = [];
        try {
            $this->objConnection->beginTransaction();
            $stmt            = $this->objConnection->prepare($strQuery);
            $intRowsAffected = 0;
            foreach ($arrayDataToWrite as $intLine => $arrayValues) {
                $arrayParameterValues = [];
                foreach ($arrayValues as $strFieldName => $strValue) {
                    $strFieldName = ':' . str_replace(' ', '_', $strFieldName);
                    if ($strValue == 'NULL') {
                        $strValue = null;
                    }
                    $stmt->bindValue($strFieldName, $strValue);
                    $arrayParameterValues[$strFieldName] = chr(39) . $strValue . chr(39);
                }
                $stmt->execute();
                $intRowsAffected += $stmt->rowCount();
            }
            $this->exposeDebugText('Number of rows affected: ' . $intRowsAffected);
            $stmt->closeCursor();
            $this->objConnection->commit();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->exposeDebugText('Unique constraint case: ' . $e->getMessage());
            } else {
                $this->objConnection->rollBack();
                if (!headers_sent()) {
                    http_response_code(403);
                }
                $this->strErrorText = vsprintf('Error %s encoutered, message is [%s]', [
                    $e->getCode(),
                    $e->getMessage(),
                ]);
                $this->exposeDebugText('After Query execution: ' . $this->strErrorText);
                error_log('Parametrized query: ' . $strQuery);
                error_log('After Query execution: ' . $this->strErrorText);
                error_log('Attempted parameters: ' . json_encode($arrayParameterValues));
                error_log('Attempted query: ' . str_replace(array_keys($arrayParameterValues), array_values($arrayParameterValues), $strQuery));
            }
        }
    }

    public function setQueryWithoutAnyReturnExpected(string $strQuery): void
    {
        if (!is_null($this->objConnection)) {
            try {
                $this->objConnection->exec($strQuery);
            } catch (\PDOException $e) {
                $this->strErrorText = vsprintf('Error %s encoutered, message is [%s]', [
                    $e->getCode(),
                    $e->getMessage(),
                ]);
                $this->exposeDebugText('After Query execution: ' . $this->strErrorText);
            }
        }
    }
}
