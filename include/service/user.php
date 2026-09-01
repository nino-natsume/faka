<?php
/**
 * Service: User
 */

class User {

    const ROLE_ADMIN = 'admin';     // 管理员、创始人
    const ROLE_WRITER = 'writer';   // 普通用户
    const ROLE_VISITOR = 'visitor'; // 游客
    const ROLE_EDITOR = 'editor';   // 内容编辑
    const OPTION_FOUNDER_UID = 'system_founder_uid';
    const USER_ID_START = 1000;

    private static $founderUid = null;

    static function getFounderUid() {
        if (self::$founderUid !== null) {
            return self::$founderUid;
        }
        $founderUid = 0;
        if (class_exists('Option')) {
            $founderUid = (int)Option::get(self::OPTION_FOUNDER_UID);
        }
        if ($founderUid <= 0 && class_exists('Database') && defined('DB_PREFIX')) {
            try {
                $db = Database::getInstance();
                $row = $db->once_fetch_array("SELECT uid FROM " . DB_PREFIX . "user WHERE role='" . self::ROLE_ADMIN . "' ORDER BY uid ASC LIMIT 1");
                $founderUid = (int)($row['uid'] ?? 0);
            } catch (Throwable $e) {
            }
        }
        if ($founderUid <= 0) {
            $founderUid = 1;
        }
        self::$founderUid = $founderUid;
        return self::$founderUid;
    }

    static function isFounderUid($uid) {
        $uid = (int)$uid;
        return $uid > 0 && $uid === self::getFounderUid();
    }

    static function isFounder($role = ROLE, $uid = UID) {
        $uid = (int)$uid;
        return $role == self::ROLE_ADMIN && self::isFounderUid($uid);
    }

    static function isAdmin($role = ROLE) {
        return $role == self::ROLE_ADMIN;
    }

    static function isVisitor($role = ROLE) {
        return $role == self::ROLE_VISITOR;
    }

    static function isEditor($role = ROLE) {
        return $role == self::ROLE_EDITOR;
    }

    static function isWriter($role = ROLE) {
        return $role == self::ROLE_WRITER;
    }

    /**
     * @deprecated This function is deprecated and will be removed in the future. Use isWriter instead.
     */
    static function isWiter($role = ROLE) {
        return $role == self::ROLE_WRITER;
    }

    /**
     * @deprecated This function is deprecated and will be removed in the future. Use isVisitor instead.
     */
    static function isVistor($role = ROLE) {
        return $role == self::ROLE_VISITOR;
    }

    static function haveEditPermission($role = ROLE) {
        if (self::isAdmin($role)) {
            return true;
        }
        if (self::isEditor($role)) {
            return true;
        }
        return false;
    }

    static function getRoleName($role, $uid = 0) {
        $role_name = '';
        switch ($role) {
            case self::ROLE_ADMIN:
                $role_name = self::isFounderUid($uid) ? '超级管理员' : '管理员';
                break;
            case self::ROLE_EDITOR:
                $role_name = '内容编辑';
                break;
            case self::ROLE_WRITER:
                $role_name = '普通用户';
                break;
            case self::ROLE_VISITOR:
                $role_name = '游客';
                break;
        }
        return $role_name;
    }

    static function checkLoginCode($login_code) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $session_code = isset($_SESSION['code']) ? $_SESSION['code'] : '';
        unset($_SESSION['code']);
        if ((!$login_code || $login_code !== $session_code) && Option::get('login_code') === 'y') {
            return false;
        }
        return true;
    }

    static function checkMailCode($mail_code) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $session_code = isset($_SESSION['mail_code']) ? $_SESSION['mail_code'] : '';
        unset($_SESSION['code']);
        if (!$mail_code || $mail_code !== $session_code) {
            return false;
        }
        return true;
    }



    static function checkRolePermission() {

        if (self::isAdmin()) {
            if (class_exists('Admin_Permission_Service')) {
                Admin_Permission_Service::checkCurrentPagePermission();
            }
            return;
        }

        $request_uri = strtolower(substr(basename($_SERVER['SCRIPT_NAME']), 0, -4));
        if (ROLE === self::ROLE_WRITER && !in_array($request_uri, ['order', 'media', 'blogger', 'comment', 'index', 'article_save', 'plugin_user'])) {
            emMsg('你所在的用户组无法使用该功能，请联系管理员', './');
        }
        if (ROLE === self::ROLE_EDITOR && !in_array($request_uri, ['article', 'twitter', 'media', 'blogger', 'comment', 'index', 'article_save', 'plugin_user'])) {
            emMsg('你所在的用户组无法使用该功能，请联系管理员', './');
        }
    }

    static function getAvatar($avatar_path) {
        if (empty($avatar_path)) {
            return DC_URL . 'admin/views/images/avatar.svg';
        }
        if (filter_var($avatar_path, FILTER_VALIDATE_URL)) {
            return $avatar_path;
        }
        if (strpos($avatar_path, '../') === false) {
            return getFileUrl('../' . $avatar_path);
        }
        return getFileUrl($avatar_path);
    }

}
