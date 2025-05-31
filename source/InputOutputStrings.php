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

trait InputOutputStrings
{
    protected function applyStringManipulations($inString, $ruleDetails)
    {
        $outString = $inString;
        if (is_array($ruleDetails)) {
            $outString = $this->applyStringManipulationsArray($outString, $ruleDetails);
        } else {
            $outString = $this->applyStringManipulationsSingle($outString, $ruleDetails);
        }
        return $outString;
    }

    private function applyStringManipulationsArray($inString, $arrayRuleDetails)
    {
        $outString = $inString;
        foreach ($arrayRuleDetails as $strRuleCurrentDetail) {
            $outString = $this->applyStringManipulationsSingle($outString, $strRuleCurrentDetail);
        }
        return $outString;
    }

    private function applyStringManipulationsSingle($inString, $strManipulationRule)
    {
        $outStr = $inString;
        switch($strManipulationRule) {
            case 'replace numeric sequence followed by single space':
                $outStr = preg_replace('#([0-9]+ )#', '', $outStr);
                break;
            case 'trim':
                $outStr = trim($outStr);
                break;
            default:
                $aryOut = $this->knownStringPatterns($strManipulationRule);
                if ($aryOut['Key Exists']) {
                    $outStr = str_ireplace($aryOut['Attributes']['Original'], $aryOut['Attributes']['Final'], $outStr);
                }
                break;
        }
        return $outStr;
    }

    protected function cleanString($strInput)
    {
        return str_replace([' ', "\n", "\r"], '', $strInput);
    }

    private function knownStringPatterns($strIdentifier)
    {
        $arrayStandard = [
            'remove EOL Unix'                        => ['Original' => chr(13), 'Final' => '',],
            'remove EOL Windows'                     => ['Original' => chr(10) . chr(13), 'Final' => '',],
            'remove colon'                           => ['Original' => ':', 'Final' => '',],
            'remove comma followed by double quotes' => ['Original' => ',"', 'Final' => '',],
            'remove dot'                             => ['Original' => '.', 'Final' => '',],
            'remove double quotes'                   => ['Original' => '"', 'Final' => '',],
            'remove double quotes followed by comma' => ['Original' => '",', 'Final' => '',],
            'remove pipeline'                        => ['Original' => '|', 'Final' => '',],
            'remove semicolon'                       => ['Original' => ';', 'Final' => '',],
            'remove slash'                           => ['Original' => '/', 'Final' => '',],
            'replace dash with space'                => ['Original' => '-', 'Final' => ' ',],
            'replace comma with dot'                 => ['Original' => ',', 'Final' => '.',],
            'replace circumflex accent with dot'     => ['Original' => '^', 'Final' => '.',],
        ];
        $bolKeyExists  = array_key_exists($strIdentifier, $arrayStandard);
        return [
            'Key Exists' => $bolKeyExists,
            'Attributes' => ($bolKeyExists ? $arrayStandard[$strIdentifier] : null),
        ];
    }

    protected function setSeparator($strCharacter)
    {
        return str_repeat($strCharacter, 90);
    }

    protected function setTab($intMultiplier = 1)
    {
        return str_repeat(' ', (4 * $intMultiplier));
    }
}
