<?php

//防盗链
if(strcmp($_SERVER['HTTP_REFERER'],'https://2016.ac/list_show.php'))
{
        header("Location: https://2016.ac/index.html");
}


//下载URL设置有效期，过期后禁止重复访问
$time = isset($_GET['timestamp'])?$_GET['timestamp']:"";
$now = time();

if($now-$time>600)
    header("Location: https://2016.ac/index.html");


$usr = $_COOKIE['cookie']['user'];

$path_parts = pathinfo($_GET['filename']);
//$path_parts = $_GET['filename'];
$file_name = $_GET['filename'];
$file_path = "/var/www/ac2016/upload/" . $file_name;


if (!file_exists($file_path)) {
    echo "can't find file";
    //header("Refresh:3;https://2016.ac/index.php");
}

if (!fopen($file_path, "r")) {
    echo "can not open";
    //header("Refresh:3;https://2016.ac/index.php");
}

if (!$str = file_get_contents($file_path)) {
    echo "can not read";
    //header("Refresh:3;https://2016.ac/index.php");
}

/* Open the cipher */
$td = mcrypt_module_open('rijndael-256', '', 'cbc', '');

/* Obtain the IV and determine the keysize length, use MCRYPT_RAND
* on Windows instead */
require_once ("connect_db.php");
//$mysqli = mysqli_connect("127.0.0.1", "root", "cuccsac", "db_ac2016", 3307);

$res = mysqli_query($mysqli, "SELECT * FROM files WHERE original_name = '$file_name'");
$row = mysqli_fetch_assoc($res);
$iv = $row['iv'];
$hash = $row['signed_hash'];
$length = $row['length'];

$file = fopen('signed_hash.txt', "w");
fwrite($file, $hash);
fclose($file);

require_once("check_login.php");

if(check_login()) {

    $ks = mcrypt_enc_get_key_size($td);

    /* Create key */
    $res = mysqli_query($mysqli, "SELECT * FROM files WHERE original_name = '$file_name'");
    $row = mysqli_fetch_assoc($res);
    $owner = $row['owner_id'];
    
    $res = mysqli_query($mysqli, "SELECT * FROM users WHERE id = '$owner'");
    $row = mysqli_fetch_assoc($res);
    $key = $row['sym_key'];


    /* Initialize encryption module for decryption */
    mcrypt_generic_init($td, $key, $iv);


    /* Decrypt encrypted string */
    echo "base64_encoded_decrypted_str:" . "$str";
    $str_enc = base64_decode($str);
    $decrypted_str = mdecrypt_generic($td, $str_enc);

    /* Terminate decryption handle and close module */
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);


    echo"</br>";echo"</br>";
    echo "decrypted_str:" . "$decrypted_str";
    echo"</br>";echo"</br>";


    $file = fopen('download.txt', "w");
    fwrite($file, $decrypted_str, $length);
    //rtrim($str, "\0");
    fclose($file);

    /*
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename="downloaded.txt"');
    readfile('download.txt');
    */

    
    $files = array('download.txt','signed_hash.txt');
    $zipname = 'file.zip';
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE);
    foreach ($files as $file) {
        $zip->addFile($file);
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.$zipname);
    header('Content-Length: ' . filesize($zipname));
    readfile($zipname);

    mysqli_close($mysqli);


}else{

    //匿名用户
    $files = array('$file_name','signed_hash.txt');
    $zipname = 'file.zip';
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE);
    foreach ($files as $file) {
        $zip->addFile($file);
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.$zipname);
    header('Content-Length: ' . filesize($zipname));
    readfile($zipname);

}






