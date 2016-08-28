<?php

$usr=isset($_POST['usr_name'])?$_POST['usr_name']:"";
$pass=isset($_POST['pass'])?$_POST['pass']:"";

function get_input($input)
{
    $input = trim($input);
    $input = stripcslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

$usr = get_input($usr);
$pass = get_input($pass);


$hash_pass = hash('sha256', $_POST['pass'], $raw_output = false);
$hash_pass = substr($hash_pass,0,45);
//echo $hash_pass;
//echo "</br>";

$mysqli = mysqli_connect("127.0.0.1", "root", "cuccsac", "db_ac2016", 3307);
$res = mysqli_query($mysqli,"SELECT * FROM users WHERE usr_name = '$usr'");
$row = mysqli_fetch_assoc($res);
$hash = $row['hash_pass'];
//echo $hash;
//echo "</br>";

if($hash == $hash_pass){
    echo "Log in success! Waiting for relocating...";
    setcookie("cookie[user]", $usr,time()+300,"","2016.ac",1);
    header("Refresh:3;https://2016.ac/MainPage.html");
}
else {
    echo "username or password error";
    header("Refresh:3; https://2016.ac/index.html");
}
