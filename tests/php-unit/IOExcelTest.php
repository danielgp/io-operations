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
class CommonCodeTest extends PHPUnit_Framework_TestCase
{

    use \danielgp\IOExcel\IOExcel;

    public function testSetArrayToExcel()
    {
        $this->setArrayToExcel([
            'Filename'   => 'C:/Windows/Temp/test.xlsx',
            'Worksheets' => [
                [
                    'Name'    => 'Worksheet1',
                    'Content' => [
                        [
                            'StartingColumnIndex' => 1,
                            'StartingRowIndex'    => 1,
                            'ContentArray'        => [
                                [
                                    'First Column'             => 10,
                                    'Second Column'            => 20,
                                    'Third Column'             => 30,
                                    'Large Text Column'        => 'This is a very large text column content',
                                    'Zero Column as Blank'     => '0',
                                    'Timestamp Column'         => '15:00:00',
                                    'Timestamp Shorter Column' => '1:00:00',
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
            ],
            'Properties' => [
                'Creator'        => 'PHPunit test',
                'LastModifiedBy' => 'PHPunit test',
                'description'    => 'PHPunit test description',
                'subject'        => 'PHPunit test subject',
                'title'          => 'PHPunit test title',
            ],
        ]);
        $this->assertFileExists('C:/Windows/Temp/test.xlsx');
    }
}
