<?php
//-----------------------------------------------------------------
// Kumpulan fungsi yang bersangkutan dengan operasi database
//-----------------------------------------------------------------
// Author          : AbsoluteHelix (OCW)
// Dibuat Pada     : 07/03/2023
// Tipe            : PHP
//----------------------------------------------------------------- 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

// Untuk generasi index/tabel data
// query = Referensi Query Model Database (ditemukan pada folder app -> models). penulisan biasanya Model::query()
// nama_tabel = nama dari tabel yang ingin ditampilkan
// search = string/text yang akan dicari di semua kolom
// ignores = array daftar kolom yang tidak mau ditampilkan pada tabel data
// paginate = apakah hasil di bagi menjadi beberapa bagian atau page
// limitPerPage = jumlah data per page jika menggunakan pagination
// return = object
function hdb_Index($model, $viewPath, $nama_tabel, $search = null, array $sorting = [], array $ignores = [], $paginate = false, $limitPerPage = 25, $mergeTambahan = [], $filterTambahan = [], $returnView = true)
{
    setIDTime();

    $query = $model::query();

    // generate columns
    $temp_c = getKolomTabel($nama_tabel);

    // ignore list
    if (count($ignores) <= 0) {
        // gunakan default ignore list
        $ignores = [
            "id",
            "created_at",
            "deleted_at",
            "updated_at"
        ];
    }

    // kolom penampungan sementara
    $columns = [];

    for ($i = 0; $i < count($temp_c); $i++) {
        // cek jika kolom ada pada daftar list ignore jika tidak
        // masukan pada kolom kontainer
        if (!in_array($temp_c[$i], $ignores)) {
            $columns[] = $temp_c[$i];
        }
    }

    // tambahan
    if (count($filterTambahan) > 0) {
        foreach ($filterTambahan as $key => $value) {

            if (is_array($value)) {
                $query->{$key}($value[0], $value[1], $value[2]);
            } else {
                $query->{$key}($value);
            }
        }
    }

    // search/pencarian
    if ($search != null) {
        for ($i = 0; $i < count($columns); $i++) {
            if ($i == 0) {
                $query->where($columns[$i], "LIKE", "%" . $search . "%");
            } else {
                $query->orWhere($columns[$i], "LIKE", "%" . $search . "%");
            }
        }
    }

    // sorting
    if (count($sorting) > 0) {
        foreach ($sorting as $key => $value) {
            $query->orderBy($key, $value);
        }
    } else {
        $query->orderBy("created_at", "desc");
    }


    if ($paginate == true) {
        // query datas akhir
        $datas = $query->paginate($limitPerPage)->withQueryString();
    } else {
        // query datas akhir
        $datas = $query->get();
    }


    $result = [
        "datas" => $datas,
        "columns" => $columns
    ];

    if ($returnView) {
        return view($viewPath, $result, $mergeTambahan);
    }

    return $result;
}

// fungsi untuk simpan/update
// @param model = Refeernsi model ($a = new Model())
// request = Request class atau secondary model
// nama_tabel = nama tabel untuk penunjuk generasi daftar kolom
// isUsingValidation = jika anda menggunakan class Validation pada request, aktifikan ini jika tidak akan return error
// writeLog = jika true maka hasil dari fungsi akan dibuatkan file log
// return = object : sukses , 0 : gagal
function hbd_Store($model, $request, $nama_tabel, $isUsingValidation = false, $writeLog = true, $urlError = null)
{
    setIDTime();

    try {

        // tetapkan model pada variabel sementara
        $data = $model;

        // dapatkan list nama kolom
        $koloms = getKolomTabel($nama_tabel);

        // ulangi daftar kolom
        for ($i = 0; $i < count($koloms); $i++) {


            // cek jika menggunakan fungsi validasi dari laravel 
            if ($isUsingValidation == true) {
                // cek jika key pada array ada
                if (array_key_exists($koloms[$i], $request)) {
                    $data[$koloms[$i]] = $request[$koloms[$i]];
                }
            } else {

                // cek jika request bukan array
                if (!is_array($request)) {
                    if ($request->has($koloms[$i])) {
                        $data[$koloms[$i]] = $request[$koloms[$i]];
                    }
                }
            }
        }

        // simpan
        $data->save();

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('penyimpanan telah berhasil'), date("Y-m-d H:i:s"), Auth::user(), "success");
        }

        return $data;
    } catch (Exception $e) {

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('error - ' . $e->getMessage()), date("Y-m-d H:i:s"), null, "danger", [], "close");
        }

        return redirect()->intended($urlError);
    }
}

// Untuk delete data secara individual
// data = referensi model/data yang ingin didelete
// permanent = jika menggunakan soft delete, jika 1 data akan di delete permanent namun jika 0 data akan di soft delete dan dapat dikembalikan
// writeLog = jika true maka hasil dari fungsi akan dibuatkan file log
// return = 1 : sukses , 0 : gagal
function hbd_Destroy($model, $redirectToUrl, $basedOn = [], $permanent = 0, $writeLog = true)
{
    setIDTime();

    try {
        // query
        $query = $model::query();

        if (count($basedOn) > 0) {
            foreach ($basedOn as $key => $value) {
                $query->where($key, $value);
            }
        }

        // cek jika delete permanent
        if ($permanent == 1) {
            $$query->forceDelete();
        } else {
            $$query->delete();
        }

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('data telah berhasil dihapus'), date("Y-m-d H:i:s"), Auth::user(), "success");
        }

        return redirect()->intended($redirectToUrl);
    } catch (Exception $e) {

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('error - ' . $e->getMessage()), date("Y-m-d H:i:s"), null, "danger", [], "close");
        }

        abort(500);
    }
}

// fungsi untuk melakukan penghapusan masal berdasarkan id
function hbd_DestroyBulk(array $requestIDs, $model, $urlKembali, $pesanQS, $writeLogs = false)
{
    setIDTime();

    try {
        $ids = $requestIDs;

        for ($i = 0; $i < count($ids); $i++) {
            $data = $model::find($ids[$i]);
            $data->delete();
        }

        if ($writeLogs) {
            createLogJson($pesanQS['msg'], date("Y-m-d H:i:s"), Auth::user(), "success", [], "close");
        }

        return genUrl($urlKembali, $pesanQS);
    } catch (Exception $e) {
        if ($writeLogs)
            createLogJson(textTransform('error - ' . $e->getMessage()), date("Y-m-d H:i:s"), null, "warning");

        return genUrl($urlKembali, $pesanQS);
    }
}

// fungsi untuk melakukan aksi tertentu secara masal berdasarkan id
function hbd_ActionBulk(array $requestIDs, $model, $actions = [], $urlKembali, $pesanQS, $writeLogs = false)
{
    setIDTime();

    try {
        $ids = $requestIDs;

        for ($i = 0; $i < count($ids); $i++) {
            $data = $model::find($ids[$i]);

            if (count($actions) > 0) {
                foreach ($actions as $key => $value) {
                    $data[$key] = $actions[$key];
                }
            }

            $data->save();
        }

        if ($writeLogs)
            createLogJson(textTransform($pesanQS['msg']), date("Y-m-d H:i:s"), Auth::user(), "success");

        return genUrl($urlKembali, $pesanQS);
    } catch (Exception $e) {
        if ($writeLogs)
            createLogJson(textTransform('error - ' . $e->getMessage()), date("Y-m-d H:i:s"), null, "warning");

        return genUrl($urlKembali, $pesanQS);
    }
}

// Untuk restore data secara individual
// data = referensi model/data yang ingin direstore
// writeLog = jika true maka hasil dari fungsi akan dibuatkan file log
// return = 1 : sukses , 0 : gagal
function hbd_Restore($data, $writeLog = true)
{
    setIDTime();

    try {
        $data->restore();

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('data telah berhasil dikembalikan'), date("Y-m-d H:i:s"), Auth::user(), "success");
        }

        return 1;
    } catch (Exception $e) {

        // write logs
        if ($writeLog == true) {
            createLogJson(textTransform('error - ' . $e->getMessage()), date("Y-m-d H:i:s"), null, "warning");
        }

        abort(500);
    }
}

// untuk generasi list/daftar nama kolom
// nama_tabel = nama tabel dimana kolom yang digenerasi berada
// return array
function getKolomTabel($nama_tabel)
{
    return Schema::getColumnListing($nama_tabel);
}

//-------------------------------------------------------------------------
// Authentikasi
//-------------------------------------------------------------------------

// request = referensi dari Request class
// credetials = kumpulan item untuk check user login 
// remember = fungsi remember me
// writeLog = jika true maka hasil dari fungsi akan dibuatkan file log
function hbd_Auth($request, array $credentials, $remember = null, $writeLog = true)
{
    setIDTime();

    if ($remember != null) {
        $attemp = Auth::attempt($credentials, $remember);
    } else {
        $attemp = Auth::attempt($credentials);
    }

    if ($attemp) {
        $request->session()->regenerate();

        // write logs
        if ($writeLog == true) {
            // basic log
            createLogJson("user dengan user " . $request->email . " telah login", date("Y-m-d H:i:s"), Auth::user(), "success");
        }
    } else {
        // write logs
        if ($writeLog == true) {
            // basic log
            createLogJson("user dengan user " . $request->email . " telah gagal login", date("Y-m-d H:i:s"), null, "warning");
        }
    }

    return $attemp;
}

// fungsi logout
// request = referensi dari Request class
// return = boolean
function hbd_Logout($request)
{
    setIDTime();

    createLogJson("user dengan nama " . Auth::user()->name . " telah logout/keluar", date("Y-m-d H:i:s"), Auth::user(), "danger", [], "close");

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return true;
}

//-------------------------------------------------------------------------
// Authentikasi
//-------------------------------------------------------------------------
