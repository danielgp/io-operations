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

    public function checkFileExistance($strFilePath, $strFileBaseName)
    {
        $fName = $strFilePath . DIRECTORY_SEPARATOR . $strFileBaseName . '.json';
        if (!file_exists($fName)) {
            throw new \RuntimeException(sprintf('File %s does not exists!', $fName));
        }
    }

    public function getFileJsonContent($fileBaseName)
    {
        $fName       = $this->checkFileExistance($fileBaseName);
        $fJson       = $this->openFileSafelyAndReturnHandle($fName, 'r', 'read');
        $fileContent = fread($fJson, ((int) filesize($fName)));
        fclose($fJson);
        return $fileContent;
    }

    public function openFileSafelyAndReturnHandle($strFileName, $strFileOperationChar, $strFileOperationName)
    {
        $fHandle = fopen($strFileName, $strFileOperationChar);
        if ($fHandle === false) {
            throw new \RuntimeException(''
                . sprintf('Unable to open file %s for %s purposes!', $strFileName, $strFileOperationName));
        }
        return $fHandle;
    }
}
