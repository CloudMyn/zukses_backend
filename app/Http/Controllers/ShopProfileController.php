<?php

namespace App\Http\Controllers;

use App\Helpers\UrlRemove;
use App\Models\Product;
use App\Models\RequermentShops;
use App\Models\ShopAddress;
use App\Models\ShopBankAccount;
use App\Models\ShopProfile;
use App\Models\StoreShippingSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopProfileController extends Controller
{

    public function requerment(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10 item per halaman
        $statusFilter = $request->get('status'); // bisa 1, 2, 3
        $search = $request->get('search'); // pencarian teks

        $query = ShopProfile::join('users', 'users.id', '=', 'shop_profiles.user_id')
            ->select('shop_profiles.*', 'users.whatsapp', 'users.email', 'users.username', 'users.name');

        // 🔹 Filter berdasarkan status
        if (!empty($statusFilter)) {
            $query->where('shop_profiles.status', $statusFilter);
        }

        // 🔹 Filter berdasarkan pencarian nama toko, username, email, atau WhatsApp
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_profiles.shop_name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.whatsapp', 'like', "%{$search}%");
            });
        }

        // 🔹 Ambil data dengan pagination
        $shops = $query->paginate($perPage);

        $data = $shops->getCollection()->transform(function ($shop) {
            $verificator = User::find($shop->verificator);
            if ($verificator) {
                $shop['verificator'] = $verificator['name'];
            }

            $addresses = ShopAddress::where('seller_id', $shop->id)
                ->leftJoin('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
                ->leftJoin('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                ->leftJoin('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
                ->leftJoin('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
                ->select(
                    'shop_addresses.*',
                    'master_provinces.name as name_provinces',
                    'master_cities.name as name_cities',
                    'master_subdistricts.name as name_district',
                    'master_postal_codes.code'
                )
                ->get();

            $banks = ShopBankAccount::where('seller_id', $shop->id)
                ->leftJoin('banks', 'banks.id', '=', 'shop_bank_accounts.bank_id')
                ->select(
                    'shop_bank_accounts.*',
                    'banks.name_bank',
                    'banks.icon'
                )
                ->get();

            $products = Product::with([
                'category.parent.parent',
                'specifications',
                'variants.values',
                'media',
                'variantPrices.compositions.value.variant',
                'variantPrices.delivery',
                'variantPrices.promotion',
                'delivery',
                'promotion',
            ])
                ->where('seller_id', $shop->id)
                ->orderBy('id', 'desc')
                ->get();

            $requirement = RequermentShops::where('shop_profil_id', $shop->id)->first();

            return [
                'shop'        => [
                    'data'      => $shop,
                    'addresses' => $addresses,
                    'banks'     => $banks,
                ],
                'requirement' => $requirement,
                'products'    => $products,
            ];
        });

        $meta = [
            'current_page' => $shops->currentPage(),
            'per_page'     => $shops->perPage(),
            'total'        => $shops->total(),
            'last_page'    => $shops->lastPage(),
            'semua' => ShopProfile::join('users', 'users.id', '=', 'shop_profiles.user_id')->count(),
            'disetujui' => ShopProfile::join('users', 'users.id', '=', 'shop_profiles.user_id')->where('shop_profiles.status', 3)->count(),
            'belum_disetujui' => ShopProfile::join('users', 'users.id', '=', 'shop_profiles.user_id')->where('shop_profiles.status', 1)->count(),
            'diblokir' => ShopProfile::join('users', 'users.id', '=', 'shop_profiles.user_id')->where('shop_profiles.status', 2)->count(),
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Toko ditemukan',
            'data'    => $data,
            'meta'    => $meta,
        ]);
    }









    public function show(Request $request)
    {
        $user = $request->user();

        $profile = ShopProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            $message = 'Shop profile not found.';
            return $this->utilityService->is401Response($message);
        }

        // Ambil alamat yang is_primary = 1 jika ada, jika tidak ambil data pertama
        $address = ShopAddress::join('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
            ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
            ->join('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
            ->join('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
            ->where('seller_id', $profile->id)
            ->select(
                'master_provinces.name as provinces',
                'master_cities.name as cities',
                'master_subdistricts.name as subdistricts',
                'master_postal_codes.code as postal_codes',
                'shop_addresses.*'
            )
            ->where('is_primary', 1)
            ->first();

        if (!$address) {
            $address = ShopAddress::join('master_provinces', 'master_provinces.id', '=', 'shop_addresses.province_id')
                ->join('master_cities', 'master_cities.id', '=', 'shop_addresses.citie_id')
                ->join('master_subdistricts', 'master_subdistricts.id', '=', 'shop_addresses.subdistrict_id')
                ->join('master_postal_codes', 'master_postal_codes.id', '=', 'shop_addresses.postal_code_id')
                ->where('seller_id', $profile->id)
                ->select(
                    'master_provinces.name as provinces',
                    'master_cities.name as cities',
                    'master_subdistricts.name as subdistricts',
                    'master_postal_codes.code as postal_codes',
                    'shop_addresses.*'
                )->first();
        }

        $bank = ShopBankAccount::where('seller_id', $profile->id)
            ->where('is_primary', 1)
            ->first();
        if (!$bank) {
            $bank = ShopBankAccount::where('seller_id', $profile->id)->first();
        }
        $delivery = StoreShippingSetting::where('seller_id', $profile->id)->first();
        $profile->address = $address;
        $profile->bank = $bank;
        $profile->delivery = $delivery;

        $message = "Data Toko ditemukan";
        return $this->utilityService->is200ResponseWithData($message, $profile);
    }



    /**
     * Create or update the shop profile.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Validate Input
        $this->validate($request, [
            'shop_name' => 'required|string|max:30',
            'description' => 'required|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg', // 2MB max
        ]);

        $logoUrl = null;

        // 2. Handle Logo Upload to MinIO
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . $user->id . '_' . time() . '.webp';
            $path = "logos/{$user->id}"; // Store in a user-specific folder

            // Upload the file to MinIO
            Storage::disk('minio')->putFileAs($path, $file, $filename);

            // Get the public URL of the file
            $logoUrl = Storage::disk('minio')->url("{$path}/{$filename}");
        }

        // 3. Prepare data for the database
        $data = [
            'shop_name' => $request->input('shop_name'),
            'description' => $request->input('description'),
        ];

        // Only update the logo path if a new file was uploaded
        if ($logoUrl) {
            $data['logo_url'] = $logoUrl;
        }

        // 4. Update or Create the profile in the database
        $shopProfile = ShopProfile::updateOrCreate(
            ['user_id' => $user->id], // Key to find the record
            $data  // Data to update or create with
        );

        $message = "Shop profile saved successfully!";
        return $this->utilityService->is200ResponseWithData($message, $shopProfile);
    }

    public function create(Request $request, $user_id)
    {

        $useProfil = DB::table('shop_profiles')->where('user_id', $user_id)->first();
        if ($useProfil) {
            $shopProfile = ShopProfile::find($useProfil->id);
            if ($request->logo) {
                $delete_image =  $shopProfile->logo_url;
                $url = UrlRemove::remover($delete_image);

                Storage::disk('minio')->delete($url);
                $fileName = 'Logo-Toko-' . time() . '.webp';
                $image = $this->utilityService->convertImageToWebp($request->file('logo'));
                Storage::disk('minio')->put($fileName, $image);
                $urlLogo = Storage::disk('minio')->url($fileName);
                $shopProfile->logo_url = $urlLogo;
            }
            if ($request->ktp) {
                if ($shopProfile->ktp_url) {
                    $delete_image =  $shopProfile->ktp_url;
                    $url = UrlRemove::remover($delete_image);
                    Storage::disk('minio')->delete($url);
                }

                $fileName = 'KTP-' . time() . '.webp';
                $image = $this->utilityService->convertImageToWebp($request->file('ktp'));
                Storage::disk('minio')->put($fileName, $image);
                $urlKTP = Storage::disk('minio')->url($fileName);
                $shopProfile->ktp_url = $urlKTP;
            }
            if ($request->selfie) {
                if ($shopProfile->selfie_url) {
                    $delete_image =  $shopProfile->selfie_url;
                    $url = UrlRemove::remover($delete_image);
                    Storage::disk('minio')->delete($url);
                }

                $fileName = 'selfie-' . time() . '.webp';
                $image = $this->utilityService->convertImageToWebp($request->file('selfie'));
                Storage::disk('minio')->put($fileName, $image);
                $urlselfie = Storage::disk('minio')->url($fileName);
                $shopProfile->selfie_url = $urlselfie;
            }
            $shopProfile->shop_name = $request->shop_name;
            $shopProfile->description = $request->description;
            $shopProfile->full_name = $request->full_name;
            $shopProfile->nik = $request->nik;
            $shopProfile->type = $request->type;
            if ($shopProfile->save()) {
                $success_message = "Data Berhasil diupdate";
                return $this->utilityService->is200ResponseWithData($success_message, $shopProfile);
            } else {
                return $this->utilityService->is500Response("problem with server");
            }
        } else {

            $fileNameLogo = 'Logo-Toko-' . time() . '.webp';
            $imageLogo = $this->utilityService->convertImageToWebp($request->file('logo'));
            Storage::disk('minio')->put($fileNameLogo, $imageLogo);
            $urlLogo = Storage::disk('minio')->url($fileNameLogo);

            $data = [
                'user_id' => $user_id,
                'shop_name' => $request->shop_name,
                'description' => $request->description,
                'type' => $request->type,
                'full_name' => $request->full_name,
                'nik' => $request->nik,
                'logo_url' => $urlLogo,
            ];
            if ($request->file('ktp')) {
                $fileNameKTP = 'KTP-' . time() . '.webp';
                $imageKTP = $this->utilityService->convertImageToWebp($request->file('ktp'));
                Storage::disk('minio')->put($fileNameKTP, $imageKTP);
                $urlKTP = Storage::disk('minio')->url($fileNameKTP);
                $data['ktp_url'] = $urlKTP;
            }

            if ($request->file('selfie')) {
                $fileNameSelfie = 'Selfie-' . time() . '.webp';
                $imageSelfie = $this->utilityService->convertImageToWebp($request->file('selfie'));
                Storage::disk('minio')->put($fileNameSelfie, $imageSelfie);
                $urlSelfie = Storage::disk('minio')->url($fileNameSelfie);
                $data['selfie_url'] = $urlSelfie;
            }
            $insert = shopProfile::create($data);
            $data = $insert->fresh();
            if ($insert) {
                $success_message = "Data Berhasil Ditambahkan";
                return $this->utilityService->is200ResponseWithData($success_message, $data);
            } else {
                return $this->utilityService->is500Response("problem with server");
            }
        }
    }

    public function destroy($id)
    {
        $shopProfile = ShopProfile::find($id);

        if (!$shopProfile) {
            return response()->json(['status' => 'error', 'message' => 'Shop profil not found'], 404);
        }

        // Hapus gambar dari Minio sebelum menghapus record
        if ($shopProfile->logo) {
            Storage::disk('minio')->delete(basename($shopProfile->logo));
        }
        if ($shopProfile->ktp) {
            Storage::disk('minio')->delete(basename($shopProfile->ktp));
        }
        if ($shopProfile->selfie) {
            Storage::disk('minio')->delete(basename($shopProfile->selfie));
        }

        $shopProfile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User Profil deleted successfully',
        ]);
    }
}
