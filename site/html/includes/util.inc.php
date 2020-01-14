<?php
/**
 * Created by PhpStorm.
 * User: julienbenoit
 * Date: 2020-01-14
 * Time: 12:00
 */

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>