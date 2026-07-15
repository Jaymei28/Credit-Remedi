<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Follow-Up Letter PDF</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            line-height: 1.5;
            margin: 40px;
        }
        .letter {
            white-space: pre-line;
            text-indent: 0 !important;
            margin: 0;
            padding: 0;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('logo.png') }}" alt="Logo" class="logo">

    <div class="letter">
        {{ ltrim($dispute->letter_content_2) }}
    </div>
</body>
</html>
