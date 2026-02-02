<?php

namespace App\Services\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelReader
{
    /**
     * Get all sheet names from an Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function getSheetNames(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetNames = [];
        
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetNames[] = $sheet->getTitle();
        }
        
        return $sheetNames;
    }

    /**
     * Read a specific column from a sheet
     *
     * @param string $filePath
     * @param string $sheetName
     * @param string $columnLetter
     * @param int $startRow
     * @return array Array of ['row' => rowNumber, 'value' => cellValue]
     */
    public function readColumn(string $filePath, string $sheetName, string $columnLetter, int $startRow = 1): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName);
        
        if (!$sheet) {
            throw new \Exception("Sheet '{$sheetName}' not found in file.");
        }
        
        $columnIndex = $this->columnLetterToIndex($columnLetter);
        $highestRow = $sheet->getHighestRow();
        
        $data = [];
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCellByColumnAndRow($columnIndex, $row)->getValue();
            
            // Skip empty cells
            if ($cellValue !== null && trim((string)$cellValue) !== '') {
                $data[] = [
                    'row' => $row,
                    'value' => trim((string)$cellValue)
                ];
            }
        }
        
        return $data;
    }

    /**
     * Read two columns from a sheet (for person names)
     *
     * @param string $filePath
     * @param string $sheetName
     * @param string $firstNameColumn
     * @param string $lastNameColumn
     * @param int $startRow
     * @return array Array of ['row' => rowNumber, 'firstName' => value, 'lastName' => value]
     */
    public function readTwoColumns(string $filePath, string $sheetName, string $firstNameColumn, string $lastNameColumn, int $startRow = 1): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName);
        
        if (!$sheet) {
            throw new \Exception("Sheet '{$sheetName}' not found in file.");
        }
        
        $firstNameIndex = $this->columnLetterToIndex($firstNameColumn);
        $lastNameIndex = $this->columnLetterToIndex($lastNameColumn);
        $highestRow = $sheet->getHighestRow();
        
        $data = [];
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $firstName = $sheet->getCellByColumnAndRow($firstNameIndex, $row)->getValue();
            $lastName = $sheet->getCellByColumnAndRow($lastNameIndex, $row)->getValue();
            
            // Skip if both are empty
            if (($firstName !== null && trim((string)$firstName) !== '') || 
                ($lastName !== null && trim((string)$lastName) !== '')) {
                $data[] = [
                    'row' => $row,
                    'firstName' => trim((string)$firstName),
                    'lastName' => trim((string)$lastName)
                ];
            }
        }
        
        return $data;
    }

    /**
     * Convert column letter (A, B, AA, etc.) to numeric index (1-based)
     *
     * @param string $letter
     * @return int
     */
    public function columnLetterToIndex(string $letter): int
    {
        return Coordinate::columnIndexFromString(strtoupper($letter));
    }
}
