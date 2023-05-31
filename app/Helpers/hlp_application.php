<?php
//-----------------------------------------------------------------
// Disini untuk fungsi khusus menyangkut applikasi yang dibuat
//-----------------------------------------------------------------
// Author          : AbsoluteHelix (OCW)
// Dibuat Pada     : 07/03/2023
// Tipe            : PHP
//----------------------------------------------------------------- 

// basik generasi index

use App\Models\Menu;
use App\Models\PenggunaHakAkses;
use Illuminate\Support\Facades\Auth;

// cek root
function getRoot($root, $root_name, $success, $fail)
{
    $res = ($root != null and $root == strtolower($root_name)) ? $success : $fail;

    return $res;
}

// cek id
function getPageID($id, $page_id, $success, $fail)
{
    $res = ($id != null and $id == $page_id) ? $success : $fail;

    return $res;
}

// tampilkan atau dapatkan daftar menu
function getAllMenu()
{
    $menus = Menu::all();
    return $menus;
}

// check jika menu memiliki child
function checkJikaMenuMemilikiAnak($id_induk)
{
    $menus = Menu::where("parent_id", $id_induk)->orderBy("nama", "asc")->get();

    return $menus;
}

// check pengguna dan menu akses
function checkPenggunaAksesMenu($jabatan_id, $menu_id)
{
    return PenggunaHakAkses::where("jabatan_id", $jabatan_id)->where("menu_id", $menu_id)->first();
}

// check akses per page
function checkMenuPerHalaman($jabatan_id, $id_halaman)
{
    // bersihkan url public
    $bersih_public = str_replace("public_", "", $id_halaman);

    $id_halaman_reformat = str_replace("_", "/", $bersih_public);

    $cekUrlAdaPadaMenu = Menu::where("url", $id_halaman_reformat)->first();

    if ($cekUrlAdaPadaMenu != null) {
        $cekAkses = checkPenggunaAksesMenu($jabatan_id, $cekUrlAdaPadaMenu->id);

        if ($cekAkses != null) {
            return true;
        } else {
            return false;
        }
    }

    return false;
}

// buat nomor acak berdasarkan jumlah yang ditentukan
function genNomor($minimalAngka, $maximumAngka, $nomorTambahan = false)
{
    $hasil = rand($minimalAngka, $maximumAngka);
    $tambahan = ($nomorTambahan != false) ? addingZero($nomorTambahan, 3) : null;

    return $hasil . $tambahan;
}

// untuk menampilkan status dengan format text
function getStatusString($kodeStatus, $denganBadge = false, $customStatus = false, $badgeColor = false)
{
    if ($customStatus == false)
        $status_container = ["dibatalkan", "belum disetujui", "disetujui"];
    else
        $status_container = $customStatus;

    if ($denganBadge == true) {
        $color_badge = "primary";

        if ($badgeColor != false) {
            $color_badge = $badgeColor[$kodeStatus];
        }

        return '<span class="badge bg-' . $color_badge . '">' . $status_container[$kodeStatus] . '</span>';
    }

    return $status_container[$kodeStatus];
}

// fungsi untuk mempersingkat Auth::user()
function user()
{
    return Auth::user();
}
