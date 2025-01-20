<?php

namespace app\core\src\utilities;

class Logger {

    public function log($data): void {

        if (!app()->getConfig()->get('debugViaFile')) return;

        $seperator = ' --------------------- ';
        $message = ($seperator . date('d-m-Y H:i:s') . ' ERROR: ' . 
            (is_string($data) ? $data : $data->getMessage()) . ' TRACE ' . 
            (is_string($data) ? $data : json_encode($data->getTrace() ?? $data, JSON_PRETTY_PRINT)) . 
            $seperator
        );

        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'debug.log';
        file_put_contents($file, PHP_EOL . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

}