<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Popiniuc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace danielgp\IOExcel;

class IOExcelTest extends \PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass()
    {
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'vendor', __DIR__)
            . DIRECTORY_SEPARATOR . 'autoload.php';
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'IOExcel.php';
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelNoParameters()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersNoFileName()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Properties' => [
                'Creator' => 'PHPunit test'
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersFileNameNotString()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename' => [],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNotArray()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => 1,
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNoName()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                'Content' => [
                    [
                        'StartingColumnIndex' => 1,
                        'StartingRowIndex'    => 1,
                        'ContentArray'        => [
                            [
                                'First Column' => 10,
                            ],
                        ],
                    ]
                ]
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNameNotString()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                'Name'    => 123,
                'Content' => [
                    [
                        'StartingColumnIndex' => 1,
                        'StartingRowIndex'    => 1,
                        'ContentArray'        => [
                            [
                                'First Column' => 10,
                            ],
                        ],
                    ]
                ]
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNoContent()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                'Name' => 'Workshet1',
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsContentNotArray()
    {
        $mock = $this->getMockForTrait(IOExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                'Name'    => 'Workshet1',
                'Content' => 'string',
            ],
        ]);
    }

    public function testSetArrayToExcel()
    {
        $mock                = $this->getMockForTrait(IOExcel::class);
        $strFileNameWithPath = str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'results', __DIR__)
            . 'test.xlsx';
        $mock->setArrayToExcel([
            'Filename'   => $strFileNameWithPath,
            'Worksheets' => [
                [
                    'Name'    => 'Worksheet1',
                    'Content' => [
                        [
                            'StartingColumnIndex' => 1,
                            'StartingRowIndex'    => 1,
                            'ContentArray'        => [
                                [
                                    'First Column'              => 10,
                                    'Second Column'             => 20,
                                    'Third Column'              => 30,
                                    'Large Text Column'         => 'This is a very large text column content',
                                    'Zero Column as Blank'      => '0',
                                    'Timestamp Column'          => '15:00:00',
                                    'Timestamp Shorter Column'  => '1:00:00',
                                    'Timestamp Negative Column' => '-8:00:00',
                                ]
                            ],
                        ],
                    ],
                ],
                [
                    'Name'    => 'Worksheet2',
                    'Content' => [
                        [
                            'StartingColumnIndex' => 27,
                            'StartingRowIndex'    => 2,
                            'ContentArray'        => [
                                [
                                    'First Column' => 22,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'Name'    => 'Worksheet3',
                    'Content' => [
                        [
                            'StartingColumnIndex' => 703,
                            'StartingRowIndex'    => 1,
                            'ContentArray'        => [
                                [
                                    'First Column' => 'This content should be on a column w. 3 letters',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Properties' => [
                'Creator'        => 'PHPunit test Creator',
                'LastModifiedBy' => 'PHPunit test',
                'description'    => 'PHPunit test Description',
                'subject'        => 'PHPunit test Subject',
                'title'          => 'PHPunit test Title',
            ],
        ]);
        $this->assertFileExists($strFileNameWithPath);
    }
}
