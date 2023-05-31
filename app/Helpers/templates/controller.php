<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // indexing atau daftar
    public function index()
    {
        return hdb_Index(Menu::class, "admin.menu.index", "menus", getQS("q"), initSortComponent("d", "sort"), [
            "id", "remember_token", "created_at", "updated_at", "deleted_at", "password"
        ], 25);
    }

    // form create
    public function create()
    {
        $menus = Menu::all();
        return hbf_Create(Menu::class, "admin.menu.create", ["menus" => $menus]);
    }

    // fungsi store atau simpan
    public function store(Request $request)
    {
        $validate = $request->validate([
            'nama' => 'required|max:255',
            'url' => 'max:255',
            'parent_id' => "",
            'icon' => ""
        ]);

        $model = getQS("edit") == null ? new Menu() : Menu::find(getQS("edit"));

        hbd_Store($model, $validate, "menus", true, true, genUrl('admin/menu', ["msg" => textTransform("terjadi kesalahan pada sistem")]));

        $url = (getQS("link") != null) ? getQS("link") : 'admin/menu/create';

        return redirect()->intended(genUrl($url, getQS("edit") == null ? genResultQS(textTransform("data telah berhasil disimpan")) : genResultQS(textTransform("data telah berhasil diperbarui"), "success", ["edit" => getQS("edit")])));
    }

    // fungsi delete data
    public function destroy($id)
    {
        return hbd_Destroy(Menu::class, genUrl("admin/menu", genResultQS("data telah berhasil dihapus")), ["id" => $id], 0, true);
    }

    // fungsi delete data secara masal
    public function destroyBulk(Request $request)
    {
        return hbd_DestroyBulk($request->ids, Menu::class, "admin/menu", genResultQS("data telah berhasil didelete"), true);
    }
}
