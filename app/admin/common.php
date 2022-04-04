<?php

use think\facade\Db;

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

if (!function_exists('get_table_fields')) {
    function get_table_fields($table, $cleanComment = false)
    {
        if (!$table) return [];

        $dbname = config('database.connections.mysql.database');

        // 从数据库中获取表字段信息
        $sql        = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        $columnList = Db::query($sql, [$dbname, $table]);

        $fieldlist = [];
        foreach ($columnList as $item) {
            $comment = $item['COLUMN_COMMENT'];
            if ($cleanComment && $comment) {
                $comment = explode(':', $comment);
                $comment = $comment[0] ?? '';
            }
            $fieldlist[$item['COLUMN_NAME']] = $comment;
        }
        return $fieldlist;
    }
}