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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            color: #666666;
            font-size: 16px;
            line-height: 1.5;
        }

        .button {
            display: inline-block;
            font-weight: bold;
            text-decoration: none;
            background-color: #007bff;
            /* Bootstrap primary color */
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 5px;
            border: none;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #0056b3;
            /* Darker shade on hover */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Verifikasi Email</h1>
        <p>Terima kasih telah mendaftar! Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:</p>
        <a href="{{ url('https://app.special-moment.info/verifikasi?token=' . $token . '') }}" class="button">Klik untuk
            verifikasi</a>
        <p>Atau Anda juga bisa mengklik tautan berikut:</p>
        <p><a
                href="{{ url('https://app.special-moment.info/verifikasi?token=' . $token . '') }}">{{ url('https://app.special-moment.info/verifikasi?token=' . $token . '') }}</a>
        </p>
    </div>
</body>

</html>
