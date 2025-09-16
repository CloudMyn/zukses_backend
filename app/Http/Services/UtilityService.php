<?php

namespace App\Http\Services;

use App\Events\NotifEvent;
use App\Models\AgentRequestInvoice;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UtilityService
{
    public function is200Response($responseMessage)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'success',
            'message' => $responseMessage
        ], 200));
    }

    public function is200ResponseWithData($responseMessage, $data)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'success',
            'message' => $responseMessage,
            'data' => $data
        ], 200));
    }
    public function is200ResponseWithDataAndSummary($responseMessage, $data, $summary)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'success',
            'message' => $responseMessage,
            'data' => $data,
            'summary' => $summary,
        ], 200));
    }

    public function is200ResponseWithDataAndMeta($data, $meta = [])
    {
        if (empty($meta)) {
            $newMeta = [
                "http_status" => 200,
            ];
        } else {
            $newMeta = $meta;
            $newMeta["http_status"] = 200;
        }
        throw new HttpResponseException(response()->json([
            'message' => 'success',
            'data' => $data,
            'meta' => $newMeta,
        ], 200));
    }

    public function is404Response($responseMessage)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $responseMessage
        ], 404));
    }

    public function is422Response($responseMessage)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $responseMessage
        ], 422));
    }

    public function is500Response($responseMessage)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'reason' => $responseMessage,
            'message' => $responseMessage
        ], 500));
    }

    public function is401Response()
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'reason' => "unauthenticated / unauthorized",
            'message' => "unauthenticated / unauthorized"
        ], 401));
    }

    public function hash_password($password)
    {
        return Hash::make($password);
    }

    public function daysBetween($dt1, $dt2)
    {
        return date_diff(
            date_create($dt2),
            date_create($dt1)
        )->format('%a');
    }

    public function getDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        $array = array();
        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        return $array;
    }

    public function getTotalDaysInMonth($date)
    {
        //membutuhkan parameter yyyy-mm
        $date = explode("-", $date);
        $tahun = $date[0];
        $bulan = $date[1];
        return cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
    }
    public function isFriday($date)
    {
        return (date('N', strtotime($date)) == 5);
    }
    public function tgl_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }


    public function convertImageToWebp($img)
    {
        $lebar_gambar = Image::make($img)->width();
        $lebar_gambar -= $lebar_gambar * 60 / 100;

        return Image::make($img)->encode('webp', 50)->resize(1840, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        // return Image::make($img)->encode('webp', 100);
    }

    public function uploadImageToMinio($img, $fileName, $direktori)
    {
        $image = $this->convertImageToWebp($img);

        Storage::disk('minio')->put($direktori . $fileName, $image);

        $urlMinio = Storage::disk('minio')->url($direktori . $fileName);
        return $urlMinio;
    }

    public function uploadToMinio($file, $fileName, $direktori)
    {
        Storage::disk('minio')->put($direktori . $fileName, $file);

        $urlMinio = Storage::disk('minio')->url($direktori . $fileName);
        return $urlMinio;
    }

    public function deleteFileInMinio($url)
    {
        $result = str_replace(ENV("MINIO_ENDPOINT") . "/" . ENV("MINIO_BUCKET") . "/", '', $url);

        Storage::disk('minio')->delete($result);
    }

    public function sendNotif($created_by, $title, $desc, $to_user = null, $to_role = null)
    {
        try {
            event(new NotifEvent($created_by, $title, $desc, $to_user, $to_role));
        } catch (Exception $e) {
        }
    }

    public function getPaymentChannels()
    {
        $apiKey = env('TRIPAY_API_KEY');
        $tripayMode = env('TRIPAY_API_MODE', 'sandbox'); // default ke sandbox
        $tripayUrl = $tripayMode === 'production'
            ? 'https://tripay.co.id/api/merchant/payment-channel'
            : 'https://tripay.co.id/api-sandbox/merchant/payment-channel';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $tripayUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response);

        return empty($error) ? $response : $error;
    }

    public function requestTransaction($merchantRef, $amount, $name, $email, $method, $items, $callback_url)
    {
        $apiKey       = env('TRIPAY_API_KEY');
        $privateKey   = env('TRIPAY_PRIVATE_KEY');
        $merchantCode = env('TRIPAY_MERCHANT_CODE');
        // $merchantRef  = $detail['merchant_ref'];
        // $amount       = $detail['amount'];

        $data = [
            'method'         => $method,
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $name,
            'customer_email' => $email,
            'order_items'    => $items,
            'callback_url' => $callback_url,
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature'    => hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response);

        return empty($error) ? $response : $error;
    }

    public function detailTransaction($codeReference)
    {
        $apiKey = env('TRIPAY_API_KEY');
        $payload = ['reference'    => $codeReference];
        $tripayMode = env('TRIPAY_API_MODE', 'sandbox'); // default ke sandbox
        $tripayUrl = $tripayMode === 'production'
            ? 'https://tripay.co.id/api/transaction/detail?'
            : 'https://tripay.co.id/api-sandbox/transaction/detail?';
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $tripayUrl . http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response);

        return empty($error) ? $response : $error;
    }

    function pointInPolygon($point, $polygon)
    {
        $x = $point[0]; // lng
        $y = $point[1]; // lat
        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 0.000001) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function checkPolygon($dataPolygon, $lat, $long)
    {
        // $dataPolygon = MasterSubdistrictPolygon::where('subdistrict_id', $subdistrict_id)->select('polygon')->first();

        $polygonPath = json_decode($dataPolygon->polygon)[0];

        // Titik yang dicek (lng, lat) — perhatikan urutan karena biasanya polygon data [lat,lng]
        $point = [$long, $lat];

        $polygon = array_map(function ($coord) {
            return [$coord[1], $coord[0]];
        }, $polygonPath);

        // dd($dataPolygon->path, $polygonPath, $polygon);

        return $this->pointInPolygon($point, $polygon);
    }
}
