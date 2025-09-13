<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #062e58;
            font-size: 26px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            color: #444444;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        ul li {
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            font-weight: bold;
            text-decoration: none;
            background-color: #062e58;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #888888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Kode OTP Reset Password Anda {{ $otp }}</h1>

    </div>
</body>

</html>
