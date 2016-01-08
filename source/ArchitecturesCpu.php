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

    private function listOfCpuArchitecturesX86()
    {
        return [
            'arm'  => [
                'bits'          => 32,
                'nick'          => 'ARM',
                'name'          => 'Advanced RISC Machine',
                'name and bits' => 'Advanced RISC Machine (32 bit)',
            ],
            'ia32' => [
                'bits'          => 32,
                'nick'          => 'x86',
                'name'          => 'Intel x86',
                'name and bits' => 'Intel x86 (32 bit)',
            ],
            'ppc'  => [
                'bits'          => 32,
                'name'          => 'Power PC',
                'name and bits' => 'Power PC (32 bit)',
            ],
        ];
    }

    private function listOfCpuArchitecturesX64()
    {
        return [
            'amd64' => [
                'bits'          => 64,
                'nick'          => 'x64',
                'name'          => 'AMD/Intel x64',
                'name and bits' => 'AMD/Intel x64 (64 bit)',
            ],
            'arm64' => [
                'bits'          => 64,
                'nick'          => 'ARM64',
                'name'          => 'Advanced RISC Machine',
                'name and bits' => 'Advanced RISC Machine (64 bit)',
            ],
            'ppc64' => [
                'bits'          => 64,
                'name'          => 'Power PC',
                'name and bits' => 'Power PC (64 bit)',
            ],
            'sun'   => [
                'bits'          => 64,
                'name'          => 'Sun Sparc',
                'name and bits' => 'Sun Sparc (64 bit)',
            ],
        ];
    }

    /**
     * Holds a list of Known CPU Srchitectures as array
     *
     * @return array
     */
    protected function listOfKnownCpuArchitectures()
    {
        $arc = array_merge($this->listOfCpuArchitecturesX86(), $this->listOfCpuArchitecturesX64());
        ksort($arc);
        return $arc;
    }
}
