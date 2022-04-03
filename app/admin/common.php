<?php
if (!function_exists('get_controller_list')) {
    function get_controller_list()
    {
        $controllerDir = app_path() . 'controller' . DIRECTORY_SEPARATOR;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $controllerList = [];
        foreach ($files as $file) {
            if (!$file->isDir() && $file->getExtension() == 'php') {
                $filePath              = $file->getRealPath();
                $name                  = str_replace($controllerDir, '', $filePath);
                $name                  = str_replace(DIRECTORY_SEPARATOR, "/", $name);
                $controllerList[$name] = $name;
            }
        }
        return $controllerList;
    }
}

if (!function_exists('get_table_list')) {
    function get_table_list()
    {
        $tableList = [];
        $tables    = \think\facade\Db::query("SHOW TABLES");

        foreach ($tables as $table) {
            $tableList[reset($table)] = reset($table);
        }

        return $tableList;
    }
}