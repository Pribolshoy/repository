<?php

namespace pribolshoy\repository\tests;

use pribolshoy\repository\services\AbstractService;

class CommonTestCase extends \PHPUnit\Framework\TestCase
{
    protected static function dump($arr, $die = true)
    {
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