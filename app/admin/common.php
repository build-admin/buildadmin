<?php

use think\facade\Db;

if (!function_exists('get_controller_list')) {
    function get_controller_list($app = 'admin'): array
    {
        $controllerDir = root_path() . 'app' . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
        return get_dir_files($controllerDir);
    }
}

if (!function_exists('get_dir_files')) {
    function get_dir_files($dir): array
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY
        );

        $fileList = [];
        foreach ($files as $file) {
            if (!$file->isDir() && $file->getExtension() == 'php') {
                $filePath        = $file->getRealPath();
                $name            = str_replace($dir, '', $filePath);
                $name            = str_replace(DIRECTORY_SEPARATOR, "/", $name);
                $fileList[$name] = $name;
            }
        }
        return $fileList;
    }
}

if (!function_exists('get_table_list')) {
    function get_table_list(): array
    {
        $tableList = [];
        $database  = config('database.connections.mysql.database');
        $tables    = Db::query("SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.TABLES WHERE table_schema = ? ", [$database]);
        foreach ($tables as $row) {
            $tableList[$row['TABLE_NAME']] = $row['TABLE_NAME'] . ($row['TABLE_COMMENT'] ? ' - ' . str_replace('表', '', $row['TABLE_COMMENT']) : '');
        }
        return $tableList;
    }
}

if (!function_exists('get_table_fields')) {
    function get_table_fields($table, $onlyCleanComment = false): array
    {
        if (!$table) return [];

        $dbname = config('database.connections.mysql.database');
        $prefix = config('database.connections.mysql.prefix');

        // 从数据库中获取表字段信息
        $sql        = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        $columnList = Db::query($sql, [$dbname, $table]);
        if (!$columnList) {
            $columnList = Db::query($sql, [$dbname, $prefix . $table]);
        }

        $fieldList = [];
        foreach ($columnList as $item) {
            if ($onlyCleanComment) {
                $fieldList[$item['COLUMN_NAME']] = '';
                if ($item['COLUMN_COMMENT']) {
                    $comment                         = explode(':', $item['COLUMN_COMMENT']);
                    $fieldList[$item['COLUMN_NAME']] = $comment[0];
                }
                continue;
            }
            $fieldList[$item['COLUMN_NAME']] = $item;
        }
        return $fieldList;
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string): string
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}