<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 - 2018 Daniel Popiniuc
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
     * @param array $inFeatures Predefined array of attributes
     * @return null
     */
    private function checkInputFeatures(array $inFeatures)
    {
        $aReturn = [];
        $check   = $this->internalCheckingErrorMessages();
        if ($inFeatures === []) {
            $aReturn[] = $check['1'];
        }
        if (!isset($inFeatures['Filename'])) {
            $aReturn[] = $check['2'];
        } elseif (!is_string($inFeatures['Filename'])) {
            $aReturn[] = $check['2.1'];
        }
        if (!isset($inFeatures['Worksheets'])) {
            $aReturn[] = $check['3'];
        } elseif (!is_array($inFeatures['Worksheets'])) {
            $aReturn[] = $check['3.1'];
        } else {
            foreach ($inFeatures['Worksheets'] as $key => $value) {
                if (!isset($value['Name'])) {
                    $aReturn[] = $check['4'];
                } elseif (!is_string($value['Name'])) {
                    $aReturn[] = $check['4.1'];
                }
                if (!isset($value['Content'])) {
                    $aReturn[] = $check['5'];
                } elseif (!is_array($value['Content'])) {
                    $aReturn[] = $check['5.1'];
                }
            }
        }
        return $aReturn;
    }

    public function internalCheckingErrorMessages()
    {
        return [
            '1'   => 'Check 1: Missing parameters!',
            '2'   => 'Check 2: No filename provided!',
            '2.1' => 'Check 2.1: Provided filename is not a string!',
            '3'   => 'Check 3: No worksheets provided!',
            '3.1' => 'Check 3.1: Provided worksheets is not an array!',
            '4'   => 'Check 4: No Name was provided for the worksheet #%s !',
            '4.1' => 'Check 4.1: The Name provided for the worksheet #%s is not a string!',
            '5'   => 'Check 5: No Content was provided for the worksheet #%s !',
            '5.1' => 'Check 5.1: The Content provided for the worksheet #%s is not an array!',
        ];
    }

    /**
     * Generate an Excel file from a given array
     *
     * @param array $inFeatures Predefined array of attributes
     */
    public function setArrayToExcel(array $inFeatures)
    {
        $checkInputs = $this->checkInputFeatures($inFeatures);
        if ($checkInputs != []) {
            throw new \PhpOffice\PhpSpreadsheet\Exception(implode(', ', array_values($checkInputs)));
        }
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        if (isset($inFeatures['Properties'])) {
            $this->setExcelProperties($objPHPExcel, $inFeatures['Properties']);
        }
        foreach ($inFeatures['Worksheets'] as $key => $wkValue) {
            if ($key > 0) {
                $objPHPExcel->createSheet();
            }
            $objPHPExcel->setActiveSheetIndex($key);
            $objPHPExcel->getActiveSheet()->setTitle($wkValue['Name']);
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
            $this->setForcedHeadersWhenNotCli($inFeatures['Filename']);
        }
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
        if (in_array(PHP_SAPI, ['cli', 'cli-server'])) {
            $objWriter->save($inFeatures['Filename']);
        } else {
            $objWriter->save('php://output');
        }
        unset($objPHPExcel);
    }

    /**
     * Outputs the header cells
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     * @param array $inputs
     */
    private function setExcelHeaderCellContent(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel, array $inputs)
    {
        $columnCounter = $inputs['StartingColumnIndex'];
        foreach ($inputs['RowValues'] as $value2) {
            $crtCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCounter);
            if ($columnCounter > 702) {
                echo $columnCounter . ' => ' . $crtCol . PHP_EOL;
            }
            $objPHPExcel
                ->getActiveSheet()
                ->getColumnDimension($crtCol)
                ->setAutoSize(true);
            $objPHPExcel
                ->getActiveSheet()
                ->setCellValue($crtCol . $inputs['StartingRowIndex'], $value2);
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
                    ]
            );
            $columnCounter++;
        }
        $objPHPExcel
            ->getActiveSheet()
            ->calculateColumnWidths();
    }

    /**
     * sets the Properties for the Excel file
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     * @param array $inProperties
     */
    private function setExcelProperties(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel, array $inProperties)
    {
        if (array_key_exists('Creator', $inProperties)) {
            $objPHPExcel->getProperties()->setCreator($inProperties['Creator']);
        }
        if (array_key_exists('LastModifiedBy', $inProperties)) {
            $objPHPExcel->getProperties()->setLastModifiedBy($inProperties['LastModifiedBy']);
        }
        if (array_key_exists('description', $inProperties)) {
            $objPHPExcel->getProperties()->setDescription($inProperties['description']);
        }
        if (array_key_exists('subject', $inProperties)) {
            $objPHPExcel->getProperties()->setSubject($inProperties['subject']);
        }
        if (array_key_exists('title', $inProperties)) {
            $objPHPExcel->getProperties()->setTitle($inProperties['title']);
        }
    }

    /**
     * Outputs the content cells
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     * @param array $inputs
     */
    private function setExcelRowCellContent(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel, array $inputs)
    {
        $columnCounter = $inputs['StartingColumnIndex'];
        foreach ($inputs['RowValues'] as $value2) {
            $crCol          = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCounter);
            $objPHPExcel
                ->getActiveSheet()
                ->getColumnDimension($crCol)
                ->setAutoSize(false);
            $crtCellAddress = $crCol . $inputs['CurrentRowIndex'];
            $cntLen         = strlen($value2);
            if (($value2 == '') || ($value2 == '00:00:00') || ($value2 == '0')) {
                $value2 = '';
            } elseif (in_array($cntLen, [7, 8, 9]) && (($cntLen - strlen(str_replace(':', '', $value2))) == 2)) {
                $objPHPExcel
                    ->getActiveSheet()
                    ->SetCellValue($crtCellAddress, ($this->setLocalTime2Seconds($value2) / 60 / 60 / 24));
                $objPHPExcel
                    ->getActiveSheet()
                    ->getStyle($crtCellAddress)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);
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
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     */
    private function setExcelWorksheetPagination(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel)
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
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel
     * @param type $inputs
     */
    private function setExcelWorksheetUsability(\PhpOffice\PhpSpreadsheet\Spreadsheet $objPHPExcel, $inputs)
    {
        // repeat coloumn headings for every new page...
        $objPHPExcel
            ->getActiveSheet()
            ->getPageSetup()
            ->setRowsToRepeatAtTopByStartAndEnd(1, $inputs['HeaderRowIndex']);
        // activate AutoFilter
        $autoFilterArea = implode('', [
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($inputs['StartingColumnIndex']),
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

    private function setForcedHeadersWhenNotCli($strFileName)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Pragma: private');
        header('Cache-control: private, must-revalidate');
        header('Content-Disposition: attachment;filename="' . $strFileName . '"');
        header('Cache-Control: max-age=0');
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
