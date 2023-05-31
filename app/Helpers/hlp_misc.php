<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
//-----------------------------------------------------------------
// Kumpulan fungsi pendukung development
//-----------------------------------------------------------------
// Author          : AbsoluteHelix (OCW)
// Dibuat Pada     : 07/03/2023
// Tipe            : PHP
//----------------------------------------------------------------- 

// generate url
// url_string = text/url pada route
// query_string = query tambahan pada url contoh = ?id=1&no=2
// format query_string
// $qs = [
// 'a' => 1,
// 'b' => 2
// ]
function genUrl($url_string, array $query_string = [])
{
    $qs_container = [];

    // tambahkan qstring jika ada
    if (count($query_string) > 0) {
        $cnt = 0;

        foreach ($query_string as $key => $value) {
            $cnt++;

            // cek jika qs paling pertama 
            // maka pkai tanda "?"
            // seterusnya pakai tanda "&"
            if ($cnt == 1) {
                $qs_container[] = "?" . $key . "=" . $value;
            } else {
                $qs_container[] = "&" . $key . "=" . $value;
            }
        }

        // ubah array jadi string dan gabungkan dengan base url
        $konversi = implode("", $qs_container);
        $base_url = $url_string . $konversi;
    } else {
        $base_url = $url_string;
    }

    return url($base_url);
}

// fungsi untuk membuat url sortir pada tabel
// pointer = nama query yang digunakan untuk menjadi petunujuk sorting
// pointer_sort = nama query sort yang digunakan untuk menjadi petunujuk sorting
// sort = jenis sortir (desc = descending, asc = arcending)
// compare = parameter yang digunakan untuk memastikan bahwa pointer dan yang di pilih sama
// return string/url
function genSortUrl($pointer, $pointer_sort, $compare)
{
    if (isset($_GET[$pointer])) {
        if ($_GET[$pointer] == $compare) {
            $d = $compare;
        } else {
            $d = $compare;
        }
    }

    if (isset($_GET[$pointer_sort])) {

        if ($_GET[$pointer] == $compare) {
            if ($_GET[$pointer_sort] == "desc") {
                $sort = "asc";
            } else {
                $sort = "desc";
            }
        } else {
            $sort = "desc";
        }
    }

    if (isset($_GET[$pointer])) {
        $url = url()->current() . '?' . $pointer . '=' . $d . '&' . $pointer_sort . '=' . $sort;
    } else {
        $url = url()->current() . '?' . $pointer . '=' . $compare . '&' . $pointer_sort . '=desc';
    }

    return $url;
}

// fungsi untuk mengetahui bahwa nama parameter telah diset atau ada pada page
// qs_name = nama query string contoh $_GET['nama_qs]
// ref = referensi untuk custom return
// compare = format [ref, cond1, cond2, sukses, gagal]
// custom_return = data return yang ingin ditampilkan bentuk array format : ['ref', 'sukses', 'ref2', 'gagal']
// return = 1/string : sukses
function isNameSet($qs_name, $compare = false, array $custom_return = [])
{

    if (isset($_GET[$qs_name])) {

        if (count($custom_return) <= 0) {
            return true;
        } else {

            // cek compare
            if ($compare == true) {
                // operator di key no 0
                if ($custom_return[0] != null) {
                    if ($custom_return[0][$custom_return[1]] == $custom_return[2]) {
                        return $custom_return[3];
                    } else {
                        return $custom_return[4];
                    }
                } else {
                    if ($custom_return[1] == $custom_return[2]) {
                        return $custom_return[3];
                    } else {
                        return $custom_return[4];
                    }
                }
            }

            // cek referensi
            if ($custom_return[0] != null) {
                return $custom_return[0][$custom_return[1]];
            }

            return $custom_return[1];
        }
    } else {
        return false;
    }
}

// fungsi untuk membuatkan log
// text = tulisan log yang akan ditambahan pada file
// format tulisan = 2023-04-03 00:00:20 : $text
// return = none
function createLogs($text)
{
    $path = "abshelix_logging/";
    $nama_file = $path . date("Y-m-d") . "_logs.txt";
    $template = Carbon::now() . " : " . $text;

    if (Storage::disk('local')->exists($nama_file)) {
        Storage::disk('local')->prepend($nama_file, $template);
    } else {
        Storage::disk('local')->put($nama_file, $template);
    }
}

// fungsi untuk membuat log dengan format json agar bisa digunakan di frontend maupun backend
function createLogJson($logText, $logDate, $pengguna = [], $levelLog = "normal", array $infoTambahan = [], $icon = "notifications")
{
    $path = "abshelix_json_logs/";
    $nama_file = $path . date("Ymd") . "_logs.json";
    $template = [
        "text" => $logText,
        "date" => $logDate,
        "user" => $pengguna,
        "level" => $levelLog,
        "infos" => $infoTambahan,
        "icon" => $icon,
        "url" => url()->current()
    ];

    $newFile = [];

    if (Storage::disk('local')->exists($nama_file)) {
        // jika sudah ada load dulu
        $file = Storage::disk('local')->get($nama_file);
        $log_array = json_decode($file, true);
        $log_array[] = $template;

        Storage::disk('local')->put($nama_file, json_encode($log_array, JSON_PRETTY_PRINT));
    } else {
        $newFile[] = $template;
        Storage::disk('local')->put($nama_file, json_encode($newFile, JSON_PRETTY_PRINT));
    }
}

// fungsi untuk generate icon berdasarkan material icon google atau font awesome
// source : https://fonts.google.com/icons
// dibutuhkan : css dan js material icon
function getIcon($icon, array $styling = [], $source = "material")
{
    $final_styling = "";

    // styleing
    if (count($styling) > 0) {
        $cnt = [];

        foreach ($styling as $key => $value) {
            $cnt[] = $key . ":" . $value . ";";
        }

        // convert array to string
        $final_styling = implode(" ", $cnt);
    }

    switch ($source) {
        case "material":
            $string = '<i class="material-icons" style="' . $final_styling . '">' . $icon . '</i>';
            break;
    }

    return $string;
}

// tampilkan error jika menggunakan validator laravel
// error = reverensi validator error biasanya $errors
// name = nama input
// generateClass = jika iya fungsi ini akan cek jika ada error pada input dengan nama :name akan mengembalikan variabel :generateClass
// jika tidak akan tampil html dibawah
// return = HTM/String
function getError($error, $name, $genereateClass = false)
{
    $gen = "";
    if ($error->has($name)) {
        if ($genereateClass == false) {
            $gen = '<div class="text-red-700">*' . $error->first($name) . '</div>';
        } else {
            $gen = $genereateClass;
        }
    }

    return $gen;
}

// fungsi untuk menadapatkan 2 kata terakhir pada url yang dipisah dengan /
// url_string = jika null fungsi ini akan menampilkan 2 kata terakhir pada url saat ini, jika diisikan string contoh "admin/pengguna" maka akan mnampilkan
// 2 kata terakhir dari url_string
// return = string
function iDToPage($url_string = null, $hapusKata = null)
{
    // init url string
    $url = ($url_string == null) ? url()->current() : genUrl($url_string);
    // jadikan string menjadi array
    $url_to_array = explode("/", $url);
    // penyimpanan sementara kata
    $id_container = [];
    // loop
    for ($i = 0; $i < count($url_to_array); $i++) {
        $last_index = count($url_to_array) - 1; // index terakhir
        $before_last_index = $last_index - 1; // -1 index sebelum index terakshir

        if ($i == $before_last_index) {
            $id_container[] = $url_to_array[$before_last_index];
        }

        if ($i == $last_index) {
            $id_container[] = $url_to_array[$last_index];
        }
    }
    // jadikan array to string
    $array_to_string = implode("_", $id_container);

    return $array_to_string;
}

// original laravel
function uploadFile($request, $namaInput, $namaFile, $destinasiFolder, $disk = "my_files")
{
    try {
        $varFile = $request[$namaInput];
        $cext = $varFile->extension();

        $nama = $namaFile . "." . $cext;
        $alamatFile = $varFile->storeAs($destinasiFolder, $nama, ['disk' => $disk]);

        return $alamatFile;
    } catch (Exception $e) {
        return null;
    }
}

// mengunakan intervention plugin
function uploadFileDenganCompress($request, $namaInput, $namaFile, $destinasiFolder, $ukuranModifier = 0.5, $kualitasGambar = 100, $gunakanAspectRatio = false)
{
    $varFile = $request[$namaInput];
    $cext = $varFile->extension();

    $nama = $namaFile . "." . $cext;
    $tujuan = "uploads/" . $destinasiFolder . "/" . $nama;

    $image = Image::make($request->file($namaInput));
    $lebar = $image->width() * $ukuranModifier;
    $tinggi = $image->height() * $ukuranModifier;

    if ($gunakanAspectRatio == true)
        $image->resize($lebar, null, function ($constraint) {
            $constraint->aspectRatio();
        });
    else
        $image->resize($lebar, $tinggi);

    $image->save($tujuan, $kualitasGambar);

    return $tujuan;
}

// fungsi untuk mendapatkan nama hari berdasarkan index
// index itu dari 0 - 6 yang 0 adalah minggu
// language = bahasa hari yang dipergunakan kode berupa [id, en]
function getDayName($index, $language = "id", $dipersingkat = false)
{
    if ($language == "id") {
        $kumpulan_hari = [
            "minggu", "senin", "selasa", "rabu", "kamis", "jumat", "sabtu"
        ];
    }

    if ($language == "en") {
        $kumpulan_hari = [
            "sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"
        ];
    }

    // jika dipersingkat true
    if ($dipersingkat)
        return limitLetter($kumpulan_hari[$index]);
    else
        return $kumpulan_hari[$index];
}

// fungsi untuk hapus huruf berdasarkan limit yang ditentukan
// misal sunday + limit(3) = sun
function limitLetter($string, $limit = 3)
{
    // ubah string jadi array
    $string_conts = str_split($string);

    // tempan menyimpan hasil
    $hasils = [];

    // loop
    for ($i = 0; $i < count($string_conts); $i++) {
        if ($i < $limit) {
            $hasils[] = $string_conts[$i];
        }
    }

    // ubah array to string
    return implode("", $hasils);
}

// fungsi untuk trim dan reformat string
// contoh : 823888222 jadi 823-888-222 atau 823/888/222
function trimReformat($string, $trimLimit = 3, $trimSimbol = "-")
{
    // ubah string jadi array
    $string_array = str_split($string, $trimLimit);

    return implode($trimSimbol, $string_array);
}

// fungsi untuk menambahkan anggka 0 pada nomor
// contoh 1 + addingZero(3) = 001
function addingZero($value, $length = 4)
{
    $str = substr("0000{$value}", -$length);

    return $str;
}

// fungsi untuk mendapatkan bulan berdasarkan index
function getBulan($index, $dipersingkat = false, $lang = "id")
{
    if ($lang == "id") {
        $kumpulanBulan = [
            "januari", "februari", "maret", "april", "mei", "juni", "juli", "agustus", "september", "oktober", "november", "desember"
        ];
    }

    if ($lang == "en") {
        $kumpulanBulan = [
            "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"
        ];
    }

    // jika dipersingkat true
    if ($dipersingkat)
        return limitLetter($kumpulanBulan[$index - 1]);
    else
        return $kumpulanBulan[$index - 1];
}

// fungsi untuk reformat tanggal
// hasil target dari 2023-04-05 jadi 05 Feb 2023 atau Senin, 02 February 2023
function dateReformat($tanggal, $tampilkanHari = false, $bulanDipersingkat = false, $pemisah = " ", $lang = "id", $formatTanggal = false, $tambahkanJam = false)
{
    $tanggal_totime = strtotime($tanggal);
    $date = date("d", $tanggal_totime);
    $bulan = date("m", $tanggal_totime);
    $tahun = date("Y", $tanggal_totime);

    // gabungkan value diatas jadi tanggal
    if ($formatTanggal) {
        return date($formatTanggal, $tanggal_totime);
    } else {
        $pukul = "";
        if ($tambahkanJam) {
            $pukul = " " . date("h:i A", $tanggal_totime);
        }

        return ($tampilkanHari) ? getDayname(date("w", $tanggal_totime)) . ", " . $date . $pemisah . getBulan($bulan, $bulanDipersingkat, $lang) . $pemisah . $tahun : $date . $pemisah . getBulan($bulan, $bulanDipersingkat, $lang) . $pemisah . $tahun . $pukul;
    }
}

// fungsi untuk init sort komponent dari querystring
function initSortComponent($rowTabel, $sort)
{
    $rowTabel = request()->query($rowTabel);
    $sort = request()->query($sort);

    $c = [];

    if ($rowTabel != null) {
        $c = [$rowTabel => $sort];
    }

    return $c;
}

// fungsi untuk mendapatkan querystring request
function getQS($string)
{
    return request()->query($string);
}

// fungsi untuk generate simmple alert message query string
function genResultQS($message, $tipe = "success", $tambahan = [])
{
    $data = [
        "msg" => textTransform($message),
        "tipe" => $tipe
    ];

    return array_merge($data, $tambahan);
}

// set timezone ke GMT +8 Bali
function setIDTime()
{
    $timezone = getFileValue("set_timezone", "settings/", "app");
    date_default_timezone_set($timezone);
}

// fungsi untuk membuat notifikasi via json
// fungsi untuk membuat log dengan format json agar bisa digunakan di frontend maupun backend
function createNotifikasi($notifText, $tipeNotifikasi, $notifDate, $pengguna = [], $levelLog = "normal", array $infoTambahan = [], $icon = "notifications")
{
    $path = "abshelix_notifikasi/";
    $nama_file = $path . date("Ymd") . "_notifs.json";
    $template = [
        "text" => $notifText,
        "date" => $notifDate,
        "user" => $pengguna,
        "level" => $levelLog,
        "infos" => $infoTambahan,
        "icon" => $icon,
        "url" => url()->current(),
        "tipe" => $tipeNotifikasi
    ];

    $newFile = [];

    if (Storage::disk('local')->exists($nama_file)) {
        // jika sudah ada load dulu
        $file = Storage::disk('local')->get($nama_file);
        $notif_array = json_decode($file, true);
        $notif_array[] = $template;

        Storage::disk('local')->put($nama_file, json_encode($notif_array, JSON_PRETTY_PRINT));
    } else {
        $newFile[] = $template;
        Storage::disk('local')->put($nama_file, json_encode($newFile, JSON_PRETTY_PRINT));
    }
}

// fungsi untuk mendapatkan jumlah hari dari dua tanggal
// https://stackoverflow.com/questions/2040560/finding-the-number-of-days-between-two-dates
function getHariDiantaraDua($now, $your_date)
{
    $datediff = $now - $your_date;

    return round($datediff / (60 * 60 * 24));
}

// fungsi untuk mendapatkan file json pada storage dan return
// sebagai array/object
function getFileJson($date, $path, $prefix = "notifs")
{
    $rdate = $date;
    $nama_file = $path . date("Ymd", strtotime($rdate)) . "_" . $prefix . ".json";
    $logs = null;

    if (Storage::disk('local')->exists($nama_file)) {
        $logs = Storage::disk('local')->get($nama_file);
    }

    return json_decode($logs);
}

// fungsi untuk mendapatkan file json pada storage dan return denga path
// sebagai array/object
function getFileJsonByPath($path, $nameFile, $disk = "local", $assoc = null)
{
    $nama_file = $path . $nameFile;
    $files = null;

    if (Storage::disk($disk)->exists($nama_file)) {
        $files = Storage::disk($disk)->get($nama_file);
    }

    return json_decode($files, $assoc);
}

// fungsi untuk ubah jam sesuai format
function timeRefomated($time, $format = "h:i A")
{
    return date($format, strtotime($time));
}

// text time hh:ii:ss to minute
function getMinute($time)
{
    $time = explode(':', $time);
    return ($time[0] * 60) + ($time[1]) + ($time[2] / 60);
}

// fungsi untuk mendapatkan jarak dari 2 waktu
function getTwoDateTime($datetime1, $datetime2, $mode = "i")
{
    $date1 = new DateTime($datetime1);
    $date2 = new DateTime($datetime2);

    return $date1->diff($date2)->{$mode};
}

// fungsi untuk check jika value null dan tampilkan hasil
function checkNull($potensiNull, $namaKeyData, $hasilJikaNull = "-")
{
    return $potensiNull != null ? $potensiNull->{$namaKeyData} : $hasilJikaNull;
}

// fungsi untuk mendapatkan list route
function getAllRoute()
{
    $routeCollection = Route::getRoutes();
    $routes = [];

    foreach ($routeCollection as $value) {
        $routes[] = [
            "methods" => $value->methods[0],
            "url" => $value->uri
        ];
    }

    return $routes;
}

// fungsi untuk buat file setting baru dengan value yang ditentukan
function createFile($path, $nama_file, $template = [], $disk = "local", $ext = "json")
{
    $filename = $path . $nama_file . "." . $ext;

    if (!Storage::disk($disk)->exists($filename)) {
        Storage::disk($disk)->put($filename, json_encode($template, JSON_PRETTY_PRINT));
    }
}

// fungsi untuk delete file 
function deleteFile($path, $nama_file, $disk = "local", $ext = "json")
{
    $filename = $path . $nama_file . "." . $ext;

    if (Storage::disk($disk)->exists($filename)) {
        Storage::disk($disk)->delete($filename);
    }
}

// fungsi untuk check file ada pada storage
function fileExist($path, $nama_file, $disk = "local", $ext = "json")
{
    $filename = $path . $nama_file . "." . $ext;

    return Storage::disk($disk)->exists($filename);
}

// fungsi untuk mendapatkan sebuah value pada file json
function getFileValue($value, $path, $nama_file, $ext = "json")
{
    $fileName = $path . $nama_file . "." . $ext;
    $values = null;
    $result = null;

    if (Storage::disk('local')->exists($fileName)) {
        $values = Storage::disk('local')->get($fileName);
        $value_decode = json_decode($values);

        $result = $value_decode->{$value};
    }

    return $result;
}
