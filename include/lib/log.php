<?php

class Log {
    private static $logDir; // 静态日志目录

    // 初始化日志目录（静态构造逻辑）
    private static function init() {
        // 仅在首次调用时初始化目录
        if (!isset(self::$logDir)) {
            self::$logDir = LOG_PATH;
            // 若目录不存在则创建
            if (!is_dir(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }
        }
    }

    // 核心日志记录方法（静态）
    private static function logFun($message, $level = 'info') {
        // 确保目录已初始化
        self::init();

        $date = date('Y-m-d');
        $time = date('H:i:s');
        $logFile = self::$logDir . '/' . $date . '.log'; // 按日期拆分文件
        $logContent = "[{$time}] [{$level}] {$message}\n";

        // 追加写入日志
        file_put_contents($logFile, $logContent, FILE_APPEND);
    }

    // 静态快捷方法：记录错误
    public static function error($message) {
        self::logFun($message, 'error');
    }

    // 静态快捷方法：记录信息
    public static function info($message) {
        self::logFun($message, 'info');
    }

    // 可按需添加其他级别（如debug/warn）
    public static function debug($message) {
        self::logFun($message, 'debug');
    }

    public static function warning($message) {
        self::logFun($message, 'warning');
    }
}

