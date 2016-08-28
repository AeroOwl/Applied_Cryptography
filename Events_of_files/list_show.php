<?php

    $mysqli = mysqli_connect("127.0.0.1", "root", "cuccsac", "db_ac2016", 3307);

    $result = mysqli_query($mysqli, "SELECT * FROM files");
    $num_results = $result->num_rows;

    echo '<p>Number of file message found:' . $num_results . '<p>';


    echo '<ul>';
    for ($i = 0; $i < $num_results; $i++) {
        $row = mysqli_fetch_assoc($result);
        $id = stripslashes($row['id']);
        $name = stripslashes($row['original_name']);
        $path = stripslashes($row['uri']);
        $date = stripslashes($row['created_at']);
        $fingerprint = stripslashes($row['signed_hash']);

        echo '<li>' . ' ' . $id;
        echo $name;

        $time = time();
        echo "$time";

        //echo "<a href = 'http://localhost/upload/$name'>".'Download'.'</a>'.'</li>';
        $url = 'https://2016.ac/download_results.php?filename='. urlencode($name) . "&timestamp=" . urlencode($time);
        echo " <a href = $url>Download</a></li> ";
    }
    echo '</ul>';

    $result->free();
    mysqli_close($mysqli);

