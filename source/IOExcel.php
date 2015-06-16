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

/**
 * Description of IOExcel
 *
 * @author Daniel Popiniuc
 */
trait IOExcel
{

    /**
     * Generate an Excel file from a given array
     *
     * @param array $inFeatures
     */
    protected function setArrayToExcel($inFeatures)
    {
        if (is_array($inFeatures)) {
            if (isset($inFeatures['filename'])) {
                if (is_string($inFeatures['filename'])) {
                    $inFeatures['filename'] = filter_var($inFeatures['filename'], FILTER_SANITIZE_STRING);
                } else {
                    return 'Provided filename is not a string!';
                }
            } else {
                return 'No filename provided';
            }
            if (!isset($inFeatures['worksheetname'])) {
                $inFeatures['worksheetname'] = 'Worksheet1';
            }
            if (!is_array($inFeatures['contentArray'])) {
                return 'No content!';
            }
        } else {
            return 'Missing parameters!';
        }
        $xlFileName  = str_replace('.xls', '', $inFeatures['filename']) . '.xlsx';
        // Create an instance
        $objPHPExcel = new \PHPExcel();
        // Set properties
        if (isset($inFeatures['properties'])) {
            if (isset($inFeatures['properties']['Creator'])) {
                $objPHPExcel->getProperties()->setCreator($inFeatures['properties']['Creator']);
            }
            if (isset($inFeatures['properties']['LastModifiedBy'])) {
                $objPHPExcel->getProperties()->setLastModifiedBy($inFeatures['properties']['LastModifiedBy']);
            }
            if (isset($inFeatures['properties']['description'])) {
                $objPHPExcel->getProperties()->setDescription($inFeatures['properties']['description']);
            }
            if (isset($inFeatures['properties']['subject'])) {
                $objPHPExcel->getProperties()->setSubject($inFeatures['properties']['subject']);
            }
            if (isset($inFeatures['properties']['title'])) {
                $objPHPExcel->getProperties()->setTitle($inFeatures['properties']['title']);
            }
        }
        // Add a worksheet to the file, returning an object to add data to
        $objPHPExcel->setActiveSheetIndex(0);
        if (is_array($inFeatures['contentArray'])) {
            $counter = 0;
            foreach ($inFeatures['contentArray'] as $key => $value) {
                $columnCounter = 0;
                if ($counter == 0) { // headers
                    foreach ($value as $key2 => $value2) {
                        $crCol          = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($crCol)->setAutoSize(true);
                        $crtCellAddress = $crCol . '1';
                        $objPHPExcel->getActiveSheet()->SetCellValue($crtCellAddress, $key2);
                        $objPHPExcel->getActiveSheet()->getStyle($crCol . '1')->getFill()->applyFromArray([
                            'type'       => 'solid',
                            'startcolor' => ['rgb' => 'CCCCCC'],
                            'endcolor'   => ['rgb' => 'CCCCCC'],
                        ]);
                        $objPHPExcel->getActiveSheet()->getStyle($crCol . '1')->applyFromArray([
                            'font' => [
                                'bold'  => true,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]);
                        $columnCounter += 1;
                    }
                    $objPHPExcel->getActiveSheet()->calculateColumnWidths();
                    $counter += 1;
                }
                $columnCounter = 0;
                foreach ($value as $key2 => $value2) {
                    if (strlen($value2) > 50) {
                        $objPHPExcel->getActiveSheet()->getStyle($crtCellAddress)->getAlignment()->setWrapText(true);
                    }
                    if ($counter == 1) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($crCol)->setAutoSize(false);
                    }
                    $crCol          = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
                    $crtCellAddress = $crCol . ($counter + 1);
                    if (($value2 == '') || ($value2 == '00:00:00') || ($value2 == '0')) {
                        $value2 = '';
                    }
                    if ((strlen($value2) == 8) && (strpos($value2, ':') !== false)) {
                        if ($value2 == '') {
                            $calculated_time_as_number = 0;
                        } else {
                            $calculated_time_as_number = $this->LocalTime2Seconds($value2) / 60 / 60 / 24;
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue($crtCellAddress, $calculated_time_as_number);
                        $objPHPExcel
                                ->getActiveSheet()
                                ->getStyle($crtCellAddress)
                                ->getNumberFormat()
                                ->setFormatCode('[h]:mm:ss;@');
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue($crtCellAddress, strip_tags($value2));
                    }
                    $columnCounter += 1;
                }
                $counter += 1;
            }
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle($inFeatures['worksheetname']);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation('portrait');
            //coresponding to A4
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(9);
            // freeze 1st top row
            $objPHPExcel->getActiveSheet()->freezePane('A2');
            // activate AutoFilter
            $objPHPExcel->getActiveSheet()->setAutoFilter('A1:' . $crCol . ($counter - 1));
            // margin is set in inches (0.7cm)
            $margin = 0.7 / 2.54;
            $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader($margin);
            $objPHPExcel->getActiveSheet()->getPageMargins()->setTop($margin * 2);
            $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom($margin);
            $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft($margin);
            $objPHPExcel->getActiveSheet()->getPageMargins()->setRight($margin);
            // add header content
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&F&RPage &P / &N');
            // repeat coloumn headings for every new page...
            $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
            // activate printing of gridlines
            $objPHPExcel->getActiveSheet()->setPrintGridlines(true);
            if (!in_array(PHP_SAPI, ['cli', 'cli-server'])) {
                // output the created content to the browser
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Pragma: private');
                header('Cache-control: private, must-revalidate');
                header('Content-Disposition: attachment;filename="' . $xlFileName . '"');
                header('Cache-Control: max-age=0');
            }
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            if (in_array(PHP_SAPI, ['cli', 'cli-server'])) {
                $objWriter->save($xlFileName);
            } else {
                $objWriter->save('php://output');
            }
            unset($objPHPExcel);
        }
    }

    /**
     * Using a lookup cache adds a slight memory overhead,
     * but boosts speed caching using a static within the method is faster than a class static,
     * though it's additional memory overhead
     *
     * @staticvar array $_indexCache
     * @param type $pColIndex
     * @return string
     */
    private static function setArrayToExcelStringFromColumnIndex($pColIndex = 0)
    {
        static $_indexCache = [];
        if (!isset($_indexCache[$pColIndex])) {
            // Determine column string
            if ($pColIndex < 26) {
                // 26 is the last # of column of 1 single letter
                $_indexCache[$pColIndex] = chr(65 + $pColIndex);
            } elseif ($pColIndex < 702) {
                // 702 is the last # of columns with 2 letters
                $_indexCache[$pColIndex] = chr(64 + ($pColIndex / 26)) . chr(65 + $pColIndex % 26);
            } else {
                // anything above 702 as # of column has 3 letters combination
                $_indexCache[$pColIndex] = chr(64 + (($pColIndex - 26) / 676))
                        . chr(65 + ((($pColIndex - 26) % 676) / 26))
                        . chr(65 + $pColIndex % 26);
            }
        }
        return $_indexCache[$pColIndex];
    }
}
