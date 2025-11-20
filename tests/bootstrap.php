<?php

if (!function_exists('EA()')) {
    function EA($arr, $die = true) {
        if ($arr) {
            print '<pre>';
            print_r($arr);
            print '</pre>';
        }

        if ($die) {
            die();
        }
    }
}

require_once __DIR__ . "/../vendor/autoload.php";