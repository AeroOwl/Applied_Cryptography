<?php

$usr = $_COOKIE['cookie']['user'];
//echo "$usr";
$file = isset($_FILES['uri'])?$_FILES['uri']:"";

function get_input($input)
{
    $input = trim($input);
    $input = stripcslashes($input);
    $input = htmlspecialchars($input);
    return $input;

}

$file = get_input($file);

require_once ("connect_db.php");


if(!function_exists("hex2bin")) { // PHP 5.4起引入的hex2bin
    function hex2bin($data)
    {
        return pack("H*", $data);
    }
}


if ('$usr' == null)
{
    echo 'Oops! Beyond authority. Please login.';

    header("Refresh:5;../MainPage.html");
}
else {
    if ((($file["type"] == "image/jpeg")
            || ($file["type"] == "text/plain"))
        && ($file["size"] < 1000000000000000000000)) {
        if ($file["error"] > 0) {
            echo "Error: " . $file["error"] . "<br />";
        } else {
            echo "Upload: " . $file["name"] . "<br />";
            echo "Type: " . $file["type"] . "<br />";
            echo "Size: " . ($file["size"] / 1024) . " Kb<br />";
            echo "Stored in: " . $file["tmp_name"];


            //服务器与用户之间的sym_key用于对称加密
            //Symmetrical encryption
			//共享文件夹‘/media/sf_AC_Share/’ 作为客户端的存储文件仓库

            if (!$file_content = file_get_contents('/media/sf_AC_Share/' . $file['name']))
                echo "cannot find the file";

            $length = strlen($file_content);

            /* Open the cipher */
            $td = mcrypt_module_open('rijndael-256', '', 'cbc', '');

            /* Create the IV and determine the keysize length, use MCRYPT_RAND
             * on Windows instead */
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            $ks = mcrypt_enc_get_key_size($td);

            /* Obtain key */
            $res = mysqli_query($mysqli, "SELECT * FROM users WHERE usr_name = '$usr'");
            $row = mysqli_fetch_assoc($res);
            $key = $row['sym_key'];
            

            /* Intialize encryption */
            mcrypt_generic_init($td, $key, $iv);


            /* Encrypt data */
            $encrypted_str = mcrypt_generic($td, $file_content);


            /* Terminate encryption handler and close module*/
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);


            /*
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            $block_size = mcrypt_enc_get_block_size($td);
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);

            mcrypt_generic_init($td, $key, $iv);
            $encrypted_file = mcrypt_generic($td, $file_content);

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            print_r(bin2hex($encrypted_file)."\n");
            */

            
            $bin_enc = base64_encode($encrypted_str);
            file_put_contents($file['tmp_name'], $bin_enc); //用加密过的内容替换文件原内容
            echo "</br>";
            echo "$bin_enc";


            $sto_path = "/var/www/ac2016/upload/" . $file['name'];

            if (file_exists($sto_path)) {
                echo $file['name'] . "already exists.";
            } else {
                move_uploaded_file($file['tmp_name'], $sto_path);
                echo "Stored in :" . $sto_path . '<br/>';

                /* 本应该在数据库存储文件散列值，用户下载文件时再进行签名，此处提早做了签名 */

                //Get hash value of file
                $ctx = hash_init('sha256');
                hash_update($ctx, $bin_enc);
                $hash_file = hash_final($ctx);


                //Sign hash value
                $sv_key = file_get_contents('/media/sf_AC_Share/openssl/server.key');
                openssl_sign($hash_file, $signature, $sv_key, $signature_alg = OPENSSL_ALGO_SHA256);


                //Store in database
                $time = getdate();
                $timestamp = $time['month'] . ' ' . $time['mday'] . ' ' . $time['year'];


                //$owner_id = select id from users where usr_name = $usr
                $res = mysqli_query($mysqli, "SELECT * FROM users WHERE usr_name = '$usr'");
                $row = mysqli_fetch_assoc($res);
                $owner = intval($row['id']);

                $name = $file['name'];
                $query = "INSERT INTO files VALUES ('','$name','$owner','$timestamp','$sto_path','$signature','$iv','$length')";

                if (mysqli_query($mysqli, $query))
                    echo "upload success";
                else
                    echo "upload fail";

            }
        }
    }else {
        echo "Invalid file";
        header("Refresh:5;https://2016.ac/MainPage.html");
    }

}


