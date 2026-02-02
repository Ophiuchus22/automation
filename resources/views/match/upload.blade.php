<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel File Matcher - Upload</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
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

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #6c757d;
        }

        .file-input-label:hover {
            background: #e9ecef;
            border-color: #667eea;
        }

        .file-input-label.has-file {
            background: #e7f3ff;
            border-color: #667eea;
            color: #667eea;
        }

        .file-name {
            margin-top: 8px;
            font-size: 13px;
            color: #667eea;
            font-weight: 500;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
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

        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 13px;
            color: #555;
        }

        .info-box strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Excel File Matcher</h1>
        <p class="subtitle">Upload two Excel files to find matching records with intelligent fuzzy matching</p>

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

        <div class="info-box">
            <strong>📋 Instructions:</strong><br>
            • File 1: Your master list or basis file<br>
            • File 2: Municipality file or file to check against File 1<br>
            • Both files must be .xls or .xlsx format (max 10MB each)
        </div>

        <form action="{{ route('match.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="file1">File 1 (Master List / Basis)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="file1" id="file1" accept=".xls,.xlsx" required>
                    <label for="file1" class="file-input-label" id="file1-label">
                        <span>📁 Click to select File 1 (.xls or .xlsx)</span>
                    </label>
                </div>
                <div id="file1-name" class="file-name"></div>
            </div>

            <div class="form-group">
                <label for="file2">File 2 (Municipality File / To Check)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="file2" id="file2" accept=".xls,.xlsx" required>
                    <label for="file2" class="file-input-label" id="file2-label">
                        <span>📁 Click to select File 2 (.xls or .xlsx)</span>
                    </label>
                </div>
                <div id="file2-name" class="file-name"></div>
            </div>

            <button type="submit" class="btn">Upload Files & Continue →</button>
        </form>
    </div>

    <script>
        // File input handlers
        document.getElementById('file1').addEventListener('change', function(e) {
            const label = document.getElementById('file1-label');
            const nameDiv = document.getElementById('file1-name');
            if (e.target.files.length > 0) {
                label.classList.add('has-file');
                label.querySelector('span').textContent = '✓ File selected';
                nameDiv.textContent = e.target.files[0].name;
            } else {
                label.classList.remove('has-file');
                label.querySelector('span').textContent = '📁 Click to select File 1 (.xls or .xlsx)';
                nameDiv.textContent = '';
            }
        });

        document.getElementById('file2').addEventListener('change', function(e) {
            const label = document.getElementById('file2-label');
            const nameDiv = document.getElementById('file2-name');
            if (e.target.files.length > 0) {
                label.classList.add('has-file');
                label.querySelector('span').textContent = '✓ File selected';
                nameDiv.textContent = e.target.files[0].name;
            } else {
                label.classList.remove('has-file');
                label.querySelector('span').textContent = '📁 Click to select File 2 (.xls or .xlsx)';
                nameDiv.textContent = '';
            }
        });
    </script>
</body>
</html>
