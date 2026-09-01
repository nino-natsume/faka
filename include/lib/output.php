<?php
/**
 * Output class
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Output {
    public static function ok($data = '') {
        header('Content-Type: application/json; charset=UTF-8');
        $result = [
            'code' => 0,
            'msg'  => 'ok',
            'data' => $data
        ];
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public static function data($data = [], $count = 0, $extra = []) {
        header('Content-Type: application/json; charset=UTF-8');
        $result = [
            'code' => 0,
            'msg'  => 'ok',
            'data' => $data,
            'count' => $count
        ];
        if (!empty($extra) && is_array($extra)) {
            $result = array_merge($result, $extra);
        }
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public static function error($msg, $httpCode = 400) {
        header('Content-Type: application/json; charset=UTF-8');
        if ($httpCode == 200) {
            header("HTTP/1.1 200 OK");
        } else {
            header("HTTP/1.1 400 Bad Request");
        }
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => ''
        ];
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public static function authError($msg) {
        header('Content-Type: application/json; charset=UTF-8');
        header("HTTP/1.1 401 Unauthorized");
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => ''
        ];
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 演示站检查 - 如果是演示站则拒绝操作
     * @param string $msg 自定义提示消息
     */
    public static function demoCheck($msg = '演示站禁止此操作') {
        if (Register::isDemoSite()) {
            self::error($msg);
        }
    }
}
