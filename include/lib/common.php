<?php


function getMyEmkey(){
    $db = Database::getInstance();
    $db_prefix = DB_PREFIX;
    $domain = getTopHost();
    $sql = "select * from {$db_prefix}authorization where domain='{$domain}'";
    $res = $db->once_fetch_array($sql);
    $emkey =  empty($res) ? null : $res['emkey'];
    return $emkey;
}

function isEmail($str) {
    // 使用PHP内置的过滤器验证邮箱
    return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
}

function isEmpty($var) {
    return is_string($var) && $var === '';
}

// 获取下单必填项
function getOrderRequired(){
    $order_required = Option::get('order_required');
    if(empty($order_required)){
        $data = [];
    }else{
        $data = json_decode($order_required, true);
    }
    return $data;
}

function dcPaymentConfigValue($value) {
    if ($value === false || $value === null) {
        return '';
    }
    return trim((string)$value);
}

function dcPaymentConfigFilled($value) {
    return dcPaymentConfigValue($value) !== '';
}

function dcPaymentUrlFilled($value) {
    $value = dcPaymentConfigValue($value);
    return $value !== '' && preg_match('#^https?://#i', $value);
}

function dcPaymentGetStorage($plugin) {
    if (!class_exists('Storage')) {
        return null;
    }
    try {
        return Storage::getInstance($plugin);
    } catch (Throwable $e) {
        return null;
    }
}

function dcPaymentStorageFilled($storage, $key) {
    if (!$storage) {
        return false;
    }
    return dcPaymentConfigFilled($storage->getValue($key));
}

function dcPaymentStorageUrlFilled($storage, $key) {
    if (!$storage) {
        return false;
    }
    return dcPaymentUrlFilled($storage->getValue($key));
}

function dcPaymentAnyEnabledMode($storage, $keys) {
    if (!$storage) {
        return false;
    }
    foreach ($keys as $key) {
        if (dcPaymentConfigFilled($storage->getValue($key))) {
            return true;
        }
    }
    return false;
}

function dcPaymentEpayChannelReady($channel, $amount = 0) {
    if (!is_array($channel)) {
        return false;
    }
    if (($channel['enabled'] ?? '1') !== '1') {
        return false;
    }
    if (!dcPaymentUrlFilled($channel['url'] ?? '')) {
        return false;
    }
    if (!dcPaymentConfigFilled($channel['appid'] ?? '') || !dcPaymentConfigFilled($channel['private_key'] ?? '')) {
        return false;
    }
    $amount = (float)$amount;
    if ($amount > 0) {
        $minAmount = (float)($channel['min_amount'] ?? 0);
        $maxAmount = (float)($channel['max_amount'] ?? 0);
        if ($minAmount > 0 && $amount < $minAmount) {
            return false;
        }
        if ($maxAmount > 0 && $amount > $maxAmount) {
            return false;
        }
    }
    return true;
}

function dcPaymentHasReadyEpayChannel($storage, $amount = 0) {
    if (!$storage) {
        return false;
    }
    $channelsJson = $storage->getValue('channels_json');
    $channels = !empty($channelsJson) ? json_decode($channelsJson, true) : [];
    if (!is_array($channels) || empty($channels)) {
        return false;
    }
    foreach ($channels as $channel) {
        if (dcPaymentEpayChannelReady($channel, $amount)) {
            return true;
        }
    }
    return false;
}

function dcPaymentUnavailableMessage($plugin = '') {
    return '后台支付插件未配置完整，请联系管理员';
}

function dcPaymentPluginReady($plugin, $amount = 0) {
    $plugin = strtolower(trim((string)$plugin));
    if ($plugin === '') {
        return false;
    }
    if ($plugin === 'balance') {
        return true;
    }

    $storage = dcPaymentGetStorage($plugin);
    if (!$storage) {
        return false;
    }

    switch ($plugin) {
        case 'alipay':
        case 'alipay2':
            return dcPaymentStorageFilled($storage, 'appid')
                && dcPaymentStorageFilled($storage, 'public_key')
                && dcPaymentStorageFilled($storage, 'private_key')
                && dcPaymentAnyEnabledMode($storage, ['pc', 'mb', 'dm']);

        case 'epay_ali':
        case 'epay_ali2':
        case 'epay_wx':
        case 'epay_qq':
            return dcPaymentStorageUrlFilled($storage, 'url')
                && dcPaymentStorageFilled($storage, 'appid')
                && dcPaymentStorageFilled($storage, 'private_key');

        case 'ynl_ali':
        case 'ynl_wx':
            return dcPaymentStorageFilled($storage, 'pid')
                && dcPaymentStorageFilled($storage, 'key');

        case 'epay_jj':
        case 'epay_jj2':
            return dcPaymentHasReadyEpayChannel($storage, $amount);

        case 'manual_pay':
            $enabled = $storage->getValue('enabled');
            if ($enabled !== false && empty($enabled)) {
                return false;
            }
            return dcPaymentStorageFilled($storage, 'contact_account');

        default:
            // 未知第三方支付插件无法判断配置项，只要支付函数存在就交给插件自身处理。
            return function_exists('pay_' . $plugin);
    }
}

function dcFilterPaymentList($mode_payment) {
    if (!is_array($mode_payment)) {
        return [];
    }
    $filtered = [];
    $seen = [];
    foreach ($mode_payment as $payment) {
        if (!is_array($payment)) {
            continue;
        }
        $plugin = $payment['plugin_name'] ?? '';
        $unique = $payment['unique'] ?? $plugin;
        if ($plugin === '' || $unique === '') {
            continue;
        }
        if (isset($seen[$unique])) {
            continue;
        }
        if (!dcPaymentPluginReady($plugin)) {
            continue;
        }
        unset($payment['active']);
        $filtered[] = $payment;
        $seen[$unique] = true;
    }
    return $filtered;
}

// 获取支付方式
function getPayment(){
    $GLOBALS['mode_payment'] = [];
    doAction('mode_payment');
    $mode_payment = dcFilterPaymentList($GLOBALS['mode_payment']);
    $balance_switch = Option::get('balance_switch');
    $balance_switch = empty($balance_switch) ? 'y' : $balance_switch;
    if($balance_switch == 'y'){
        $mode_payment = array_merge($mode_payment, [
            [
                'plugin_name' => 'balance', // 插件名. 与插件目录名保持一致
                'icon' => './content/static/img/balance.png',
                'title' => '余额支付', // 当前支付方式名称
                'unique' => 'balance', // 当前支付方式唯一标识，所有支付插件中此项禁止重复
                'name' => '余额支付'
            ]
        ]);
    }
    if(isset($mode_payment[0])){
        foreach ($mode_payment as &$payment) {
            unset($payment['active']);
        }
        unset($payment);
        $mode_payment[0]['active'] = true;
    }
    return $mode_payment;
}

function generateUUIDv4() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}


/**
 * 获取客户端IP
 * @return mixed|string
 */
function getClientIP() {
    $ip = '';

    // 1. 优先检查 HTTP_CLIENT_IP（可能是代理转发）
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  // 2. 检查 HTTP_X_FORWARDED_FOR（多层代理）

        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]); // 取第一个IP
    }
    // 3. 最后使用 REMOTE_ADDR
    else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // 过滤无效IP
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

function getPreviousUrl(){
	$previousUrl = $_SERVER['HTTP_REFERER'];  
	return $previousUrl;
}

function getCurrentUrl(){
	// 检查服务器是否使用HTTPS  
	$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';  
	  
	// 获取协议类型  
	$protocol = $https ? 'https://' : 'http://';  
	  
	// 获取主机名（例如：www.example.com）  
	$host = $_SERVER['HTTP_HOST'];  
	  
	// 获取资源路径（例如：/path/to/myfile.html）  
	$uri = $_SERVER['REQUEST_URI'];  
	  
	// 获取URL  
	$url = $protocol . $host . $uri; 
	return $url;
}

function orderStatusText($status){
    $text = '未知状态';
    if($status == 0) $text = '未支付';
    if($status == 1) $text = '待发货';
    if($status == 2) $text = '已完成';
    if($status == -1) $text = '部分发货';
    if($status == 3) $text = '已取消';
    if($status == 4) $text = '待收货';
    return $text;
}

function goodsTypeText($goods_type){
    $text = '未知类型';
    if($goods_type == 'duli') $text = '一卡一密';
    if($goods_type == 'xuni') $text = '虚拟服务';
    if($goods_type == 'guding') $text = '固定卡密';
    if($goods_type == 'post') $text = '接口类型';
    if($goods_type == 'physical') $text = '实物发货';
    return $text;
}


function isFolder($path, $create = false, $permissions = 0755, $recursive = true) {
    // 检查路径是否已经存在
    if (!is_dir($path)) {
        // 尝试创建目录
        if (mkdir($path, $permissions, $recursive)) {
            return true;
        } else {
            echo "目录 {$path} 创建失败。\n";
            return false;
        }
    } else {
        return true;
    }
}

/**
 * 请求接口返回内容
 * @param string $url [请求的URL地址]
 * @param string $params [请求的参数]
 * @param int $ipost [是否采用POST形式]
 * @return  string
 */
function emCurl($url, $params = false, $ispost = 0, $header = false, $timeout = 0) {
    $protocol = substr($url, 0, 5);
    $httpInfo = [];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
    if ($timeout > 0) {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    } else {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }


    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    if ('https' == $protocol) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    $response = curl_exec($ch);
    if ($response === false) {
        //        echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));

    curl_close($ch);
    // print_r($httpInfo);
    return $response;
}

/**
 * 请求接口返回内容
 * @param string $url [请求的URL地址]
 * @param string $params [请求的参数]
 * @param int $ipost [是否采用POST形式]
 * @return  string
 */
function ebCurl($url, $params = false, $ispost = 0, $header = false, $timeout = 0) {
    $protocol = substr($url, 0, 5);
    $httpInfo = [];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
    if ($timeout > 0) {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    } else {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }


    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    if ('https' == $protocol) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    $response = curl_exec($ch);
    if ($response === false) {
        //        echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));

    curl_close($ch);
    // print_r($httpInfo);
    return $response;
}

/**
 * 判断客户端设备是否为手机
 */
if (!function_exists('dcIsDefaultUserViewportMobileMode')) {
    /**
     * 默认用户模板：PC 浏览器窗口变窄后，通过前端写入的 cookie 强制走移动端 App 页面。
     * 仅作用于 /user/ 路径，且仅当当前 PC 用户模板为 default 时生效，避免影响其它模板。
     */
    function dcIsDefaultUserViewportMobileMode() {
        $flag = isset($_GET['__dc_user_mobile_app']) ? $_GET['__dc_user_mobile_app'] : ($_COOKIE['dc_default_user_mobile_app'] ?? '');
        if ((string)$flag !== '1') {
            return false;
        }

        $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $requestPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
        if (preg_match('#/user(?:/|$)#i', $scriptName) !== 1 && preg_match('#/user(?:/|$)#i', $requestPath) !== 1) {
            return false;
        }

        global $stationData;
        if (isset($stationData) && is_array($stationData) && !empty($stationData['id'])) {
            $pcUserTpl = trim((string)($stationData['user_tpl'] ?? ''));
            if ($pcUserTpl === '' || $pcUserTpl === 'em_null_tpl') {
                $pcUserTpl = 'default';
            }
        } elseif (class_exists('Option')) {
            $pcUserTpl = trim((string)Option::get('nonce_user_tpl'));
        } else {
            $pcUserTpl = '';
        }

        return strtolower($pcUserTpl) === 'default';
    }
}

function isMobile() {
    if ((defined('DC_FORCE_MOBILE_TEMPLATE') && DC_FORCE_MOBILE_TEMPLATE) || !empty($GLOBALS['__dc_force_mobile_template'])) {
        return true;
    }
    if (function_exists('dcIsDefaultUserViewportMobileMode') && dcIsDefaultUserViewportMobileMode()) {
        return true;
    }
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
    $mobile_browser = '0';
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) $mobile_browser++;
    if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false)) $mobile_browser++;
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) $mobile_browser++;
    if (isset($_SERVER['HTTP_PROFILE'])) $mobile_browser++;
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    $mobile_agents = [
        'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
    ];
    if (in_array($mobile_ua, $mobile_agents)) $mobile_browser++;
    if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) $mobile_browser++;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) $mobile_browser = 0;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) $mobile_browser++;
    if ($mobile_browser > 0) return true; else
        return false;
}

/**
 * 检测某个值是否存在于二维数组中
 */
function keyValueExistsInArray($array, $key, $value) {
    foreach ($array as $subArray) {
        if (isset($subArray[$key]) && $subArray[$key] == $value) {
            return true;
        }
    }
    return false;
}


function d($arr){
    echo '<pre>'; print_r($arr);
}
function dd($arr){
    echo '<pre>'; var_dump($arr);
}


function emAutoload($class) {

    $class = strtolower($class);
    if (file_exists(DC_ROOT . '/include/model/' . $class . '.php')) {
        require_once(DC_ROOT . '/include/model/' . $class . '.php');
    } elseif (file_exists(DC_ROOT . '/include/lib/' . $class . '.php')) {
        require_once(DC_ROOT . '/include/lib/' . $class . '.php');
    } elseif (file_exists(DC_ROOT . '/include/controller/' . $class . '.php')) {
        require_once(DC_ROOT . '/include/controller/' . $class . '.php');
    } elseif (file_exists(DC_ROOT . '/include/service/' . $class . '.php')) {
        require_once(DC_ROOT . '/include/service/' . $class . '.php');
    }
}


/**
 * Convert HTML Code
 */
function htmlClean($content, $nl2br = true) {
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    if ($nl2br) {
        $content = nl2br($content);
    }
    $content = str_replace('  ', '&nbsp;&nbsp;', $content);
    $content = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $content);
    return $content;
}

if (!function_exists('getIp')) {
    function getIp() {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $list[0];
        }
        if (!ip2long($ip)) {
            $ip = '';
        }
        return $ip;
    }
}

if (!function_exists('getUA')) {
    function getUA() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
}
/**
 * 获取当前域名（用于授权验证）
 * 返回完整域名，精确匹配授权
 */
function getTopHost() {
    $domain = getDomain();
    return strtolower($domain);
}
/**
 * 获取当前完整域名
 */
function getDomain() {

    // 获取主机名（包含子域名）
    $host = '';
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } elseif (isset($_SERVER['SERVER_NAME'])) {
        $host = $_SERVER['SERVER_NAME'];
    }
    // 组合完整域名
    return $host;
}

/**
 * 规范化域名：去掉协议、路径，只保留 host[:port]
 */
function dcNormalizeDomain($domain) {
    $domain = trim((string)$domain);
    if ($domain === '') {
        return '';
    }
    $domain = preg_replace('#^https?://#i', '', $domain);
    $domain = preg_replace('#/.*$#', '', $domain);
    $domain = trim($domain);
    return strtolower($domain);
}

/**
 * 获取博客独立域名列表
 */
function dcGetBlogIndependentDomains() {
    $raw = (string)Option::get('blog_independent_domain');
    if ($raw === '') {
        return [];
    }
    $domains = [];
    foreach (preg_split('/[\r\n,]+/', $raw) as $domain) {
        $domain = dcNormalizeDomain($domain);
        if ($domain !== '') {
            $domains[] = $domain;
        }
    }
    return array_values(array_unique($domains));
}

/**
 * 判断当前访问域名是否为博客独立域名
 */
function dcIsBlogIndependentDomain($host = null) {
    $host = dcNormalizeDomain($host === null ? getDomain() : $host);
    if ($host === '') {
        return false;
    }
    return in_array($host, dcGetBlogIndependentDomains(), true);
}

/**
 * 判断是否为本地测试域名。本地调试域名不参与前台标题的未授权提示。
 */
function dcIsLocalTestingDomain($domain = null) {
    $domain = dcNormalizeDomain($domain === null ? getDomain() : $domain);
    if ($domain === '') {
        return false;
    }
    $host = $domain;
    if ($host[0] === '[') {
        $pos = strpos($host, ']');
        if ($pos !== false) {
            $host = substr($host, 1, $pos - 1);
        }
    } elseif (substr_count($host, ':') === 1) {
        $host = preg_replace('/:\d+$/', '', $host);
    }
    return $host === 'localhost'
        || $host === '::1'
        || preg_match('/^127(?:\.\d{1,3}){0,3}$/', $host)
        || preg_match('/\.(local|test)$/i', $host);
}

/**
 * 当前请求用于授权展示判断的域名：
 * 分站独立域名 / 分站备用域名 / 博客独立域名统一沿用主站 blogurl 的授权状态。
 */
function dcGetCurrentAuthDomain() {
    global $stationData, $userData;
    $domain = dcNormalizeDomain(getTopHost());
    $useMainDomain = (!empty($stationData['id']) && (int)$stationData['id'] > 0)
        || (!empty($userData['station']['id']) && (int)$userData['station']['id'] > 0)
        || dcIsBlogIndependentDomain($domain);

    if ($useMainDomain) {
        $mainDomain = '';
        try {
            if (class_exists('Cache')) {
                $opts = Cache::getInstance()->readCache('options');
                if (is_array($opts) && !empty($opts['blogurl'])) {
                    $mainDomain = dcNormalizeDomain($opts['blogurl']);
                }
            }
        } catch (Throwable $e) {
            $mainDomain = '';
        }
        if ($mainDomain === '' && class_exists('Option')) {
            $mainDomain = dcNormalizeDomain(Option::get('blogurl'));
        }
        if ($mainDomain !== '') {
            return $mainDomain;
        }
    }

    return $domain;
}

/**
 * 获取主站基础地址。与 DC_URL 不同，DC_URL 是当前访问域名；
 * 当请求来自博客独立域名时，主站页面/后台应使用配置里的主站 blogurl。
 */
function dcGetMainBaseUrl($path = '') {
    $base = '';
    try {
        if (class_exists('Cache')) {
            $options = Cache::getInstance()->readCache('options');
            if (is_array($options) && !empty($options['blogurl'])) {
                $base = trim((string)$options['blogurl']);
            }
        }
    } catch (Throwable $e) {
        $base = '';
    }
    if ($base === '') {
        $base = defined('DC_URL') ? DC_URL : realUrl();
    }
    if (!preg_match('#^https?://#i', $base)) {
        $protocol = 'http://';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https://';
        } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = 'https://';
        }
        $base = $protocol . $base;
    }

    $parts = parse_url($base);
    if (is_array($parts) && !empty($parts['host'])) {
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'];
        if (isset($parts['port'])) {
            $host .= ':' . (int)$parts['port'];
        }
        $basePath = isset($parts['path']) ? rtrim(str_replace('\\', '/', (string)$parts['path']), '/') : '';
        if ($basePath !== '' && preg_match('#\.php$#i', $basePath)) {
            $basePath = rtrim(str_replace('\\', '/', dirname($basePath)), '/');
            if ($basePath === '.') {
                $basePath = '';
            }
        }
        $base = $scheme . '://' . $host . $basePath . '/';
    } else {
        $base = rtrim($base, '/') . '/';
    }

    $path = (string)$path;
    if ($path === '') {
        return $base;
    }
    if ($path[0] === '?') {
        return rtrim($base, '/') . '/' . $path;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * 获取当前完整访问地址。
 */
function dcGetCurrentUrl() {
    $protocol = 'http://';
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https://';
    } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = 'https://';
    }
    $host = $_SERVER['HTTP_HOST'] ?? getDomain();
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $protocol . $host . $uri;
}

/**
 * 校验登录后跳转地址，避免开放重定向。允许相对路径、当前域名、主站域名、博客独立域名。
 */
function dcGetSafeRedirectUrl($url, $fallback = '') {
    $url = trim(stripslashes((string)$url));
    if ($url === '') {
        return $fallback;
    }
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
    if (preg_match('/[\r\n]/', $url)) {
        return $fallback;
    }
    if ($url[0] === '/' && substr($url, 0, 2) !== '//') {
        return $url;
    }

    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host']) || empty($parts['scheme'])) {
        return $fallback;
    }
    if (!in_array(strtolower((string)$parts['scheme']), ['http', 'https'], true)) {
        return $fallback;
    }

    $targetHost = strtolower((string)$parts['host']);
    if (isset($parts['port'])) {
        $targetHost .= ':' . (int)$parts['port'];
    }
    $targetHost = dcNormalizeDomain($targetHost);

    $allowedDomains = [];
    $currentDomain = dcNormalizeDomain(getDomain());
    if ($currentDomain !== '') {
        $allowedDomains[] = $currentDomain;
    }
    $mainDomain = dcNormalizeDomain(Option::get('blogurl'));
    if ($mainDomain !== '') {
        $allowedDomains[] = $mainDomain;
    }
    foreach (dcGetBlogIndependentDomains() as $domain) {
        if ($domain !== '') {
            $allowedDomains[] = $domain;
        }
    }
    $allowedDomains = array_values(array_unique($allowedDomains));

    return in_array($targetHost, $allowedDomains, true) ? $url : $fallback;
}

/**
 * 生成用户中心登录地址，可携带登录成功后的安全返回地址。
 */
function dcGetUserLoginUrl($redirect = '') {
    $base = defined('DC_URL') ? DC_URL : realUrl();
    $loginUrl = rtrim($base, '/') . '/user/account.php?action=signin';
    $redirect = dcGetSafeRedirectUrl($redirect, '');
    if ($redirect !== '') {
        $loginUrl .= '&redirect=' . rawurlencode($redirect);
    }
    return $loginUrl;
}

/**
 * 生成博客独立访问地址。配置了独立域名时优先使用独立域名，否则沿用当前站点地址。
 */
function dcGetBlogBaseUrl($path = '') {
    $domains = dcGetBlogIndependentDomains();
    if (!empty($domains)) {
        $domain = $domains[0];
        $currentDomain = dcNormalizeDomain(getDomain());
        if ($currentDomain !== '' && in_array($currentDomain, $domains, true)) {
            $domain = $currentDomain;
        }
        $protocol = 'http://';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https://';
        } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = 'https://';
        }
        $base = $protocol . $domain . '/';
    } else {
        $base = defined('DC_URL') ? DC_URL : realUrl();
    }
    $path = (string)$path;
    if ($path === '') {
        return $base;
    }
    if ($path[0] === '?') {
        return rtrim($base, '/') . '/' . $path;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * 获取博客首页地址：独立域名为域名根目录，未配置时为主站 /blog
 */
function dcGetBlogHomeUrl($path = '') {
    $hasIndependentDomain = !empty(dcGetBlogIndependentDomains());
    if ($hasIndependentDomain) {
        return dcGetBlogBaseUrl($path);
    }
    $base = defined('DC_URL') ? DC_URL : realUrl();
    if ($path === '') {
        return rtrim($base, '/') . '/blog';
    }
    $path = (string)$path;
    if ($path[0] === '?') {
        return rtrim($base, '/') . '/blog' . $path;
    }
    return rtrim($base, '/') . '/blog/' . ltrim($path, '/');
}

/**
 * 获取博客首页分页地址。
 * - 主站普通模式：/blog?page=
 * - 主站伪静态：/blog/page/
 * - 博客独立域名：/page/
 */
function dcGetBlogListPaginationUrls() {
    $homeUrl = dcGetBlogHomeUrl();
    $homeUrl = trim((string)$homeUrl);
    if ($homeUrl === '') {
        return ['home' => '', 'base' => ''];
    }

    if (defined('IS_BLOG_DOMAIN') && IS_BLOG_DOMAIN) {
        $baseUrl = rtrim($homeUrl, '/') . '/page/';
    } elseif (Option::get('isurlrewrite') === '0') {
        $baseUrl = rtrim($homeUrl, '/') . '?page=';
    } else {
        $baseUrl = rtrim($homeUrl, '/') . '/page/';
    }

    return [
        'home' => $homeUrl,
        'base' => $baseUrl,
    ];
}
/**
 * 获取站点地址(仅限根目录脚本使用,目前仅用于首页ajax请求)
 */
function getBlogUrl() {
    $phpself = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    if (preg_match("/^.*\//", $phpself, $matches)) {
        return 'http://' . $_SERVER['HTTP_HOST'] . $matches[0];
    } else {
        return DC_URL;
    }
}

/**
 * 获取当前访问的base url
 */
function realUrl() {
    static $real_url = NULL;
    if ($real_url !== NULL) {
        return $real_url;
    }

    $dcshop_path = DC_ROOT . DIRECTORY_SEPARATOR;
    $script_path = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
    $script_path = str_replace('\\', '/', $script_path);
    $path_element = explode('/', $script_path);

    $this_match = '';
    $best_match = '';
    $current_deep = 0;
    $max_deep = count($path_element);
    while ($current_deep < $max_deep) {
        $this_match = $this_match . $path_element[$current_deep] . DIRECTORY_SEPARATOR;
        if (substr($dcshop_path, strlen($this_match) * (-1)) === $this_match) {
            $best_match = $this_match;
        }
        $current_deep++;
    }
    $best_match = str_replace(DIRECTORY_SEPARATOR, '/', $best_match);

    $protocol = 'http://';
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') { // 兼容nginx反向代理的情况
        $protocol = 'https://';
    } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = 'https://';
    }
    $host = $_SERVER['HTTP_HOST'];
    $real_url = $protocol . $host . $best_match;
    return $real_url;
}

/**
 * 检查插件
 */
function checkPlugin($plugin) {
    if (is_string($plugin) && preg_match("/^[\w\-\/]+\.php$/", $plugin) && file_exists(DC_ROOT . '/content/plugins/' . $plugin)) {
        // 额外检查：读取文件前几行，确保使用的是 DC_ROOT 而不是旧的 EM_ROOT
        $plugin_file = DC_ROOT . '/content/plugins/' . $plugin;
        $content = @file_get_contents($plugin_file, false, null, 0, 500); // 只读取前500字节
        
        // 如果插件使用了旧的 EM_ROOT 常量，跳过加载并记录警告
        if ($content && strpos($content, "defined('EM_ROOT')") !== false) {
            error_log("Plugin skipped [{$plugin}]: Uses deprecated EM_ROOT constant, please update to DC_ROOT");
            // 存储到全局变量供后台显示
            if (!isset($GLOBALS['plugin_compatibility_errors'])) {
                $GLOBALS['plugin_compatibility_errors'] = [];
            }
            $GLOBALS['plugin_compatibility_errors'][] = [
                'plugin' => $plugin,
                'error' => '插件使用了旧版常量 EM_ROOT，请更新为 DC_ROOT'
            ];
            return false;
        }
        
        return true;
    }

    return false;
}

/**
 * 获取模板主入口文件路径
 *
 * @param string $basePath 模板根目录
 * @param string $slug 模板标识
 * @param string $ext 文件扩展名（默认为 .php）
 * @return string|false 模板主入口文件路径或 false
 */
function getTemplateBootstrapFile($basePath, $slug, $ext = '.php') {
    if (!is_string($slug) || $slug === '') {
        return false;
    }
    $safeSlug = preg_replace('/^([\w-]+)$/i', '$1', $slug);
    if ($safeSlug !== $slug) {
        return false;
    }
    return rtrim($basePath, '/\\') . '/' . $safeSlug . '/' . $safeSlug . $ext;
}

/**
 * 检测模板主入口文件是否存在
 *
 * @param string $basePath 模板根目录
 * @param string $slug 模板标识
 * @param string $ext 文件扩展名（默认为 .php）
 * @return bool 模板主入口文件是否存在
 */
function checkTemplateBootstrap($basePath, $slug, $ext = '.php') {
    $bootstrapFile = getTemplateBootstrapFile($basePath, $slug, $ext);
    if ($bootstrapFile === false || !file_exists($bootstrapFile)) {
        return false;
    }
    $content = @file_get_contents($bootstrapFile, false, null, 0, 2048);
    if ($content === false || !preg_match('/\$__slug\s*=\s*[\'\"]([^\'\"]+)[\'\"]\s*;/', $content, $matches)) {
        return false;
    }
    return isset($matches[1]) && trim($matches[1]) === $slug;
}

/**
 * 加载模板主入口文件
 *
 * @param string $basePath 模板根目录
 * @param string $slug 模板标识
 * @param string $ext 文件扩展名（默认为 .php）
 * @return bool 模板主入口文件是否加载成功
 */
function loadTemplateBootstrap($basePath, $slug, $ext = '.php') {
    static $loadedBootstraps = [];
    $bootstrapFile = getTemplateBootstrapFile($basePath, $slug, $ext);
    if ($bootstrapFile === false || !checkTemplateBootstrap($basePath, $slug, $ext)) {
        return false;
    }
    if (!isset($loadedBootstraps[$bootstrapFile])) {
        require_once $bootstrapFile;
        $loadedBootstraps[$bootstrapFile] = true;
    }
    return true;
}

function getTemplateSettingFile($basePath, $slug, $ext = '.php') {
    if (!is_string($slug) || $slug === '') {
        return false;
    }
    $safeSlug = preg_replace('/^([\w-]+)$/i', '$1', $slug);
    if ($safeSlug !== $slug) {
        return false;
    }
    $tplPath = rtrim($basePath, '/\\') . '/' . $safeSlug . '/';
    if (!is_dir($tplPath)) {
        return false;
    }
    $pluginStyleSetting = $tplPath . $safeSlug . '_setting' . $ext;
    if (file_exists($pluginStyleSetting)) {
        return $pluginStyleSetting;
    }
    return false;
}

function hasTemplateSettingFile($basePath, $slug, $ext = '.php') {
    return getTemplateSettingFile($basePath, $slug, $ext) !== false;
}

function getTemplateCallbackFile($basePath, $slug, $ext = '.php') {
    if (!is_string($slug) || $slug === '') {
        return false;
    }
    $safeSlug = preg_replace('/^([\w-]+)$/i', '$1', $slug);
    if ($safeSlug !== $slug) {
        return false;
    }
    $tplPath = rtrim($basePath, '/\\') . '/' . $safeSlug . '/';
    if (!is_dir($tplPath)) {
        return false;
    }
    $pluginStyleCallback = $tplPath . $safeSlug . '_callback' . $ext;
    if (file_exists($pluginStyleCallback)) {
        return $pluginStyleCallback;
    }
    return false;
}

function runTemplateCallback($basePath, $slug, $callbackFunction, $ext = '.php') {
    $callbackFile = getTemplateCallbackFile($basePath, $slug, $ext);
    if ($callbackFile === false) {
        return false;
    }
    require_once $callbackFile;
    if (is_string($callbackFunction) && function_exists($callbackFunction)) {
        $callbackFunction();
        return true;
    }
    return false;
}

/**
 * 验证email地址格式
 */
function checkMail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }

    return false;
}

/**
 * 截取编码为utf8的字符串
 *
 * @param string $strings 预处理字符串
 * @param int $start 开始处 eg:0
 * @param int $length 截取长度
 */
function subString($strings, $start, $length) {
    $sub_str = mb_substr($strings, $start, $length, 'utf8');
    return mb_strlen($sub_str, 'utf8') < mb_strlen($strings, 'utf8') ? $sub_str . '...' : $sub_str;
}

/**
 * 从可能包含html标记的内容中萃取纯文本摘要
 */
function extractHtmlData($data, $len) {
    $data = subString(strip_tags($data), 0, $len + 30);
    $search = array(
        "/([\r\n])[\s]+/", // 去掉空白字符
        "/&(quot|#34);/i", // 替换 HTML 实体
        "/&(amp|#38);/i",
        "/&(lt|#60);/i",
        "/&(gt|#62);/i",
        "/&(nbsp|#160);/i",
        "/&(iexcl|#161);/i",
        "/&(cent|#162);/i",
        "/&(pound|#163);/i",
        "/&(copy|#169);/i",
        "/\"/i",
    );
    $replace = array(" ", "\"", "&", " ", " ", "", chr(161), chr(162), chr(163), chr(169), "");
    $data = trim(subString(preg_replace($search, $replace, $data), 0, $len));
    return $data;
}

/**
 * 递归复制目录（包含所有子目录和文件）
 * @param string $sourceDir 原目录路径（必须存在）
 * @param string $targetDir 目标目录路径（不存在则自动创建）
 * @param bool $skipSymlink 是否跳过符号链接（默认true，避免循环）
 * @return bool 复制是否成功
 */
function copyDirectory($sourceDir, $targetDir, $skipSymlink = true) {
    // 标准化路径（统一末尾分隔符）
    $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    // 检查原目录是否存在且是目录
    if (!is_dir($sourceDir)) {
        trigger_error("原目录 {$sourceDir} 不存在或不是目录", E_USER_ERROR);
        return false;
    }

    // 自动创建目标目录（含多级目录）
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            trigger_error("目标目录 {$targetDir} 创建失败（权限不足）", E_USER_ERROR);
            return false;
        }
    }

    // 打开原目录并遍历所有内容
    $dirHandle = opendir($sourceDir);
    if (!$dirHandle) {
        trigger_error("无法打开原目录 {$sourceDir}（权限不足）", E_USER_ERROR);
        return false;
    }

    // 遍历目录中的每个项（文件/子目录/隐藏文件）
    while (($item = readdir($dirHandle)) !== false) {
        // 跳过 . 和 ..（当前目录/上级目录）
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourceItem = $sourceDir . $item; // 原项的完整路径
        $targetItem = $targetDir . $item; // 目标项的完整路径

        // 跳过符号链接（可选）
        if ($skipSymlink && is_link($sourceItem)) {
            continue;
        }

        // 如果是目录：递归复制子目录
        if (is_dir($sourceItem)) {
            if (!copyDirectory($sourceItem, $targetItem, $skipSymlink)) {
                closedir($dirHandle);
                return false;
            }
        }
        // 如果是文件：复制文件（保留权限）
        elseif (is_file($sourceItem)) {
            // copy() 复制文件内容，chmod() 同步权限
            if (!copy($sourceItem, $targetItem)) {
                trigger_error("文件 {$sourceItem} 复制失败", E_USER_WARNING);
                closedir($dirHandle);
                return false;
            }
            // 同步文件权限（可选）
            chmod($targetItem, fileperms($sourceItem));
        }
    }

    // 关闭目录句柄
    closedir($dirHandle);
    return true;
}

/**
 * 转换文件大小单位
 *
 * @param string $fileSize 文件大小 kb
 */
function changeFileSize($fileSize) {
    if ($fileSize >= 1073741824) {
        $fileSize = round($fileSize / 1073741824, 2) . 'GB';
    } elseif ($fileSize >= 1048576) {
        $fileSize = round($fileSize / 1048576, 2) . 'MB';
    } elseif ($fileSize >= 1024) {
        $fileSize = round($fileSize / 1024, 2) . 'KB';
    } else {
        $fileSize .= '字节';
    }
    return $fileSize;
}

/**
 * 获取文件名后缀
 */
function getFileSuffix($fileName) {
    return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
}

/**
 * 将相对路径转换为完整URL，eg：../content/uploadfile/xxx.jpeg
 */
function getFileUrl($filePath) {
    if (stripos($filePath, 'http') === false) {
        return DC_URL . substr($filePath, 3);
    }
    return $filePath;
}

/**
 * 去除url的参数
 */
function rmUrlParams($url) {
    $urlInfo = explode("?", $url);
    if (empty($urlInfo[0])) {
        return $url;
    }
    return $urlInfo[0];
}

function isImage($mimetype) {
    if (strpos($mimetype, "image") !== false) {
        return true;
    }
    return false;
}

function isVideo($fileName) {
    $suffix = getFileSuffix($fileName);
    return $suffix === 'mp4';
}

function isAudio($fileName) {
    $suffix = getFileSuffix($fileName);
    return $suffix === 'mp3';
}

function isZip($fileName) {
    $suffix = getFileSuffix($fileName);
    if (in_array($suffix, ['zip', 'rar', '7z', 'gz'])) {
        return true;
    }
    return false;
}

/**
 * 分页函数
 *
 * @param int $count 条目总数
 * @param int $perlogs 每页显示条数目
 * @param int $page 当前页码
 * @param string $url 页码的地址
 * @return string
 */
function pagination($count, $perlogs, $page, $url, $anchor = '') {
    $pnums = @ceil($count / $perlogs);
    $re = '';
    $urlHome = preg_replace("|[\?&/][^\./\?&=]*page[=/\-]|", "", $url);

    $frontContent = '';
    $paginContent = '';
    $endContent = '';
    $circle_a = 1;
    $circle_b = $pnums;
    $neighborNum = 1;
    $minKey = 4;

    if ($pnums == 1)
        return $re;
    if ($page >= 1 && $pnums >= 7) {
        $frontContent .= " <a class='btn ghost' href=\"$urlHome$anchor\">1</a> ";
        $frontContent .= " <em class='btn ghost'> ... </em> ";
        $endContent .= " <em class='btn ghost'> ... </em> ";
        $endContent .= " <a class='btn ghost' href=\"$url$pnums$anchor\">$pnums</a> ";
        if ($pnums >= 12) {
            $minKey = 7;
            $neighborNum = 3;
        }
        if ($page < $minKey) {
            $circle_b = $minKey;
            $frontContent = '';
        }
        if ($page > ($pnums - $minKey + 1)) {
            $circle_a = $pnums - $minKey + 1;
            $endContent = '';
        }
        if ($page > ($minKey - 1) && $page < ($pnums - $minKey + 2)) {
            $circle_a = $page - $neighborNum;
            $circle_b = $page + $neighborNum;
        }
        if ($page != 1) {
            $frontContent = " <a class='btn ghost' href=\"$url" . ($page - 1) . "$anchor\" title=\"Previous Page\">&laquo;</a> " . $frontContent;
        }
        if ($page != $pnums) {
            $endContent .= " <a class='btn ghost' href=\"$url" . ($page + 1) . "$anchor\" title=\"Next Page\">&raquo;</a> ";
        }
    }
    for ($i = $circle_a; $i <= $circle_b; $i++) {
        if ($i == $page) {
            $paginContent .= " <span class='btn ghost active'>$i</span> ";
        } elseif ($i == 1) {
            $paginContent .= " <a class='btn ghost' href=\"$urlHome$anchor\">$i</a> ";
        } else {
            $paginContent .= " <a class='btn ghost' href=\"$url$i$anchor\">$i</a> ";
        }
    }
    $re = $frontContent . $paginContent . $endContent;
    return $re;
}

/**
 * 该函数在插件中调用,挂载插件函数到预留的钩子上
 */
function addAction($hook, $actionFunc) {
    // 通过全局变量来存储挂载点上挂载的插件函数
    global $emHooks;
    if (!isset($emHooks[$hook]) || !in_array($actionFunc, $emHooks[$hook])) {
        $emHooks[$hook][] = $actionFunc;
    }
    return true;
}

/**
 * 挂载执行方式1（插入式挂载）：执行挂在钩子上的函数,支持多参数 eg:doAction('post_comment', $author, $email, $url, $comment);
 * eg：在挂载点插入扩展内容
 */
function doAction($hook) {
    global $emHooks;
    $args = array_slice(func_get_args(), 1);
    if (isset($emHooks[$hook])) {
        foreach ($emHooks[$hook] as $function) {
            call_user_func_array($function, $args);
        }
    }
}

/**
 * 挂载执行方式2（单次接管式挂载）：执行挂在钩子上的第一个函数,仅执行行一次，接收输入input，且会修改传入的变量$ret
 * eg：接管文件上传函数，将上传本地改为上传云端
 */
function doOnceAction($hook, $input, &$ret) {
    global $emHooks;
    $args = [$input, &$ret];
    $func = !empty($emHooks[$hook][0]) ? $emHooks[$hook][0] : '';
    if ($func) {
        call_user_func_array($func, $args);
    }
}

/**
 * 挂载执行方式3（轮流接管式挂载）：执行挂在钩子上的所有函数，上一个执行结果作为下一个的输入，且会修改传入的变量$ret
 * eg：不同插件对文章内容进行不同的修改替换。
 */
function doMultiAction($hook, $input, &$ret) {
    global $emHooks;
    $args = [$input, &$ret];
    if (isset($emHooks[$hook])) {
        foreach ($emHooks[$hook] as $function) {
            call_user_func_array($function, $args);
            $args = [&$ret, &$ret];
        }
    }
}

/**
 * 截取文章内容前len个字符
 */
function subContent($content, $len, $clean = 0) {
    if ($clean) {
        $content = strip_tags($content);
    }
    return subString($content, 0, $len);
}

/**
 * 时间转化函数
 * @param $timestamp int 时间戳(秒)
 * @param $format
 * @return false|string
 */
function smartDate($timestamp, $format = 'Y-m-d H:i') {
    $sec = time() - $timestamp;
    if ($sec < 60) {
        $op = $sec . ' 秒前';
    } elseif ($sec < 3600) {
        $op = floor($sec / 60) . " 分钟前";
    } elseif ($sec < 3600 * 24) {
        $op = "约 " . floor($sec / 3600) . " 小时前";
    } else {
        $op = date($format, $timestamp);
    }
    return $op;
}

function getRandStr($length = 12, $special_chars = true, $numeric_only = false) {
    if ($numeric_only) {
        $chars = '0123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }
    }
    $randStr = '';
    $chars_length = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $randStr .= substr($chars, mt_rand(0, $chars_length - 1), 1);
    }
    return $randStr;
}

/**
 * 上传文件到当前服务器
 * @param $attach array 文件FILE信息
 * @param $result array 上传结果
 */
function upload2local($attach, &$result) {
    $fileName = $attach['name'];
    $tmpFile = $attach['tmp_name'];
    $fileSize = $attach['size'];

    $fileName = Database::getInstance()->escape_string($fileName);

    $ret = upload($fileName, $tmpFile, $fileSize);
    $success = 0;
    switch ($ret) {
        case '105':
            $message = '上传失败。文件上传目录不可写 (content/uploadfile)';
            break;
        default:
            $message = '上传成功';
            $success = 1;
            break;
    }

    $result = [
        'success'   => $success,
        'message'   => $message,
        'url'       => $success ? getFileUrl($ret['file_path']) : '',
        'file_info' => $success ? $ret : [],
    ];
}

/**
 * 文件上传
 *
 * 返回的数组索引
 * mime_type 文件类型
 * size      文件大小(单位KB)
 * file_path 文件路径
 * width     宽度
 * height    高度
 * 可选值（仅在上传文件是图片且系统开启缩略图时起作用）
 * thum_file   缩略图的路径
 *
 * @param string $fileName 文件名
 * @param string $tmpFile 上传后的临时文件
 * @param string $fileSize 文件大小 KB
 * @return array | string 文件数据 索引
 *
 */
function upload($fileName, $tmpFile, $fileSize) {
    $extension = getFileSuffix($fileName);
    $file_info = [];
    $file_info['file_name'] = $fileName;
    $file_info['mime_type'] = get_mimetype($extension);
    $file_info['size'] = $fileSize;
    $file_info['width'] = 0;
    $file_info['height'] = 0;

    $fileName = substr(md5($fileName), 0, 4) . time() . '.' . $extension;

    // 读取、写入文件使用绝对路径，兼容API文件上传
    $uploadFullPath = Option::UPLOADFILE_FULL_PATH . gmdate('Ym') . '/';
    $uploadFullFile = $uploadFullPath . $fileName;
    $thumFullFile = $uploadFullPath . 'thum-' . $fileName;

    // 输出文件信息使用相对路径，兼容头像上传等业务场景
    $uploadPath = Option::UPLOADFILE_PATH . gmdate('Ym') . '/';
    $uploadFile = $uploadPath . $fileName;
    $thumFile = $uploadPath . 'thum-' . $fileName;

    $file_info['file_path'] = $uploadFile;

    if (!createDirectoryIfNeeded($uploadFullPath)) {
        return '105'; // 创建上传目录失败
    }

    doAction('attach_upload', $tmpFile);

    // 生成缩略图
    $is_thumbnail = Option::get('isthumbnail') === 'y';
    if ($is_thumbnail && resizeImage($tmpFile, $thumFullFile, Option::get('att_imgmaxw'), Option::get('att_imgmaxh'))) {
        $file_info['thum_file'] = $thumFile;
    }

    // 完成上传
    if (@is_uploaded_file($tmpFile) && @!move_uploaded_file($tmpFile, $uploadFullFile)) {
        @unlink($tmpFile);
        return '105'; //上传失败。上传目录不可写
    }

    // 提取图片宽高
    if (in_array($file_info['mime_type'], array('image/jpeg', 'image/png', 'image/gif', 'image/bmp'))) {
        $size = getimagesize($uploadFullFile);
        if ($size) {
            $file_info['width'] = $size[0];
            $file_info['height'] = $size[1];
        }
    }
    return $file_info;
}

function createDirectoryIfNeeded($path) {
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            return false;
        }
    }
    return true;
}

/**
 * 图片生成缩略图
 *
 * @param string $img 预缩略的图片
 * @param string $thum_path 生成缩略图路径
 * @param int $max_w 缩略图最大宽度 px
 * @param int $max_h 缩略图最大高度 px
 * @return bool
 */
function resizeImage($img, $thum_path, $max_w, $max_h) {
    if (!in_array(getFileSuffix($thum_path), array('jpg', 'png', 'jpeg', 'gif'))) {
        return false;
    }
    if (!function_exists('ImageCreate')) {
        return false;
    }

    $size = chImageSize($img, $max_w, $max_h);
    $newwidth = $size['w'];
    $newheight = $size['h'];
    $w = $size['rc_w'];
    $h = $size['rc_h'];
    if ($w <= $max_w && $h <= $max_h) {
        return false;
    }
    return imageCropAndResize($img, $thum_path, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
}

/**
 * 裁剪、缩放图片
 *
 * @param string $src_image 原始图
 * @param string $dst_path 裁剪后的图片保存路径
 * @param int $dst_x 新图坐标x
 * @param int $dst_y 新图坐标y
 * @param int $src_x 原图坐标x
 * @param int $src_y 原图坐标y
 * @param int $dst_w 新图宽度
 * @param int $dst_h 新图高度
 * @param int $src_w 原图宽度
 * @param int $src_h 原图高度
 */
function imageCropAndResize($src_image, $dst_path, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
    if (!function_exists('imagecreatefromstring')) {
        return false;
    }

    $src_img = imagecreatefromstring(file_get_contents($src_image));
    if (!$src_img) {
        return false;
    }

    if (function_exists('imagecopyresampled')) {
        $new_img = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($new_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    } elseif (function_exists('imagecopyresized')) {
        $new_img = imagecreate($dst_w, $dst_h);
        imagecopyresized($new_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    } else {
        return false;
    }

    switch (getFileSuffix($dst_path)) {
        case 'png':
            if (function_exists('imagepng') && imagepng($new_img, $dst_path)) {
                ImageDestroy($new_img);
                return true;
            }
            return false;
        case 'jpg':
        default:
            if (function_exists('imagejpeg') && imagejpeg($new_img, $dst_path)) {
                ImageDestroy($new_img);
                return true;
            }
            return false;
        case 'gif':
            if (function_exists('imagegif') && imagegif($new_img, $dst_path)) {
                ImageDestroy($new_img);
                return true;
            }
            return false;
    }
}

/**
 * 按比例计算图片缩放尺寸
 *
 * @param string $img 图片路径
 * @param int $max_w 最大缩放宽
 * @param int $max_h 最大缩放高
 * @return array
 */
function chImageSize($img, $max_w, $max_h) {
    $size = @getimagesize($img);
    if (!$size) {
        return [];
    }
    $w = $size[0];
    $h = $size[1];
    //计算缩放比例
    @$w_ratio = $max_w / $w;
    @$h_ratio = $max_h / $h;
    //决定处理后的图片宽和高
    if (($w <= $max_w) && ($h <= $max_h)) {
        $tn['w'] = $w;
        $tn['h'] = $h;
    } else if (($w_ratio * $h) < $max_h) {
        $tn['h'] = ceil($w_ratio * $h);
        $tn['w'] = $max_w;
    } else {
        $tn['w'] = ceil($h_ratio * $w);
        $tn['h'] = $max_h;
    }
    $tn['rc_w'] = $w;
    $tn['rc_h'] = $h;
    return $tn;
}

/**
 * 获取Gravatar头像
 */
if (!function_exists('getGravatar')) {
    function getGravatar($email, $s = 40) {
        $hash = md5($email);
        $gravatar_url = "//cravatar.cn/avatar/$hash?s=$s";
        doOnceAction('get_Gravatar', $email, $gravatar_url);

        return $gravatar_url;
    }
}

/**
 * 获取指定月份的天数
 * @param $month string 月份 01-12
 * @param $year string 年份 0000
 * @return false|string
 */
function getMonthDayNum($month, $year) {
    return date("t", strtotime($year . $month . '01'));
}

/**
 * 解压zip
 * @param string $zipfile 要解压的文件
 * @param string $path 解压到该目录
 * @param string $type
 * @return int
 */
function emUnZip($zipfile, $path, $type = 'tpl') {
    if (!class_exists('ZipArchive', FALSE)) {
        return 3;//zip模块问题
    }
    $zip = new ZipArchive();
    if (@$zip->open($zipfile) !== TRUE) {
        return 2;//文件权限问题
    }
    $r = explode('/', $zip->getNameIndex(0), 2);
    $dir = isset($r[0]) ? $r[0] . '/' : '';
    switch ($type) {
        case 'tpl':
            $tplName = substr($dir, 0, -1);
            $legacyHeader = $zip->getFromName($dir . 'header.php');
            $bootstrapFile = $tplName !== '' ? $zip->getFromName($dir . $tplName . '.php') : false;
            $bootstrapValid = false;
            if (false !== $bootstrapFile && preg_match('/\$__slug\s*=\s*[\'\"]([^\'\"]+)[\'\"]\s*;/', $bootstrapFile, $matches)) {
                $bootstrapValid = isset($matches[1]) && trim($matches[1]) === $tplName;
            }
            if (false === $legacyHeader && !$bootstrapValid) {
                $zip->close();
                return -2;
            }
            break;
        case 'plugin':
            $plugin_name = substr($dir, 0, -1);
            $re = $zip->getFromName($dir . $plugin_name . '.php');
            if (false === $re) {
                return -1;
            }
            break;
        case 'backup':
            $sql_name = substr($dir, 0, -1);
            if (getFileSuffix($sql_name) != 'sql') {
                return -3;
            }
            break;
        case 'update':
            break;
    }
    if (true === @$zip->extractTo($path)) {
        $zip->close();
        return 0;
    }

    return 1; //文件权限问题
}

/**
 * Zip compression
 */
function emZip($orig_fname, $content) {
    if (!class_exists('ZipArchive', FALSE)) {
        return false;
    }
    $zip = new ZipArchive();
    $tempzip = DC_ROOT . '/content/cache/emtemp.zip';
    $res = $zip->open($tempzip, ZipArchive::CREATE);
    if ($res === TRUE) {
        $zip->addFromString($orig_fname, $content);
        $zip->close();
        $zip_content = file_get_contents($tempzip);
        unlink($tempzip);
        return $zip_content;
    }

    return false;
}

/**
 * Download remote files
 * @param string $source file url
 * @return string Temporary file path
 */
function emFetchFile($source) {
    $temp_file = tempnam(DC_ROOT . '/content/cache/', 'tmp_');
    $wh = fopen($temp_file, 'w+b');

    $ctx_opt = set_ctx_option();
    $ctx = stream_context_create($ctx_opt);
    $rh = @fopen($source, 'rb', false, $ctx);

    if (!$rh || !$wh) {
        return false;
    }

    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 4096)) === false) {
            return false;
        }
    }
    fclose($rh);
    fclose($wh);
    return $temp_file;
}



/**
 * Download remote files
 * @param string $source file url
 * @return string Temporary file path
 */
function emDownFile($source) {
    $ctx_opt = set_ctx_option();
    $context = stream_context_create($ctx_opt);
    $content = file_get_contents($source, false, $context);
    if ($content === false) {
        return false;
    }

    $temp_file = tempnam(DC_ROOT . '/content/cache/', 'tmp_');
    if ($temp_file === false) {
        emMsg('emDownFile：Failed to create temporary file.');
    }
    $ret = file_put_contents($temp_file, $content);
    if ($ret === false) {
        emMsg('emDownFile：Failed to write temporary file.');
    }

    return $temp_file;
}

function set_ctx_option() {

    $emkey = getMyEmKey();
    return [
        'http' => [
            'timeout' => 120,
            'method'  => 'GET',
            'header'  => "Referer: " . DC_URL . "\r\n"
                . "Emkey: " . $emkey . "\r\n"
                . "User-Agent: DCSHOP " . Option::DC_VERSION . "\r\n",
        ],
        "ssl"  => [
            "verify_peer"      => false,
            "verify_peer_name" => false,
        ]
    ];
}

/**
 * 删除文件或目录
 */
function emDeleteFile($file) {
    if (empty($file)) {
        return false;
    }
    if (@is_file($file)) {
        return @unlink($file);
    }
    $ret = true;
    if ($handle = @opendir($file)) {
        while ($filename = @readdir($handle)) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            if (!emDeleteFile($file . '/' . $filename)) {
                $ret = false;
            }
        }
    } else {
        $ret = false;
    }
    @closedir($handle);
    if (file_exists($file) && !rmdir($file)) {
        $ret = false;
    }
    return $ret;
}

/**
 * 页面跳转
 */
function emDirect($directUrl) {
    header("Location: $directUrl");
    exit;
}

/**
 * 显示系统信息
 *
 * @param string $msg 信息
 * @param string $url 返回地址
 * @param boolean $isAutoGo 是否自动返回 true false
 */
function emMsg($msg, $url = 'javascript:history.back(-1);', $isAutoGo = false) {
    $is404 = false;
    if ($msg == '404') {
        header("HTTP/1.1 404 Not Found");
        $msg = '这个页面好像走丢了~';
        $is404 = true;
    }

    // 根据消息类型选择 emoji、标题和配色
    if ($is404) {
        $emoji = '🔍';
        $title = '找不到页面啦';
        $accent = '#f59e0b';
        $bg = 'linear-gradient(135deg,#fffbeb 0%,#fef3c7 50%,#fde68a 100%)';
        $cardBorder = '#fde68a';
    } elseif (mb_strpos($msg, '成功') !== false || mb_strpos($msg, '完成') !== false) {
        $emoji = '🎉';
        $title = '搞定啦！';
        $accent = '#10b981';
        $bg = 'linear-gradient(135deg,#ecfdf5 0%,#d1fae5 50%,#a7f3d0 100%)';
        $cardBorder = '#a7f3d0';
    } elseif (mb_strpos($msg, '下架') !== false || mb_strpos($msg, '删除') !== false || mb_strpos($msg, '不存在') !== false) {
        $emoji = '😅';
        $title = '哎呀~';
        $accent = '#f97316';
        $bg = 'linear-gradient(135deg,#fff7ed 0%,#ffedd5 50%,#fed7aa 100%)';
        $cardBorder = '#fed7aa';
    } elseif (mb_strpos($msg, '错误') !== false || mb_strpos($msg, '失败') !== false || mb_strpos($msg, '禁止') !== false) {
        $emoji = '😵';
        $title = '出了点小状况';
        $accent = '#ef4444';
        $bg = 'linear-gradient(135deg,#fef2f2 0%,#fee2e2 50%,#fecaca 100%)';
        $cardBorder = '#fecaca';
    } else {
        $emoji = '💡';
        $title = '温馨提示';
        $accent = '#6366f1';
        $bg = 'linear-gradient(135deg,#eef2ff 0%,#e0e7ff 50%,#c7d2fe 100%)';
        $cardBorder = '#c7d2fe';
    }

    $autoRefresh = $isAutoGo ? "<meta http-equiv=\"refresh\" content=\"2;url=$url\" />" : '';
    $countdown = $isAutoGo ? '<p class="em-cd">马上带你回去~ <span id="em-sec">2</span>s</p>' : '';
    $countdownJs = $isAutoGo ? '<script>!function(){var s=2,e=document.getElementById("em-sec");setInterval(function(){s>0&&(e.textContent=--s)},1e3)}()</script>' : '';

    $backBtn = '';
    if ($url != 'none') {
        $backBtn = '<a class="em-btn" href="' . $url . '">带我回去</a>';
    }

    echo <<<EOT
<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="applicable-device" content="pc,mobile">
    {$autoRefresh}
    <title>{$title}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:{$bg};font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif;color:#334155;padding:20px}
        .em-card{background:#fff;border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,.06);max-width:400px;width:100%;padding:44px 36px 36px;text-align:center;animation:em-pop .5s cubic-bezier(.34,1.56,.64,1);border:2px solid {$cardBorder}}
        @keyframes em-pop{0%{opacity:0;transform:scale(.9) translateY(20px)}60%{transform:scale(1.02) translateY(-4px)}100%{opacity:1;transform:scale(1) translateY(0)}}
        .em-emoji{font-size:56px;margin-bottom:16px;animation:em-bounce 1s ease infinite;display:inline-block}
        @keyframes em-bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
        .em-title{font-size:20px;font-weight:700;color:#1e293b;margin-bottom:10px}
        .em-msg{font-size:15px;line-height:1.8;color:#64748b;margin-bottom:28px;word-break:break-word}
        .em-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 32px;background:{$accent};color:#fff;text-decoration:none;border-radius:50px;font-size:14px;font-weight:600;transition:all .25s;border:none;cursor:pointer;letter-spacing:.5px}
        .em-btn:hover{filter:brightness(1.1);transform:translateY(-2px) scale(1.03);box-shadow:0 6px 20px rgba(0,0,0,.12)}
        .em-btn:active{transform:translateY(0) scale(.98)}
        .em-cd{font-size:13px;color:#94a3b8;margin-top:16px;animation:em-fade 1s ease infinite alternate}
        @keyframes em-fade{from{opacity:.6}to{opacity:1}}
        .em-footer{margin-top:24px;font-size:12px;color:#cbd5e1}
        @media(max-width:480px){.em-card{padding:32px 24px 28px;border-radius:16px;margin:0 8px}.em-emoji{font-size:48px}.em-title{font-size:18px}.em-msg{font-size:14px}}
    </style>
</head>
<body>
    <div class="em-card">
        <div class="em-emoji">{$emoji}</div>
        <div class="em-title">{$title}</div>
        <div class="em-msg">{$msg}</div>
        {$backBtn}
        {$countdown}
    </div>
    {$countdownJs}
</body>
</html>
EOT;
    exit;
}

function show_404_page($show_404_only = false) {
    doAction('page_not_found');
    if ($show_404_only) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }

    $isBlogContext = false;
    if (defined('BLOG_TEMPLATE_PATH') && class_exists('View')) {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10) as $trace) {
            $class = isset($trace['class']) ? $trace['class'] : '';
            if (in_array($class, ['Log_Controller', 'Blogsort_Controller', 'Tag_Controller', 'Search_Controller', 'Record_Controller', 'Author_Controller'], true)) {
                $isBlogContext = true;
                break;
            }
        }
    }
    if ($isBlogContext && is_file(BLOG_TEMPLATE_PATH . '404.php')) {
        header("HTTP/1.1 404 Not Found");
        include View::getBlogView('404');
        exit;
    }

    if (is_file(TEMPLATE_PATH . '404.php')) {
        header("HTTP/1.1 404 Not Found");
        include View::getView('404');
        exit;
    }

    emMsg('404', DC_URL);
}

/**
 * hmac 加密
 *
 * @param unknown_type $algo hash算法 md5
 * @param unknown_type $data 用户名和到期时间
 * @param unknown_type $key
 * @return unknown
 */
if (!function_exists('hash_hmac')) {
    function hash_hmac($algo, $data, $key) {
        $packs = array('md5' => 'H32', 'sha1' => 'H40');

        if (!isset($packs[$algo])) {
            return false;
        }

        $pack = $packs[$algo];

        if (strlen($key) > 64) {
            $key = pack($pack, $algo($key));
        } elseif (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }

        $ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
        $opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

        return $algo($opad . pack($pack, $algo($ipad . $data)));
    }
}

/**
 * 根据文件后缀获取其mine类型
 */
function get_mimetype($extension) {
    $ct['htm'] = 'text/html';
    $ct['html'] = 'text/html';
    $ct['txt'] = 'text/plain';
    $ct['asc'] = 'text/plain';
    $ct['bmp'] = 'image/bmp';
    $ct['gif'] = 'image/gif';
    $ct['jpeg'] = 'image/jpeg';
    $ct['jpg'] = 'image/jpeg';
    $ct['jpe'] = 'image/jpeg';
    $ct['png'] = 'image/png';
    $ct['webp'] = 'image/webp';
    $ct['ico'] = 'image/vnd.microsoft.icon';
    $ct['mpeg'] = 'video/mpeg';
    $ct['mpg'] = 'video/mpeg';
    $ct['mpe'] = 'video/mpeg';
    $ct['qt'] = 'video/quicktime';
    $ct['mov'] = 'video/quicktime';
    $ct['avi'] = 'video/x-msvideo';
    $ct['wmv'] = 'video/x-ms-wmv';
    $ct['mp2'] = 'audio/mpeg';
    $ct['mp3'] = 'audio/mpeg';
    $ct['rm'] = 'audio/x-pn-realaudio';
    $ct['ram'] = 'audio/x-pn-realaudio';
    $ct['rpm'] = 'audio/x-pn-realaudio-plugin';
    $ct['ra'] = 'audio/x-realaudio';
    $ct['wav'] = 'audio/x-wav';
    $ct['css'] = 'text/css';
    $ct['zip'] = 'application/zip';
    $ct['pdf'] = 'application/pdf';
    $ct['doc'] = 'application/msword';
    $ct['bin'] = 'application/octet-stream';
    $ct['exe'] = 'application/octet-stream';
    $ct['class'] = 'application/octet-stream';
    $ct['dll'] = 'application/octet-stream';
    $ct['xls'] = 'application/vnd.ms-excel';
    $ct['ppt'] = 'application/vnd.ms-powerpoint';
    $ct['wbxml'] = 'application/vnd.wap.wbxml';
    $ct['wmlc'] = 'application/vnd.wap.wmlc';
    $ct['wmlsc'] = 'application/vnd.wap.wmlscriptc';
    $ct['dvi'] = 'application/x-dvi';
    $ct['spl'] = 'application/x-futuresplash';
    $ct['gtar'] = 'application/x-gtar';
    $ct['gzip'] = 'application/x-gzip';
    $ct['js'] = 'application/x-javascript';
    $ct['swf'] = 'application/x-shockwave-flash';
    $ct['tar'] = 'application/x-tar';
    $ct['xhtml'] = 'application/xhtml+xml';
    $ct['au'] = 'audio/basic';
    $ct['snd'] = 'audio/basic';
    $ct['midi'] = 'audio/midi';
    $ct['mid'] = 'audio/midi';
    $ct['m3u'] = 'audio/x-mpegurl';
    $ct['tiff'] = 'image/tiff';
    $ct['tif'] = 'image/tiff';
    $ct['rtf'] = 'text/rtf';
    $ct['wml'] = 'text/vnd.wap.wml';
    $ct['wmls'] = 'text/vnd.wap.wmlscript';
    $ct['xsl'] = 'text/xml';
    $ct['xml'] = 'text/xml';

    return isset($ct[strtolower($extension)]) ? $ct[strtolower($extension)] : 'text/html';
}

/**
 * 将字符串转换为时区无关的UNIX时间戳
 */
function emStrtotime($timeStr) {
    if (!$timeStr) {
        return false;
    }

    $timezone = Option::get('timezone');

    $unixPostDate = strtotime($timeStr);
    if (!$unixPostDate) {
        return false;
    }

    $serverTimeZone = date_default_timezone_get();
    if (empty($serverTimeZone) || $serverTimeZone == 'UTC') {
        $unixPostDate -= (int)$timezone * 3600;
    } elseif ($serverTimeZone) {
        /*
         * 如果服务器配置默认了时区，那么PHP将会把传入的时间识别为时区当地时间
         * 但是我们传入的时间实际是blog配置的时区的当地时间，并不是服务器时区的当地时间
         * 因此，我们需要将strtotime得到的时间去掉/加上两个时区的时差，得到utc时间
         */
        $offset = getTimeZoneOffset($serverTimeZone);
        // 首先减去/加上本地时区配置的时差
        $unixPostDate -= (int)$timezone * 3600;
        // 再减去/加上服务器时区与utc的时差，得到utc时间
        $unixPostDate -= $offset;
    }
    return $unixPostDate;
}

/**
 * 加载jQuery
 */
function emLoadJQuery() {
    static $isJQueryLoaded = false;
    if (!$isJQueryLoaded) {
        global $emHooks;
        if (!isset($emHooks['index_head'])) {
            $emHooks['index_head'] = array();
        }
        array_unshift($emHooks['index_head'], 'loadJQuery');
        $isJQueryLoaded = true;

        function loadJQuery() {
            echo '<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>';
        }
    }
}

/**
 * 计算时区的时差
 * @param string $remote_tz 远程时区
 * @param string $origin_tz 标准时区
 *
 * @throws Exception
 */
function getTimeZoneOffset($remote_tz, $origin_tz = 'UTC') {
    if (($origin_tz === null) && !is_string($origin_tz = date_default_timezone_get())) {
        return false; // A UTC timestamp was returned -- bail out!
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime('now', $origin_dtz);
    $remote_dt = new DateTime('now', $remote_dtz);
    return $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
}

/**
 * Upload the cut pictures (cover and avatar)
 */
function uploadCropImg() {
    $attach = isset($_FILES['image']) ? $_FILES['image'] : '';

    $uploadCheckResult = Media::checkUpload($attach);
    if ($uploadCheckResult !== true) {
        Output::error($uploadCheckResult);
    }

    $ret = '';
    upload2local($attach, $ret);
    if (empty($ret['success'])) {
        Output::error($ret['message']);
    }
    return $ret;
}

function getSiteFaviconUrl($default = '') {
    $favicon = Option::get('admin_favicon');
    if (empty($favicon)) {
        return $default;
    }
    if (preg_match('/^(https?:)?\/\//i', $favicon) || strpos($favicon, 'data:') === 0) {
        return $favicon;
    }
    if (strpos($favicon, '/') === 0) {
        return $favicon;
    }
    return DC_URL . ltrim($favicon, './');
}

function getVirtualCurrencyName($default = '积分') {
    $default = trim(strip_tags((string)$default));
    if ($default === '') {
        $default = '积分';
    }

    $name = '';
    try {
        $name = Option::get('virtual_currency_name');
    } catch (Throwable $e) {
        $name = '';
    }
    $name = trim(strip_tags((string)$name));
    if ($name === '') {
        $name = $default;
    }

    if (function_exists('mb_strlen') && mb_strlen($name, 'UTF-8') > 12) {
        $name = mb_substr($name, 0, 12, 'UTF-8');
    } elseif (!function_exists('mb_strlen') && strlen($name) > 36) {
        $name = substr($name, 0, 36);
    }
    return $name;
}

if (!function_exists('split')) {
    function split($str, $delimiter) {
        return preg_split($str, $delimiter);
    }
}

if (!function_exists('get_os')) {
    function get_os($user_agent) {
        if (false !== stripos($user_agent, "win")) {
            $os = 'Windows';
        } else if (false !== stripos($user_agent, "mac")) {
            $os = 'MAC';
        } else if (false !== stripos($user_agent, "linux")) {
            $os = 'Linux';
        } else if (false !== stripos($user_agent, "unix")) {
            $os = 'Unix';
        } else if (false !== stripos($user_agent, "bsd")) {
            $os = 'BSD';
        } else {
            $os = 'unknown';
        }
        return $os;
    }
}

if (!function_exists('get_browse')) {
    function get_browse($user_agent) {
        if (false !== stripos($user_agent, "MSIE")) {
            $br = 'MSIE';
        } else if (false !== stripos($user_agent, "Edg")) {
            $br = 'Edge';
        } else if (false !== stripos($user_agent, "Firefox")) {
            $br = 'Firefox';
        } else if (false !== stripos($user_agent, "Chrome")) {
            $br = 'Chrome';
        } else if (false !== stripos($user_agent, "Safari")) {
            $br = 'Safari';
        } else if (false !== stripos($user_agent, "Opera")) {
            $br = 'Opera';
        } else {
            $br = 'unknown';
        }
        return $br;
    }
}

// 获取内容中的第一张图片
if (!function_exists('getFirstImage')) {
    function getFirstImage($content) {
        // 匹配 Markdown 中的图片
        preg_match('/!\[.*?\]\((.*?)\)/', $content, $matches);

        if (!empty($matches[1])) {
            return $matches[1];
        }

        // 匹配 HTML 中的图片
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $imgNode = $xpath->query('//img')->item(0);

        if ($imgNode) {
            return $imgNode->getAttribute('src');
        }

        return null;
    }
}

// 检查PHP是否支持GD图形库
function checkGDSupport() {
    if (function_exists("gd_info") && function_exists('imagepng')) {
        return true;
    } else {
        return false;
    }
}

