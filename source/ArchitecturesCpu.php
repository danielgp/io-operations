<?php

/*
 * The MIT License
 *
 * Copyright 2016 Daniel Popiniuc
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

namespace danielgp\browser_agent_info;

trait ArchiecturesCpu
{

    private function addStdandardTrunk($inArray, $trnkArray)
    {
        $outArray = [];
        foreach ($inArray as $key => $value) {
            $value2         = array_merge($value, $trnkArray);
            ksort($value2);
            $outArray[$key] = $value2;
        }
        return $outArray;
    }

    private function detectX64Architecture($usrA)
    {
        $idn      = ['amd64;', 'AMD64', 'x86_64;'];
        $byPatern = ($this->eVlByAltSubstrings($idn, $usrA, 'ia32') === 'ia32');
        $sReturn  = '';
        if ($byPatern || (strpos($usrA, 'Win64;') && strpos($usrA, 'x64;'))) {
            $sReturn = 'amd64';
        }
        return $sReturn;
    }

    private function detectX64ArchitectureByOs($usrA)
    {
        $identifiers = ['x86_64;', 'x86-64;', 'Win64;', 'x64;', 'amd64;', 'AMD64', 'WOW64', 'x64_64;'];
        return $this->eVlByAltSubstrings($identifiers, $usrA, 'amd64');
    }

    private function detectX86Architecture($usrA)
    {
        $identifiers = ['i386', 'i586', 'ia32;', 'WOW64', 'x86;'];
        return $this->eVlByAltSubstrings($identifiers, $usrA, 'ia32');
    }

    private function enrichParticularArrayValue($inSingleArray)
    {
        $outCompleteArray = [];
        foreach ($inSingleArray as $keyS => $valueS) {
            $trnkArray                  = [];
            $trnkArray['name and bits'] = $valueS['name'] . ' (' . $valueS['bits'] . ' bit)';
            $value2                     = array_merge($valueS, $trnkArray);
            ksort($value2);
            $outCompleteArray[$keyS]    = $value2;
        }
        return $outCompleteArray;
    }

    private function eVlByAltSubstrings($inArray, $inSubject, $vlFinal)
    {
        $sReturn          = '';
        $regularExpresion = '/' . implode('|', $inArray) . '/';
        if (preg_match($regularExpresion, $inSubject) === 1) {
            $sReturn = $vlFinal;
        }
        return $sReturn;
    }

    private function listOfCpuArchitecturesX86()
    {
        $lst = [
            'arm'  => [
                'nick' => 'ARM',
                'name' => 'Advanced RISC Machine',
            ],
            'ia32' => [
                'nick' => 'x86',
                'name' => 'Intel x86',
            ],
            'ppc'  => [
                'nick' => 'PPC',
                'name' => 'Power PC',
            ],
        ];
        return $this->enrichParticularArrayValue($this->addStdandardTrunk($lst, ['bits' => 32]));
    }

    protected function getArchitectureFromUserAgentBrowser($usrA)
    {
        $knownArchitectures = $this->listOfKnownCpuArchitectures();
        if ($this->detectX86Architecture($usrA) == 'ia32') {
            $aReturn = $knownArchitectures['ia32'];
        } elseif (strpos($usrA, 'Android;')) {
            $aReturn = $knownArchitectures['arm'];
        } elseif ($this->detectX64Architecture($usrA) == 'amd64') {
            $aReturn = $knownArchitectures['amd64'];
        } else {
            $aReturn = $knownArchitectures['ia32'];
        }
        return $aReturn;
    }

    protected function getArchitectureFromUserAgentOperatingSystem($usrA)
    {
        $knowCpuArchitecture = $this->listOfKnownCpuArchitectures();
        if ($this->detectX64ArchitectureByOs($usrA) == 'amd64') {
            $aReturn = $knowCpuArchitecture['amd64'];
        } elseif (strpos($usrA, 'Android;')) {
            $aReturn = $knowCpuArchitecture['arm'];
        } else {
            $aReturn = $knowCpuArchitecture['ia32'];
        }
        return $aReturn;
    }

    private function listOfCpuArchitecturesX64()
    {
        $lst = [
            'amd64' => [
                'nick' => 'x64',
                'name' => 'AMD/Intel x64',
            ],
            'arm64' => [
                'nick' => 'ARM64',
                'name' => 'Advanced RISC Machine',
            ],
            'ppc64' => [
                'nick' => 'PPC64',
                'name' => 'Power PC',
            ],
            'sun'   => [
                'nick' => 'Sparc',
                'name' => 'Sun Sparc',
            ],
        ];
        return $this->enrichParticularArrayValue($this->addStdandardTrunk($lst, ['bits' => 64]));
    }

    /**
     * Holds a list of Known CPU Srchitectures as array
     *
     * @return array
     */
    private function listOfKnownCpuArchitectures()
    {
        $arc = array_merge($this->listOfCpuArchitecturesX86(), $this->listOfCpuArchitecturesX64());
        ksort($arc);
        return $arc;
    }
}
