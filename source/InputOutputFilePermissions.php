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

trait InputOutputFilePermissions
{
    /**
     * Returns an array with meaningful content of permissions
     *
     * @param int $permissionNumber
     * @return array
     */
    public function explainPerms($permissionNumber)
    {
        $firstFlag            = $this->matchFirstFlagSingle($permissionNumber);
        $permissionsString    = substr(sprintf('%o', $permissionNumber), -4);
        $numericalPermissions = $this->numericalPermissionsArray();
        return [
            'Permissions' => $permissionNumber,
            'Code'        => $permissionsString,
            'First'       => $firstFlag,
            'Overall'     => implode('', [
                $firstFlag['code'],
                $numericalPermissions[substr($permissionsString, 1, 1)]['code'],
                $numericalPermissions[substr($permissionsString, 2, 1)]['code'],
                $numericalPermissions[substr($permissionsString, 3, 1)]['code'],
            ]),
            'First'       => $firstFlag,
            'Owner'       => $numericalPermissions[substr($permissionsString, 1, 1)],
            'Group'       => $numericalPermissions[substr($permissionsString, 2, 1)],
            'World/Other' => $numericalPermissions[substr($permissionsString, 3, 1)],
        ];
    }

    private function firstFlagCodeArray()
    {
        return [
            0x8000 => '-',
            0x6000 => 'b',
            0x2000 => 'c',
            0x4000 => 'd',
            0xA000 => 'l',
            0x1000 => 'p',
            0xC000 => 's',
        ];
    }

    private function firstFlagDescriptionArray($codeFlag)
    {
        $aCodes = [
            '-' => 'Regular',
            'b' => 'Block special',
            'c' => 'Character special',
            'd' => 'Directory',
            'l' => 'Symbolic Link',
            'p' => 'FIFO pipe',
            's' => 'Socket',
            'w' => 'Whiteout',
        ];
        return ['code' => $codeFlag, 'name' => $aCodes[$codeFlag]];
    }

    private function matchFirstFlagSingle($permissionNumber)
    {
        $aCodes      = $this->firstFlagCodeArray();
        $matchedCode = 'w';
        foreach ($aCodes as $key => $value) {
            if (($permissionNumber & $key) == $key) {
                $matchedCode = $value;
            }
        }
        return $this->firstFlagDescriptionArray($matchedCode);
    }

    private function numericalPermissionsArray()
    {
        return [
            0 => ['code' => '---', 'name' => 'none'],
            1 => ['code' => '--x', 'name' => 'execute only'],
            2 => ['code' => '-w-', 'name' => 'write only'],
            3 => ['code' => '-wx', 'name' => 'write and execute'],
            4 => ['code' => 'r--', 'name' => 'read only'],
            5 => ['code' => 'r-x', 'name' => 'read and execute'],
            6 => ['code' => 'rw-', 'name' => 'read and write'],
            7 => ['code' => 'rwx', 'name' => 'read, write and execute'],
        ];
    }
}
