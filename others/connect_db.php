<?php

$mysqli = mysqli_connect("127.0.0.1", "root", "cuccsac", "db_ac2016", 3307);

if(!$mysqli){
    log("database connected failed",mysqli_error($mysqli));
}

mysqli_query($mysqli,"set character set 'utf-8'");
mysqli_query($mysqli,"set names 'utf8'");

