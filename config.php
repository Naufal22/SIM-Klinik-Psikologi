<?php

date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'klinik_psikologi';


$conn = mysqli_connect($host, $user, $pass, $dbname);

// if (!$conn) {
//     die('koneksi gagal');
// } else {
//     echo 'koneksi berhasil';
// }

$main_url = 'http://localhost/klinik-psikologi/';


function uploadGbr($url){
    $namafile   = $_FILES['gambar']['name'];
    $ukuran     = $_FILES['gambar']['size'];
    $tmp        = $_FILES['gambar']['tmp_name'];

    $eksentesiValid = ['jpg', 'jpeg', 'png'];
    $ekstensiFile   = explode('.',$namafile);
    $ekstensiFile   = strtolower(end($ekstensiFile));

    if(!in_array($ekstensiFile, $eksentesiValid)) {
        echo "<script>
                alert('Input user baru gagal, file yang anda upload bukan gambar !');
                window.location = '$url'
            </script>";
        die();
    }

    if ($ukuran > 1000000) {
        echo "<script>
                alert('Input user baru gagal, maksimal ukuran gambar !');
                window.location = '$url'
            </script>";
        die();
    }

    $namafileBaru = time() ."-". $namafile;

    move_uploaded_file($tmp, '../_dist/gambar/' . $namafileBaru);
    return $namafileBaru;

};

?>