//-----------------------------------------------------------------
// Kumpulan fungsi pendukung development JS
//-----------------------------------------------------------------
// Author          : AbsoluteHelix (OCW)
// Dibuat Pada     : 07/03/2023
// Tipe            : Javascript / JS
//----------------------------------------------------------------- 

function addFilter(text_id, base_url){

    v = document.getElementById(text_id);
    url = base_url + "?q=" + v.value;

    final = url.replace(/&amp;/g, "&")

    window.open(final, "_SELF");
}

function lihatPassword(passwordInputClass, lihatPasswordButtonID, iconOff, iconOn){
    if($("." + passwordInputClass).attr("type") == "password"){
        $("." + passwordInputClass).prop("type", "text");

        icon = '<i class="material-icons" style="margin-right:-7px;">'+ iconOff +'</i>';

        $("#" + lihatPasswordButtonID).html(icon);
    } else {
        $("." + passwordInputClass).prop("type", "password");

        icon = '<i class="material-icons" style="margin-right:-7px;">'+ iconOn +'</i>';

        $("#" + lihatPasswordButtonID).html(icon);
    }
}

function acakPassword(inputTujuan, jumlahAcakInput){
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let hasil = "";

    length = $(jumlahAcakInput).val();
    counter = 0;

    while(counter < length){
        hasil += characters.charAt(Math.floor(Math.random() * characters.length));
        counter++;
    }

    $(inputTujuan).val(hasil);
}

function deleteSemuaTerpilih(classCheckbox, deleteUrl, tokenCsrf){
    var ceks = document.querySelectorAll("." + classCheckbox + ":checked");
    var ids = Array.prototype.map.call(ceks, function(element) {
        return element.value;
    });

    if(ids.length <= 0){
        alert("Tolong Pilih Data Terlebih Dahulu");
    } else {
        $.ajax({
            type: "POST",
            url: deleteUrl,
            data: {
                "_token": tokenCsrf,
                "ids" : ids,
            },
            success: function (response) {
                window.open(response, "_self");
            }
        });
    }
}

function aksiSemuaTerpilih(classCheckbox, aksiUrl, tokenCsrf){
    var ceks = document.querySelectorAll("." + classCheckbox + ":checked");
    var ids = Array.prototype.map.call(ceks, function(element) {
        return element.value;
    });

    if(ids.length <= 0){
        alert("Tolong Pilih Data Terlebih Dahulu");
    } else {
        $.ajax({
            type: "POST",
            url: aksiUrl,
            data: {
                "_token": tokenCsrf,
                "ids" : ids,
            },
            success: function (response) {
                window.open(response, "_self");
            }
        });
    }
}

function editYangTerpilih(classCheckbox, urlWithoutQuearyStringEdit){
    var ceks = document.querySelectorAll("."+ classCheckbox +":checked");
    var ids = Array.prototype.map.call(ceks, function(element) {
        return element.value;
    });

    if(ids.length == 1){
        url = urlWithoutQuearyStringEdit + "?edit=" + ids[0];

        window.open(url, "_SELF");
    }
}

// Konfirmasi Fungsi
function konfirmasiWindowDeleteCallback(deleteCallback, classCheckbox, urlDelete, csrfToken){

    const modal = new bootstrap.Modal(document.getElementById("konfirmasi_dialog"), {})
    modal.show();

    // cek jika diklik
    $("#konfirmasi_yes").click(function(){
        deleteCallback(classCheckbox, urlDelete, csrfToken)
    });

    $("#konfirmasi_no").click(function(){
        return false;
    });
}

// Konfirmasi Fungsi
function konfirmasiWindowUntukForm(formID, event){

    event.preventDefault();

    form = document.getElementById(formID);

    const modal = new bootstrap.Modal(document.getElementById("konfirmasi_dialog"), {})
    modal.show();

    // cek jika diklik
    $("#konfirmasi_yes").click(function(){
        form.submit();
    });

    $("#konfirmasi_no").click(function(){
        modal.show("hide");
    });
}

// Konfirmasi Fungsi
function konfirmasiWindowUrl(url){

    const modal = new bootstrap.Modal(document.getElementById("konfirmasi_dialog"), {})
    modal.show();

    // cek jika diklik
    $("#konfirmasi_yes").click(function(){
        window.open(url, "_SELF")
    });

    $("#konfirmasi_no").click(function(){
        return false;
    });
}

// Tabel
function pilihRecord(id){
    if($("#ck_" + id).is(":checked")){
        $("#tr_" + id).removeClass("tr-selected");
        $("#ck_" + id).prop("checked", false);
    } else {
        $("#tr_" + id).addClass("tr-selected");
        $("#ck_" + id).prop("checked", true);
    }
}

function getCekArray(classCek){
    var ceks = document.querySelectorAll("."+classCek+":checked");
    var ids = Array.prototype.map.call(ceks, function(element) {
        return element.value;
    });

    return ids;
}

// menampilkan snackbar / toast
function snackbars() {
    // Get the snackbar DIV
    var x = document.getElementById("snackbar");

    // Add the "show" class to DIV
    x.className = "show";

    // After 3 seconds, remove the show class from DIV
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}
