<?php

ini_set('memory_limit', '-1');
require 'vendor/autoload.php';
// Load the config file
require_once 'config.php';
use Ifsnop\Mysqldump as IMysqldump;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (! isset($_POST['code']) || ! isset($_POST['token'])) {
        exit;
    }

    $code = $_POST['code'] ?: '';
    $token = $_POST['token'] ?: '';

    if (! $code || ! $token) {
        exit;
    }

    $db = array_values(array_filter($config, function ($item) use ($code, $token) {
        return $item['code'] === $code && $item['token'] === $token;
    }));
    if (count($db) == 0 || count($db) > 1) {
        exit;
    }
    $db = $db[0];

    $dumpSettings = [
        'compress' => IMysqldump\Mysqldump::GZIPSTREAM, // Compression level
        'no-data' => false, // Exclude table data from dump
        'add-drop-table' => true, // Add DROP TABLE statements
    ];

    $hostname = isset($db['db_host']) && $db['db_host'] ? $db['db_host'] : 'localhost';
    $username = $db['db_user'];
    $password = $db['db_pass'];
    $database = $db['db'];
    $filename = "{$database}_backup_".date('YmdHis').'.sql.gz';

    try {
        $dump = new IMysqldump\Mysqldump("mysql:host=$hostname;dbname=$database", $username, $password, $dumpSettings);
        $dump->start($filename);

        if (file_exists($filename)) {
            $file = fopen($filename, 'r');
            $len = 1024;
            $fileContent = fread($file, $len);
            while (! feof($file)) {
                $fileContent .= fread($file, $len);
            }
            fclose($file);
            unlink($filename);
            echo $fileContent;
            exit;
        } else {
            exit;
        }

    } catch (\Exception $e) {
        exit;
    }
}
