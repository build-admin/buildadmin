<?php

use ba\Filesystem;

if (!function_exists('get_controller_list')) {
    function get_controller_list($app = 'admin'): array
    {
        $controllerDir = root_path() . 'app' . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
        return Filesystem::getDirFiles($controllerDir);
    }
}
