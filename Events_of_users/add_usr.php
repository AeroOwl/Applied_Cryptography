<?php

$usr=isset($_POST['usr_name'])?$_POST['usr_name']:"";
$pass=isset($_POST['pass'])?$_POST['pass']:"";
$re_pass=isset($_POST['re_pass'])?$_POST['re_pass']:"";

function get_input($input) {
    $input = trim($input);
    $input = stripcslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

$usr = get_input($usr);
$pass = get_input($pass);
$re_pass = get_input($re_pass);

//连接数据库 判断是否已经注册 合法字符集判断 口令强度校验 获取口令散列值 添加用户 *分配公私钥

//$mysqli = mysqli_connect("127.0.0.1", "root", "cuccsac", "db_ac2016", 3307);
require_once ("connect_db.php");

$valid_flag = 1;

function charset_is_valid($chr) {
    if($chr>='a'&&$chr<='z'||
       $chr>='A'&&$chr<='Z'||
       $chr>='0'&&$chr<='9'){
     return 1;
    }else
        return 0;
}

$salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);


$hash_pass = hash('sha256', $pass, $raw_output = false);
echo $hash_pass;
echo "</br>";


$sym_key = hash_pbkdf2($algorithm = 'sha256', $password = $pass, $salt, $count = 5000, $key_length = 32, $raw_output = false);
echo $sym_key;
echo "</br>";


if(mysqli_query($mysqli, "SELECT * FROM users WHERE usr_name = '$usr'"))
{
    if($pass === $re_pass){
        for($i=0;$i<strlen($pass);$i++){
            $chr = substr($pass,$i,1);
            if (!charset_is_valid($chr)) {
                $valid_flag = 2;
            }
        }
        if (strlen($pass)<6)
            $valid_flag = 0;


        $query = "INSERT INTO users(id,usr_name,hash_pass,public_key,private_key,sym_key) VALUES ('','$usr','$hash_pass','','','$sym_key')";

        if($valid_flag == 1) {
            if(mysqli_query($mysqli,$query)) {
                setcookie("cookie[user]", $usr,time()+120,"","2016.ac",1);
                echo "Register Success! Waiting for relocating...";

                header("Refresh:3;https://2016.ac/MainPage.html");
            }
            else {
                echo "insert fail";
            }
        }
        elseif($valid_flag==2)
        {
            echo 'Invalid username';
            header("Refresh:5;https://2016.ac/signup.html");
        }
        else
        {
            echo "U need a stronger password";
            header("Refresh:5;https://2016.ac/signup.html");
        }

    }
    else
        echo "repeat password is inconsistent";
}
else
    echo "username has been taken";

mysqli_close($mysqli);





