<?php

namespace App\Services\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExporter
{
    /**
     * Export File 2 with highlighted matched rows (no modifications to original file)
     *
     * @param string $file2Path Original File 2 path
     * @param string $sheetName Sheet name to export
     * @param array $results Match results
     * @param int $threshold Match threshold
     * @return string Path to generated file
     */
    public function exportWithHighlighting(string $file2Path, string $sheetName, array $results, int $threshold): string
    {
        // Load the original File 2
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file2Path);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            throw new \Exception("Sheet '{$sheetName}' not found in file.");
        }

        // Create a map of File 2 row numbers to match scores
        $rowScores = [];
        foreach ($results as $result) {
            $rowScores[$result['file2_row']] = $result['score'];
        }

        // Get the highest row number in the sheet
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Apply highlighting to matched rows only
        foreach ($rowScores as $rowNumber => $score) {
            if ($score >= $threshold) {
                // Only highlight rows that meet the threshold
                // Apply green background to the entire row
                $rowRange = 'A' . $rowNumber . ':' . $highestColumn . $rowNumber;
                
                $sheet->getStyle($rowRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE'); // Light green
            }
        }

        // Save to temporary file
        $filename = 'matched_file_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
