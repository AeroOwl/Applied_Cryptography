<?php
function check_login(){
    if(!isset($_COOKIE['user'])){
    header('Location:../index.php');
    return false;
    }
    else
        return true;
    
}
