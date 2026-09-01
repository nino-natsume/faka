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

    $conf_detect_url = $detect_url == 'y' ? 'checked="checked"' : '';

    $login_code = isset($options_cache['login_code']) ? $options_cache['login_code'] : '';
    $captcha_type = isset($options_cache['captcha_type']) ? $options_cache['captcha_type'] : 'num';

    $tzlist = [
        'Etc/GMT'              => '(UTC)协调世界时',
        'Africa/Casablanca'    => '(UTC)卡萨布兰卡',
        'Atlantic/Reykjavik'   => '(UTC)蒙罗维亚，雷克雅未克',
        'Europe/London'        => '(UTC)都柏林，爱丁堡，里斯本，伦敦',
        'Africa/Lagos'         => '(UTC+01:00)中非西部',
        'Europe/Paris'         => '(UTC+01:00)布鲁塞尔，哥本哈根，马德里，巴黎',
        'Africa/Windhoek'      => '(UTC+01:00)温得和克',
        'Europe/Warsaw'        => '(UTC+01:00)萨拉热窝，斯科普里，华沙，萨格勒布',
        'Europe/Budapest'      => '(UTC+01:00)贝尔格莱德，布拉迪斯拉发，布达佩斯，卢布尔雅那，布拉格',
        'Europe/Berlin'        => '(UTC+01:00)阿姆斯特丹，柏林，伯尔尼，罗马，斯德哥尔摩，维也纳',
        'Europe/Istanbul'      => '(UTC+02:00)伊斯坦布尔',
        'Europe/Kaliningrad'   => '(UTC+02:00)加里宁格勒(RTZ 1)',
        'Africa/Johannesburg'  => '(UTC+02:00)哈拉雷，比勒陀利亚',
        'Asia/Damascus'        => '(UTC+02:00)大马士革',
        'Asia/Amman'           => '(UTC+02:00)安曼',
        'Africa/Cairo'         => '(UTC+02:00)开罗',
        'Africa/Tripoli'       => '(UTC+02:00)的黎波里',
        'Asia/Jerusalem'       => '(UTC+02:00)耶路撒冷',
        'Asia/Beirut'          => '(UTC+02:00)贝鲁特',
        'Europe/Kiev'          => '(UTC+02:00)赫尔辛基，基辅，里加，索非亚，塔林，维尔纽斯',
        'Europe/Bucharest'     => '(UTC+02:00)雅典，布加勒斯特',
        'Africa/Nairobi'       => '(UTC+03:00)内罗毕',
        'Asia/Baghdad'         => '(UTC+03:00)巴格达',
        'Europe/Minsk'         => '(UTC+03:00)明斯克',
        'Asia/Riyadh'          => '(UTC+03:00)科威特，利雅得',
        'Europe/Moscow'        => '(UTC+03:00)莫斯科，圣彼得堡，伏尔加格勒(RTZ 2)',
        'Asia/Tehran'          => '(UTC+03:30)德黑兰',
        'Europe/Samara'        => '(UTC+04:00)伊热夫斯克，萨马拉(RTZ 3)',
        'Asia/Yerevan'         => '(UTC+04:00)埃里温',
        'Asia/Bak'             => '(UTC+04:00)巴库',
        'Asia/Tbilisi'         => '(UTC+04:00)第比利斯',
        'Indian/Mauritius'     => '(UTC+04:00)路易港',
        'Asia/Dubai'           => '(UTC+04:00)阿布扎比，马斯喀特',
        'Asia/Kabu'            => '(UTC+04:30)喀布尔',
        'Asia/Karachi'         => '(UTC+05:00)伊斯兰堡，卡拉奇',
        'Asia/Yekaterinburg'   => '(UTC+05:00)叶卡捷琳堡(RTZ 4)',
        'Asia/Tashkent'        => '(UTC+05:00)阿什哈巴德，塔什干',
        'Asia/Colombo'         => '(UTC+05:30)斯里加亚渥登普拉',
        'Asia/Calcutta'        => '(UTC+05:30)钦奈，加尔各答，孟买，新德里',
        'Asia/Katmandu'        => '(UTC+05:45)加德满都',
        'Asia/Novosibirsk'     => '(UTC+06:00)新西伯利亚(RTZ 5)',
        'Asia/Dhaka'           => '(UTC+06:00)达卡',
        'Asia/Almaty'          => '(UTC+06:00)阿斯塔纳',
        'Asia/Rangoon'         => '(UTC+06:30)仰光',
        'Asia/Krasnoyarsk'     => '(UTC+07:00)克拉斯诺亚尔斯克(RTZ 6)',
        'Asia/Bangkok'         => '(UTC+07:00)曼谷，河内，雅加达',
        'Asia/Ulaanbaatar'     => '(UTC+08:00)乌兰巴托',
        'Asia/Irkutsk'         => '(UTC+08:00)伊尔库茨克(RTZ 7)',
        'Asia/Shanghai'        => '(UTC+08:00)北京，重庆，香港特别行政区，乌鲁木齐',
        'Asia/Taipei'          => '(UTC+08:00)台北',
        'Asia/Singapore'       => '(UTC+08:00)吉隆坡，新加坡',
        'Australia/Perth'      => '(UTC+08:00)珀斯',
        'Asia/Tokyo'           => '(UTC+09:00)大阪，札幌，东京',
        'Asia/Yakutsk'         => '(UTC+09:00)雅库茨克(RTZ 8)',
        'Asia/Seoul'           => '(UTC+09:00)首尔',
        'Australia/Darwin'     => '(UTC+09:30)达尔文',
        'Australia/Adelaide'   => '(UTC+09:30)阿德莱德',
        'Pacific/Port_Moresby' => '(UTC+10:00)关岛，莫尔兹比港',
        'Australia/Sydney'     => '(UTC+10:00)堪培拉，墨尔本，悉尼',
        'Australia/Brisbane'   => '(UTC+10:00)布里斯班',
        'Asia/Vladivostok'     => '(UTC+10:00)符拉迪沃斯托克，马加丹(RTZ 9)',
        'Australia/Hobart'     => '(UTC+10:00)霍巴特',
        'Asia/Magadan'         => '(UTC+10:00)马加丹',
        'Asia/Srednekolymsk'   => '(UTC+11:00)乔库尔达赫(RTZ 10)',
        'Pacific/Guadalcanal'  => '(UTC+11:00)所罗门群岛，新喀里多尼亚',
        'Etc/GMT-12'           => '(UTC+12:00)协调世界时+12',
        'Pacific/Auckland'     => '(UTC+12:00)奥克兰，惠灵顿',
        'Pacific/Fiji'         => '(UTC+12:00)斐济',
        'Asia/Kamchatka'       => '(UTC+12:00)阿纳德尔，彼得罗巴甫洛夫斯克-堪察加(RTZ 11)',
        'Pacific/Tongatapu'    => '(UTC+13:00)努库阿洛法',
        'Pacific/Apia'         => '(UTC+13:00)萨摩亚群岛',
        'Pacific/Kiritimati'   => '(UTC+14:00)圣诞岛',
        'Atlantic/Azores'      => '(UTC-01:00)亚速尔群岛',
        'Atlantic/Cape_Verde'  => '(UTC-01:00)佛得角群岛',
        'Etc/GMT+2'            => '(UTC-02:00)协调世界时-02',
        'America/Cayenne'      => '(UTC-03:00)卡宴，福塔雷萨',
        'America/Sao_Paulo'    => '(UTC-03:00)巴西利亚',
        'America/Buenos_Aires' => '(UTC-03:00)布宜诺斯艾利斯',
        'America/Godthab'      => '(UTC-03:00)格陵兰',
        'America/Bahia'        => '(UTC-03:00)萨尔瓦多',
        'America/Montevideo'   => '(UTC-03:00)蒙得维的亚',
        'America/St_Johns'     => '(UTC-03:30)纽芬兰',
        'America/La_Paz'       => '(UTC-04:00)乔治敦，拉巴斯，马瑙斯，圣胡安',
        'America/Asuncion'     => '(UTC-04:00)亚松森',
        'America/Halifax'      => '(UTC-04:00)大西洋时间(加拿大)',
        'America/Cuiaba'       => '(UTC-04:00)库亚巴',
        'America/Caracas'      => '(UTC-04:30)加拉加斯',
        'America/New_York'     => '(UTC-05:00)东部时间(美国和加拿大)',
        'America/Indianapolis' => '(UTC-05:00)印地安那州(东部)',
        'America/Bogota'       => '(UTC-05:00)波哥大，利马，基多，里奥布朗库',
        'America/Guatemala'    => '(UTC-06:00)中美洲',
        'America/Chicago'      => '(UTC-06:00)中部时间(美国和加拿大)',
        'America/Mexico_City'  => '(UTC-06:00)瓜达拉哈拉，墨西哥城，蒙特雷',
        'America/Regina'       => '(UTC-06:00)萨斯喀彻温',
        'America/Phoenix'      => '(UTC-07:00)亚利桑那',
        'America/Chihuahua'    => '(UTC-07:00)奇瓦瓦，拉巴斯，马萨特兰',
        'America/Denver'       => '(UTC-07:00)山地时间(美国和加拿大)',
        'America/Santa_Isabel' => '(UTC-08:00)下加利福尼亚州',
        'America/Los_Angeles'  => '(UTC-08:00)太平洋时间(美国和加拿大)',
        'America/Anchorage'    => '(UTC-09:00)阿拉斯加',
        'Pacific/Honolulu'     => '(UTC-10:00)夏威夷',
        'Etc/GMT+11'           => '(UTC-11:00)协调世界时-11',
        'Etc/GMT+12'           => '(UTC-12:00)国际日期变更线西',
    ];



    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>系统配置</cite></a>';


    include View::getAdmView('header');
    require_once(View::getAdmView('setting'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'blog') {
    $options_cache = $CACHE->readCache('options');
    extract($options_cache);

    $blogSettingDefaults = [
        'blog_independent_domain' => '',
        'iscomment'           => 'y',
        'ischkcomment'        => 'n',
        'login_comment'       => 'n',
        'comment_code'        => 'n',
        'comment_interval'    => '15',
        'comment_paging'      => 'y',
        'comment_pnum'        => '10',
        'comment_order'       => 'newer',
        'rss_output_num'      => '10',
        'rss_output_fulltext' => 'y',
        'isthumbnail'         => 'y',
        'att_imgmaxw'         => '420',
        'att_imgmaxh'         => '460',
    ];
    foreach ($blogSettingDefaults as $key => $value) {
        if (!isset($$key) || $$key === '') {
            $$key = $value;
        }
    }

    $conf_comment_code = $comment_code == 'y' ? 'checked="checked"' : '';
    $conf_iscomment = $iscomment == 'y' ? 'checked="checked"' : '';
    $conf_login_comment = $login_comment == 'y' ? 'checked="checked"' : '';
    $conf_ischkcomment = $ischkcomment == 'y' ? 'checked="checked"' : '';
    $conf_isthumbnail = $isthumbnail == 'y' ? 'checked="checked"' : '';
    $conf_comment_paging = $comment_paging == 'y' ? 'checked="checked"' : '';

    $ex1 = $ex2 = $ex3 = $ex4 = '';
    if ($rss_output_fulltext == 'y') {
        $ex1 = 'selected="selected"';
    } else {
        $ex2 = 'selected="selected"';
    }
    if ($comment_order == 'newer') {
        $ex3 = 'selected="selected"';
    } else {
        $ex4 = 'selected="selected"';
    }

    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>博客配置</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_blog'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'blog_save') {
    LoginAuth::checkToken();
    $blogDomainRaw = Input::postStrVar('blog_independent_domain', '');
    $blogDomains = [];
    foreach (preg_split('/[\r\n,]+/', $blogDomainRaw) as $blogDomain) {
        $blogDomain = function_exists('dcNormalizeDomain') ? dcNormalizeDomain($blogDomain) : strtolower(trim(preg_replace('#^https?://#i', '', (string)$blogDomain)));
        if ($blogDomain === '') {
            continue;
        }
        if (!preg_match('/^[a-z0-9.-]+(:\d+)?$/i', $blogDomain)) {
            Output::error('博客独立域名格式不正确：' . $blogDomain);
        }
        if (!in_array($blogDomain, $blogDomains, true)) {
            $blogDomains[] = $blogDomain;
        }
    }
    $getData = [
        'blog_independent_domain' => implode("\n", $blogDomains),
        'iscomment'           => Input::postStrVar('iscomment', 'n'),
        'ischkcomment'        => Input::postStrVar('ischkcomment', 'n'),
        'login_comment'       => Input::postStrVar('login_comment', 'n'),
        'comment_code'        => Input::postStrVar('comment_code', 'n'),
        'comment_interval'    => Input::postIntVar('comment_interval', 15),
        'comment_paging'      => Input::postStrVar('comment_paging', 'n'),
        'comment_pnum'        => Input::postIntVar('comment_pnum'),
        'comment_order'       => Input::postStrVar('comment_order', 'newer'),
        'rss_output_num'      => Input::postIntVar('rss_output_num', 10),
        'rss_output_fulltext' => Input::postStrVar('rss_output_fulltext', 'y'),
        'isthumbnail'         => Input::postStrVar('isthumbnail', 'n'),
        'att_imgmaxw'         => Input::postIntVar('att_imgmaxw', 420),
        'att_imgmaxh'         => Input::postIntVar('att_imgmaxh', 460),
    ];

    if ($getData['comment_code'] == 'y' && !checkGDSupport()) {
        Output::error('开启评论验证码失败，服务器PHP不支持GD图形库');
    }

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



    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>首页设置</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_index'));
    include View::getAdmView('footer');
    View::output();
}

if ($action == 'index_save') {
    LoginAuth::checkToken();
    $getData = [
        'roll_bulletin'    => Input::postStrVar('roll_bulletin', ''),
        'home_bulletin'    => Input::postStrVar('home_bulletin', ''),
        'sales_switch' => Input::postStrVar('sales_switch', 'n'),
        'stock_switch' => Input::postStrVar('stock_switch', 'n'),
        'order_email_switch' => Input::postStrVar('order_email_switch', 'n'),
        'order_pwd_switch' => Input::postStrVar('order_pwd_switch', 'n'),
        'order_tel_switch' => Input::postStrVar('order_tel_switch', 'n'),
        'balance_switch' => Input::postStrVar('balance_switch', 'n'),
        'pay_redirect' => Input::postStrVar('pay_redirect', 'list'),
        'kami_order' => Input::postStrVar('kami_order', 'asc'),
    ];

    foreach ($getData as $key => $val) {
        Option::updateOption($key, $val);
    }
    $CACHE->updateCache(array('tags', 'options', 'comment', 'record'));
    Output::ok();
}

if ($action == 'save') {
    LoginAuth::checkToken();
    $getData = [
        'blogname'            => Input::postStrVar('blogname'),
        'blog_site_name'      => Input::postStrVar('blog_site_name'),
        'blogurl'             => Input::postStrVar('blogurl'),
        'bloginfo'            => Input::postStrVar('bloginfo'),
        'icp'                 => Input::postStrVar('icp'),
        'footer_info'         => Input::postStrVar('footer_info'),
        'timezone'            => Input::postStrVar('timezone'),
        'detect_url'          => Input::postStrVar('detect_url', 'n'),
        'login_code'          => Input::postStrVar('login_code', 'n'),
        'captcha_type'        => in_array(Input::postStrVar('captcha_type', 'num'), ['num','alpha','mix','zh','math','random']) ? Input::postStrVar('captcha_type', 'num') : 'num',
        'panel_menu_title'    => Input::postStrVar('panel_menu_title'),
        'personal_center_icon'    => Input::postStrVar('personal_center_icon'),
        'logo'    => Input::postStrVar('logo'),
        'site_subtitle'    => Input::postStrVar('site_subtitle'),
        'admin_favicon'    => Input::postStrVar('admin_favicon'),
    ];

    if ($getData['login_code'] == 'y' && !checkGDSupport()) {
        Output::error('开启图形验证码失败，服务器PHP不支持GD图形库');
    }

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


    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>SEO设置</cite></a>';

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


    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>邮箱设置</cite></a>';

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

//    d($data);die;

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
        echo $exc->getMessage();
        exit("<small class='text-danger'>发送失败</small>");
    }
}

if ($action == 'user') {
    header('Location: ./shop.php?action=user');
    exit;
}

if ($action === 'admin_account') {
    $db = Database::getInstance();
    $dbPrefix = DB_PREFIX;
    $groupModel = new Admin_Group_Model();
    $founderUid = (int)User::getFounderUid();
    $statsRow = $db->once_fetch_array("SELECT 
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_count,
        SUM(CASE WHEN role = 'admin' AND uid = {$founderUid} THEN 1 ELSE 0 END) AS founder_count,
        SUM(CASE WHEN role IS NULL OR role = '' OR role = 'writer' OR role = 'visitor' THEN 1 ELSE 0 END) AS user_count
        FROM {$dbPrefix}user");
    $adminAccountStats = [
        'admin_count' => (int)($statsRow['admin_count'] ?? 0),
        'founder_count' => (int)($statsRow['founder_count'] ?? 0),
        'user_count' => (int)($statsRow['user_count'] ?? 0),
        'group_count' => count($groupModel->getAll()),
    ];
    $backendPermissionGroups = Admin_Permission_Service::getPermissionGroups();
    $backendPermissionPresets = Admin_Permission_Service::getPermissionPresets();

    // 当前后台账户信息（个人资料编辑）
    $User_Model = new User_Model();
    $currentAdmin = $User_Model->getOneUser(UID);
    $currentAdmin['avatar_url'] = User::getAvatar($currentAdmin['photo'] ?? '');
    $currentAdmin['role_name'] = User::getRoleName(ROLE, UID);

    $br = '<a href="./">数据中心</a><a href="./setting.php">系统管理</a><a><cite>后台账户</cite></a>';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_backend_account'));
    include View::getAdmView('footer');
    View::output();
}

if ($action === 'level') {
    header('Location: ./shop.php?action=user');
    exit;
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

if ($action === 'agreement') {
    $user_agreement = Option::get('user_agreement');
    $privacy_policy = Option::get('privacy_policy');
    if ($user_agreement === null) $user_agreement = '';
    if ($privacy_policy === null) $privacy_policy = '';

    $siteName = htmlspecialchars(Option::get('blogname') ?: '本站');

    if (empty($user_agreement)) {
        $user_agreement = '<h2>用户服务协议</h2>'
            . '<p>欢迎使用 <strong>' . $siteName . '</strong>（以下简称"本站"）提供的虚拟商品自动发卡服务。请您在注册或使用本站服务之前，仔细阅读本协议。注册即表示您已充分阅读、理解并同意接受本协议的全部内容。</p>'
            . '<h3>一、服务说明</h3>'
            . '<p>本站是一个虚拟商品在线交易平台，提供卡密、兑换码、账号、充值服务等数字商品的自动化销售与发放服务。用户可通过本站选购商品并在线支付，系统将自动发送商品信息至用户。</p>'
            . '<h3>二、用户账号</h3>'
            . '<ol><li>用户注册时应提供真实、准确的信息，并妥善保管账号及密码。</li>'
            . '<li>用户应对其账号下的一切行为和交易负责。因账号被盗、密码泄露等非本站原因导致的损失，由用户自行承担。</li>'
            . '<li>用户不得将账号转让、出借给他人使用。</li>'
            . '<li>本站有权对异常账号（如批量注册、恶意下单等）进行限制或封禁。</li></ol>'
            . '<h3>三、交易规则</h3>'
            . '<ol><li>用户下单前应仔细阅读商品描述、使用说明及注意事项，下单付款即视为认可商品内容。</li>'
            . '<li>虚拟商品具有可复制性和不可回收性，<strong>一经发货（卡密发出），原则上不支持退款</strong>。</li>'
            . '<li>如遇卡密无效、重复发放等质量问题，请在收货后 24 小时内联系客服，经核实后可进行补发或退款。</li>'
            . '<li>因用户自身原因（如填写错误、未及时提取、泄露卡密等）造成的损失，本站不承担责任。</li>'
            . '<li>用户不得利用本站进行任何违法交易或将购买的商品用于违法用途。</li></ol>'
            . '<h3>四、用户行为规范</h3>'
            . '<p>用户在使用本站服务时，应遵守国家法律法规，不得从事以下行为：</p>'
            . '<ol><li>利用本站从事欺诈、洗钱、赌博或其他违法违规活动。</li>'
            . '<li>恶意下单、刷单、利用系统漏洞套取利益。</li>'
            . '<li>干扰或破坏本站正常运营，包括但不限于攻击服务器、爬取数据等。</li>'
            . '<li>发布虚假信息、恶意差评或损害本站声誉的行为。</li></ol>'
            . '<h3>五、知识产权</h3>'
            . '<p>本站的界面设计、程序代码、商标标识等知识产权归本站所有。商品内容的知识产权归原始权利人所有，用户购买后应在授权范围内使用。</p>'
            . '<h3>六、免责声明</h3>'
            . '<ol><li>本站作为交易平台，不对第三方商品的实际效果和后续服务承担担保责任。</li>'
            . '<li>因不可抗力（如网络故障、支付平台异常、政策变化等）导致的服务中断或交易延迟，本站不承担赔偿责任，但将尽力协助解决。</li>'
            . '<li>用户因违反本协议或法律法规产生的一切后果由用户自行承担。</li></ol>'
            . '<h3>七、协议变更</h3>'
            . '<p>本站有权根据运营需要修改本协议内容，修改后将在本站公布。继续使用本站服务即视为同意变更后的协议。</p>'
            . '<h3>八、争议解决</h3>'
            . '<p>本协议受中华人民共和国法律管辖。如发生争议，双方应友好协商解决；协商不成的，提交本站所在地有管辖权的人民法院处理。</p>';
        Option::updateOption('user_agreement', $user_agreement);
    }

    if (empty($privacy_policy)) {
        $privacy_policy = '<h2>隐私政策</h2>'
            . '<p><strong>' . $siteName . '</strong>（以下简称"本站"）非常重视用户的隐私保护。本隐私政策说明我们在您使用虚拟商品自动发卡服务过程中，如何收集、使用、存储和保护您的个人信息。</p>'
            . '<h3>一、信息收集</h3>'
            . '<p>我们可能收集以下类型的信息：</p>'
            . '<ol><li><strong>注册信息：</strong>用户名、邮箱地址、手机号码等您在注册账号时主动提供的信息。</li>'
            . '<li><strong>订单信息：</strong>商品名称、订单号、支付金额、支付方式、下单时间、收货邮箱/手机等与交易直接相关的数据。</li>'
            . '<li><strong>设备与日志信息：</strong>IP 地址、浏览器类型、操作系统、访问时间等用于安全防护和服务优化的技术信息。</li>'
            . '<li><strong>客服沟通记录：</strong>您与客服交流时提供的信息，用于处理售后和纠纷。</li></ol>'
            . '<h3>二、信息使用</h3>'
            . '<p>我们收集的信息仅用于以下目的：</p>'
            . '<ol><li>处理您的订单、完成商品发放和交易结算。</li>'
            . '<li>提供客服支持和售后服务。</li>'
            . '<li>发送订单通知、支付确认等服务类消息。</li>'
            . '<li>监测异常交易，防范欺诈和滥用行为。</li>'
            . '<li>改善平台功能和用户体验。</li>'
            . '<li>履行法律法规规定的义务。</li></ol>'
            . '<h3>三、信息存储与保护</h3>'
            . '<ol><li>您的个人信息存储在安全的服务器中，我们采取加密传输、访问控制等合理的安全措施保护您的数据。</li>'
            . '<li>用户密码经过不可逆加密存储，即使数据库泄露也无法直接获取明文密码。</li>'
            . '<li>我们仅在业务需要和法律要求的最短期限内保留您的信息。</li>'
            . '<li>尽管我们尽力保护您的信息安全，但互联网并非绝对安全的环境，请您也注意保管好自己的账号和密码。</li></ol>'
            . '<h3>四、信息共享</h3>'
            . '<p>我们不会向第三方出售您的个人信息。仅在以下情况下可能共享必要信息：</p>'
            . '<ol><li>为完成支付，向合作的第三方支付机构传递必要的订单和金额信息。</li>'
            . '<li>根据法律法规、政府监管要求或司法裁定。</li>'
            . '<li>在获得您明确同意的情况下。</li></ol>'
            . '<h3>五、Cookie 与本地存储</h3>'
            . '<p>本站使用 Cookie 记住您的登录状态和偏好设置。您可以通过浏览器设置管理或清除 Cookie，但这可能导致需要重新登录或部分功能无法正常使用。</p>'
            . '<h3>六、用户权利</h3>'
            . '<p>您有权：</p>'
            . '<ol><li>查看、修改您的个人资料和账号信息。</li>'
            . '<li>请求删除您的账号及关联数据（删除后不可恢复，历史订单记录将按法规要求保留必要期限）。</li>'
            . '<li>对我们的信息处理方式提出疑问或投诉。</li></ol>'
            . '<h3>七、未成年人保护</h3>'
            . '<p>本站服务面向具有完全民事行为能力的用户。如您是未满 18 周岁的未成年人，请在监护人的陪同和同意下使用本站服务。</p>'
            . '<h3>八、政策变更</h3>'
            . '<p>我们可能根据业务发展和法规变化更新本隐私政策，更新后将在本站公布。继续使用本站服务即视为同意变更后的隐私政策。</p>'
            . '<h3>九、联系我们</h3>'
            . '<p>如您对本隐私政策有任何疑问或需要行使上述权利，请通过本站提供的联系方式与我们取得联系，我们将在 15 个工作日内处理您的请求。</p>';
        Option::updateOption('privacy_policy', $privacy_policy);
    }

    $CACHE->updateCache('options');
    include View::getAdmView('header');
    include View::getAdmView('setting_agreement');
    include View::getAdmView('footer');
    View::output();
}

if ($action === 'agreement_save') {
    LoginAuth::checkToken();
    $user_agreement = isset($_POST['user_agreement']) ? $_POST['user_agreement'] : '';
    $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
    Option::updateOption('user_agreement', $user_agreement);
    Option::updateOption('privacy_policy', $privacy_policy);
    $CACHE->updateCache('options');
    Output::ok('保存成功');
}

if ($action === 'admin_account_index') {
    new User_Model();
    $groupModel = new Admin_Group_Model();
    new Member_Model();
    $db = Database::getInstance();
    $dbPrefix = DB_PREFIX;
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = max(0, ($page - 1) * $limit);
    $keyword = trim(Input::getStrVar('keyword'));
    $where = " AND u.role = 'admin'";
    if ($keyword !== '') {
        $keyword = addslashes($keyword);
        $where .= " AND (u.uid='{$keyword}' OR u.username LIKE '%{$keyword}%' OR u.nickname LIKE '%{$keyword}%' OR u.tel LIKE '%{$keyword}%' OR u.email LIKE '%{$keyword}%')";
    }

    $sql = "SELECT u.*, m.name level_name, g.name AS group_name, g.menu_permissions AS group_permissions
        FROM {$dbPrefix}user u
        LEFT JOIN {$dbPrefix}member m ON u.level = m.id
        LEFT JOIN {$dbPrefix}admin_group g ON u.admin_group_id = g.id
        WHERE 1=1 {$where}
        ORDER BY u.uid DESC LIMIT {$start}, {$limit}";
    $res = $db->fetch_all($sql);
    $list = [];
    foreach ($res as $row) {
        $isFounder = User::isFounderUid((int)$row['uid']) ? 1 : 0;
        $row['login'] = htmlspecialchars($row['username']);
        $row['nickname'] = htmlspecialchars(empty($row['nickname']) ? $row['username'] : $row['nickname']);
        $row['email'] = htmlspecialchars($row['email']);
        $row['tel'] = htmlspecialchars($row['tel']);
        $row['level_name'] = empty($row['level_name']) ? '未设置会员等级' : htmlspecialchars($row['level_name']);
        $row['role_name'] = $isFounder === 1 ? '创始人' : '管理员';
        $row['is_founder'] = $isFounder;
        $row['admin_group_id'] = (int)($row['admin_group_id'] ?? 0);
        if ($row['admin_group_id'] > 0 && !empty($row['group_name'])) {
            $row['group_name'] = htmlspecialchars($row['group_name']);
            $row['permission_summary'] = Admin_Permission_Service::getPermissionSummary($row['group_permissions'] ?? []);
        } else {
            $row['group_name'] = Admin_Group_Model::DEFAULT_GROUP_NAME;
            $row['permission_summary'] = '全部菜单';
        }
        $row['create_time'] = smartDate($row['create_time']);
        $row['can_revoke'] = ($isFounder !== 1 && (int)$row['uid'] !== (int)UID) ? 1 : 0;
        $row['can_change_group'] = ($isFounder !== 1 && (int)$row['uid'] !== (int)UID) ? 1 : 0;
        $list[] = $row;
    }

    $countRow = $db->once_fetch_array("SELECT COUNT(*) AS total FROM {$dbPrefix}user u WHERE 1=1 {$where}");
    output::data($list, (int)($countRow['total'] ?? 0));
}

if ($action === 'admin_account_search') {
    $keyword = trim(Input::getStrVar('keyword'));
    if ($keyword === '') {
        output::data([], 0);
    }

    new Member_Model();
    $db = Database::getInstance();
    $dbPrefix = DB_PREFIX;
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $start = max(0, ($page - 1) * $limit);
    $keyword = addslashes($keyword);
    $where = " AND (u.role IS NULL OR u.role = '' OR u.role = 'writer' OR u.role = 'visitor')";
    $where .= " AND (u.uid='{$keyword}' OR u.username LIKE '%{$keyword}%' OR u.nickname LIKE '%{$keyword}%' OR u.tel LIKE '%{$keyword}%' OR u.email LIKE '%{$keyword}%')";

    $sql = "SELECT u.*, m.name level_name FROM {$dbPrefix}user u LEFT JOIN {$dbPrefix}member m ON u.level = m.id WHERE 1=1 {$where} ORDER BY u.uid DESC LIMIT {$start}, {$limit}";
    $res = $db->fetch_all($sql);
    $list = [];
    foreach ($res as $row) {
        $row['login'] = htmlspecialchars($row['username']);
        $row['nickname'] = htmlspecialchars(empty($row['nickname']) ? $row['username'] : $row['nickname']);
        $row['email'] = htmlspecialchars($row['email']);
        $row['tel'] = htmlspecialchars($row['tel']);
        $row['role_name'] = '普通用户';
        $row['level_name'] = empty($row['level_name']) ? '未设置会员等级' : htmlspecialchars($row['level_name']);
        $row['create_time'] = smartDate($row['create_time']);
        $list[] = $row;
    }

    $countRow = $db->once_fetch_array("SELECT COUNT(*) AS total FROM {$dbPrefix}user u WHERE 1=1 {$where}");
    output::data($list, (int)($countRow['total'] ?? 0));
}

if ($action === 'admin_account_set') {
    LoginAuth::checkToken();
    $uid = Input::postIntVar('uid');
    $groupId = Input::postIntVar('group_id');
    if ($uid <= 0) {
        output::error('参数错误');
    }
    if ($groupId <= 0) {
        output::error('请先选择用户组');
    }

    $userModel = new User_Model();
    $groupModel = new Admin_Group_Model();
    $userRow = $userModel->getOneUser($uid);
    if (empty($userRow)) {
        output::error('用户不存在');
    }
    if (empty($groupModel->getById($groupId))) {
        output::error('所选用户组不存在');
    }

    if (($userRow['role'] ?? '') === User::ROLE_ADMIN) {
        output::error('该用户已经是后台账户');
    }

    $userModel->updateUser(['role' => User::ROLE_ADMIN, 'admin_group_id' => $groupId], $uid);
    $CACHE->updateCache('user');
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log($uid, 'admin_edit', '后台设为后台账户，用户组ID：' . $groupId);
    }
    output::ok();
}

if ($action === 'admin_account_group_save') {
    LoginAuth::checkToken();
    $uid = Input::postIntVar('uid');
    $groupId = Input::postIntVar('group_id');
    if ($uid <= 0 || $groupId <= 0) {
        output::error('参数错误');
    }
    if (User::isFounderUid($uid)) {
        output::error('创始人账户不支持在此调整用户组');
    }
    if ($uid === (int)UID) {
        output::error('当前登录账号不可在此调整自身用户组');
    }

    $userModel = new User_Model();
    $groupModel = new Admin_Group_Model();
    $userRow = $userModel->getOneUser($uid);
    if (empty($userRow)) {
        output::error('用户不存在');
    }
    if (($userRow['role'] ?? '') !== User::ROLE_ADMIN) {
        output::error('该账号当前不是后台账户');
    }
    if (empty($groupModel->getById($groupId))) {
        output::error('所选用户组不存在');
    }

    $userModel->updateUser(['admin_group_id' => $groupId], $uid);
    $CACHE->updateCache('user');
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log($uid, 'admin_edit', '后台调整后台账户用户组，用户组ID：' . $groupId);
    }
    output::ok();
}

if ($action === 'admin_account_cancel') {
    LoginAuth::checkToken();
    $uid = Input::postIntVar('uid');
    if ($uid <= 0) {
        output::error('参数错误');
    }
    if (User::isFounderUid($uid)) {
        output::error('创始人账号不可取消管理权限');
    }
    if ($uid === (int)UID) {
        output::error('当前登录账号不可在此页面取消自身管理权限');
    }

    $userModel = new User_Model();
    $userRow = $userModel->getOneUser($uid);
    if (empty($userRow)) {
        output::error('用户不存在');
    }
    if (($userRow['role'] ?? '') !== User::ROLE_ADMIN) {
        output::error('该账号当前不属于后台账户');
    }

    $userModel->updateUser(['role' => User::ROLE_WRITER, 'admin_group_id' => 0], $uid);
    $CACHE->updateCache('user');
    if (class_exists('User_Log_Model')) {
        User_Log_Model::log($uid, 'admin_edit', '后台取消后台账户权限');
    }
    output::ok();
}

if ($action === 'admin_group_all') {
    $groupModel = new Admin_Group_Model();
    $groups = $groupModel->getAll(true);
    foreach ($groups as &$group) {
        $group['name'] = htmlspecialchars($group['name']);
        if (!empty($group['is_default_group'])) {
            $group['permission_summary'] = '全部菜单';
        } else {
            $group['permission_summary'] = Admin_Permission_Service::getPermissionSummary($group['menu_permissions'] ?? []);
        }
    }
    output::data($groups, count($groups));
}

if ($action === 'admin_group_index') {
    $page = Input::getIntVar('page', 1);
    $limit = Input::getIntVar('limit', 10);
    $keyword = trim(Input::getStrVar('keyword'));
    $groupModel = new Admin_Group_Model();
    $groups = $groupModel->getAll(true);
    $filtered = [];
    foreach ($groups as $group) {
        if ($keyword !== '' && mb_stripos($group['name'], $keyword) === false && strpos((string)$group['id'], $keyword) === false) {
            continue;
        }
        $group['name'] = htmlspecialchars($group['name']);
        if (!empty($group['is_default_group'])) {
            $group['permission_summary'] = '全部菜单';
        } else {
            $group['permission_summary'] = Admin_Permission_Service::getPermissionSummary($group['menu_permissions'] ?? []);
        }
        $group['update_time_text'] = $group['update_time'] > 0 ? smartDate($group['update_time']) : '-';
        $filtered[] = $group;
    }
    $total = count($filtered);
    $start = max(0, ($page - 1) * $limit);
    $paged = array_slice($filtered, $start, $limit);
    output::data(array_values($paged), $total);
}

if ($action === 'admin_group_save') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    $name = trim(Input::postStrVar('name'));
    $menuPermissions = isset($_POST['menu_permissions']) ? (array)$_POST['menu_permissions'] : [];
    $menuPermissions = Admin_Permission_Service::normalizePermissions($menuPermissions);
    $groupModel = new Admin_Group_Model();
    if ($groupModel->isDefaultGroup($id)) {
        $name = Admin_Group_Model::DEFAULT_GROUP_NAME;
        $menuPermissions = Admin_Permission_Service::getAllPermissionKeys();
    }
    if ($name === '') {
        output::error('请填写用户组名称');
    }
    if (mb_strlen($name) > 30) {
        output::error('用户组名称请控制在30个字以内');
    }
    if (empty($menuPermissions)) {
        output::error('请至少选择一个可见菜单');
    }

    if ($groupModel->isNameExist($name, $id)) {
        output::error('该用户组名称已存在');
    }
    $savedId = $groupModel->save($id, $name, $menuPermissions);
    output::ok(['id' => (int)$savedId]);
}

if ($action === 'admin_group_delete') {
    LoginAuth::checkToken();
    $id = Input::postIntVar('id');
    if ($id <= 0) {
        output::error('参数错误');
    }
    $groupModel = new Admin_Group_Model();
    $group = $groupModel->getById($id);
    if (empty($group)) {
        output::error('用户组不存在');
    }
    if ($groupModel->isDefaultGroup($id)) {
        output::error('默认后台组不允许删除');
    }
    if ($groupModel->getBindCount($id) > 0) {
        output::error('该用户组下仍有后台账户，请先调整账户分组');
    }
    $groupModel->delete($id);
    output::ok();
}

if ($action == 'api') {
    $apikey = Option::get('apikey');
    $is_openapi = Option::get('is_openapi');
    $conf_is_openapi = $is_openapi == 'y' ? 'checked="checked"' : '';

    include View::getAdmView('header');
    require_once(View::getAdmView('setting_api'));
    include View::getAdmView('footer');
    View::output();
}

// (旧 action=level 页面渲染 / level_save 保存接口已合并至 action=user / user_config_save，上方 469 行已做重定向)

if ($action == 'api_save') {
    LoginAuth::checkToken();

    $isOpenapiEnabled = Input::postStrVar('is_openapi', 'n');
    Option::updateOption('is_openapi', $isOpenapiEnabled);
    $CACHE->updateCache('options');
    Output::ok();
}

if ($action == 'api_reset') {
    LoginAuth::checkToken();

    $apikey = md5(getRandStr(32));

    Option::updateOption('apikey', $apikey);
    $CACHE->updateCache('options');
    header('Location: ./setting.php?action=api&ok_reset=1');
}

// ==========================================
// 个人资料编辑（原 blogger.php 合并）
// ==========================================

if ($action == 'profile_update') {
    LoginAuth::checkToken();
    $User_Model = new User_Model();
    $nickname = Input::postStrVar('name');
    $description = Input::postStrVar('description');
    $login = Input::postStrVar('username');
    $email = trim(Input::postStrVar('email'));

    if (empty($nickname)) {
        Output::error('昵称不能为空');
    } elseif ($User_Model->isNicknameExist($nickname, UID)) {
        Output::error('昵称已被占用');
    } elseif ($User_Model->isUserExist($login, UID)) {
        Output::error('登录名已被占用');
    }
    if ($email !== '' && !checkMail($email)) {
        Output::error('请正确填写邮箱地址');
    }
    if ($email !== '' && $User_Model->isMailExist($email, UID)) {
        Output::error('该邮箱已被其他账号使用');
    }

    $d = [
        'nickname'    => $nickname,
        'description' => $description,
        'username'    => $login,
        'email'       => $email,
    ];

    $User_Model->updateUser($d, UID);
    $CACHE->updateCache('user');
    Output::ok();
}

if ($action === 'profile_change_password') {
    LoginAuth::checkToken();
    if (Register::isDemoSite()) {
        Output::error('当前演示站禁止此操作哦！');
    }

    $new_passwd = Input::postStrVar('new_passwd');
    $new_passwd2 = Input::postStrVar('new_passwd2');

    if (strlen($new_passwd) < 6) {
        Output::error('密码不得小于6位');
    } elseif ($new_passwd !== $new_passwd2) {
        Output::error('两次密码不一致');
    }

    $PHPASS = new PasswordHash(8, true);
    $new_passwd = $PHPASS->HashPassword($new_passwd);
    $d['password'] = $new_passwd;

    $User_Model = new User_Model();
    $User_Model->updateUser($d, UID);
    $CACHE->updateCache('user');
    Output::ok();
}

if ($action === 'profile_change_email') {
    LoginAuth::checkToken();
    $User_Model = new User_Model();
    $email = Input::postStrVar('email');
    $mail_code = Input::postStrVar('mail_code');

    if (!checkMail($email)) {
        Output::error('请正确填写邮箱');
    } elseif ($User_Model->isMailExist($email, UID)) {
        Output::error('邮箱已被占用');
    }

    if (!User::checkMailCode($mail_code)) {
        Output::error('验证码错误');
    }

    $d = [
        'email' => $email,
    ];

    $User_Model->updateUser($d, UID);
    $CACHE->updateCache('user');
    Output::ok();
}

if ($action == 'profile_delicon') {
    $User_Model = new User_Model();
    $User_Model->updateUser(array('photo' => ''), UID);
    $CACHE->updateCache('user');
    emDirect('./setting.php');
}

if ($action == 'profile_update_avatar') {
    $ret = uploadCropImg();
    $file_path = $ret['file_info']['file_path'];

    $User_Model = new User_Model();
    $User_Model->updateUser(array('photo' => $file_path), UID);
    $CACHE->updateCache('user');
    Output::ok($file_path);
}
