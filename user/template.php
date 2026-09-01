<?php

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

$db = Database::getInstance();
$db_prefix = DB_PREFIX;

function stationTemplateSettingMeta($kind)
{
    switch ($kind) {
        case 'user_tpl':
            return [
                'path' => USER_TPLS_PATH,
                'license_prefix' => 'user_template:',
                'page_error' => '用户后台模板配置文件不存在或已损坏',
                'ajax_error' => '用户后台模板配置文件不存在或已损坏',
            ];
        case 'bottom_nav':
            return [
                'path' => BOTTOM_NAV_TPLS_PATH,
                'license_prefix' => 'bottom_nav_template:',
                'page_error' => '底部导航模板配置文件不存在或已损坏',
                'ajax_error' => '底部导航模板配置文件不存在或已损坏',
            ];
        default:
            return [
                'path' => TPLS_PATH,
                'license_prefix' => 'template:',
                'page_error' => '分店模板配置文件不存在或已损坏',
                'ajax_error' => '分店模板配置文件不存在或已损坏',
            ];
    }
}

function stationTemplateSettingLicenseMessage($status)
{
    switch ((string)$status) {
        case 'unauthorized':
            return '模板未授权，请联系站长';
        case 'expired':
            return '模板已到期，请联系站长续期';
        case 'blocked':
            return '模板已被禁用，请联系站长';
        case 'tampered':
            return '模板授权异常，请联系站长';
        default:
            return '模板授权状态异常，请联系站长';
    }
}

function stationTemplateInvokeSettingView($tpl)
{
    $func = new ReflectionFunction('plugin_setting_view');
    if ($func->getNumberOfParameters() > 0) {
        plugin_setting_view($tpl);
    } else {
        plugin_setting_view();
    }
}

function stationTemplateInvokeSettingSave($tpl)
{
    $func = new ReflectionFunction('plugin_setting');
    if ($func->getNumberOfParameters() > 0) {
        plugin_setting($tpl);
    } else {
        plugin_setting();
    }
}

if ($action === 'upload') {
    $Media_Model = new Media_Model();
    $attach = isset($_FILES['image']) ? $_FILES['image'] : (isset($_FILES['file']) ? $_FILES['file'] : '');

    if (empty($attach) || !is_array($attach) || empty($attach['name'])) {
        Output::error('请选择要上传的文件', 200);
    }

    $uploadCheckResult = Media::checkUpload($attach);
    if ($uploadCheckResult !== true) {
        Output::error($uploadCheckResult, 200);
    }

    $ret = '';
    upload2local($attach, $ret);
    if (empty($ret['success']) || empty($ret['file_info'])) {
        Output::error($ret['message'] ?? '上传失败', 200);
    }

    $Media_Model->addMedia($ret['file_info']);
    $filePath = $ret['file_info']['file_path'] ?? '';
    if (!empty($filePath) && substr($filePath, 0, 1) !== '/') {
        $filePath = '/' . $filePath;
    }
    Output::ok(['src' => $filePath, 'url' => $filePath]);
}

if($action == 'setting_page'){
    $kind = trim((string)Input::getStrVar('kind'));
    $kind = in_array($kind, ['user_tpl', 'bottom_nav'], true) ? $kind : 'front';
    $meta = stationTemplateSettingMeta($kind);
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile($meta['path'], $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap($meta['path'], $safeTpl) || !loadTemplateBootstrap($meta['path'], $safeTpl) || !is_dir($meta['path'] . $safeTpl . '/') || $settingFile === false) {
        emMsg($meta['page_error'], './station.php?action=setting_tpl');
    }
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = ($meta['license_prefix'] ?? 'template:') . $safeTpl;
    // 分站模板配置页打开时强制同步一次授权状态，避免历史分站域名缓存的 unauthorized 继续拦截。
    if (!PluginLicense::verify($licenseKey, true)) {
        emMsg(stationTemplateSettingLicenseMessage(PluginLicense::getStatus($licenseKey)), './station.php?action=setting_tpl');
    }
    $GLOBALS['__template_setting_kind'] = $kind;
    $GLOBALS['__template_setting_tpl'] = $safeTpl;

    include View::getUserView('open_head');
    require_once DC_ROOT . '/include/lib/template_setting_mobile.php';
    require_once $settingFile;
    stationTemplateInvokeSettingView($safeTpl);
    include View::getUserView('open_foot');
}
if($action == 'setting_ajax'){
    if (!LoginAuth::checkAjaxToken()) {
        output::error('安全token校验失败，请刷新页面重试');
    }
    $kind = trim((string)Input::getStrVar('kind'));
    $kind = in_array($kind, ['user_tpl', 'bottom_nav'], true) ? $kind : 'front';
    $meta = stationTemplateSettingMeta($kind);
    $tpl = trim((string)Input::getStrVar('tpl'));
    $safeTpl = preg_replace('/^([\w-]+)$/i', '$1', $tpl);
    $settingFile = getTemplateSettingFile($meta['path'], $safeTpl);
    if ($safeTpl !== $tpl || !checkTemplateBootstrap($meta['path'], $safeTpl) || !loadTemplateBootstrap($meta['path'], $safeTpl) || !is_dir($meta['path'] . $safeTpl . '/') || $settingFile === false) {
        output::error($meta['ajax_error']);
    }
    require_once DC_ROOT . '/include/lib/plugin_license.php';
    $licenseKey = ($meta['license_prefix'] ?? 'template:') . $safeTpl;
    if (!PluginLicense::verify($licenseKey, true)) {
        output::error(stationTemplateSettingLicenseMessage(PluginLicense::getStatus($licenseKey)));
    }
    $GLOBALS['__template_setting_kind'] = $kind;
    $GLOBALS['__template_setting_tpl'] = $safeTpl;

    require_once $settingFile;
    stationTemplateInvokeSettingSave($safeTpl);
}
