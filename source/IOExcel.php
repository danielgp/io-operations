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

    private function checkInputFeatures(array $inFeatures)
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
                return 'No worksheetname provided';
            }
            if (!is_array($inFeatures['contentArray'])) {
                return 'No content!';
            }
        } else {
            return 'Missing parameters!';
        }
        return null;
    }

    /**
     * Generate an Excel file from a given array
     *
     * @param array $inFeatures
     */
    protected function setArrayToExcel(array $inFeatures)
    {
        $checkInputs = $this->checkInputFeatures($inFeatures);
        if (!is_null($checkInputs)) {
            echo '<hr/>' . $checkInputs . '<hr/>';
            return '';
        }
        $xlFileName  = str_replace('.xls', '', $inFeatures['filename']) . '.xlsx';
        // Create an instance
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $this->setExcelProperties($objPHPExcel, $inFeatures['properties']);
        // Add a worksheet to the file, returning an object to add data to
        $objPHPExcel->setActiveSheetIndex(0);
        if (is_array($inFeatures['contentArray'])) {
            foreach ($inFeatures['contentArray'] as $key => $value) {
                if ($key == 0) { // headers
                    $this->setExcelCellHeader($objPHPExcel, array_keys($value));
                    $columnCounter = count($value) - 1;
                    $crCol         = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
                }
                $this->setExcelCellContent($objPHPExcel, ($key + 1), $value);
            }
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle($inFeatures['worksheetname']);
            $this->setExcelWorksheetLayout($objPHPExcel, $crCol, count($inFeatures['contentArray']));
            if (!in_array(PHP_SAPI, ['cli', 'cli-server'])) {
                // output the created content to the browser and skip-it otherwise
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

    private function setExcelCellContent(\PHPExcel $objPHPExcel, $counter, $value)
    {
        $columnCounter = 0;
        foreach ($value as $value2) {
            $crCol          = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
            $crtCellAddress = $crCol . ($counter + 1);
            if (strlen($value2) > 50) {
                $objPHPExcel->getActiveSheet()->getStyle($crtCellAddress)->getAlignment()->setWrapText(true);
            }
            if ($counter == 1) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($crCol)->setAutoSize(false);
            }
            if (($value2 == '') || ($value2 == '00:00:00') || ($value2 == '0')) {
                $value2 = '';
            } elseif ((strlen($value2) == 8) && (strpos($value2, ':') !== false)) {
                $objPHPExcel
                        ->getActiveSheet()
                        ->SetCellValue($crtCellAddress, ($this->setLocalTime2Seconds($value2) / 60 / 60 / 24));
                $objPHPExcel
                        ->getActiveSheet()
                        ->getStyle($crtCellAddress)
                        ->getNumberFormat()
                        ->setFormatCode('[H]:mm:ss;@');
            } else {
                $objPHPExcel
                        ->getActiveSheet()
                        ->SetCellValue($crtCellAddress, strip_tags($value2));
            }
            $columnCounter += 1;
        }
    }

    private function setExcelCellHeader(\PHPExcel $objPHPExcel, $value)
    {
        $columnCounter = 0;
        foreach ($value as $value2) {
            $crCol          = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
            $objPHPExcel->getActiveSheet()->getColumnDimension($crCol)->setAutoSize(true);
            $crtCellAddress = $crCol . '1';
            $objPHPExcel->getActiveSheet()->SetCellValue($crtCellAddress, $value2);
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
    }

    private function setExcelProperties(\PHPExcel $objPHPExcel, $inProperties)
    {
        if (isset($inProperties)) {
            if (isset($inProperties['Creator'])) {
                $objPHPExcel->getProperties()->setCreator($inProperties['Creator']);
            }
            if (isset($inProperties['LastModifiedBy'])) {
                $objPHPExcel->getProperties()->setLastModifiedBy($inProperties['LastModifiedBy']);
            }
            if (isset($inProperties['description'])) {
                $objPHPExcel->getProperties()->setDescription($inProperties['description']);
            }
            if (isset($inProperties['subject'])) {
                $objPHPExcel->getProperties()->setSubject($inProperties['subject']);
            }
            if (isset($inProperties['title'])) {
                $objPHPExcel->getProperties()->setTitle($inProperties['title']);
            }
        }
    }

    private function setExcelWorksheetLayout(\PHPExcel $objPHPExcel, $crCol, $counter)
    {
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation('portrait');
        //coresponding to A4
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(9);
        // freeze 1st top row
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        // activate AutoFilter
        $objPHPExcel->getActiveSheet()->setAutoFilter('A1:' . $crCol . $counter);
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
    }

    private function setLocalTime2Seconds($RSQLtime)
    {
        $sign = '';
        if (is_null($RSQLtime) || ($RSQLtime == '')) {
            $RSQLtime = '00:00:00';
        } elseif (substr($RSQLtime, 0, 1) == '-') {
            //extract negative sign and keep it separatly until ending
            $RSQLtime = substr($RSQLtime, 1, strlen($RSQLtime) - 1);
            $sign     = '-';
        }
        $resultParts = [
            'seconds' => substr($RSQLtime, -2),
            'minutes' => substr($RSQLtime, -5, 2) * 60,
            'hours'   => substr($RSQLtime, 0, strlen($RSQLtime) - 6) * 60 * 60,
        ];
        return $sign . implode('', array_values($resultParts));
    }
}
