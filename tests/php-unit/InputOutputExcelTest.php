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

class InputOutputExcelTest extends \PHPUnit\Framework\TestCase
{
    use InputOutputExcel;
    
    #[\Override]
    protected function setUp(): void
    {
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'vendor', __DIR__)
            . DIRECTORY_SEPARATOR . 'autoload.php';
        require_once str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', 'source', __DIR__)
            . DIRECTORY_SEPARATOR . 'InputOutputExcel.php';
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelNoParameters()
    {
        $this->setArrayToExcel([]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersNoFileName()
    {
        $this->setArrayToExcel([
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
        $mock = $this->getMockForTrait(InputOutputExcel::class);
        $mock->setArrayToExcel([
            'Filename' => [],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNotArray()
    {
        $this->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => 1,
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNoName()
    {
        $this->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                [
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
                ]
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNameNotString()
    {
        $this->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                [
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
                ]
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsNoContent()
    {
        $mock = $this->getMockForTrait(InputOutputExcel::class);
        $mock->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                [
                    'Name' => 'Workshet1',
                ]
            ],
        ]);
    }

    /**
     * @expectedException \PhpOffice\PhpSpreadsheet\Exception
     */
    public function testSetArrayToExcelSomeParametersWorksheetsContentNotArray()
    {
        $this->setArrayToExcel([
            'Filename'   => 'test',
            'Worksheets' => [
                [
                    'Name'    => 'Workshet1',
                    'Content' => 'string',
                ]
            ],
        ]);
    }

    public function testSetArrayToExcel()
    {
        $strFileNameWithPath = str_replace('tests' . DIRECTORY_SEPARATOR . 'php-unit', '', __DIR__)
            . DIRECTORY_SEPARATOR . 'test_results.xlsx';
        $this->setArrayToExcel([
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
