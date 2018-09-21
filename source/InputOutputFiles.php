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

trait InputOutputFiles
{

    public function checkFileExistance($strFilePath, $strFileName)
    {
        $fName = $this->gluePathWithFileName($strFilePath, $strFileName);
        if (!file_exists($fName)) {
            throw new \RuntimeException(sprintf('File %s does not exists!', $fName));
        }
        return $fName;
    }
    
    public function getFileEntireContent($strInputFile)
    {
        $contentInputFile = file($strInputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if ($contentInputFile === false) {
            throw new \RuntimeException(sprintf('Unable to read file %s...', $strInputFile));
        }
        return $contentInputFile;
    }

    public function getFileJsonContent($strFilePath, $strFileName)
    {
        $fName       = $this->checkFileExistance($strFilePath, $strFileName);
        $fJson       = $this->openFileSafelyAndReturnHandle($fName, 'r', 'read');
        $fileContent = fread($fJson, ((int) filesize($fName)));
        fclose($fJson);
        return $fileContent;
    }
    
    /**
     * returns an array with non-standard holidays from a JSON file
     *
     * @param string $strFileName
     * @return mixed
     */
    public function getArrayFromJsonFile($strFilePath, $strFileName)
    {
        $jSonContent   = $this->getFileJsonContent($strFilePath, $strFileName);
        $arrayToReturn = json_decode($jSonContent, true);
        $jsonError     = json_last_error();
        if ($jsonError == JSON_ERROR_NONE) {
            return $arrayToReturn;
        } else {
            $fName = $this->gluePathWithFileName($strFilePath, $strFileName);
            throw new \RuntimeException(sprintf('Unable to interpret JSON from %s file...', $fName));
        }
    }
    
    public function gluePathWithFileName($strFilePath, $strFileName)
    {
        return $strFilePath . DIRECTORY_SEPARATOR . $strFileName;
    }

    public function openFileSafelyAndReturnHandle($strFileName, $strFileOperationChar, $strFileOperationName)
    {
        $fHandle = fopen($strFileName, $strFileOperationChar);
        if ($fHandle === false) {
            throw new \RuntimeException(sprintf('Unable to open file %s for %s purposes!'
            . '', $strFileName, $strFileOperationName));
        }
        return $fHandle;
    }
}
