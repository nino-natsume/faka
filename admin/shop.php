<?php
/**
 * setting
 */

/**
 * @var string $action
 * @var object $CACHE
 */

require_once 'globals.php';

if (empty($action)) {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);

    $br = '<a href="./">数据中心</a><a href="./shop.php">商城配置</a><a><cite>基础设置</cite></a>';
    include View::getAdmView('header');
    require_once(View::getAdmView('shop'));
    include View::getAdmView('footer');
    View::output();
}
if ($action == 'index_save') {
    LoginAuth::checkToken();

    $virtualCurrencyName = Input::postStrVar('virtual_currency_name', '积分');
    $virtualCurrencyName = preg_replace('/[\r\n\t]+/u', '', strip_tags($virtualCurrencyName));
    if ($virtualCurrencyName === '') {
        $virtualCurrencyName = '积分';
    }
    $virtualCurrencyNameLen = function_exists('mb_strlen') ? mb_strlen(stripslashes($virtualCurrencyName), 'UTF-8') : strlen(stripslashes($virtualCurrencyName));
    if ($virtualCurrencyNameLen > 12) {
        Output::error('虚拟货币名称最多 12 个字符');
    }
    
    $getData = [
        'balance_switch' => Input::postStrVar('balance_switch', 'n'),
        'virtual_currency_name' => $virtualCurrencyName,
        'continue_pay_switch' => Input::postStrVar('continue_pay_switch', 'n'),
        'continue_pay_timeout' => max(1, min(1440, Input::postIntVar('continue_pay_timeout', 30))),
        'pay_redirect' => Input::postStrVar('pay_redirect', 'list'),
        'order_goods_img_switch' => Input::postStrVar('order_goods_img_switch', 'n'),
        'kami_order' => Input::postStrVar('kami_order', 'asc'),
    ];
    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('tags', 'options', 'comment', 'record'));
    Output::ok();
}

if ($action == 'gg') {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);


    $br = '<a href="./">数据中心</a><a href="./shop.php">商城配置</a><a><cite>公告设置</cite></a>';


    include View::getAdmView('header');
    require_once(View::getAdmView('shop_gg'));
    include View::getAdmView('footer');
    View::output();
}
if ($action == 'gg_save') {
    LoginAuth::checkToken();
    $getData = [
        'roll_bulletin'    => Input::postStrVar('roll_bulletin', ''),
        'home_bulletin'    => Input::postStrVar('home_bulletin', ''),
    ];
    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('tags', 'options', 'comment', 'record'));
    Output::ok();
}

if ($action == 'btx') {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);



    $br = '<a href="./">数据中心</a><a href="./shop.php">商城配置</a><a><cite>下单必填项</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('shop_btx'));
    include View::getAdmView('footer');
    View::output();
}
if ($action == 'btx_save') {
    LoginAuth::checkToken();
    $getData = [
        'order_required' => Input::postStrVar('order_required'),
    ];
    // d($getData);die;
    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('tags', 'options', 'comment', 'record'));
    Output::ok();
}


if ($action == 'index') {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);

    $sales_switch = empty($sales_switch) ? 'y' : $sales_switch;
    $stock_switch = empty($stock_switch) ? 'y' : $stock_switch;
    $order_email_switch = empty($order_email_switch) ? 'y' : $order_email_switch;
    $order_pwd_switch = empty($order_pwd_switch) ? 'y' : $order_pwd_switch;
    $order_tel_switch = empty($order_tel_switch) ? 'y' : $order_tel_switch;
    $balance_switch = empty($balance_switch) ? 'y' : $balance_switch;
    $pay_redirect = empty($pay_redirect) ? 'list' : $pay_redirect;
    $kami_order = empty($kami_order) ? 'asc' : $kami_order;

    $br = '<ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">数据中心</a></li>
        <li class="breadcrumb-item"><a href="./setting.php">系统管理</a></li>
        <li class="breadcrumb-item active" aria-current="page">首页设置</li>
    </ol>';


    include View::getAdmView('header');
    require_once(View::getAdmView('setting_index'));
    include View::getAdmView('footer');
    View::output();
}




if ($action == 'save') {
    LoginAuth::checkToken();
    $getData = [
        'blogname'            => Input::postStrVar('blogname'),
        'blogurl'             => Input::postStrVar('blogurl'),
        'bloginfo'            => Input::postStrVar('bloginfo'),
        'icp'                 => Input::postStrVar('icp'),
        'footer_info'         => Input::postStrVar('footer_info'),
        'timezone'            => Input::postStrVar('timezone'),
        'detect_url'          => Input::postStrVar('detect_url', 'n'),
        'panel_menu_title'    => Input::postStrVar('panel_menu_title'),
    ];

    if ($getData['blogurl'] && substr($getData['blogurl'], -1) != '/') {
        $getData['blogurl'] .= '/';
    }
    if ($getData['blogurl'] && strncasecmp($getData['blogurl'], 'http', 4)) {
        $getData['blogurl'] = 'http://' . $getData['blogurl'];
    }

    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('tags', 'options', 'comment', 'record'));
    Output::ok();
}

if ($action == 'seo') {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);

    $ex0 = $ex1 = $ex2 = $ex3 = '';
    $t = 'ex' . $isurlrewrite;
    $$t = 'checked="checked"';

    $opt0 = $opt1 = $opt2 = '';
    $t = 'opt' . $log_title_style;
    $$t = 'selected="selected"';

    $isalias = $isalias == 'y' ? 'checked="checked"' : '';
    $isalias_html = $isalias_html == 'y' ? 'checked="checked"' : '';

    $br = '<ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">数据中心</a></li>
        <li class="breadcrumb-item"><a href="./setting.php">系统管理</a></li>
        <li class="breadcrumb-item active" aria-current="page">SEO设置</li>
    </ol>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_seo'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'seo_save') {
    LoginAuth::checkToken();

    $permalink = Input::postStrVar('permalink', '0');
    $isalias = Input::postStrVar('isalias', 'n');
    $isalias_html = Input::postStrVar('isalias_html', 'n');

    $getData = [
        'site_title'       => Input::postStrVar('site_title', ''),
        'site_description' => Input::postStrVar('site_description', ''),
        'site_key'         => Input::postStrVar('site_key', ''),
        'isurlrewrite'     => Input::postStrVar('permalink', '0'),
        'isalias'          => Input::postStrVar('isalias', 'n'),
        'isalias_html'     => Input::postStrVar('isalias_html', 'n'),
        'log_title_style'  => Input::postStrVar('log_title_style', '0'),
    ];

    if ($permalink != '0' || $isalias == 'y') {
        $t = parse_url(DC_URL);
        $rw_rule = '<IfModule mod_rewrite.c>
                       RewriteEngine on
                       RewriteCond %{REQUEST_FILENAME} !-f
                       RewriteCond %{REQUEST_FILENAME} !-d
                       RewriteBase ' . $t['path'] . '
                       RewriteRule . ' . $t['path'] . 'index.php [L]
                    </IfModule>';
        if (!file_put_contents(DC_ROOT . '/.htaccess', $rw_rule)) {
            Output::error('保存失败：根目录下的.htaccess不可写');
        }
    }

    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('options', 'navi'));
    Output::ok();
}

if ($action == 'mail') {
    $options_cache = $CACHE->readCache('options');
    $smtp_mail = isset($options_cache['smtp_mail']) ? $options_cache['smtp_mail'] : '';
    $smtp_pw = isset($options_cache['smtp_pw']) ? $options_cache['smtp_pw'] : '';
    $smtp_from_name = isset($options_cache['smtp_from_name']) ? $options_cache['smtp_from_name'] : '';
    $smtp_server = isset($options_cache['smtp_server']) ? $options_cache['smtp_server'] : '';
    $smtp_port = isset($options_cache['smtp_port']) ? $options_cache['smtp_port'] : '';
    $mail_notice_comment = isset($options_cache['mail_notice_comment']) ? $options_cache['mail_notice_comment'] : '';
    $mail_notice_post = isset($options_cache['mail_notice_post']) ? $options_cache['mail_notice_post'] : '';
    $mail_template = isset($options_cache['mail_template']) ? $options_cache['mail_template'] : '';

    $conf_mail_notice_comment = $mail_notice_comment == 'y' ? 'checked="checked"' : '';
    $conf_mail_notice_post = $mail_notice_post == 'y' ? 'checked="checked"' : '';

    $br = '<ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">数据中心</a></li>
        <li class="breadcrumb-item"><a href="./setting.php">系统管理</a></li>
        <li class="breadcrumb-item active" aria-current="page">邮箱设置</li>
    </ol>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_mail'));
    include View::getAdmView('footer');
    View::output();

}

if ($action == 'mail_save') {
    LoginAuth::checkToken();
    $data = [
        'smtp_mail'           => Input::postStrVar('smtp_mail'),
        'smtp_pw'             => Input::postStrVar('smtp_pw'),
        'smtp_from_name'      => Input::postStrVar('smtp_from_name'),
        'smtp_server'         => Input::postStrVar('smtp_server'),
        'smtp_port'           => Input::postStrVar('smtp_port'),
        'mail_notice_comment' => Input::postStrVar('mail_notice_comment', 'n'),
        'mail_notice_post'    => Input::postStrVar('mail_notice_post', 'n'),
        'mail_template'       => Input::postStrVar('mail_template'),
    ];

    foreach ($data as $key => $val) {
        Option::updateOption($key, $val);
    }

    $CACHE->updateCache(array('options'));
    Output::ok();
}

if ($action == 'mail_test') {
    $data = [
        'smtp_mail'      => isset($_POST['smtp_mail']) ? addslashes($_POST['smtp_mail']) : '',
        'smtp_pw'        => isset($_POST['smtp_pw']) ? addslashes($_POST['smtp_pw']) : '',
        'smtp_from_name' => isset($_POST['smtp_from_name']) ? addslashes($_POST['smtp_from_name']) : '',
        'smtp_server'    => isset($_POST['smtp_server']) ? addslashes($_POST['smtp_server']) : '',
        'smtp_port'      => isset($_POST['smtp_port']) ? (int)$_POST['smtp_port'] : '',
        'testTo'         => isset($_POST['testTo']) ? $_POST['testTo'] : '',
    ];

    if (!checkMail($data['testTo'])) {
        exit("<small class='text-info'>请正确填写邮箱</small>");
    }

    $mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = $data['smtp_port'] == '587' ? 'STARTTLS' : 'ssl';
    $mail->Port = $data['smtp_port'];
    $mail->Host = $data['smtp_server'];
    $mail->Username = $data['smtp_mail'];
    $mail->Password = $data['smtp_pw'];
    $mail->From = $data['smtp_mail'];
    $mail->FromName = $data['smtp_from_name'];
    $mail->AddAddress($data['testTo']);
    $mail->Subject = '测试邮件';
    $mail->isHTML();
    $mail->Body = Notice::getMailTemplate('这是一封测试邮件');

    try {
        return $mail->Send();
    } catch (Exception $exc) {
        exit("<small class='text-danger'>发送失败</small>");
    }
}

if ($action == 'user') {
    $options_cache = $CACHE->readCache('options');
    $is_signup = isset($options_cache['is_signup']) ? $options_cache['is_signup'] : '';
    $ischkarticle = isset($options_cache['ischkarticle']) ? $options_cache['ischkarticle'] : '';
    $article_uneditable = isset($options_cache['article_uneditable']) ? $options_cache['article_uneditable'] : '';
    $forbid_user_upload = isset($options_cache['forbid_user_upload']) ? $options_cache['forbid_user_upload'] : '';
    $posts_per_day = isset($options_cache['posts_per_day']) ? $options_cache['posts_per_day'] : '';
    $posts_name = isset($options_cache['posts_name']) ? $options_cache['posts_name'] : '';
    $email_code = isset($options_cache['email_code']) ? $options_cache['email_code'] : '';
    $att_maxsize = isset($options_cache['att_maxsize']) ? $options_cache['att_maxsize'] : '';
    $att_type = isset($options_cache['att_type']) ? $options_cache['att_type'] : '';

    $conf_is_signup = $is_signup == 'y' ? 'checked="checked"' : '';
    $conf_email_code = $email_code == 'y' ? 'checked="checked"' : '';
    $conf_ischkarticle = $ischkarticle == 'y' ? 'checked="checked"' : '';
    $conf_forbid_user_upload = $forbid_user_upload == 'y' ? 'checked="checked"' : '';
    $conf_article_uneditable = $article_uneditable == 'y' ? 'checked="checked"' : '';

    $login_switch = isset($options_cache['login_switch']) ? $options_cache['login_switch'] : 'y';
    $register_switch = isset($options_cache['register_switch']) ? $options_cache['register_switch'] : 'y';
    $register_email_switch = isset($options_cache['register_email_switch']) ? $options_cache['register_email_switch'] : 'n';
    $register_tel_switch = isset($options_cache['register_tel_switch']) ? $options_cache['register_tel_switch'] : 'n';
    $login_email_switch = isset($options_cache['login_email_switch']) ? $options_cache['login_email_switch'] : 'y';
    $login_tel_switch = isset($options_cache['login_tel_switch']) ? $options_cache['login_tel_switch'] : 'y';
    $login_username_switch = isset($options_cache['login_username_switch']) ? $options_cache['login_username_switch'] : 'y';
    $register_username_switch = isset($options_cache['register_username_switch']) ? $options_cache['register_username_switch'] : 'y';
    $register_bind_tel = isset($options_cache['register_bind_tel']) ? $options_cache['register_bind_tel'] : 'n';
    $register_bind_email = isset($options_cache['register_bind_email']) ? $options_cache['register_bind_email'] : 'n';
    $register_bind_invite = isset($options_cache['register_bind_invite']) ? $options_cache['register_bind_invite'] : 'n';
    $sms_bind_phone_daily_limit = isset($options_cache['sms_bind_phone_daily_limit']) ? $options_cache['sms_bind_phone_daily_limit'] : 5;
    $sms_login_daily_limit = isset($options_cache['sms_login_daily_limit']) ? $options_cache['sms_login_daily_limit'] : 10;
    $levelSettings = Level_Service::getSettings();
    $memberModel = new Member_Model();
    $memberLevels = $memberModel->getMembersAll();

    $br = '<a href="./">数据中心</a><a href="./shop.php">商城配置</a><a><cite>用户配置</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_user'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'user_save') {
    LoginAuth::checkToken();
    $data = [
        'register_email_switch'          => Input::postStrVar('register_email_switch', 'n'),
        'register_tel_switch'          => Input::postStrVar('register_tel_switch', 'n'),
        'login_email_switch'          => Input::postStrVar('login_email_switch', 'n'),
        'login_tel_switch'          => Input::postStrVar('login_tel_switch', 'n'),
        'login_username_switch'     => Input::postStrVar('login_username_switch', 'n'),
        'register_username_switch'  => Input::postStrVar('register_username_switch', 'n'),
        'register_bind_tel'  => Input::postStrVar('register_bind_tel', 'n'),
        'register_bind_email' => Input::postStrVar('register_bind_email', 'n'),
        'register_bind_invite' => Input::postStrVar('register_bind_invite', 'n'),
        'login_switch'          => Input::postStrVar('login_switch', 'n'),
        'register_switch'          => Input::postStrVar('register_switch', 'n'),
        'email_code'         => Input::postStrVar('email_code', 'n'),
        'ischkarticle'       => Input::postStrVar('ischkarticle', 'n'),
        'article_uneditable' => Input::postStrVar('article_uneditable', 'n'),
        'forbid_user_upload' => Input::postStrVar('forbid_user_upload', 'n'),
        'posts_per_day'      => Input::postIntVar('posts_per_day', 0),
        'posts_name'         => Input::postStrVar('posts_name'),
        'att_maxsize'        => Input::postIntVar('att_maxsize'),
        'att_type'           => str_replace('php', 'x', strtolower(Input::postStrVar('att_type', ''))),
    ];

    foreach ($data as $key => $val) {
        Option::updateOption($key, $val);
    }

    $CACHE->updateCache('options');
    Output::ok();
}

if ($action === 'user_config_save') {
    LoginAuth::checkToken();

    $userData = [
        'register_email_switch' => Input::postStrVar('register_email_switch', 'n'),
        'register_tel_switch'   => Input::postStrVar('register_tel_switch', 'n'),
        'login_email_switch'    => Input::postStrVar('login_email_switch', 'n'),
        'login_tel_switch'      => Input::postStrVar('login_tel_switch', 'n'),
        'login_username_switch' => Input::postStrVar('login_username_switch', 'n'),
        'register_username_switch' => Input::postStrVar('register_username_switch', 'n'),
        'register_bind_tel'    => Input::postStrVar('register_bind_tel', 'n'),
        'register_bind_email'  => Input::postStrVar('register_bind_email', 'n'),
        'register_bind_invite' => Input::postStrVar('register_bind_invite', 'n'),
        'login_switch'          => Input::postStrVar('login_switch', 'n'),
        'register_switch'       => Input::postStrVar('register_switch', 'n'),
        'sms_bind_phone_daily_limit' => Input::postIntVar('sms_bind_phone_daily_limit', 5),
        'sms_login_daily_limit'      => Input::postIntVar('sms_login_daily_limit', 10),
    ];

    $memberModel = new Member_Model();
    $memberLevels = $memberModel->getMembersAll();
    $defaultGradeInput = Input::postIntVar('level_default_grade', 0);
    $defaultGrade = $defaultGradeInput > 0 ? $defaultGradeInput : 0;
    if ($defaultGrade <= 0 && !empty($memberLevels[0]['id'])) {
        $defaultGrade = (int)$memberLevels[0]['id'];
    }

    $withdrawMethodMap = Level_Service::getWithdrawMethodMap();
    $withdrawMethods = [];
    foreach ($withdrawMethodMap as $methodKey => $methodName) {
        if (Input::postStrVar('withdraw_method_' . $methodKey, '') === $methodKey) {
            $withdrawMethods[] = $methodKey;
        }
    }
    if (empty($withdrawMethods)) {
        Output::error('请至少选择一种提现方式');
    }

    $levelData = [
        Level_Service::OPT_DEFAULT_GRADE    => $defaultGrade,
        Level_Service::OPT_DEPOSIT_GRADE    => Input::postIntVar('level_deposit_grade', 0),
        Level_Service::OPT_UPGRADE_PROFIT   => Input::postStrVar('level_upgrade_profit', '0'),
        Level_Service::OPT_COMMITS_DIST     => Input::postIntVar('level_commits_distribute', 0),
        Level_Service::OPT_INFINITE_DIV     => Input::postIntVar('level_infinite_division', 0),
        Level_Service::OPT_INFINITE_SKIP    => Input::postIntVar('level_infinite_skip', 1),
        Level_Service::OPT_COMMISSION_BASE  => in_array(Input::postStrVar('commission_base', 'total'), ['total', 'profit']) ? Input::postStrVar('commission_base', 'total') : 'total',
        Level_Service::OPT_COMMISSION_RATIO => max(0, min(100, (float)Input::postStrVar('commission_ratio', '100'))),
        Level_Service::OPT_UPGRADE_REWARD_TYPES => implode(',', array_filter([
            Input::postStrVar('upgrade_reward_open', '') ? 'open' : '',
            Input::postStrVar('upgrade_reward_upgrade', '') ? 'upgrade' : '',
            Input::postStrVar('upgrade_reward_renew', '') ? 'renew' : '',
        ])),
        Level_Service::OPT_UPGRADE_PRICE_MODE => in_array(Input::postStrVar('upgrade_price_mode', 'diff'), ['diff', 'full']) ? Input::postStrVar('upgrade_price_mode', 'diff') : 'diff',
        Level_Service::OPT_UPGRADE_LEVEL_CHECK => max(0, min(2, Input::postIntVar('upgrade_level_check', 2))),
        Level_Service::OPT_ORDER_LEVEL_CHECK => max(0, min(1, Input::postIntVar('order_level_check', 0))),
        Level_Service::OPT_WITHDRAW_SWITCH  => Input::postIntVar('withdraw_switch', 0),
        Level_Service::OPT_WITHDRAW_FEE     => Input::postStrVar('withdraw_fee_rate', '0'),
        Level_Service::OPT_WITHDRAW_MIN     => Input::postStrVar('withdraw_min_amount', '10'),
        Level_Service::OPT_WITHDRAW_METHODS => implode(',', $withdrawMethods),
        Level_Service::OPT_RECHARGE_MIN     => Input::postStrVar('balance_recharge_min', '1'),
        Level_Service::OPT_RECHARGE_MAX     => Input::postStrVar('balance_recharge_max', '10000'),
        Level_Service::OPT_INVITE_ONLY      => Input::postIntVar('invite_register_only', 0),
    ];

    foreach ($userData as $key => $val) {
        Option::updateOption($key, $val);
    }

    Level_Service::saveSettings($levelData);
    $CACHE->updateCache('options');
    Output::ok();
}

if ($action == 'station_setting') {
    $options_cache = $CACHE->readCache('options');
    $station_domain = isset($options_cache['station_domain']) ? $options_cache['station_domain'] : '';
    $station_cname_domain = isset($options_cache['station_cname_domain']) ? $options_cache['station_cname_domain'] : '';
    $station_domain_retain = isset($options_cache['station_domain_retain']) ? $options_cache['station_domain_retain'] : '';
    $station_domain_change_price = isset($options_cache['station_domain_change_price']) ? $options_cache['station_domain_change_price'] : '0';
    $station_domain_strict = isset($options_cache['station_domain_strict']) ? $options_cache['station_domain_strict'] : '0';
    $station_extra_domains = isset($options_cache['station_extra_domains']) ? $options_cache['station_extra_domains'] : '';
    $station_slug_mode = isset($options_cache['station_slug_mode']) ? $options_cache['station_slug_mode'] : '1';
    $station_switch = isset($options_cache[Level_Service::OPT_STATION_SWITCH]) ? $options_cache[Level_Service::OPT_STATION_SWITCH] : '1';
    $station_upgrade_price_mode = isset($options_cache['station_upgrade_price_mode']) ? $options_cache['station_upgrade_price_mode'] : 'diff';

    $br = '<a href="./">数据中心</a><a href="./shop.php">商城配置</a><a><cite>分店配置</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('station/setting'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'station_setting_save') {
    LoginAuth::checkToken();
    $station_domain_strict_val = isset($_POST['station_domain_strict']) ? '1' : '0';
    $data = [
        'station_domain'               => Input::postStrVar('station_domain'),
        'station_cname_domain'         => Input::postStrVar('station_cname_domain'),
        'station_domain_retain'        => Input::postStrVar('station_domain_retain'),
        'station_domain_change_price'  => Input::postStrVar('station_domain_change_price'),
        'station_domain_strict'        => $station_domain_strict_val,
        'station_extra_domains'        => Input::postStrVar('station_extra_domains'),
        'station_slug_mode'            => isset($_POST['station_slug_mode']) ? '1' : '0',
        Level_Service::OPT_STATION_SWITCH => isset($_POST['station_switch']) ? '1' : '0',
        'station_upgrade_price_mode' => in_array(Input::postStrVar('station_upgrade_price_mode', 'diff'), ['diff', 'full']) ? Input::postStrVar('station_upgrade_price_mode', 'diff') : 'diff',
    ];

    foreach ($data as $key => $val) {
        Option::updateOption($key, $val);
    }

    $CACHE->updateCache('options');
    Output::ok();
}
