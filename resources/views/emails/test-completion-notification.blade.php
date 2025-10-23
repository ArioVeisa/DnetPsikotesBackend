<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completion Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #475569;
        }
        .value {
            color: #1e293b;
        }
        .status-badge {
            background-color: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Test Completion Notification</h1>
        <p>DWP Psikotes System</p>
    </div>

    <div class="content">
        <h2>Hello Admin!</h2>
        <p>A candidate has successfully completed their test. Here are the details:</p>

        <div class="info-box">
            <h3>📋 Test Information</h3>
            <div class="info-row">
                <span class="label">Test Name:</span>
                <span class="value">{{ $testName }}</span>
            </div>
            <div class="info-row">
                <span class="label">Target Position:</span>
                <span class="value">{{ $targetPosition }}</span>
            </div>
            <div class="info-row">
                <span class="label">Completion Time:</span>
                <span class="value">{{ $completedAt }}</span>
            </div>
            <div class="info-row">
                <span class="label">Score:</span>
                <span class="value">{{ $score }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status-badge">COMPLETED</span>
            </div>
        </div>

        <div class="info-box">
            <h3>👤 Candidate Information</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">{{ $candidateName }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $candidateEmail }}</span>
            </div>
            <div class="info-row">
                <span class="label">Position:</span>
                <span class="value">{{ $candidatePosition }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/results" class="button">
                View Results Dashboard
            </a>
        </div>

        <p style="margin-top: 30px;">
            <strong>Next Steps:</strong><br>
            • Review the candidate's test results<br>
            • Generate detailed reports if needed<br>
            • Contact the candidate for follow-up if required
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from DWP Psikotes System.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>