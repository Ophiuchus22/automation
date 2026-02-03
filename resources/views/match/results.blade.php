<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel File Matcher - Results</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8f9fa;
            color: #333;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .stat-card.success {
            background: #f8f9fa;
            border-color: #6c757d;
        }

        .stat-card.warning {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #6c757d;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #495057;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }

        .score-high {
            background: #d4edda;
            color: #155724;
        }

        .score-medium {
            background: #fff3cd;
            color: #856404;
        }

        .score-low {
            background: #f8d7da;
            color: #721c24;
        }

        .match-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .match-exact {
            background: #d4edda;
            color: #155724;
        }

        .match-contains {
            background: #cfe2ff;
            color: #084298;
        }

        .match-fuzzy {
            background: #e7e7e7;
            color: #555;
        }

        .match-none {
            background: #f8d7da;
            color: #721c24;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-results-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Matching Results</h1>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">{{ $total_rows }}</div>
                <div class="stat-label">Total File 2 Rows</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value">{{ $matched_rows }}</div>
                <div class="stat-label">Matched (≥{{ $threshold }})</div>
            </div>
            @if($possible_matches > 0)
            <div class="stat-card warning">
                <div class="stat-value">{{ $possible_matches }}</div>
                <div class="stat-label">Possible Matches (70-{{ $threshold - 1 }})</div>
            </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="{{ route('match.index') }}" class="btn btn-secondary">
                ← Upload New Files
            </a>
        </div>

        <!-- Results Table -->
        @if(count($results) > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>File 2 Row</th>
                        <th>File 2 Original</th>
                        <th>File 2 Normalized</th>
                        <th>File 1 Row</th>
                        <th>File 1 Original</th>
                        <th>Match Type</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr>
                        <td><strong>{{ $result['file2_row'] }}</strong></td>
                        <td>{{ $result['file2_original'] }}</td>
                        <td style="color: #666; font-size: 12px;">{{ $result['file2_normalized'] }}</td>
                        <td>
                            @if($result['file1_row'])
                                <strong>{{ $result['file1_row'] }}</strong>
                            @else
                                <span style="color: #999;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($result['file1_original'])
                                {{ $result['file1_original'] }}
                            @else
                                <span style="color: #999;">No match</span>
                            @endif
                        </td>
                        <td>
                            @if($result['match_type'] === 'exact')
                                <span class="match-type match-exact">Exact</span>
                            @elseif($result['match_type'] === 'contains')
                                <span class="match-type match-contains">Contains</span>
                            @elseif($result['match_type'] === 'fuzzy')
                                <span class="match-type match-fuzzy">Fuzzy</span>
                            @else
                                <span class="match-type match-none">No Match</span>
                            @endif
                        </td>
                        <td>
                            @if($result['score'] >= $threshold)
                                <span class="score-badge score-high">{{ $result['score'] }}</span>
                            @elseif($result['score'] >= 70)
                                <span class="score-badge score-medium">{{ $result['score'] }}</span>
                            @else
                                <span class="score-badge score-low">{{ $result['score'] }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="no-results">
            <div class="no-results-icon">🔍</div>
            <h3>No matches found</h3>
            <p>No records met the matching criteria. Try lowering the threshold or checking "Show possible matches".</p>
        </div>
        @endif
    </div>
</body>
</html>
