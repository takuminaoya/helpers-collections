<?php

//-----------------------------------------------------------------
// Kumpulan fungsi pendukung development frontend
//-----------------------------------------------------------------
// Author          : AbsoluteHelix (OCW)
// Dibuat Pada     : 10/03/2023
// Tipe            : PHP/HTML/CSS
//----------------------------------------------------------------- 

// pembuatan global element single line html
function formElement($element, $isian, array $atts = [])
{
    return '<' . $element . ' ' . formAttributs($atts) . '>' . $isian . '</' . $element . '>';
}

// pembuatan label
function formButton($namaButton, $tipeButton = "submit", array $atts = [], $inputGroups = [])
{
    if (count($inputGroups) > 0) {

        $groups = implode(" ", $inputGroups);
        $button = '<button type="' . $tipeButton . '"' . formAttributs($atts) . '>' . $namaButton . '</button>';

        $input_groups = '<div class="input-group">
            ' . $groups . ' ' . $button . '
        </div>';

        return $input_groups;
    }

    return '<button type="' . $tipeButton . '"' . formAttributs($atts) . '>' . $namaButton . '</button>';
}

// pembuatan label
function formLabel($for, $label, array $atts = [])
{
    return '<label for="' . $for . '" ' . formAttributs($atts) . '>' . $label . '</label>';
}

// pembuatan input dengan PHP
function formInput($namaInput, $tipeInput, array $atts = [], $icon = false)
{
    // with icon
    if ($icon != false) {
        $generasiInput = '<input type="' . $tipeInput . '" name="' . $namaInput . '" ' . formAttributs($atts) . '>';
        $generasiHtml = '<div class="input-group mb-3">
            <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1" style="border-radius:10px 0px 0px 10px !important;">' . $icon . '</span>
            </div>
            ' . $generasiInput . '
        </div>';
    } else {
        $generasiHtml = '<input type="' . $tipeInput . '" name="' . $namaInput . '" ' . formAttributs($atts) . '>';
    }

    return $generasiHtml;
}

// form attributs
function formAttributs(array $atts)
{
    // cek attributs
    if (count($atts) > 0) {
        $attributKontainer = [];

        foreach ($atts as $key => $value) {

            // cek jika value adalah array
            if (is_array($value)) {
                $stringvalues = implode(" ", $value);

                $generateAttribut = $key . "='" . $stringvalues . "'";
            } else if ($value == null) { // check jika value adalha null
                $generateAttribut = $key;
            } else {
                $generateAttribut = $key . "='" . $value . "'";
            }

            // masukan ke kontainer
            $attributKontainer[] = $generateAttribut;
        }

        // convert attribut menjadi string
        $stringAttributs = implode(" ", $attributKontainer);

        return $stringAttributs;
    }
}

// initialisasi alret untuk menampilkan result message
function displayResult($checker, $tipe)
{
    $gen = null;

    if (isNameSet($checker)) {
        if (isNameSet($tipe)) {
            $gen = '<div class="alert alert-' . $_GET[$tipe]  . '" role="alert">' . $_GET[$checker] . '</div>';
        } else {
            $gen = '<div class="alert alert-success" role="alert">' . $_GET[$checker] . '</div>';
        }
    }

    return $gen;
}

// untuk merapikan text
// Contoh : value_test = Value Test
// text = string/text yang mau dirapikan
// replace = ubah lambang atau kode yang di tentukan di parameter ini menjadi spasi default : "_"
// tipe = tipe text format kapital, huruf besar semua, atau huruf kecil semua 
// tipe list = capitalize, upper, lower
function textTransform($text, $replace = "_", $tipe = "capitalize")
{

    $normalise = strtolower($text);
    $final = str_replace($replace, " ", $normalise);

    switch ($tipe) {
        case 'capitalize':
            $result = ucwords($final);
            break;

        case 'lower':
            $result = $final;
            break;

        case 'upper':
            $result = strtoupper($final);
            break;
    }

    return $result;
}

// pembuatan select
function formSelect($namaSelect, $dataSet, array $atts = [], $tipeSelect = "static", $key = null, $values = null, $opsiKosong = null, $condition1 = null)
{
    $dataSetContainer = [];
    $selected = "";

    if ($opsiKosong != null) {
        $dataSetContainer[] = '<option value="">' . $opsiKosong . '</option>';
    }

    if ($tipeSelect == "static") {
        foreach ($dataSet as $key => $value) {
            if ($condition1 != null && $value == $condition1) {
                $selected = "selected";
            } else {
                $selected = "";
            }

            $dataSetContainer[] = '<option value="' . $value . '" ' . $selected . ' >' . textTransform($key) . '</option>';
        }
    } else {
        foreach ($dataSet as $data) {
            if ($condition1 != null && $data[$key] == $condition1) {
                $selected = "selected";
            } else {
                $selected = "";
            }

            $dataSetContainer[] = '<option value="' . $data[$key] . '" ' . $selected . '>' . textTransform($data[$values]) . '</option>';
        }
    }

    $selectContainer = '<select name=' . $namaSelect . ' ' . formAttributs($atts) . ' >' . implode("", $dataSetContainer) . '</select>';

    return $selectContainer;
}

// fungsi untuk generasi create controller
function hbf_Create($model, $viewPath, $dataTambahan = [])
{
    // kontainer hasil
    $data = getQS("edit") != null ? $model::find(getQS("edit")) : null;

    return view($viewPath, [
        "data" => $data
    ], $dataTambahan);
}

// fungsi untuk menambahakan format pada td tanpa switchcase
// ["tanggal" => date($data[$col])]
function valueReformated($namaValue, $dataValue, array $listValue)
{
    // cek jika key exist/ada
    if (array_key_exists($namaValue, $listValue)) {
        return $listValue[$namaValue];
    } else {
        return $dataValue[$namaValue];
    }
}
