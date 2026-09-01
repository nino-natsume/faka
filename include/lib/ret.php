<?php
/**
 * Output class
 * @package DCSHOP
 * @link https://dcshop.xzsc.cc
 */

class Ret {
    public static function success($msg = 'success', $data = '') {
        header('Content-Type: application/json; charset=UTF-8');
        $result = [
            'code' => 200,
            'msg'  => $msg,
            'data' => $data
        ];
        die(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    public static function error($msg) {
        header('Content-Type: application/json; charset=UTF-8');
        $result = [
            'code' => 400,
            'msg'  => $msg,
        ];
        die(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


}
