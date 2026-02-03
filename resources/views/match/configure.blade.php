<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel File Matcher - Configure</title>
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
            max-width: 800px;
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

        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .section-title::before {
            content: '';
            width: 3px;
            height: 20px;
            background: #6c757d;
            margin-right: 10px;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        select, input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #6c757d;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-option {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }

        .checkbox-option input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .btn {
            background: #495057;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease;
        }

        .btn:hover {
            background: #343a40;
        }

        .btn:active {
            background: #212529;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .slider-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .slider-container input[type="range"] {
            flex: 1;
        }

        .slider-value {
            font-weight: 600;
            color: #495057;
            min-width: 40px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Configure Matching</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('match.run') }}" method="POST">
            @csrf
            <input type="hidden" name="file1_path" value="{{ $file1_path }}">
            <input type="hidden" name="file2_path" value="{{ $file2_path }}">

            <!-- Sheet Selection -->
            <div class="section">
                <div class="section-title">Sheet Selection</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="file1_sheet">File 1 Sheet</label>
                        <select name="file1_sheet" id="file1_sheet" required>
                            @foreach($file1_sheets as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="file2_sheet">File 2 Sheet</label>
                        <select name="file2_sheet" id="file2_sheet" required>
                            @foreach($file2_sheets as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Column Selection -->
            <div class="section">
                <div class="section-title">Column Selection</div>
                
                <div class="form-group">
                    <label for="file1_column">File 1 Business/Establishment Column (e.g., B)</label>
                    <input type="text" name="file1_column" id="file1_column" value="B" placeholder="B" required>
                    <p class="info-text">Column containing business or establishment names in File 1</p>
                </div>

                <div class="form-group">
                    <label for="file2_column">File 2 Business/Establishment Column (e.g., D)</label>
                    <input type="text" name="file2_column" id="file2_column" value="D" placeholder="D" required>
                    <p class="info-text">Column containing business or establishment names in File 2</p>
                </div>
            </div>

            <!-- Advanced Options -->
            <div class="section">
                <div class="section-title">Matching Options</div>
                
                <div class="form-group">
                    <label for="threshold">Match Threshold</label>
                    <div class="slider-container">
                        <input type="range" name="threshold" id="threshold" min="0" max="100" value="85">
                        <span class="slider-value" id="threshold-value">85</span>
                    </div>
                    <p class="info-text">Minimum score (0-100) to consider a match. Default: 85</p>
                </div>

                <div class="form-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="show_possible" value="1">
                        <span>Show possible matches (score 70-84)</span>
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="file1_start_row">File 1 Start Row</label>
                        <input type="number" name="file1_start_row" id="file1_start_row" value="1" min="1">
                        <p class="info-text">Skip header rows if needed</p>
                    </div>
                    <div class="form-group">
                        <label for="file2_start_row">File 2 Start Row</label>
                        <input type="number" name="file2_start_row" id="file2_start_row" value="1" min="1">
                        <p class="info-text">Skip header rows if needed</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">Run Match</button>
        </form>
    </div>

    <script>
        // Threshold slider
        const thresholdSlider = document.getElementById('threshold');
        const thresholdValue = document.getElementById('threshold-value');

        thresholdSlider.addEventListener('input', function() {
            thresholdValue.textContent = this.value;
        });
    </script>
</body>
</html>
