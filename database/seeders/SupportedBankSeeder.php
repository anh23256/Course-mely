<?php

namespace Database\Seeders;

use App\Models\SupportedBank;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportedBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "name" => "Ngân hàng TMCP Công thương Việt Nam",
                "code" => "ICB",
                "bin" => "970415",
                "short_name" => "VietinBank",
                'logo' => 'https://api.vietqr.io/img/ICB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/vietinbank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Ngoại Thương Việt Nam",
                "code" => "VCB",
                "bin" => "970436",
                "short_name" => "Vietcombank",
                'logo' => "https://api.vietqr.io/img/VCB.png",
                'logo_rounded' => "https://img.bankhub.dev/rounded/vietcombank.png",
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Đầu tư và Phát triển Việt Nam",
                "code" => "BIDV",
                "bin" => "970418",
                "short_name" => "BIDV",
                'logo' => 'https://api.vietqr.io/img/BIDV.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/bidv.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam",
                "code" => "Agribank",
                "bin" => "970405",
                "short_name" => "Agribank",
                'logo' => 'https://api.vietqr.io/img/VBA.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/agribank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Quân đội",
                "code" => "MB",
                "bin" => "970422",
                "short_name" => "MBBank",
                'logo' => 'https://api.vietqr.io/img/MB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/mbbank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Kỹ thương Việt Nam",
                "code" => "TCB",
                "bin" => "970407",
                "short_name" => "Techcombank",
                'logo' => 'https://api.vietqr.io/img/TCB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/techcombank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Á Châu",
                "code" => "ACB",
                "bin" => "970416",
                "short_name" => "ACB",
                'logo' => 'https://api.vietqr.io/img/ACB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/acb.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Việt Nam Thịnh Vượng",
                "code" => "VPB",
                "bin" => "970432",
                "short_name" => "VPBank",
                'logo' => 'https://api.vietqr.io/img/VPB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/vpbank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Tiên Phong",
                "code" => "TPB",
                "bin" => "970423",
                "short_name" => "TPBank",
                'logo' => 'https://api.vietqr.io/img/TPB.png',
                'logo_rounded' => 'https://img.bankhub.dev/rounded/tpbank.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($data as $item) {
            SupportedBank::query()->create($item);
        }
    }
}
