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
     * manages the inputs checks
     *
     * @param array $inFeatures
     * @return null
     * @throws \PHPExcel_Exception
     */
    private function checkInputFeatures(array &$inFeatures)
    {
        if (!is_array($inFeatures)) {
            throw new \PHPExcel_Exception('Check 1: Missing parameters!');
        }
        if (!isset($inFeatures['Filename'])) {
            throw new \PHPExcel_Exception('Check 2: No filename provided!');
        } elseif (!is_string($inFeatures['Filename'])) {
            throw new \PHPExcel_Exception('Check 2.1: Provided filename is not a string!');
        }
        if (!isset($inFeatures['Worksheets'])) {
            throw new \PHPExcel_Exception('Check 3: No worksheets provided!');
        } elseif (!is_array($inFeatures['Worksheets'])) {
            throw new \PHPExcel_Exception('Check 3.1: Provided worksheets is not an array!');
        } else {
            foreach ($inFeatures['Worksheets'] as $key => $value) {
                if (!isset($value['Name'])) {
                    throw new \PHPExcel_Exception('Check 4: No Name was provided for the worksheet #' . $key . ' !');
                } elseif (!is_string($value['Name'])) {
                    throw new \PHPExcel_Exception('Check 4.1: The Name provided for the worksheet #'
                    . $key . ' is not a string!');
                }
                if (!isset($value['Content'])) {
                    throw new \PHPExcel_Exception('Check 5: No Content was provided for the worksheet #'
                    . $key . ' !');
                } elseif (!is_array($value['Content'])) {
                    throw new \PHPExcel_Exception('Check 4.1: The Content provided for the worksheet #'
                    . $key . ' is not an array!');
                }
            }
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
        $objPHPExcel = new \PHPExcel();
        if (isset($inFeatures['Properties'])) {
            $this->setExcelProperties($objPHPExcel, $inFeatures['Properties']);
        }
        foreach ($inFeatures['Worksheets'] as $key => $wkValue) {
            if ($key > 0) {
                $objPHPExcel
                        ->createSheet();
            }
            $objPHPExcel
                    ->setActiveSheetIndex($key);
            $objPHPExcel
                    ->getActiveSheet()
                    ->setTitle($wkValue['Name']);
            foreach ($wkValue['Content'] as $cntValue) {
                $rowIndex = $cntValue['StartingRowIndex'];
                foreach ($cntValue['ContentArray'] as $key => $value) {
                    if ($key == 0) {
                        $this->setExcelHeaderCellContent($objPHPExcel, [
                            'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                            'StartingRowIndex'    => $rowIndex,
                            'RowValues'           => array_keys($value),
                        ]);
                    }
                    $this->setExcelRowCellContent($objPHPExcel, [
                        'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                        'CurrentRowIndex'     => ($rowIndex + 1),
                        'RowValues'           => $value,
                    ]);
                    $rowIndex++;
                }
            }
            $this->setExcelWorksheetPagination($objPHPExcel);
            $this->setExcelWorksheetUsability($objPHPExcel, [
                'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                'HeaderRowIndex'      => $cntValue['StartingRowIndex'],
            ]);
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $inFeatures['Filename'] = filter_var($inFeatures['Filename'], FILTER_SANITIZE_STRING);
        if (!in_array(PHP_SAPI, ['cli', 'cli-server'])) {
            // output the created content to the browser and skip-it otherwise
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');
            header('Content-Disposition: attachment;filename="' . $inFeatures['Filename'] . '"');
            header('Cache-Control: max-age=0');
        }
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        if (in_array(PHP_SAPI, ['cli', 'cli-server'])) {
            $objWriter->save($inFeatures['Filename']);
        } else {
            $objWriter->save('php://output');
        }
        unset($objPHPExcel);
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
                $_indexCache[$pColIndex] = chr(64 + $pColIndex);
            } elseif ($pColIndex < 702) {
                // 702 is the last # of columns with 2 letters
                $_indexCache[$pColIndex] = chr(64 + ($pColIndex / 26)) . chr(64 + $pColIndex % 26);
            } else {
                // anything above 702 as # of column has 3 letters combination
                $_indexCache[$pColIndex] = chr(64 + (($pColIndex - 26) / 676))
                        . chr(64 + ((($pColIndex - 26) % 676) / 26))
                        . chr(64 + $pColIndex % 26);
            }
        }
        return $_indexCache[$pColIndex];
    }

    /**
     * Ouputs the header cells
     *
     * @param \PHPExcel $objPHPExcel
     * @param array $inputs
     */
    private function setExcelHeaderCellContent(\PHPExcel $objPHPExcel, array $inputs)
    {
        $columnCounter = $inputs['StartingColumnIndex'];
        foreach ($inputs['RowValues'] as $value2) {
            $crtCol = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
            $objPHPExcel
                    ->getActiveSheet()
                    ->getColumnDimension($crtCol)
                    ->setAutoSize(true);
            $objPHPExcel
                    ->getActiveSheet()
                    ->SetCellValue($crtCol . $inputs['StartingRowIndex'], $value2);
            $objPHPExcel
                    ->getActiveSheet()
                    ->getStyle($crtCol . $inputs['StartingRowIndex'])
                    ->getFill()
                    ->applyFromArray([
                        'type'       => 'solid',
                        'startcolor' => ['rgb' => 'CCCCCC'],
                        'endcolor'   => ['rgb' => 'CCCCCC'],
            ]);
            $objPHPExcel
                    ->getActiveSheet()
                    ->getStyle($crtCol . $inputs['StartingRowIndex'])
                    ->applyFromArray([
                        'font' => [
                            'bold'  => true,
                            'color' => ['rgb' => '000000'],
                        ]
            ]);
            $columnCounter++;
        }
        $objPHPExcel
                ->getActiveSheet()
                ->calculateColumnWidths();
    }

    /**
     * sets the Properties for the Excel file
     *
     * @param \PHPExcel $objPHPExcel
     * @param array $inProperties
     */
    private function setExcelProperties(\PHPExcel $objPHPExcel, array $inProperties)
    {
        if (isset($inProperties['Creator'])) {
            $objPHPExcel
                    ->getProperties()
                    ->setCreator($inProperties['Creator']);
        }
        if (isset($inProperties['LastModifiedBy'])) {
            $objPHPExcel
                    ->getProperties()
                    ->setLastModifiedBy($inProperties['LastModifiedBy']);
        }
        if (isset($inProperties['description'])) {
            $objPHPExcel
                    ->getProperties()
                    ->setDescription($inProperties['description']);
        }
        if (isset($inProperties['subject'])) {
            $objPHPExcel
                    ->getProperties()
                    ->setSubject($inProperties['subject']);
        }
        if (isset($inProperties['title'])) {
            $objPHPExcel
                    ->getProperties()
                    ->setTitle($inProperties['title']);
        }
    }

    /**
     * Outputs the content cells
     *
     * @param \PHPExcel $objPHPExcel
     * @param array $inputs
     */
    private function setExcelRowCellContent(\PHPExcel $objPHPExcel, array $inputs)
    {
        $columnCounter = $inputs['StartingColumnIndex'];
        foreach ($inputs['RowValues'] as $value2) {
            $crCol          = $this->setArrayToExcelStringFromColumnIndex($columnCounter);
            $objPHPExcel
                    ->getActiveSheet()
                    ->getColumnDimension($crCol)
                    ->setAutoSize(false);
            $crtCellAddress = $crCol . $inputs['CurrentRowIndex'];
            if (($value2 == '') || ($value2 == '00:00:00') || ($value2 == '0')) {
                $value2 = '';
            } elseif (((strlen($value2) == 8) || (strlen($value2) == 7)) && (strpos($value2, ':') !== false)) {
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

    /**
     * sets the Pagination
     *
     * @param \PHPExcel $objPHPExcel
     */
    private function setExcelWorksheetPagination(\PHPExcel $objPHPExcel)
    {
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation('portrait');
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(9); //coresponding to A4
        $margin = 0.7 / 2.54; // margin is set in inches (0.7cm)
        $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader($margin);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop($margin * 2);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom($margin);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft($margin);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight($margin);
        // add header content
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&F&RPage &P / &N');
        $objPHPExcel->getActiveSheet()->setPrintGridlines(true); // activate printing of gridlines
    }

    /**
     * Sets a few usability features
     *
     * @param \PHPExcel $objPHPExcel
     * @param type $inputs
     */
    private function setExcelWorksheetUsability(\PHPExcel $objPHPExcel, $inputs)
    {
        // repeat coloumn headings for every new page...
        $objPHPExcel
                ->getActiveSheet()
                ->getPageSetup()
                ->setRowsToRepeatAtTopByStartAndEnd(1, $inputs['HeaderRowIndex']);
        // activate AutoFilter
        $autoFilterArea = implode('', [
            $this->setArrayToExcelStringFromColumnIndex($inputs['StartingColumnIndex']),
            $inputs['HeaderRowIndex'],
            ':',
            $objPHPExcel->getActiveSheet()->getHighestDataColumn(),
            $objPHPExcel->getActiveSheet()->getHighestDataRow(),
        ]);
        $objPHPExcel
                ->getActiveSheet()
                ->setAutoFilter($autoFilterArea);
        // freeze 1st top row
        $objPHPExcel
                ->getActiveSheet()
                ->freezePane('A' . ($inputs['HeaderRowIndex'] + 1));
    }

    /**
     * Converts the time string given into native Excel format (number)
     *
     * @param string $RSQLtime
     * @return string
     */
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
