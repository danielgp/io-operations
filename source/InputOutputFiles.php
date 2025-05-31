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

trait InputOutputFiles
{
    use InputOutputFilePermissions;

    public function checkFileExistance($strFilePath, $strFileName)
    {
        $fName = $this->gluePathWithFileName($strFilePath, $strFileName);
        if (!file_exists($fName)) {
            throw new \RuntimeException(sprintf('File %s does not exists!', $fName));
        }
        return $fName;
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
        if (json_last_error() != JSON_ERROR_NONE) {
            $fName = $this->gluePathWithFileName($strFilePath, $strFileName);
            throw new \RuntimeException(sprintf('Unable to interpret JSON from %s file...', $fName));
        }
        return $arrayToReturn;
    }

    /**
     * Returns the details about Communicator (current) file
     * w/o any kind of verification of file existence
     *
     * @param string $fileGiven
     * @return array
     */
    public function getFileDetailsRaw($fileGiven)
    {
        $info              = new \SplFileInfo($fileGiven);
        $aFileBasicDetails = [
            'File Extension'         => $info->getExtension(),
            'File Group'             => $info->getGroup(),
            'File Inode'             => $info->getInode(),
            'File Link Target'       => ($info->isLink() ? $info->getLinkTarget() : '-'),
            'File Name'              => $info->getBasename('.' . $info->getExtension()),
            'File Name w. Extension' => $info->getFilename(),
            'File Owner'             => $info->getOwner(),
            'File Path'              => $info->getPath(),
            'Name'                   => $info->getRealPath(),
            'Type'                   => $info->getType(),
        ];
        $aDetails          = array_merge($aFileBasicDetails, $this->getFileDetailsRawStatistic($info, $fileGiven));
        ksort($aDetails);
        return $aDetails;
    }

    private function getFileDetailsRawStatistic(\SplFileInfo $info, $fileGiven)
    {
        return [
            'File is Dir'        => $info->isDir(),
            'File is Executable' => $info->isExecutable(),
            'File is File'       => $info->isFile(),
            'File is Link'       => $info->isLink(),
            'File is Readable'   => $info->isReadable(),
            'File is Writable'   => $info->isWritable(),
            'File Permissions'   => $this->explainPerms($info->getPerms()),
            'Size'               => $info->getSize(),
            'Sha1'               => sha1_file($fileGiven),
            'Sha256'             => hash_file('sha256', $fileGiven),
            'Timestamp Accessed' => $this->getFileTimes($info->getATime()),
            'Timestamp Changed'  => $this->getFileTimes($info->getCTime()),
            'Timestamp Modified' => $this->getFileTimes($info->getMTime()),
        ];
    }

    public function getFileEntireContent($strInputFile)
    {
        $contentInputFile = file($strInputFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if ($contentInputFile === false) {
            throw new \RuntimeException(sprintf('Unable to read file %s...', $strInputFile));
        }
        return $contentInputFile;
    }

    private function getFileTimes($timeAsPhpNumber)
    {
        return [
            'PHP number' => $timeAsPhpNumber,
            'SQL format' => date('Y-m-d H:i:s', $timeAsPhpNumber),
        ];
    }

    public function getMostRecentFile($strFileFirst, $strFileSecond)
    {
        $strFileNameFirst = $this->checkFileExistance('', $strFileFirst);
        $sReturn          = $strFileFirst;
        if (file_exists($strFileSecond)) {
            $infoFirst         = new \SplFileInfo($strFileNameFirst);
            $strFileNameSecond = $this->checkFileExistance('', $strFileSecond);
            $infoSecond        = new \SplFileInfo($strFileNameSecond);
            $sReturn           = $strFileFirst;
            if ($infoFirst->getMTime() <= $infoSecond->getMTime()) {
                $sReturn = $strFileSecond;
            }
        }
        return $sReturn;
    }

    public function getFileJsonContent($strFilePath, $strFileName)
    {
        $fName       = $this->checkFileExistance($strFilePath, $strFileName);
        $fJson       = $this->openFileSafelyAndReturnHandle($fName, 'r', 'read');
        $fileContent = fread($fJson, ((int) filesize($fName)));
        fclose($fJson);
        return $fileContent;
    }

    public function gluePathWithFileName($strFilePath, $strFileName)
    {
        $sReturn = $strFileName;
        if (strpos($strFileName, DIRECTORY_SEPARATOR) === false) {
            $sReturn = $strFilePath . DIRECTORY_SEPARATOR . $strFileName;
        }
        return $sReturn;
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
