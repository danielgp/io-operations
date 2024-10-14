<?php

/**
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 - 2024 Daniel Popiniuc
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

namespace danielgp\io_operations;

/**
 * Description of InputOutputExcel
 *
 * @author Daniel Popiniuc
 */
trait InputOutputExcel
{

    private $objPHPExcel;

    /**
     * manages further inputs checks
     *
     * @param array $inFeaturesWorksheets Predefined array of attributes
     * @param array $check Checking messages
     * @return array|string[]
     */
    private function checkInputFeatureContent($inFeaturesWorksheets, $check)
    {
        $aReturn = [];
        foreach ($inFeaturesWorksheets as $key => $value) {
            if (!array_key_exists('Name', $value)) {
                $aReturn[] = sprintf($check['4'], $key);
            } elseif (!is_string($value['Name'])) {
                $aReturn[] = sprintf($check['4.1'], $key);
            }
            if (!array_key_exists('Content', $value)) {
                $aReturn[] = sprintf($check['5'], $key);
            } elseif (!is_array($value['Content'])) {
                $aReturn[] = sprintf($check['5.1'], $key);
            }
        }
        return $aReturn;
    }

    /**
     * manages the inputs checks
     *
     * @param array $inFeatures Predefined array of attributes
     * @return array|string[]
     */
    private function checkInputFeatures(array $inFeatures)
    {
        $aReturn = [];
        $check   = $this->internalCheckingErrorMessages();
        if ($inFeatures === []) {
            $aReturn[] = $check['1'];
        }
        if (!array_key_exists('Filename', $inFeatures)) {
            $aReturn[] = $check['2'];
        } elseif (!is_string($inFeatures['Filename'])) {
            $aReturn[] = $check['2.1'];
        }
        if (!array_key_exists('Worksheets', $inFeatures)) {
            $aReturn[] = $check['3'];
        } elseif (!is_array($inFeatures['Worksheets'])) {
            $aReturn[] = $check['3.1'];
        } elseif (array_key_exists('Worksheets', $inFeatures)) {
            $bReturn = $this->checkInputFeatureContent($inFeatures['Worksheets'], $check);
            if ($bReturn !== []) {
                $aReturn = array_merge($aReturn, $bReturn);
            }
        }
        if ($aReturn != []) {
            throw new \PhpOffice\PhpSpreadsheet\Exception(implode(', ', $aReturn));
        }
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
        $this->checkInputFeatures($inFeatures);
        $this->objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        if (isset($inFeatures['Properties'])) {
            $this->setExcelProperties($inFeatures['Properties']);
        }
        foreach ($inFeatures['Worksheets'] as $key => $wkValue) {
            if ($key > 0) {
                $this->objPHPExcel->createSheet();
            }
            $this->objPHPExcel->setActiveSheetIndex($key);
            $this->objPHPExcel->getActiveSheet()->setTitle($wkValue['Name']);
            foreach ($wkValue['Content'] as $cntValue) {
                $rowIndex = $cntValue['StartingRowIndex'];
                foreach ($cntValue['ContentArray'] as $key2 => $value2) {
                    if ($key2 == 0) {
                        $this->setExcelHeaderCellContent([
                            'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                            'StartingRowIndex'    => $rowIndex,
                            'RowValues'           => array_keys($value2),
                        ]);
                    }
                    $aRow = [
                        'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                        'CurrentRowIndex'     => ($rowIndex + 1),
                        'RowValues'           => $value2,
                    ];
                    if (array_key_exists('ContentFormatCode', $cntValue)) {
                        $aRow['ContentFormatCode'] = $cntValue['ContentFormatCode'];
                    }
                    if (array_key_exists('WrapText', $cntValue)) {
                        $aRow['WrapText'] = $cntValue['WrapText'];
                    }
                    if (array_key_exists('ContentFormatting', $cntValue)) {
                        $aRow['ContentFormatting'] = $cntValue['ContentFormatting'];
                    }
                    $this->setExcelRowCellContent($aRow);
                    $rowIndex++;
                }
                $this->setExcelWorksheetPagination();
                $this->setExcelWorksheetUsability([
                    'StartingColumnIndex' => $cntValue['StartingColumnIndex'],
                    'HeaderRowIndex'      => $cntValue['StartingRowIndex'],
                ]);
            }
        }
        $this->objPHPExcel->setActiveSheetIndex(0);
        $inFeatures['Filename'] = filter_var($inFeatures['Filename'], FILTER_SANITIZE_STRING);
        $bolForceSave           = false;
        if (array_key_exists('ForceSave', $inFeatures)) {
            $bolForceSave = $inFeatures['ForceSave'];
        }
        if (array_key_exists('FileFormat', $inFeatures)) {
            switch ($inFeatures['FileFormat']) {
                case 'Excel2007':
                default:
                    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
                    break;
                case 'Excel97-2003':
                    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xls($this->objPHPExcel);
                    break;
            }
        } else {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
        }
        if ($bolForceSave || in_array(PHP_SAPI, ['cli', 'cli-server'])) {
            $strFileNamePath = '';
            if (array_key_exists('FilePath', $inFeatures)) {
                $strFileNamePath = $inFeatures['FilePath'];
            }
            $objWriter->save($strFileNamePath . $inFeatures['Filename']);
        } else {
            $this->setForcedHeadersWhenNotCli($inFeatures['Filename']);
            $objWriter->save('php://output');
            ob_flush();
        }
        unset($this->objPHPExcel);
    }

    /**
     * Outputs the header cells
     *
     * @param array $inputs
     */
    private function setExcelHeaderCellContent(array $inputs)
    {
        $columnCounter = $inputs['StartingColumnIndex'];
        foreach ($inputs['RowValues'] as $value2) {
            $crtCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCounter);
            $this->objPHPExcel
                ->getActiveSheet()
                ->getColumnDimension($crtCol)
                ->setAutoSize(true);
            $this->objPHPExcel
                ->getActiveSheet()
                ->setCellValue($crtCol . $inputs['StartingRowIndex'], $value2);
            $this->objPHPExcel
                ->getActiveSheet()
                ->getStyle($crtCol . $inputs['StartingRowIndex'])
                ->getFill()
                ->applyFromArray([
                    'type'       => 'solid',
                    'startcolor' => ['rgb' => 'CCCCCC'],
                    'endcolor'   => ['rgb' => 'CCCCCC'],
            ]);
            $this->objPHPExcel
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
        $this->objPHPExcel
            ->getActiveSheet()
            ->calculateColumnWidths();
    }

    /**
     * sets the Properties for the Excel file
     *
     * @param array $inProperties
     */
    private function setExcelProperties(array $inProperties)
    {
        if (array_key_exists('Creator', $inProperties)) {
            $this->objPHPExcel->getProperties()->setCreator($inProperties['Creator']);
        }
        if (array_key_exists('LastModifiedBy', $inProperties)) {
            $this->objPHPExcel->getProperties()->setLastModifiedBy($inProperties['LastModifiedBy']);
        }
        if (array_key_exists('description', $inProperties)) {
            $this->objPHPExcel->getProperties()->setDescription($inProperties['description']);
        }
        if (array_key_exists('subject', $inProperties)) {
            $this->objPHPExcel->getProperties()->setSubject($inProperties['subject']);
        }
        if (array_key_exists('title', $inProperties)) {
            $this->objPHPExcel->getProperties()->setTitle($inProperties['title']);
        }
    }

    /**
     * Outputs the content cells
     *
     * @param array $inputs
     */
    private function setExcelRowCellContent(array $inputs)
    {
        $clsDate       = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        $columnCounter = $inputs['StartingColumnIndex'];
        $strRegExpr    = '/^(\d{1,4}[ \.\/\-][A-Z]{3,9}([ \.\/\-]\d{1,4})?|[A-Z]{3,9}[ \.\/\-]\d{1,4}'
            . '([ \.\/\-]\d{1,4})?|\d{1,4}[ \.\/\-]\d{1,4}([ \.\/\-]\d{1,4})?)( \d{1,2}:\d{1,2}'
            . '(:\d{1,2})?)?$/iu';
        $strDateFormat = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD;
        foreach ($inputs['RowValues'] as $key2 => $value2) {
            $crCol       = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCounter);
            $this->objPHPExcel
                ->getActiveSheet()
                ->getColumnDimension($crCol)
                ->setAutoSize(true);
            $crtCellAddr = $crCol . $inputs['CurrentRowIndex'];
            $cntLen      = strlen($value2);
            if (($value2 == '') || ($value2 == '00:00:00') || ($value2 == '0')) {
                $value2 = '';
            } elseif (in_array($cntLen, [7, 8, 9]) && (($cntLen - strlen(str_replace(':', '', $value2))) == 2)) {
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->SetCellValue($crtCellAddr, ($this->setLocalTime2Seconds($value2) / 60 / 60 / 24));
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->getStyle($crtCellAddr)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);
            } elseif (preg_match($strRegExpr, $value2) && !is_integer($value2) && !is_numeric($value2)) {
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->SetCellValue($crtCellAddr, $clsDate->stringToExcel($value2));
                if (!array_key_exists($key2, $inputs['ContentFormatCode'])) {
                    $inputs['ContentFormatCode'][$key2] = $strDateFormat;
                }
                $this->objPHPExcel
                    ->getActiveSheet()
                    ->getStyle($crtCellAddr)
                    ->getNumberFormat()
                    ->setFormatCode($inputs['ContentFormatCode'][$key2]);
            } else {
                if (array_key_exists('WrapText', $inputs) && in_array($key2, $inputs['WrapText'])) {
                    $this->objPHPExcel
                        ->getActiveSheet()
                        ->getStyle($crtCellAddr)
                        ->getAlignment()
                        ->setWrapText(true);
                }
                if (array_key_exists('ContentFormatting', $inputs) && array_key_exists($key2, $inputs['ContentFormatting'])) {
                    $this->objPHPExcel
                        ->getActiveSheet()
                        ->setCellValueExplicit($crtCellAddr, strip_tags($value2), $inputs['ContentFormatting'][$key2]);
                } else {
                    $this->objPHPExcel
                        ->getActiveSheet()
                        ->setCellValue($crtCellAddr, strip_tags($value2));
                }
            }
            $columnCounter += 1;
        }
    }

    /**
     * sets the Pagination
     *
     */
    private function setExcelWorksheetPagination()
    {
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation('portrait');
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(9); //coresponding to A4
        $margin = 0.7 / 2.54; // margin is set in inches (0.7cm)
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setHeader($margin);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setTop($margin * 2);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setBottom($margin);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setLeft($margin);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setRight($margin);
        // add header content
        $this->objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&F&RPage &P / &N');
        $this->objPHPExcel->getActiveSheet()->setPrintGridlines(true); // activate printing of gridlines
    }

    /**
     * Sets a few usability features
     *
     * @param array $inputs
     */
    private function setExcelWorksheetUsability($inputs)
    {
        // repeat coloumn headings for every new page...
        $this->objPHPExcel
            ->getActiveSheet()
            ->getPageSetup()
            ->setRowsToRepeatAtTopByStartAndEnd(1, $inputs['HeaderRowIndex']);
        // activate AutoFilter
        $autoFilterArea = implode('', [
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($inputs['StartingColumnIndex']),
            $inputs['HeaderRowIndex'],
            ':',
            $this->objPHPExcel->getActiveSheet()->getHighestDataColumn(),
            $this->objPHPExcel->getActiveSheet()->getHighestDataRow(),
        ]);
        $this->objPHPExcel
            ->getActiveSheet()
            ->setAutoFilter($autoFilterArea);
        // freeze 1st top row
        $this->objPHPExcel
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
     * @param string $intRsqlTime
     * @return string
     */
    private function setLocalTime2Seconds($intRsqlTime)
    {
        $sign = '';
        if (is_null($intRsqlTime) || ($intRsqlTime == '')) {
            $intRsqlTime = '00:00:00';
        } elseif (substr($intRsqlTime, 0, 1) == '-') {
            //extract negative sign and keep it separatly until ending
            $intRsqlTime = substr($intRsqlTime, 1, strlen($intRsqlTime) - 1);
            $sign        = '-';
        }
        $resultParts = [
            'seconds' => substr($intRsqlTime, -2),
            'minutes' => substr($intRsqlTime, -5, 2) * 60,
            'hours'   => substr($intRsqlTime, 0, strlen($intRsqlTime) - 6) * 60 * 60,
        ];
        return $sign . implode('', array_values($resultParts));
    }
}
