<!DOCTYPE html>
<html>
<head>
    <title>Permintaan Pendaftaran Berhasil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
        }

        tr:nth-child(odd), th:nth-child(odd), td:nth-child(odd)  {
           background-color: #f5f5f5;
        }

        .abu {
            background-color: #f5f5f5;
        }

        .pertama {
            width: 100px;
            display: inline-block;
        }

        .kedua {
            display: flex;
            align-self: center;
            margin: auto 0;
        }

        .flex {
            display: flex;
            width: 100%;
            align-items: center;
        }
        
    </style>
</head>
<body>
    <h1>Permintaan Pendaftaran Berhasil</h1>
    <p>Anda telah melakukan permintaan Pendaftaran dengan rincian sebagai berikut</p>
    <div class="abu flex">
        <span class="pertama"><b>Request Number</b></span>
        <span class="kedua">{{ $data['request_number'] }}</span>
    </div>
    <div class="flex">
        <span class="pertama"><b>Invoice Number</b></span>
        <span class="kedua">{{ $data['inv_number'] }}</span>
    </div>
    <div class="abu flex">
        <span class="pertama"><b>Nama Agen</b></span>
        <span class="kedua">{{ $data['nama_agent'] }}</span>
    </div>
    <div class="flex">
        <span class="pertama"><b>Nama Kapal</b></span>
        <span class="kedua">{{ $data['nama_kapal'] }}</span>
    </div>
    <div class="abu flex">
        <span class="pertama"><b>Nama Tongkang</b></span>
        <span class="kedua">{{ $data['nama_tongkang'] }}</span>
    </div>
    <div class="abu flex">
        <span class="pertama"><b>Dari</b></span>
        <span class="kedua">{{ $data['dari'] }}</span>
    </div>
    <div class="flex">
        <span class="pertama"><b>Ke</b></span>
        <span class="kedua">{{ $data['ke'] }}</span>
    </div>
    <div class="abu flex">
        <span class="pertama"><b>Nama Servis</b></span>
        <span class="kedua">{{ $data['nama_servis'] }}</span>
    </div>
    <div class="flex">
        <span class="pertama"><b>Total Servis</b></span>
        <span class="kedua">{{ $data['total_servis'] }}</span>
    </div>
    <div class="abu flex">
        <span class="pertama"><b>Dibayar</b></span>
        <span class="kedua">{{ $data['dibayar'] }}</span>
    </div>
    <div class="flex">
        <span class="pertama"><b>Status</b></span>
        <span class="kedua">{{ $data['status'] }}</span>
    </div>
</body>
</html>