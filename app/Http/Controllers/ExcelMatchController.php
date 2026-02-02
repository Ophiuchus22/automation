<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadExcelRequest;
use App\Http\Requests\RunMatchRequest;
use App\Services\Excel\ExcelReader;
use App\Services\Matching\Matcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExcelMatchController extends Controller
{
    protected $excelReader;
    protected $matcher;

    public function __construct(ExcelReader $excelReader, Matcher $matcher)
    {
        $this->excelReader = $excelReader;
        $this->matcher = $matcher;
    }

    /**
     * Show the upload form
     */
    public function index()
    {
        return view('match.upload');
    }

    /**
     * Handle file uploads and show configuration form
     */
    public function upload(UploadExcelRequest $request)
    {
        try {
            // Store uploaded files
            $file1 = $request->file('file1');
            $file2 = $request->file('file2');

            $file1Extension = $file1->getClientOriginalExtension();
            $file2Extension = $file2->getClientOriginalExtension();

            $file1Name = 'file1_' . Str::random(10) . '.' . $file1Extension;
            $file2Name = 'file2_' . Str::random(10) . '.' . $file2Extension;

            $file1Path = $file1->storeAs('uploads', $file1Name);
            $file2Path = $file2->storeAs('uploads', $file2Name);

            // Get full paths
            $file1FullPath = Storage::path($file1Path);
            $file2FullPath = Storage::path($file2Path);

            // Get sheet names
            $file1Sheets = $this->excelReader->getSheetNames($file1FullPath);
            $file2Sheets = $this->excelReader->getSheetNames($file2FullPath);

            return view('match.configure', [
                'file1_path' => $file1Path,
                'file2_path' => $file2Path,
                'file1_sheets' => $file1Sheets,
                'file2_sheets' => $file2Sheets,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error processing files: ' . $e->getMessage()]);
        }
    }

    /**
     * Run the matching algorithm
     */
    public function run(RunMatchRequest $request)
    {
        try {
            $file1FullPath = Storage::path($request->file1_path);
            $file2FullPath = Storage::path($request->file2_path);

            $matchMode = $request->match_mode;
            $threshold = $request->threshold ?? 85;
            $showPossible = $request->boolean('show_possible');
            $file1StartRow = $request->file1_start_row ?? 1;
            $file2StartRow = $request->file2_start_row ?? 1;

            // Read File 1 data
            $file1RawData = $this->excelReader->readColumn(
                $file1FullPath,
                $request->file1_sheet,
                $request->file1_column,
                $file1StartRow
            );

            // Prepare File 1 data with normalization
            $file1Data = [];
            foreach ($file1RawData as $item) {
                if ($matchMode === 'person') {
                    $key = $this->matcher->extractPersonKeyFromFile1($item['value']);
                } else {
                    $key = $this->matcher->normalizeText($item['value'], true);
                }

                $file1Data[] = [
                    'row' => $item['row'],
                    'key' => $key,
                    'original' => $item['value']
                ];
            }

            // Read File 2 data
            if ($matchMode === 'person') {
                $file2RawData = $this->excelReader->readTwoColumns(
                    $file2FullPath,
                    $request->file2_sheet,
                    $request->file2_first_name_column,
                    $request->file2_last_name_column,
                    $file2StartRow
                );

                // Prepare File 2 data
                $file2Data = [];
                foreach ($file2RawData as $item) {
                    $key = $this->matcher->buildPersonKey($item['firstName'], $item['lastName']);
                    $file2Data[] = [
                        'row' => $item['row'],
                        'key' => $key,
                        'original' => trim($item['firstName'] . ' ' . $item['lastName'])
                    ];
                }
            } else {
                $file2RawData = $this->excelReader->readColumn(
                    $file2FullPath,
                    $request->file2_sheet,
                    $request->file2_column,
                    $file2StartRow
                );

                // Prepare File 2 data
                $file2Data = [];
                foreach ($file2RawData as $item) {
                    $key = $this->matcher->normalizeText($item['value'], true);
                    $file2Data[] = [
                        'row' => $item['row'],
                        'key' => $key,
                        'original' => $item['value']
                    ];
                }
            }

            // Run matching
            $results = $this->matcher->matchFiles($file1Data, $file2Data, $matchMode, $threshold, $showPossible);

            // Calculate statistics
            $totalRows = count($file2Data);
            $matchedRows = count(array_filter($results, fn($r) => $r['score'] >= $threshold));
            $possibleMatches = count(array_filter($results, fn($r) => $r['score'] >= 70 && $r['score'] < $threshold));

            // Store results in session for download
            $downloadToken = Str::random(32);
            session(['match_results_' . $downloadToken => $results]);

            // Clean up uploaded files
            Storage::delete($request->file1_path);
            Storage::delete($request->file2_path);

            return view('match.results', [
                'results' => $results,
                'total_rows' => $totalRows,
                'matched_rows' => $matchedRows,
                'possible_matches' => $possibleMatches,
                'threshold' => $threshold,
                'download_token' => $downloadToken,
            ]);
        } catch (\Exception $e) {
            // Clean up files on error
            if ($request->file1_path) {
                Storage::delete($request->file1_path);
            }
            if ($request->file2_path) {
                Storage::delete($request->file2_path);
            }

            return back()->withErrors(['error' => 'Error running match: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Download results as CSV
     */
    public function download(Request $request, string $token)
    {
        $results = session('match_results_' . $token);

        if (!$results) {
            abort(404, 'Results not found or expired.');
        }

        $filename = 'match_results_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'File 2 Row',
                'File 2 Original Value',
                'File 2 Normalized',
                'File 1 Row',
                'File 1 Original Value',
                'Match Type',
                'Score'
            ]);

            // CSV data
            foreach ($results as $result) {
                fputcsv($file, [
                    $result['file2_row'],
                    $result['file2_original'],
                    $result['file2_normalized'],
                    $result['file1_row'] ?? 'N/A',
                    $result['file1_original'] ?? 'N/A',
                    $result['match_type'],
                    $result['score']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
