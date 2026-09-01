<?php
/**
 * 现代化模板(default) - 模板配置文件
 * 
 * 配置分类：
 * 1. 主题配色 - 全站主题颜色配置
 * 2. 基础设置 - 浏览器图标等基础配置
 * 3. 首页设置 - 商品列表布局、分类显示等
 * 4. 商品详情页设置 - 详情页元素显示控制
 * 5. 头部设置 - 头部按钮显示控制
 * 6. 底部设置 - 底部信息显示控制
 * 7. 买家帮助页面 - 售后须知、客服链接等
 */

defined('DC_ROOT') || exit('access denied!');

if (!function_exists('frontTplSettingKey')) {
    function frontTplSettingKey($tpl = 'default') {
        return 'front_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$tpl);
    }
}

function plugin_setting_view() {
    $tpl = TplOptions::getInstance();
    $data = $tpl->getTemplateOptions(frontTplSettingKey('default'));
    $_default_banner_items = [
        ['img' => 'content/templates/default/img/Banner.png',  'url' => '', 'newtab' => 'y'],
        ['img' => 'content/templates/default/img/Banner1.png', 'url' => '', 'newtab' => 'y'],
        ['img' => 'content/templates/default/img/Banner2.png', 'url' => '', 'newtab' => 'y'],
    ];
    $_default_custom_category_icons = [
        ['ri' => 'ri-money-cny-circle-line', 'ri_color' => '#ff6600', 'name' => '充值中心', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-gamepad-line',          'ri_color' => '#2196F3', 'name' => '游戏点卡', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-vip-crown-line',        'ri_color' => '#ff9800', 'name' => '会员服务', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-key-2-line',            'ri_color' => '#4caf50', 'name' => '软件激活', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-movie-line',            'ri_color' => '#e91e63', 'name' => '影音会员', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-gift-line',             'ri_color' => '#9c27b0', 'name' => '礼品卡券', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-smartphone-line',       'ri_color' => '#00bcd4', 'name' => '数字商品', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-coupon-line',           'ri_color' => '#ff5722', 'name' => '优惠券',   'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-service-line',          'ri_color' => '#607d8b', 'name' => '生活服务', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-book-open-line',        'ri_color' => '#795548', 'name' => '在线教育', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-computer-line',         'ri_color' => '#3f51b5', 'name' => '办公软件', 'url' => '', 'img' => '', 'newtab' => 'n'],
        ['ri' => 'ri-apps-2-line',           'ri_color' => '#999999', 'name' => '更多分类', 'url' => '', 'img' => '', 'newtab' => 'n'],
    ];
    $_default_nav_items = [
        ['name' => '首页', 'url' => '/',     'ri' => 'ri-home-4-line',  'ri_color' => '', 'newtab' => 'n'],
        ['name' => '博客', 'url' => '/blog', 'ri' => 'ri-article-line', 'ri_color' => '', 'newtab' => 'n'],
    ];
    $_has_nav_items_config = array_key_exists('nav_items', $data);
    
    // ========== 主题配色 ==========
    $data['theme_primary'] = empty($data['theme_primary']) ? '#2196F3' : $data['theme_primary'];
    $data['theme_price'] = empty($data['theme_price']) ? '#ff6600' : $data['theme_price'];
    $data['theme_button'] = empty($data['theme_button']) ? '#2f69d9' : $data['theme_button'];
    $data['theme_accent'] = empty($data['theme_accent']) ? '#ff9800' : $data['theme_accent'];
    
    // ========== 基础设置 ==========
    $data['bg_video_url'] = array_key_exists('bg_video_url', $data) ? $data['bg_video_url'] : 'https://cloud.video.taobao.com/vod/S4hdTIy3b5jYVonIpXe0DtkJbbv6N4ImQLw89_diq9w.mp4';
    $data['bg_video_mobile_show'] = empty($data['bg_video_mobile_show']) ? 'n' : $data['bg_video_mobile_show'];
    // ========== 轮播图设置 ==========
    $data['banner_show']      = empty($data['banner_show'])      ? 'y'     : $data['banner_show'];
    $data['banner_items']     = array_key_exists('banner_items', $data) ? (array)$data['banner_items'] : $_default_banner_items;
    $data['banner_speed']     = empty($data['banner_speed'])     ? '3000'  : $data['banner_speed'];
    $data['banner_animation'] = empty($data['banner_animation']) ? 'slide' : $data['banner_animation'];
    $data['banner_height']    = empty($data['banner_height'])    ? '270'   : $data['banner_height'];
    // ========== 公告设置 ==========
    $data['roll_notice_mobile_show'] = empty($data['roll_notice_mobile_show']) ? 'n' : $data['roll_notice_mobile_show'];
    
    // ========== 首页设置 ==========
    $data['normal_stock_show'] = empty($data['normal_stock_show']) ? 'y' : $data['normal_stock_show'];
    $data['normal_sales_show'] = empty($data['normal_sales_show']) ? 'y' : $data['normal_sales_show'];
    $data['normal_des_show']   = empty($data['normal_des_show'])   ? 'y' : $data['normal_des_show'];
    $data['category_show'] = empty($data['category_show']) ? 'y' : $data['category_show'];
    $data['category_mobile_cols'] = empty($data['category_mobile_cols']) ? '5' : $data['category_mobile_cols'];
    $data['category_slide_mode'] = empty($data['category_slide_mode']) ? 'y' : $data['category_slide_mode'];
    $data['category_pc_cols'] = empty($data['category_pc_cols']) ? '0' : $data['category_pc_cols'];
    $data['custom_category_icons'] = array_key_exists('custom_category_icons', $data) ? (array)$data['custom_category_icons'] : $_default_custom_category_icons;
    $data['category_mobile_rows'] = empty($data['category_mobile_rows']) ? '2' : $data['category_mobile_rows'];
    $data['category_pc_slide_mode'] = empty($data['category_pc_slide_mode']) ? 'y' : $data['category_pc_slide_mode'];
    $data['goods_list_style'] = empty($data['goods_list_style']) ? 'card' : $data['goods_list_style'];
    $data['goods_list_columns'] = empty($data['goods_list_columns']) ? '5' : $data['goods_list_columns'];
    $data['goods_list_columns_pc'] = empty($data['goods_list_columns_pc']) ? $data['goods_list_columns'] : $data['goods_list_columns_pc'];
    $data['goods_list_layout'] = empty($data['goods_list_layout']) ? 'grid' : $data['goods_list_layout'];
    if ($data['goods_list_layout'] === 'list') {
        $data['goods_list_columns_pc'] = (string)min(3, max(2, (int)$data['goods_list_columns_pc']));
        $data['goods_list_columns_mobile'] = '1';
    } else {
        $data['goods_list_columns_pc'] = empty($data['goods_list_columns_pc']) ? '5' : $data['goods_list_columns_pc'];
        $data['goods_list_columns_mobile'] = '2';
    }
    $data['list_per_page'] = empty($data['list_per_page']) ? '12' : $data['list_per_page'];
    $data['single_page_mode'] = empty($data['single_page_mode']) ? 'n' : $data['single_page_mode'];
    $data['pay_type'] = empty($data['pay_type']) ? 2 : $data['pay_type'];
    // ========== 商品卡片扩展设置 ==========
    $data['card_soldout_show'] = empty($data['card_soldout_show']) ? 'y' : $data['card_soldout_show'];
    $data['card_type_show'] = empty($data['card_type_show']) ? 'y' : $data['card_type_show'];
    $data['card_buy_style'] = empty($data['card_buy_style']) ? 'icon_cart' : $data['card_buy_style'];
    $data['card_buy_text'] = isset($data['card_buy_text']) ? $data['card_buy_text'] : '购买';
    $data['normal_float_help_show'] = empty($data['normal_float_help_show']) ? 'y' : $data['normal_float_help_show'];
    $data['float_help_top'] = isset($data['float_help_top']) && $data['float_help_top'] !== '' ? (int)$data['float_help_top'] : 60;
    $data['float_help_icon'] = empty($data['float_help_icon']) ? '' : $data['float_help_icon'];
    
    // ========== 商品详情页设置 ==========
    $data['detail_cover_show'] = empty($data['detail_cover_show']) ? 'y' : $data['detail_cover_show'];
    $data['detail_title_show'] = empty($data['detail_title_show']) ? 'y' : $data['detail_title_show'];
    $data['detail_sales_show'] = empty($data['detail_sales_show']) ? 'y' : $data['detail_sales_show'];
    $data['detail_stock_show'] = empty($data['detail_stock_show']) ? 'y' : $data['detail_stock_show'];
    $data['detail_price_show'] = empty($data['detail_price_show']) ? 'y' : $data['detail_price_show'];
    
    // ========== 单页购买模式设置 ==========
    $data['single_sales_show'] = empty($data['single_sales_show']) ? 'y' : $data['single_sales_show'];
    $data['single_stock_show'] = empty($data['single_stock_show']) ? 'y' : $data['single_stock_show'];
    $data['single_price_show'] = empty($data['single_price_show']) ? 'y' : $data['single_price_show'];
    $data['single_float_help_show'] = empty($data['single_float_help_show']) ? 'y' : $data['single_float_help_show'];
    
    // ========== 商品详情页悬浮图标 ==========
    $data['detail_float_help_show'] = empty($data['detail_float_help_show']) ? 'y' : $data['detail_float_help_show'];
    
    // ========== 头部设置 ==========
    $data['header_menu_show'] = empty($data['header_menu_show']) ? 'y' : $data['header_menu_show'];
    $data['header_search_show'] = empty($data['header_search_show']) ? 'y' : $data['header_search_show'];
    $data['header_user_show'] = empty($data['header_user_show']) ? 'y' : $data['header_user_show'];
    $data['header_order_show'] = empty($data['header_order_show']) ? 'n' : $data['header_order_show'];
    $data['header_help_show'] = empty($data['header_help_show']) ? 'y' : $data['header_help_show'];
    $data['shop_header_bg'] = empty($data['shop_header_bg']) ? '' : $data['shop_header_bg'];
    $data['shop_title_color'] = empty($data['shop_title_color']) ? '' : $data['shop_title_color'];
    $data['shop_subtitle_color'] = empty($data['shop_subtitle_color']) ? '' : $data['shop_subtitle_color'];
    $data['shop_nav_active_color'] = empty($data['shop_nav_active_color']) ? '' : $data['shop_nav_active_color'];
    $data['shop_nav_active_bg'] = empty($data['shop_nav_active_bg']) ? '' : $data['shop_nav_active_bg'];
    
    // 顶部导航列表
    $data['nav_items'] = $_has_nav_items_config ? (array)$data['nav_items'] : $_default_nav_items;
    
    // ========== 头部设置（移动端） ==========
    // 默认仅显示：搜索、买家帮助（两个），菜单和个人中心默认关闭
    $data['mobile_menu_show'] = isset($data['mobile_menu_show']) && $data['mobile_menu_show'] !== '' ? $data['mobile_menu_show'] : 'n';
    $data['mobile_search_show'] = empty($data['mobile_search_show']) ? 'y' : $data['mobile_search_show'];
    $data['mobile_user_show'] = isset($data['mobile_user_show']) && $data['mobile_user_show'] !== '' ? $data['mobile_user_show'] : 'n';
    $data['mobile_help_show'] = empty($data['mobile_help_show']) ? 'y' : $data['mobile_help_show'];
    
    // ========== 底部设置 ==========
    $data['footer_show'] = empty($data['footer_show']) ? 'y' : $data['footer_show'];
    
    // ========== 买家帮助页面 ==========
    $data['service_qq'] = array_key_exists('service_qq', $data) ? $data['service_qq'] : '191955552';
    $data['service_wechat'] = array_key_exists('service_wechat', $data) ? $data['service_wechat'] : '191955552';
    $data['after_sale_notice'] = empty($data['after_sale_notice']) ? "1. 兑换码均为一次性商品，用码安装后自动绑定设备UDID，无法更改。\n2. 因联系信息泄露给他人、导致被他人查单提卡抢先安装的自己承担损失。\n3. 未使用的可联系客服补差升级或销码退款，已使用的不支持退款。" : $data['after_sale_notice'];
    $data['contact_presale_url'] = array_key_exists('contact_presale_url', $data) ? $data['contact_presale_url'] : 'https://dcshop.xzsc.cc/';
    $data['contact_aftersale_url'] = array_key_exists('contact_aftersale_url', $data) ? $data['contact_aftersale_url'] : 'https://dcshop.xzsc.cc/';
    
    // 常见问题默认数据
    $default_faq = [
        ['q' => '如何购买商品', 'a' => '选择商品 > 填写联系信息 > 确认付款 > 一键复制卡密。'],
        ['q' => '如何提取卡密', 'a' => '支付成功后，系统会自动跳转到订单详情页面，您可以直接复制卡密信息。如果页面关闭，可以通过"查询订单"功能，输入您的联系信息查询订单。'],
        ['q' => '如何查看订单号', 'a' => '点击页面顶部的"查询订单"按钮，输入您购买时填写的联系信息（手机号/邮箱等），即可查看所有相关订单。'],
        ['q' => '如何使用证书兑换码', 'a' => '购买成功后获取兑换码，前往对应平台的兑换页面，输入兑换码即可完成兑换。具体兑换方式请参考商品详情说明。'],
        ['q' => '如何使用越狱工具定制码', 'a' => '购买定制码后，按照商品详情中的教程进行操作。如有疑问，请联系客服获取帮助。'],
        ['q' => '红包转账外币支付请联系客服', 'a' => '如需使用红包、转账或外币支付，请先联系客服确认支付方式和金额，避免支付错误导致的损失。'],
    ];
    $data['faq_list'] = empty($data['faq_list']) ? $default_faq : $data['faq_list'];
    // 系统分类数据（供JS一键同步按钮使用）
    $_db_inst = Database::getInstance();
    $_url_to_relative = function($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (strpos($url, DC_URL) === 0) {
            $_path = (string)parse_url($url, PHP_URL_PATH);
            $_query = (string)parse_url($url, PHP_URL_QUERY);
            $_fragment = (string)parse_url($url, PHP_URL_FRAGMENT);
            $url = $_path !== '' ? $_path : '/';
            if ($_query !== '') {
                $url .= '?' . $_query;
            }
            if ($_fragment !== '') {
                $url .= '#' . $_fragment;
            }
        }
        if ($url !== '' && !preg_match('#^(https?:)?//#i', $url) && strpos($url, 'javascript:') !== 0 && $url[0] !== '/') {
            $url = '/' . ltrim($url, '/');
        }
        return $url === '' ? '/' : $url;
    };
    $_sys_sorts_raw = $_db_inst->fetch_all("SELECT sid, sortname, sortimg, sorticon FROM " . DB_PREFIX . "sort WHERE type='goods' ORDER BY taxis DESC, sid ASC") ?: [];
    $_sort_json = json_encode(array_map(function($s) {
        return ['sid' => (int)$s['sid'], 'name' => htmlspecialchars($s['sortname']), 'img' => $s['sortimg'] ?: '', 'ri' => $s['sorticon'] ?: '', 'url' => '/?sort_id=' . (int)$s['sid']];
    }, $_sys_sorts_raw));
    $_goods_sort_rows = $_db_inst->fetch_all("SELECT sid, pid, sortname FROM " . DB_PREFIX . "sort WHERE type='goods' ORDER BY pid ASC, taxis DESC, sid ASC") ?: [];
    $_goods_sort_name_map = [];
    foreach ($_goods_sort_rows as $_row) {
        $_goods_sort_name_map[(int)$_row['sid']] = htmlspecialchars_decode((string)$_row['sortname'], ENT_QUOTES);
    }
    $_nav_goods_sort_items = [];
    foreach ($_goods_sort_rows as $_row) {
        $_sid = (int)$_row['sid'];
        $_pid = (int)$_row['pid'];
        $_name = htmlspecialchars_decode((string)$_row['sortname'], ENT_QUOTES);
        $_label = $_name;
        if ($_pid > 0 && !empty($_goods_sort_name_map[$_pid])) {
            $_label = $_goods_sort_name_map[$_pid] . ' / ' . $_name;
        }
        $_nav_goods_sort_items[] = [
            'name' => $_name,
            'label' => $_label,
            'url' => $_url_to_relative(Url::sort($_sid)),
        ];
    }
    $_blog_sort_rows = $_db_inst->fetch_all("SELECT sid, pid, sortname FROM " . DB_PREFIX . "sort WHERE type='blog' ORDER BY pid ASC, taxis DESC, sid ASC") ?: [];
    $_blog_sort_name_map = [];
    foreach ($_blog_sort_rows as $_row) {
        $_blog_sort_name_map[(int)$_row['sid']] = htmlspecialchars_decode((string)$_row['sortname'], ENT_QUOTES);
    }
    $_nav_blog_sort_items = [];
    foreach ($_blog_sort_rows as $_row) {
        $_sid = (int)$_row['sid'];
        $_pid = (int)$_row['pid'];
        $_name = htmlspecialchars_decode((string)$_row['sortname'], ENT_QUOTES);
        $_label = $_name;
        if ($_pid > 0 && !empty($_blog_sort_name_map[$_pid])) {
            $_label = $_blog_sort_name_map[$_pid] . ' / ' . $_name;
        }
        $_nav_blog_sort_items[] = [
            'name' => $_name,
            'label' => $_label,
            'url' => $_url_to_relative(Url::blogSort($_sid)),
        ];
    }
    $_article_rows = $_db_inst->fetch_all("SELECT gid, title, sortid FROM " . DB_PREFIX . "blog WHERE type='blog' AND hide='n' AND checked='y' ORDER BY top DESC, sortop DESC, date DESC LIMIT 300") ?: [];
    $_nav_article_items = [];
    foreach ($_article_rows as $_row) {
        $_gid = (int)$_row['gid'];
        $_name = htmlspecialchars_decode((string)$_row['title'], ENT_QUOTES);
        $_label = $_name;
        $_sortid = isset($_row['sortid']) ? (int)$_row['sortid'] : 0;
        if ($_sortid > 0 && !empty($_blog_sort_name_map[$_sortid])) {
            $_label .= ' · ' . $_blog_sort_name_map[$_sortid];
        }
        $_nav_article_items[] = [
            'name' => $_name,
            'label' => $_label,
            'url' => $_url_to_relative(Url::log($_gid)),
        ];
    }
    $_goods_rows = $_db_inst->fetch_all("SELECT id, title, sort_id FROM " . DB_PREFIX . "goods WHERE delete_time IS NULL AND is_on_shelf = 1 ORDER BY id DESC LIMIT 300") ?: [];
    $_nav_goods_items = [];
    foreach ($_goods_rows as $_row) {
        $_gid = (int)$_row['id'];
        $_name = htmlspecialchars_decode((string)$_row['title'], ENT_QUOTES);
        $_label = $_name;
        $_sortid = isset($_row['sort_id']) ? (int)$_row['sort_id'] : 0;
        if ($_sortid > 0 && !empty($_goods_sort_name_map[$_sortid])) {
            $_label .= ' · ' . $_goods_sort_name_map[$_sortid];
        }
        $_nav_goods_items[] = [
            'name' => $_name,
            'label' => $_label,
            'url' => $_url_to_relative(Url::goods($_gid)),
        ];
    }
    $_page_rows = $_db_inst->fetch_all("SELECT gid, title FROM " . DB_PREFIX . "blog WHERE type='page' ORDER BY date DESC") ?: [];
    $_nav_page_items = [];
    foreach ($_page_rows as $_row) {
        $_gid = (int)$_row['gid'];
        $_name = htmlspecialchars_decode((string)$_row['title'], ENT_QUOTES);
        $_nav_page_items[] = [
            'name' => $_name,
            'label' => $_name,
            'url' => $_url_to_relative(Url::art($_gid)),
        ];
    }
    $_nav_quick_pick_json = json_encode([
        'goods_sort' => $_nav_goods_sort_items,
        'blog_sort' => $_nav_blog_sort_items,
        'article' => $_nav_article_items,
        'goods' => $_nav_goods_items,
        'page' => $_nav_page_items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $_legacy_nav_items = [];
    $Navi_Model = new Navi_Model();
    foreach ($Navi_Model->getNavis() as $nav) {
        if (!empty($nav['pid']) || (!empty($nav['hide']) && $nav['hide'] === 'y')) {
            continue;
        }
        $_legacy_nav_items[] = [
            'name' => htmlspecialchars_decode($nav['naviname'], ENT_QUOTES),
            'url' => $_url_to_relative(htmlspecialchars_decode($nav['url'], ENT_QUOTES)),
            'ri' => empty($nav['naviicon']) ? '' : $nav['naviicon'],
            'ri_color' => '',
            'newtab' => empty($nav['newtab']) ? 'n' : $nav['newtab'],
        ];
    }
    if (!$_has_nav_items_config && empty($data['nav_items'])) {
        $data['nav_items'] = !empty($_legacy_nav_items)
            ? $_legacy_nav_items
            : [['name' => '首页', 'url' => '/', 'newtab' => 'n', 'ri' => 'ri-home-line', 'ri_color' => '']];
    }
    $_legacy_nav_json = json_encode($_legacy_nav_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>
    <style>
        #form-btn {
            background: #eee;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            line-height: 50px;
            margin: 0 auto;
            text-align: center;
            z-index: 100;
        }
        #open-box {
            padding-bottom: 60px;
        }
        .section-title {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed #e6e6e6;
        }
        .section-title:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }
        .section-title .layui-form-label {
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }
        .switch-inline-item {
            display: inline-flex;
            align-items: center;
            margin-right: 25px;
            margin-bottom: 10px;
        }
        .switch-inline-item .switch-label {
            margin-right: 8px;
            color: #666;
            font-size: 13px;
        }
        /* FAQ配置样式 */
        .faq-config-list {
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            padding: 10px;
            background: #fafafa;
        }
        .faq-config-item {
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 10px;
            position: relative;
        }
        .faq-config-item:last-child {
            margin-bottom: 0;
        }
        .faq-config-item .faq-num {
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            background: #1e9fff;
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 12px;
            text-align: center;
            line-height: 20px;
        }
        .faq-config-item .faq-inputs {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-left: 15px;
        }
        .faq-config-item .faq-q {
            width: 200px;
            flex-shrink: 0;
        }
        .faq-config-item .faq-a {
            flex: 1;
        }
        .faq-config-item .faq-del {
            flex-shrink: 0;
            cursor: pointer;
            color: #ff5722;
            font-size: 18px;
            padding: 5px;
            line-height: 1;
        }
        .faq-config-item .faq-del:hover {
            color: #d32f2f;
        }
        .faq-add-btn {
            margin-top: 10px;
            cursor: pointer;
            color: #1e9fff;
            font-size: 14px;
        }
        .faq-add-btn:hover {
            color: #0c8de4;
        }
        .faq-add-btn i {
            margin-right: 5px;
        }
        /* 自定义分类图标配置样式 */
        .ci-config-list {
            border: 1px solid #e6e6e6;
            border-radius: 10px;
            padding: 12px;
            background: #fafcff;
        }
        .ci-config-item {
            display: flex;
            align-items: stretch;
            gap: 10px;
            background: #fff;
            border: 1px solid #e8eef5;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            transition: box-shadow .2s ease, border-color .2s ease, opacity .2s ease;
        }
        .ci-config-item:last-child { margin-bottom: 0; }
        .ci-config-item:hover {
            border-color: rgba(30, 159, 255, 0.28);
            box-shadow: 0 8px 22px rgba(30, 159, 255, 0.08);
        }
        .ci-config-item .ci-num {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            margin-top: 7px;
            background: #1E9FFF;
            color: #fff;
            border-radius: 50%;
            font-size: 12px;
            text-align: center;
            line-height: 22px;
        }
        .ci-config-item .ci-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
            flex-wrap: wrap;
        }
        .ci-config-item .ci-img-wrap { flex: 2; min-width: 160px; display: flex; gap: 4px; align-items: center; }
        .ci-config-item .ci-img-wrap .ci-img { flex: 1; min-width: 0; }
        .ci-config-item .ci-img-wrap .ci-upload-btn { flex-shrink: 0; white-space: nowrap; padding: 0 8px; height: 36px; line-height: 36px; }
        .ci-config-item .ci-name { flex: 1; min-width: 70px; }
        .ci-config-item .ci-url { flex: 2; min-width: 140px; }
        .ci-config-item .ci-url-wrap { flex: 2.6; min-width: 260px; display: flex; gap: 6px; align-items: center; }
        .ci-config-item .ci-url-wrap .ci-url { flex: 1; min-width: 0; }
        .ci-config-item .ci-ri-wrap { display:flex; align-items:center; flex-shrink:0; }
        .ci-nav-picker-btn { flex-shrink: 0; white-space: nowrap; height: 36px; line-height: 36px; padding: 0 10px; }
        .ci-ri-btn { width:38px; height:38px; border:1.5px dashed #ddd; border-radius:8px; background:#fafafa; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.2s; padding:0; }
        .ci-ri-btn:hover { border-color:var(--theme-primary,#2196F3); background:rgba(33,150,243,0.06); }
        .ci-ri-btn.ci-has-icon { border-style:solid; border-color:#d0d0d0; background:#fff; }
        .ci-ri-btn i { font-size:20px; color:var(--theme-primary,#2196F3); }
        .ci-ri-btn i.ci-ri-ph { font-size:16px; color:#ccc; }
        .ci-config-item .ci-del {
            flex-shrink: 0;
            cursor: pointer;
            color: #ff5722;
            font-size: 18px;
            padding: 4px;
            line-height: 36px;
        }
        .ci-config-item .ci-del:hover { color: #d32f2f; }
        /* 拖拽手柄 */
        .ci-drag { flex-shrink: 0; cursor: grab; color: #c0cada; font-size: 18px; line-height: 36px; user-select: none; }
        .ci-drag:hover { color: #1E9FFF; }
        .ci-config-item.ci-dragging { opacity: 0.35; box-shadow: none; }
        .ci-config-item.ci-drag-over { border: 2px dashed #5fb878 !important; background: #f4fff6 !important; }
        /* Tab内容区 */
        .tab-inner { padding: 10px 0 20px; }
        .tab-inner .layui-form-item { margin-bottom: 18px; }
        .ci-action-bar { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .group-label { font-weight: 600; color: #555; font-size: 13px; padding: 10px 0 6px; border-bottom: 1px solid #f0f0f0; margin-bottom: 14px; }
        .setting-status-box { margin: 0 0 16px; padding: 12px 14px; border-radius: 8px; border: 1px solid rgba(33,150,243,0.18); background: rgba(33,150,243,0.06); color: #1f5fae; font-size: 13px; line-height: 1.7; }
        .setting-status-box.is-off { border-color: rgba(255,152,0,0.28); background: rgba(255,152,0,0.08); color: #9a6200; }
        .dependent-setting-panel { transition: opacity .2s ease, filter .2s ease; }
        .dependent-setting-panel.is-disabled { opacity: 0.48; filter: grayscale(0.08); }
        .dependent-setting-panel.is-disabled .group-label { color: #888; }
        /* 导航快捷选择弹窗样式增强 */
        .nav-quick-panel { padding: 16px; background: #fff; }
        .nav-quick-tip { font-size: 13px; color: #888; margin-bottom: 15px; line-height: 1.6; display: flex; align-items: center; gap: 6px; }
        .nav-quick-tip i { color: #1E9FFF; font-size: 16px; }
        .nav-quick-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; }
        .nav-quick-tab { display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 16px; border-radius: 17px; background: #f5f7fa; color: #555; cursor: pointer; transition: all .25s; font-size: 13px; font-weight: 500; border: 1px solid transparent; }
        .nav-quick-tab:hover { background: #e6f1fc; color: #1E9FFF; }
        .nav-quick-tab.active { background: #1E9FFF; color: #fff; box-shadow: 0 3px 8px rgba(30,159,255,0.3); }
        .nav-quick-search { margin-bottom: 16px; position: relative; }
        .nav-quick-search input { border-radius: 8px; padding-left: 36px; border-color: #e0e0e0; transition: all .3s; height: 38px; }
        .nav-quick-search i.ri-search-line { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 16px; pointer-events: none; }
        .nav-quick-list { max-height: 340px; overflow-y: auto; border-radius: 8px; padding: 4px; display: grid; gap: 10px; grid-template-columns: 1fr; }
        /* 滚动条美化 */
        .nav-quick-list::-webkit-scrollbar { width: 6px; }
        .nav-quick-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }
        .nav-quick-list::-webkit-scrollbar-track { background: transparent; }
        .nav-quick-item { background: #fff; border: 1px solid #ebeef5; border-radius: 8px; padding: 12px 16px; padding-right: 40px; cursor: pointer; transition: all .25s; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center; }
        .nav-quick-item:hover { border-color: #1E9FFF; box-shadow: 0 4px 12px rgba(30,159,255,0.12); transform: translateY(-2px); }
        .nav-quick-item-icon { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); font-size: 20px; color: #1E9FFF; opacity: 0; transition: all .25s; margin-right: -10px; }
        .nav-quick-item:hover .nav-quick-item-icon { opacity: 1; margin-right: 0; }
        .nav-quick-item:hover .nav-quick-item-title { color: #1E9FFF; }
        .nav-quick-item-title { font-size: 14px; color: #333; line-height: 1.5; font-weight: 500; transition: color .2s; }
        .nav-quick-item-meta { font-size: 12px; color: #999; margin-top: 6px; word-break: break-all; display: flex; align-items: center; gap: 4px; }
        .nav-quick-empty { text-align: center; color: #999; padding: 40px 10px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .nav-quick-empty i { font-size: 48px; color: #ddd; }
        /* 轮播图列表 */
        /* 轮播图列表 — 与自定义图标列表 ci-config-item 保持相同风格 */
        .bn-full-item { display: block !important; }
        .bn-full-label { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; }
        .bn-config-list { background: #f2f2f2; border-radius: 4px; padding: 10px; margin-bottom: 10px; }
        .bn-config-item { background: #fff; border: 1px solid #e6e6e6; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; position: relative; }
        .bn-config-item:last-child { margin-bottom: 0; }
        .bn-num { position: absolute; left: -8px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; background: #5fb878; color: #fff; border-radius: 50%; font-size: 11px; text-align: center; line-height: 20px; }
        .bn-tip { font-size: 12px; color: #999; margin-bottom: 10px; }
        .bn-inputs { display: flex; gap: 8px; align-items: center; margin-left: 15px; flex-wrap: nowrap; overflow: hidden; }
        .bn-img-cell { display: flex; gap: 4px; align-items: center; flex: 3; min-width: 0; overflow: hidden; }
        .bn-img-preview { width: 64px; height: 40px; border-radius: 4px; border: 1px solid #e6e6e6; background: #f0f0f0 center/cover no-repeat; flex-shrink: 0; }
        .bn-img-input { flex: 1; min-width: 0; }
        .bn-upload-btn { flex-shrink: 0; white-space: nowrap; padding: 0 8px !important; height: 36px !important; line-height: 36px !important; }
        .bn-url-wrap { flex: 2; min-width: 140px; display: flex; gap: 6px; align-items: center; }
        .bn-url { flex: 1; min-width: 0; }
        .bn-nav-picker-btn { flex-shrink: 0; white-space: nowrap; height: 36px; line-height: 36px; padding: 0 10px; }
        .bn-del { flex-shrink: 0; cursor: pointer; color: #ff5722; font-size: 18px; padding: 4px; line-height: 1; }
        .bn-del:hover { color: #d32f2f; }
        /* Tab 高亮 */
        .layui-tab-title li.layui-this { color: var(--theme-primary,#2196F3) !important; font-weight: 600; border-bottom: 2px solid var(--theme-primary,#2196F3) !important; }
        .layui-tab-title li:hover { color: var(--theme-primary,#2196F3) !important; }
        /* 主题配色预设 */
        .theme-presets {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .theme-preset {
            width: 80px;
            height: 50px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .theme-preset:hover {
            transform: scale(1.05);
        }
        .theme-preset.active {
            border-color: #333;
        }
        .theme-preset::after {
            content: attr(data-name);
            position: absolute;
            bottom: 2px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        .color-input-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .color-input-group input[type="text"] {
            flex: 1;
        }
        .color-input-group input[type="color"] {
            width: 38px;
            height: 38px;
            padding: 0;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .ci-config-item {
                flex-direction: column;
                gap: 8px;
            }
            .ci-config-item .ci-num {
                margin-top: 0;
            }
            .ci-config-item .ci-inputs {
                width: 100%;
                min-width: 0;
            }
            .ci-config-item .ci-img-wrap,
            .ci-config-item .ci-url-wrap,
            .ci-config-item .ci-name,
            .ci-config-item .ci-url {
                width: 100%;
                min-width: 0;
            }
            .ci-config-item .ci-url-wrap {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    <form class="layui-form" id="form" method="post" action="?tpl=default&action=setting_ajax">
    <input type="hidden" name="token" value="<?= htmlspecialchars(LoginAuth::genToken(), ENT_QUOTES) ?>">
    <input type="file" id="ci-file-global" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" style="display:none">
    <div style="padding:15px 20px 80px;" id="open-box">
    <div class="layui-tab" lay-filter="setting-tab">
        <ul class="layui-tab-title">
            <li class="layui-this"><i class="ri-palette-line"></i> 外观配色</li>
            <li><i class="ri-image-line"></i> 轮播图/公告</li>
            <li><i class="ri-home-line"></i> 首页·分类</li>
            <li><i class="ri-shopping-bag-line"></i> 首页·商品</li>
            <li><i class="ri-layout-grid-line"></i> 单页购买模式</li>
            <li><i class="ri-shopping-cart-line"></i> 商品详情</li>
            <li><i class="ri-layout-top-line"></i> 头部/底部</li>
            <li><i class="ri-customer-service-line"></i> 买家帮助</li>
        </ul>
        <div class="layui-tab-content">

            <!-- ===== TAB 1: 外观配色 ===== -->
            <div class="layui-tab-item layui-show"><div class="tab-inner">
                <div class="group-label">主题配色</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">预设主题</label>
                    <div class="layui-input-block">
                        <div class="theme-presets">
                            <div class="theme-preset" data-name="默认蓝" data-primary="#2196F3" data-price="#ff6600" data-button="#2f69d9" data-accent="#ff9800" data-header="#0c6be1" data-navcolor="#E1F5FE" style="background:linear-gradient(#0c6be1 0 14px, transparent 14px), linear-gradient(135deg,#2196F3 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="科技紫" data-primary="#7c4dff" data-price="#ff5722" data-button="#651fff" data-accent="#ff9100" data-header="#651fff" data-navcolor="#ff5722" style="background:linear-gradient(#651fff 0 14px, transparent 14px), linear-gradient(135deg,#7c4dff 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="活力橙" data-primary="#ff9800" data-price="#e91e63" data-button="#f57c00" data-accent="#e91e63" data-header="#f57c00" data-navcolor="#ffffff" style="background:linear-gradient(#f57c00 0 14px, transparent 14px), linear-gradient(135deg,#ff9800 50%,#e91e63 50%);"></div>
                            <div class="theme-preset" data-name="清新绿" data-primary="#4caf50" data-price="#ff5722" data-button="#388e3c" data-accent="#ff9800" data-header="#388e3c" data-navcolor="#ffd740" style="background:linear-gradient(#388e3c 0 14px, transparent 14px), linear-gradient(135deg,#4caf50 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="商务灰" data-primary="#607d8b" data-price="#ff6600" data-button="#455a64" data-accent="#ff9800" data-header="#455a64" data-navcolor="#ff6600" style="background:linear-gradient(#455a64 0 14px, transparent 14px), linear-gradient(135deg,#607d8b 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="玫瑰红" data-primary="#e91e63" data-price="#ff6600" data-button="#c2185b" data-accent="#ff9800" data-header="#c2185b" data-navcolor="#ffd740" style="background:linear-gradient(#c2185b 0 14px, transparent 14px), linear-gradient(135deg,#e91e63 50%,#ff6600 50%);"></div>
                            <div class="theme-preset" data-name="青蓝" data-primary="#00bcd4" data-price="#ff5722" data-button="#0097a7" data-accent="#ffc107" data-header="#0097a7" data-navcolor="#ffc107" style="background:linear-gradient(#0097a7 0 14px, transparent 14px), linear-gradient(135deg,#00bcd4 50%,#ff5722 50%);"></div>
                            <div class="theme-preset" data-name="黑金" data-primary="#d4af37" data-price="#e5c158" data-button="#262626" data-accent="#ff4757" data-header="#262626" data-navcolor="#d4af37" style="background:linear-gradient(#262626 0 14px, transparent 14px), linear-gradient(135deg,#262626 50%,#d4af37 50%);"></div>
                        </div>
                        <div class="layui-form-mid layui-text-em">点击预设快速配色，或下方自定义</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">主题色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group" style="max-width:250px;">
                            <input type="text" name="theme_primary" id="theme_primary" value="<?= htmlspecialchars($data['theme_primary']) ?>" class="layui-input" placeholder="#2196F3">
                            <input type="color" id="theme_primary_picker" value="<?= $data['theme_primary'] ?>">
                        </div>
                        <div class="layui-form-mid layui-text-em">选中状态、链接、图标等主要颜色</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">价格色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group" style="max-width:250px;">
                            <input type="text" name="theme_price" id="theme_price" value="<?= htmlspecialchars($data['theme_price']) ?>" class="layui-input" placeholder="#ff6600">
                            <input type="color" id="theme_price_picker" value="<?= $data['theme_price'] ?>">
                        </div>
                        <div class="layui-form-mid layui-text-em">商品价格、金额显示颜色</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">按钮色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group" style="max-width:250px;">
                            <input type="text" name="theme_button" id="theme_button" value="<?= htmlspecialchars($data['theme_button']) ?>" class="layui-input" placeholder="#2f69d9">
                            <input type="color" id="theme_button_picker" value="<?= $data['theme_button'] ?>">
                        </div>
                        <div class="layui-form-mid layui-text-em">确认付款、提交等主要按钮颜色</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">强调色</label>
                    <div class="layui-input-block">
                        <div class="color-input-group" style="max-width:250px;">
                            <input type="text" name="theme_accent" id="theme_accent" value="<?= htmlspecialchars($data['theme_accent']) ?>" class="layui-input" placeholder="#ff9800">
                            <input type="color" id="theme_accent_picker" value="<?= $data['theme_accent'] ?>">
                        </div>
                        <div class="layui-form-mid layui-text-em">优惠券、警告提示等强调颜色</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:24px;">基础资源</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">视频背景地址</label>
                    <div class="layui-input-block">
                        <input type="text" value="<?= htmlspecialchars($data['bg_video_url']) ?>" placeholder="留空则不启用视频背景，填入可直接访问的 MP4 视频地址" class="layui-input" name="bg_video_url">
                        <div class="layui-form-mid layui-text-em">支持可直接访问的 MP4 链接；桌面端默认启用，移动端是否启用由下方开关控制。注意：手机浏览器一旦启用视频背景，仍可能发生预加载、嗅探或自动播放，这属于浏览器机制，无法彻底避免</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">手机端视频背景</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['bg_video_mobile_show'] == 'y' ? 'checked' : '' ?> name="bg_video_mobile_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">关闭时：手机端始终不显示背景视频；开启时：手机端也会尝试显示背景视频，是否自动播放取决于浏览器策略</div>
                    </div>
                </div>

            </div></div>

            <!-- ===== TAB 2: 轮播图/公告 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">首页轮播</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">启用轮播图</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['banner_show'] == 'y' ? 'checked' : '' ?> name="banner_show" lay-skin="switch" lay-filter="banner_show_switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">开启后在首页顶部显示轮播图，与滚动公告在同一行展示（PC：左图右公告；手机：上下排列）</div>
                    </div>
                </div>

                <div class="setting-status-box" id="banner_show_status"></div>

                <div class="dependent-setting-panel" id="banner_show_panel">

                <div class="layui-form-item bn-full-item">
                    <div class="bn-full-label">轮播图列表</div>
                    <div class="bn-tip">建议尺寸：PC端 <strong>1200×400 px</strong>，移动端 <strong>750×280 px</strong>；支持 JPG/PNG/WebP/GIF</div>
                    <div class="bn-config-list" id="bn-list">
                    <?php foreach ($data['banner_items'] as $i => $bn): ?>
                    <div class="bn-config-item">
                        <span class="bn-num"><?= $i + 1 ?></span>
                        <div class="bn-inputs">
                            <div class="bn-img-cell">
                                <div class="bn-img-preview" style="background-image:url('<?= htmlspecialchars($bn['img'] ?? '') ?>')"></div>
                                <input type="text" name="bn_img[]" class="bn-img-input layui-input" value="<?= htmlspecialchars($bn['img'] ?? '') ?>" placeholder="图片地址（上传后自动填入）">
                                <button type="button" class="layui-btn layui-btn-xs layui-btn-warm bn-upload-btn"><i class="ri-upload-line"></i> 上传</button>
                            </div>
                            <div class="bn-url-wrap">
                                <input type="text" name="bn_url[]" value="<?= htmlspecialchars($bn['url'] ?? '') ?>" placeholder="点击跳转链接（选填）" class="layui-input bn-url">
                                <button type="button" class="layui-btn layui-btn-primary bn-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">
                                <input type="checkbox" lay-skin="primary" lay-filter="bn_newtab_dummy" title="新窗口" <?= ($bn['newtab'] ?? 'y') == 'y' ? 'checked' : '' ?>>
                                <input type="hidden" name="bn_newtab[]" value="<?= htmlspecialchars($bn['newtab'] ?? 'y') ?>" class="bn-newtab-val">
                            </div>
                            <span class="bn-del" onclick="removeBn(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="faq-add-btn" onclick="addBn()" style="margin-top:8px;"><i class="layui-icon layui-icon-add-circle"></i>添加一张</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">轮播高度</label>
                    <div class="layui-input-block">
                        <div style="display:flex;align-items:center;gap:8px;max-width:240px;">
                            <input type="number" name="banner_height" value="<?= htmlspecialchars($data['banner_height']) ?>" min="120" max="600" class="layui-input">
                            <span style="color:#999;font-size:13px;white-space:nowrap;">px</span>
                        </div>
                        <div class="layui-form-mid layui-text-em">PC端轮播图高度，默认 300px，建议 200~500px</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">切换速度</label>
                    <div class="layui-input-block">
                        <div style="display:flex;align-items:center;gap:8px;max-width:240px;">
                            <input type="number" name="banner_speed" value="<?= htmlspecialchars($data['banner_speed']) ?>" min="500" max="10000" step="500" class="layui-input">
                            <span style="color:#999;font-size:13px;white-space:nowrap;">毫秒</span>
                        </div>
                        <div class="layui-form-mid layui-text-em">每张图片停留的时间，默认 3000ms（3秒），建议 2000~6000ms</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">切换动画</label>
                    <div class="layui-input-block">
                        <input type="radio" name="banner_animation" value="slide" <?= $data['banner_animation'] == 'slide' ? 'checked' : '' ?> title="横向滑动">
                        <input type="radio" name="banner_animation" value="fade"  <?= $data['banner_animation'] == 'fade'  ? 'checked' : '' ?> title="淡入淡出">
                        <div class="layui-form-mid layui-text-em">横向滑动：图片左右过渡；淡入淡出：透明度渐变过渡</div>
                    </div>
                </div>

                </div>

                <div class="group-label" style="margin-top:24px;">网站公告</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">手机端是否显示</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['roll_notice_mobile_show'] == 'y' ? 'checked' : '' ?> name="roll_notice_mobile_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">控制的是 Hero 区域右侧的<strong>网站公告</strong>（notice-card），关闭后手机访问时隐藏该公告，PC端不受影响。公告内容在后台「系统设置 → 公告管理」中编辑</div>
                    </div>
                </div>

            </div></div>

            <!-- ===== TAB 3: 首页·分类 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">分类展示</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">显示分类模块</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['category_show'] == 'y' ? 'checked' : '' ?> name="category_show" lay-skin="switch" lay-filter="category_show_switch" value="y" title=" ON |OFF ">
                    </div>
                </div>

                <div class="setting-status-box" id="category_show_status"></div>

                <div class="dependent-setting-panel" id="category_show_panel">

                <div class="layui-form-item">
                    <label class="layui-form-label">手机每行图标数</label>
                    <div class="layui-input-block">
                        <input type="radio" name="category_mobile_cols" value="3" <?= $data['category_mobile_cols'] == '3' ? 'checked' : '' ?> title="3个/行">
                        <input type="radio" name="category_mobile_cols" value="4" <?= $data['category_mobile_cols'] == '4' ? 'checked' : '' ?> title="4个/行">
                        <input type="radio" name="category_mobile_cols" value="5" <?= $data['category_mobile_cols'] == '5' ? 'checked' : '' ?> title="5个/行">
                        <div class="layui-form-mid layui-text-em">手机端分类图标每行排列几个，数量越多图标越小</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">手机每屏显示行数</label>
                    <div class="layui-input-block">
                        <input type="radio" name="category_mobile_rows" value="0" <?= $data['category_mobile_rows'] == '0' ? 'checked' : '' ?> title="不限（全部显示）">
                        <input type="radio" name="category_mobile_rows" value="1" <?= $data['category_mobile_rows'] == '1' ? 'checked' : '' ?> title="1行">
                        <input type="radio" name="category_mobile_rows" value="2" <?= $data['category_mobile_rows'] == '2' ? 'checked' : '' ?> title="2行">
                        <input type="radio" name="category_mobile_rows" value="3" <?= $data['category_mobile_rows'] == '3' ? 'checked' : '' ?> title="3行">
                        <div class="layui-form-mid layui-text-em">手机端每屏最多显示几行；图标超出后自动分页，左右滑动切换。设为"不限"则全部显示不分页（需同时开启滑动模式才生效）</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">PC端每行列数</label>
                    <div class="layui-input-block">
                        <input type="radio" name="category_pc_cols" value="0" <?= $data['category_pc_cols'] == '0' ? 'checked' : '' ?> title="自动适应">
                        <input type="radio" name="category_pc_cols" value="4" <?= $data['category_pc_cols'] == '4' ? 'checked' : '' ?> title="4列">
                        <input type="radio" name="category_pc_cols" value="5" <?= $data['category_pc_cols'] == '5' ? 'checked' : '' ?> title="5列">
                        <input type="radio" name="category_pc_cols" value="6" <?= $data['category_pc_cols'] == '6' ? 'checked' : '' ?> title="6列">
                        <input type="radio" name="category_pc_cols" value="8" <?= $data['category_pc_cols'] == '8' ? 'checked' : '' ?> title="8列">
                        <div class="layui-form-mid layui-text-em">电脑端每行显示几个分类图标（仅关闭滑动模式时有效）；开启滑动模式时电脑端通过两侧箭头或鼠标滚轮翻页</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">手机横向滑动</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['category_slide_mode'] == 'y' ? 'checked' : '' ?> name="category_slide_mode" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">开启：手机端分类图标横向排成一排可左右滑动（配合"每屏行数"可分页）；关闭：按网格多行排列</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">PC端横向滑动</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['category_pc_slide_mode'] == 'y' ? 'checked' : '' ?> name="category_pc_slide_mode" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">开启：电脑端分类图标横向排成一排，两侧显示翻页箭头，鼠标滚轮也可横向滚动；关闭：按"PC每行列数"以网格排列</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">自定义图标列表</label>
                    <div class="layui-input-block">
                        <div class="ci-action-bar">
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="addCi()"><i class="layui-icon layui-icon-add-circle"></i> 新增一行</button>
                            <button type="button" class="layui-btn layui-btn-sm" onclick="syncSysCategories()" style="background:#5fb878;border-color:#5fb878;color:#fff;"><i class="ri-refresh-line"></i> 从系统分类同步</button>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" onclick="clearAllCi()"><i class="ri-delete-bin-line"></i> 清空列表</button>
                        </div>
                        <div class="ci-config-list" id="ci-list">
                            <?php foreach ($data['custom_category_icons'] as $i => $ci): ?>
                            <div class="ci-config-item">
                                <span class="ci-drag" title="拖拽排序">⠿</span>
                                <span class="ci-num"><?= $i + 1 ?></span>
                                <div class="ci-inputs">
                                    <div class="ci-img-wrap">
                                        <input type="text" name="ci_img[]" value="<?= htmlspecialchars($ci['img'] ?? '') ?>" placeholder="图标图片URL" class="layui-input ci-img">
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-warm ci-upload-btn" onclick="ciUpload(this)">上传</button>
                                    </div>
                                    <div class="ci-ri-wrap">
                                        <button type="button" class="ci-ri-btn <?= !empty($ci['ri']) ? 'ci-has-icon' : '' ?>" onclick="ciPickRi(this)" title="点击选择 Remix 图标">
                                            <i class="<?= !empty($ci['ri']) ? htmlspecialchars($ci['ri']) : 'ri-add-line ci-ri-ph' ?>"<?= (!empty($ci['ri']) && !empty($ci['ri_color'])) ? ' style="color:'.htmlspecialchars($ci['ri_color']).'"' : '' ?>></i>
                                        </button>
                                        <input type="hidden" name="ci_ri[]" value="<?= htmlspecialchars($ci['ri'] ?? '') ?>" class="ci-ri-val">
                                        <input type="hidden" name="ci_ri_color[]" value="<?= htmlspecialchars($ci['ri_color'] ?? '') ?>" class="ci-ri-color-val">
                                    </div>
                                    <input type="text" name="ci_name[]" value="<?= htmlspecialchars($ci['name'] ?? '') ?>" placeholder="显示名称" class="layui-input ci-name">
                                    <div class="ci-url-wrap">
                                        <input type="text" name="ci_url[]" value="<?= htmlspecialchars($ci['url'] ?? '') ?>" placeholder="点击跳转链接（留空=默认筛选）" class="layui-input ci-url">
                                        <button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">
                                        <input type="checkbox" lay-skin="primary" lay-filter="ci_newtab_dummy" title="新窗口" <?= ($ci['newtab'] ?? 'n') == 'y' ? 'checked' : '' ?>>
                                        <input type="hidden" name="ci_newtab[]" value="<?= htmlspecialchars($ci['newtab'] ?? 'n') ?>" class="ci-newtab-val">
                                    </div>
                                    <span class="ci-del" onclick="removeCi(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="layui-form-mid layui-text-em">填写后将替代系统商品分类展示；清空则恢复系统分类。拖动左侧 ⠿ 可调整顺序。跳转链接留空时点击图标自动筛选对应分类商品</div>
                    </div>
                </div>

                </div>

            </div></div>

            <!-- ===== TAB 4: 首页·商品 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">列表布局</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品卡片布局</label>
                    <div class="layui-input-block">
                        <input type="radio" name="goods_list_layout" value="grid" <?= $data['goods_list_layout'] == 'grid' ? 'checked' : '' ?> title="竖向卡片" lay-filter="goods_list_layout">
                        <input type="radio" name="goods_list_layout" value="list" <?= $data['goods_list_layout'] == 'list' ? 'checked' : '' ?> title="横向卡片" lay-filter="goods_list_layout">
                        <div class="layui-form-mid layui-text-em" id="goods_list_layout_tip"><?= $data['goods_list_layout'] == 'list' ? '仅适用于非单页购买模式。当前为横向卡片布局：PC端仍会结合每行卡片数展示多个横向卡片，手机端固定为每行 1 个。' : '仅适用于非单页购买模式。系统会根据你选择的布局，结合 PC端每行卡片数 和 手机端每行卡片数 来渲染商品列表；当前为竖向卡片布局。' ?></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">PC端每行卡片数</label>
                    <div class="layui-input-block">
                        <input type="radio" name="goods_list_columns_pc" value="2" <?= $data['goods_list_columns_pc'] == '2' ? 'checked' : '' ?> title="2个（图大）">
                        <input type="radio" name="goods_list_columns_pc" value="3" <?= $data['goods_list_columns_pc'] == '3' ? 'checked' : '' ?> title="3个">
                        <input type="radio" name="goods_list_columns_pc" value="4" <?= $data['goods_list_columns_pc'] == '4' ? 'checked' : '' ?> title="4个">
                        <input type="radio" name="goods_list_columns_pc" value="5" <?= $data['goods_list_columns_pc'] == '5' ? 'checked' : '' ?> title="5个（推荐）">
                        <input type="radio" name="goods_list_columns_pc" value="6" <?= $data['goods_list_columns_pc'] == '6' ? 'checked' : '' ?> title="6个（图小）">
                        <div class="layui-form-mid layui-text-em" id="goods_list_columns_pc_tip"><?= $data['goods_list_layout'] == 'list' ? '非单页购买模式下，PC端首页商品列表每行显示几个商品卡片。当前为横向卡片布局，为保证美观，PC端每行卡片数最大不超过 3 个，默认推荐 3 个。' : '非单页购买模式下，PC端首页商品列表每行显示几个商品卡片。当前为竖向卡片布局，默认推荐 5 个。' ?></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">手机端每行卡片数</label>
                    <div class="layui-input-block">
                        <input type="radio" name="goods_list_columns_mobile" value="1" <?= $data['goods_list_columns_mobile'] == '1' ? 'checked' : '' ?> title="1个">
                        <input type="radio" name="goods_list_columns_mobile" value="2" <?= $data['goods_list_columns_mobile'] == '2' ? 'checked' : '' ?> title="2个">
                        <div class="layui-form-mid layui-text-em" id="goods_list_columns_mobile_tip"><?= $data['goods_list_layout'] == 'list' ? '非单页购买模式下，手机端首页商品列表每行显示几个商品卡片。当前为横向卡片布局，手机端默认且固定每行只显示 1 个。' : '非单页购买模式下，手机端首页商品列表每行显示几个商品卡片。当前为竖向卡片布局，仅支持每行显示 2 个。' ?></div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">每页商品数量</label>
                    <div class="layui-input-block">
                        <div style="display:flex;align-items:center;gap:8px;max-width:220px;">
                            <input type="number" name="list_per_page" value="<?= (int)$data['list_per_page'] ?>" min="4" max="100" class="layui-input">
                            <span style="color:#999;font-size:13px;white-space:nowrap;">个 / 页</span>
                        </div>
                        <div class="layui-form-mid layui-text-em">首页商品列表每页显示多少个，超出则显示分页器，默认 12</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:24px;">卡片显示</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品卡片信息</label>
                    <div class="layui-input-block">
                        <span class="switch-inline-item">
                            <span class="switch-label">库存数量</span>
                            <input type="checkbox" <?= $data['normal_stock_show'] == 'y' ? 'checked' : '' ?> name="normal_stock_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        </span>
                        <span class="switch-inline-item">
                            <span class="switch-label">已售数量</span>
                            <input type="checkbox" <?= $data['normal_sales_show'] == 'y' ? 'checked' : '' ?> name="normal_sales_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        </span>
                        <span class="switch-inline-item">
                            <span class="switch-label">商品简介</span>
                            <input type="checkbox" <?= $data['normal_des_show'] == 'y' ? 'checked' : '' ?> name="normal_des_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        </span>
                        <span class="switch-inline-item">
                            <span class="switch-label">售空遮罩</span>
                            <input type="checkbox" <?= $data['card_soldout_show'] == 'y' ? 'checked' : '' ?> name="card_soldout_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        </span>
                        <span class="switch-inline-item">
                            <span class="switch-label">商品类型</span>
                            <input type="checkbox" <?= $data['card_type_show'] == 'y' ? 'checked' : '' ?> name="card_type_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        </span>
                        <div class="layui-form-mid layui-text-em">控制商品卡片上显示哪些信息；商品类型指「自动发货」「人工服务」「实物发货」等标识；售空遮罩：库存为0时在商品图片上显示"已售空"</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">购买按钮样式</label>
                    <div class="layui-input-block">
                        <input type="radio" name="card_buy_style" lay-filter="card_buy_style" value="icon_cart" <?= $data['card_buy_style'] === 'icon_cart' ? 'checked' : '' ?> title="购物车图标">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;margin:0 12px 0 4px;border:1px solid #e5e5e5;border-radius:50%;color:var(--theme-primary,#2196F3);vertical-align:middle;"><i class="ri-shopping-cart-2-line"></i></span>
                        <input type="radio" name="card_buy_style" lay-filter="card_buy_style" value="icon_add" <?= $data['card_buy_style'] === 'icon_add' ? 'checked' : '' ?> title="加号图标">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;margin:0 12px 0 4px;border:1px solid #e5e5e5;border-radius:50%;color:var(--theme-primary,#2196F3);vertical-align:middle;"><i class="ri-add-line"></i></span>
                        <input type="radio" name="card_buy_style" lay-filter="card_buy_style" value="icon_bag" <?= $data['card_buy_style'] === 'icon_bag' ? 'checked' : '' ?> title="购物袋图标">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;margin:0 12px 0 4px;border:1px solid #e5e5e5;border-radius:50%;color:var(--theme-primary,#2196F3);vertical-align:middle;"><i class="ri-shopping-bag-line"></i></span>
                        <input type="radio" name="card_buy_style" lay-filter="card_buy_style" value="btn_text" <?= $data['card_buy_style'] === 'btn_text' ? 'checked' : '' ?> title="文字按钮">
                        <div style="display:inline-flex;align-items:center;margin-left:10px;">
                            <input type="text" name="card_buy_text" value="<?= htmlspecialchars($data['card_buy_text']) ?>" placeholder="按钮文字" class="layui-input" style="width:80px;height:32px;" <?= $data['card_buy_style'] !== 'btn_text' ? 'disabled' : '' ?>>
                        </div>
                        <div class="layui-form-mid layui-text-em">商品卡片右下角的购买按钮样式；文字按钮颜色会跟随模板预设主题按钮色</div>
                    </div>
                </div>

               
            </div></div>

            <!-- ===== TAB 5: 单页购买模式 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">

                <div class="group-label">模式开关</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">单页购买模式</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['single_page_mode'] == 'y' ? 'checked' : '' ?> name="single_page_mode" lay-skin="switch" lay-filter="single_page_mode_switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">开启后首页所有商品折叠展示，用户点击商品直接展开下单，无需跳转详情页，适合商品较多的场景</div>
                    </div>
                </div>

                <div class="setting-status-box" id="single_page_mode_status"></div>

                <div class="dependent-setting-panel" id="single_page_mode_panel">

                <div class="group-label" style="margin-top:20px;">首页折叠购买区</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">显示哪些信息</label>
                    <div class="layui-input-block">
                        <span class="switch-inline-item"><span class="switch-label">已售销量</span>
                            <input type="checkbox" <?= $data['single_sales_show'] == 'y' ? 'checked' : '' ?> name="single_sales_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">库存数量</span>
                            <input type="checkbox" <?= $data['single_stock_show'] == 'y' ? 'checked' : '' ?> name="single_stock_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">商品售价</span>
                            <input type="checkbox" <?= $data['single_price_show'] == 'y' ? 'checked' : '' ?> name="single_price_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">悬浮帮助</span>
                            <input type="checkbox" <?= $data['single_float_help_show'] == 'y' ? 'checked' : '' ?> name="single_float_help_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <div class="layui-form-mid layui-text-em">仅在当前开启「单页购买模式」后生效</div>
                    </div>
                </div>

                </div>

            </div></div>

            <!-- ===== TAB 6: 商品详情 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">

                <div class="group-label">商品详情页（点击商品封面进入的页面）</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">显示哪些信息</label>
                    <div class="layui-input-block">
                        <span class="switch-inline-item"><span class="switch-label">商品图片</span>
                            <input type="checkbox" <?= $data['detail_cover_show'] == 'y' ? 'checked' : '' ?> name="detail_cover_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">商品标题</span>
                            <input type="checkbox" <?= $data['detail_title_show'] == 'y' ? 'checked' : '' ?> name="detail_title_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">已售销量</span>
                            <input type="checkbox" <?= $data['detail_sales_show'] == 'y' ? 'checked' : '' ?> name="detail_sales_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">库存数量</span>
                            <input type="checkbox" <?= $data['detail_stock_show'] == 'y' ? 'checked' : '' ?> name="detail_stock_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">商品售价</span>
                            <input type="checkbox" <?= $data['detail_price_show'] == 'y' ? 'checked' : '' ?> name="detail_price_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">悬浮帮助</span>
                            <input type="checkbox" <?= $data['detail_float_help_show'] == 'y' ? 'checked' : '' ?> name="detail_float_help_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">支付按钮布局</label>
                    <div class="layui-input-block">
                        <input type="radio" name="pay_type" value="1" <?= $data['pay_type'] == 1 ? 'checked' : '' ?> title="一行一个（大按钮）">
                        <input type="radio" name="pay_type" value="2" <?= $data['pay_type'] == 2 ? 'checked' : '' ?> title="一行两个">
                        <div class="layui-form-mid layui-text-em">控制商品详情购买区内支付方式按钮的排列方式；单页购买模式也会共用此设置</div>
                    </div>
                </div>

            </div></div>

            <!-- ===== TAB 7: 头部/底部 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">

                <div class="group-label">电脑端顶部导航栏</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">导航菜单列表</label>
                    <div class="layui-input-block">
                        <div class="ci-action-bar">
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="addNav()"><i class="layui-icon layui-icon-add-circle"></i> 新增导航</button>
                            <button type="button" class="layui-btn layui-btn-sm" onclick="syncSysNav()" style="background:#5fb878;border-color:#5fb878;color:#fff;"><i class="ri-refresh-line"></i> 从旧导航管理同步</button>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" onclick="clearAllNav()"><i class="ri-delete-bin-line"></i> 清空列表</button>
                        </div>
                        <div class="ci-config-list" id="nav-list">
                            <?php foreach ($data['nav_items'] as $i => $nav): ?>
                            <div class="ci-config-item">
                                <span class="ci-drag" title="拖拽排序">⠿</span>
                                <span class="ci-num"><?= $i + 1 ?></span>
                                <div class="ci-inputs">
                                    <div class="ci-ri-wrap">
                                        <button type="button" class="ci-ri-btn <?= !empty($nav['ri']) ? 'ci-has-icon' : '' ?>" onclick="ciPickRi(this)" title="点击选择 Remix 图标">
                                            <i class="<?= !empty($nav['ri']) ? htmlspecialchars($nav['ri']) : 'ri-add-line ci-ri-ph' ?>"<?= (!empty($nav['ri']) && !empty($nav['ri_color'])) ? ' style="color:'.htmlspecialchars($nav['ri_color']).'"' : '' ?>></i>
                                        </button>
                                        <input type="hidden" name="nav_ri[]" value="<?= htmlspecialchars($nav['ri'] ?? '') ?>" class="ci-ri-val">
                                        <input type="hidden" name="nav_ri_color[]" value="<?= htmlspecialchars($nav['ri_color'] ?? '') ?>" class="ci-ri-color-val">
                                    </div>
                                    <input type="text" name="nav_name[]" value="<?= htmlspecialchars($nav['name'] ?? '') ?>" placeholder="导航名称" class="layui-input ci-name" style="min-width:100px;">
                                    <div class="ci-url-wrap">
                                        <input type="text" name="nav_url[]" value="<?= htmlspecialchars($nav['url'] ?? '') ?>" placeholder="跳转链接（支持手填或右侧快捷选择）" class="layui-input ci-url">
                                        <button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">
                                        <input type="checkbox" lay-skin="primary" lay-filter="nav_newtab_dummy" title="新窗口" <?= ($nav['newtab'] ?? 'n') == 'y' ? 'checked' : '' ?>>
                                        <input type="hidden" name="nav_newtab[]" value="<?= htmlspecialchars($nav['newtab'] ?? 'n') ?>" class="nav-newtab-val">
                                    </div>
                                    <span class="ci-del" onclick="removeNav(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="layui-form-mid layui-text-em">拖动左侧 ⠿ 可调整顺序。跳转链接右侧可快速选择商品分类、文章分类、文章、商品详情或页面；保存后将覆盖「后台 - 导航管理」在前端的展示。</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:20px;">导航栏样式</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">头部背景</label>
                    <div class="layui-input-block">
                        <input type="text" name="shop_header_bg" id="shop_header_bg" value="<?= htmlspecialchars($data['shop_header_bg']) ?>" placeholder="颜色值或渐变，如 #1976D2 或 linear-gradient(...)" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">标题颜色</label>
                    <div class="layui-input-block">
                        <div style="display:flex;gap:8px;align-items:center;max-width:420px;">
                            <input type="text" name="shop_title_color" id="shop_title_color" value="<?= htmlspecialchars($data['shop_title_color']) ?>" placeholder="#ffffff" class="layui-input">
                            <input type="color" id="shop_title_picker" value="<?= !empty($data['shop_title_color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $data['shop_title_color']) ? htmlspecialchars($data['shop_title_color']) : '#ffffff' ?>" style="width:40px;height:38px;padding:0;border:1px solid #e6e6e6;border-radius:6px;cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">副标题色</label>
                    <div class="layui-input-block">
                        <div style="display:flex;gap:8px;align-items:center;max-width:420px;">
                            <input type="text" name="shop_subtitle_color" id="shop_subtitle_color" value="<?= htmlspecialchars($data['shop_subtitle_color']) ?>" placeholder="rgba(255,255,255,0.8)" class="layui-input">
                            <input type="color" id="shop_subtitle_picker" value="<?= !empty($data['shop_subtitle_color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $data['shop_subtitle_color']) ? htmlspecialchars($data['shop_subtitle_color']) : '#cccccc' ?>" style="width:40px;height:38px;padding:0;border:1px solid #e6e6e6;border-radius:6px;cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">高亮文字</label>
                    <div class="layui-input-block">
                        <div style="display:flex;gap:8px;align-items:center;max-width:420px;">
                            <input type="text" name="shop_nav_active_color" id="shop_nav_active_color" value="<?= htmlspecialchars($data['shop_nav_active_color']) ?>" placeholder="#ff6b6b" class="layui-input">
                            <input type="color" id="shop_nav_color_picker" value="<?= !empty($data['shop_nav_active_color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $data['shop_nav_active_color']) ? htmlspecialchars($data['shop_nav_active_color']) : '#ff6b6b' ?>" style="width:40px;height:38px;padding:0;border:1px solid #e6e6e6;border-radius:6px;cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">高亮背景</label>
                    <div class="layui-input-block">
                        <div style="display:flex;gap:8px;align-items:center;max-width:420px;">
                            <input type="text" name="shop_nav_active_bg" id="shop_nav_active_bg" value="<?= htmlspecialchars($data['shop_nav_active_bg']) ?>" placeholder="rgba(255,255,255,0.2)" class="layui-input">
                            <input type="color" id="shop_nav_bg_picker" value="<?= !empty($data['shop_nav_active_bg']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $data['shop_nav_active_bg']) ? htmlspecialchars($data['shop_nav_active_bg']) : '#ffffff' ?>" style="width:40px;height:38px;padding:0;border:1px solid #e6e6e6;border-radius:6px;cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">预设主题</label>
                    <div class="layui-input-block">
                        <div class="shop-gradient-presets" style="display:flex;gap:8px;flex-wrap:wrap;padding-top:4px;">
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" data-active="#a5f3fc" data-activebg="rgba(255,255,255,0.15)" data-title="#ffffff" data-subtitle="rgba(255,255,255,0.75)" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="紫色渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #1976D2 0%, #42A5F5 100%)" data-active="#fef08a" data-activebg="rgba(255,255,255,0.15)" data-title="#ffffff" data-subtitle="rgba(255,255,255,0.8)" style="background:linear-gradient(135deg, #1976D2 0%, #42A5F5 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="蓝色渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" data-active="#fde68a" data-activebg="rgba(0,0,0,0.1)" data-title="#ffffff" data-subtitle="rgba(255,255,255,0.85)" style="background:linear-gradient(135deg, #11998e 0%, #38ef7d 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="绿色渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)" data-active="#d9f99d" data-activebg="rgba(0,0,0,0.1)" data-title="#ffffff" data-subtitle="rgba(255,255,255,0.85)" style="background:linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="橙红渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #232526 0%, #414345 100%)" data-active="#67e8f9" data-activebg="rgba(255,255,255,0.1)" data-title="#f0f0f0" data-subtitle="rgba(255,255,255,0.6)" style="background:linear-gradient(135deg, #232526 0%, #414345 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="深灰渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="linear-gradient(135deg, #1f1f1f 0%, #000000 100%)" data-active="#d4af37" data-activebg="rgba(212,175,55,0.15)" data-title="#d4af37" data-subtitle="rgba(212,175,55,0.7)" style="background:linear-gradient(135deg, #1f1f1f 0%, #000000 100%);width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid transparent;" title="黑金渐变"></span>
                            <span class="shop-gradient-preset" data-gradient="" data-active="" data-activebg="" data-title="" data-subtitle="" style="background:#fff;width:30px;height:30px;border-radius:4px;cursor:pointer;border:2px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;" title="清除">×</span>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">显示哪些按钮</label>
                    <div class="layui-input-block">
                        <span class="switch-inline-item"><span class="switch-label">搜索</span>
                            <input type="checkbox" <?= $data['header_search_show'] == 'y' ? 'checked' : '' ?> name="header_search_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">个人中心</span>
                            <input type="checkbox" <?= $data['header_user_show'] == 'y' ? 'checked' : '' ?> name="header_user_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">查询订单</span>
                            <input type="checkbox" <?= $data['header_order_show'] == 'y' ? 'checked' : '' ?> name="header_order_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">买家帮助</span>
                            <input type="checkbox" <?= $data['header_help_show'] == 'y' ? 'checked' : '' ?> name="header_help_show" lay-skin="switch" value="y" title=" ON |OFF "></span>
                    </div>
                </div>

                <div class="group-label" style="margin-top:20px;">手机端顶部导航栏</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">显示哪些按钮</label>
                    <div class="layui-input-block">
                        <span class="switch-inline-item"><span class="switch-label">菜单</span>
                            <input type="checkbox" <?= $data['mobile_menu_show'] == 'y' ? 'checked' : '' ?> name="mobile_menu_show" lay-skin="switch" lay-filter="mobile_nav_switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">搜索</span>
                            <input type="checkbox" <?= $data['mobile_search_show'] == 'y' ? 'checked' : '' ?> name="mobile_search_show" lay-skin="switch" lay-filter="mobile_nav_switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">个人中心</span>
                            <input type="checkbox" <?= $data['mobile_user_show'] == 'y' ? 'checked' : '' ?> name="mobile_user_show" lay-skin="switch" lay-filter="mobile_nav_switch" value="y" title=" ON |OFF "></span>
                        <span class="switch-inline-item"><span class="switch-label">买家帮助</span>
                            <input type="checkbox" <?= $data['mobile_help_show'] == 'y' ? 'checked' : '' ?> name="mobile_help_show" lay-skin="switch" lay-filter="mobile_nav_switch" value="y" title=" ON |OFF "></span>
                        <div class="layui-form-mid layui-text-em">手机端顶部空间有限，最多同时开启 3 个按钮</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:20px;">页面底部</div>
                <div class="layui-form-item">
                    <label class="layui-form-label">显示底部版权</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['footer_show'] == 'y' ? 'checked' : '' ?> name="footer_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">是否在页面最底部显示版权信息</div>
                    </div>
                </div>

            </div></div>

            <!-- ===== TAB 8: 买家帮助 ===== -->
            <div class="layui-tab-item"><div class="tab-inner">
                <div class="group-label">入口图标</div>

             <div class="layui-form-item">
                    <label class="layui-form-label">悬浮帮助图标</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?= $data['normal_float_help_show'] == 'y' ? 'checked' : '' ?> name="normal_float_help_show" lay-skin="switch" value="y" title=" ON |OFF ">
                        <div class="layui-form-mid layui-text-em">是否在首页右侧显示悬浮的「买家帮助」小图标，点击可查看常见问题和联系客服</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">图标垂直位置</label>
                    <div class="layui-input-block">
                        <div style="display:flex;align-items:center;gap:12px;max-width:340px;">
                            <input type="range" name="float_help_top" id="float_help_top_range"
                                min="10" max="90" step="1"
                                value="<?= (int)$data['float_help_top'] ?>"
                                style="flex:1;" oninput="document.getElementById('float_help_top_val').textContent=this.value">
                            <span id="float_help_top_val" style="min-width:36px;text-align:right;font-size:13px;color:#555;"><?= (int)$data['float_help_top'] ?></span>
                            <span style="color:#999;font-size:13px;">%</span>
                        </div>
                        <div class="layui-form-mid layui-text-em">图标在页面右侧的上下位置，10% = 靠近顶部，90% = 靠近底部，默认 60%</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">自定义悬浮图标</label>
                    <div class="layui-input-block">
                        <div class="upload-preview-group" style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                            <div id="float_help_icon_preview" style="width:60px;height:60px;border:1px dashed #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fafafa;flex-shrink:0;">
                                <?php if(!empty($data['float_help_icon'])): ?>
                                <img src="<?= htmlspecialchars($data['float_help_icon']) ?>" style="max-width:100%;max-height:100%;">
                                <?php else: ?><i class="ri-image-add-line" style="font-size:24px;color:#ccc;"></i>
                                <?php endif; ?>
                            </div>
                            <input type="text" name="float_help_icon" id="float_help_icon" value="<?= htmlspecialchars($data['float_help_icon']) ?>" class="layui-input" placeholder="图片URL" style="max-width:280px;flex:1;">
                            <button type="button" class="layui-btn layui-btn-sm" id="upload_float_help_icon"><i class="ri-upload-line"></i> 上传</button>
                            <button type="button" class="layui-btn layui-btn-sm layui-btn-danger" onclick="clearFloatHelpIcon()"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <div class="layui-form-mid layui-text-em">自定义悬浮买家帮助图标图片，留空则使用默认图标样式。建议上传 50×50 px 的 PNG 或 GIF</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:24px;">客服信息</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">QQ客服号</label>
                    <div class="layui-input-block">
                        <input type="text" name="service_qq" value="<?= htmlspecialchars($data['service_qq']) ?>" placeholder="填写QQ号码，如：123456789" class="layui-input">
                        <div class="layui-form-mid layui-text-em">买家点击「联系客服」时弹窗中显示的QQ号，支持一键复制。不填则不显示QQ联系方式</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">微信客服号</label>
                    <div class="layui-input-block">
                        <input type="text" name="service_wechat" value="<?= htmlspecialchars($data['service_wechat']) ?>" placeholder="填写微信号，如：wxid_xxx" class="layui-input">
                        <div class="layui-form-mid layui-text-em">买家点击「联系客服」时弹窗中显示的微信号，支持一键复制。不填则不显示微信联系方式</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">售前咨询链接</label>
                    <div class="layui-input-block">
                        <input type="text" name="contact_presale_url" value="<?= htmlspecialchars($data['contact_presale_url']) ?>" placeholder="点击「售前咨询」按钮后跳转的网址，如QQ或客服系统链接" class="layui-input">
                        <div class="layui-form-mid layui-text-em">买家帮助页「售前咨询」按钮的跳转地址，可填 QQ 咨询链接、在线客服链接等</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">售后客服链接</label>
                    <div class="layui-input-block">
                        <input type="text" name="contact_aftersale_url" value="<?= htmlspecialchars($data['contact_aftersale_url']) ?>" placeholder="点击「售后客服」按钮后跳转的网址" class="layui-input">
                        <div class="layui-form-mid layui-text-em">买家帮助页「售后客服」按钮的跳转地址，填写售后处理平台或QQ链接</div>
                    </div>
                </div>

                <div class="group-label" style="margin-top:24px;">帮助内容</div>

                <div class="layui-form-item">
                    <label class="layui-form-label">售后须知内容</label>
                    <div class="layui-input-block">
                        <textarea name="after_sale_notice" placeholder="每行写一条须知，例如：&#10;虚拟商品一经售出概不退款&#10;如遇问题请联系在线客服" class="layui-textarea" style="min-height:120px;"><?= htmlspecialchars($data['after_sale_notice']) ?></textarea>
                        <div class="layui-form-mid layui-text-em">显示在「售后须知」弹窗中，每行写一条。建议说明退款政策、使用注意事项等</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">常见问题</label>
                    <div class="layui-input-block">
                        <div class="faq-config-list" id="faq-list">
                            <?php foreach ($data['faq_list'] as $i => $faq): ?>
                            <div class="faq-config-item">
                                <span class="faq-num"><?= $i + 1 ?></span>
                                <div class="faq-inputs">
                                    <input type="text" name="faq_q[]" value="<?= htmlspecialchars($faq['q']) ?>" placeholder="问题" class="layui-input faq-q">
                                    <input type="text" name="faq_a[]" value="<?= htmlspecialchars($faq['a']) ?>" placeholder="答案" class="layui-input faq-a">
                                    <span class="faq-del" onclick="removeFaq(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="faq-add-btn" onclick="addFaq()">
                            <i class="layui-icon layui-icon-add-circle"></i>添加问题
                        </div>
                        <div class="layui-form-mid layui-text-em">显示在「买家帮助」页的常见问题，买家可点击查看解答。左侧填问题，右侧填答案</div>
                    </div>
                </div>

            </div></div>

        </div>
    </div>
    </div>
    <div id="form-btn">
        <div class="layui-input-block" style="margin:0 auto;">
            <button type="submit" class="layui-btn" lay-submit lay-filter="submit">保存配置</button>
        </div>
    </div>
    </form>

    <script>
        // 系统分类数据（供同步按钮使用）
        var _sysCategories = <?= $_sort_json ?>;
        var _legacyNavItems = <?= $_legacy_nav_json ?>;
        var _navQuickSources = <?= !empty($_nav_quick_pick_json) ? $_nav_quick_pick_json : '{}' ?>;

        // ===== FAQ =====
        function updateFaqNumbers() {
            $('#faq-list .faq-config-item').each(function(i){ $(this).find('.faq-num').text(i+1); });
        }
        function addFaq() {
            var n = $('#faq-list .faq-config-item').length + 1;
            $('#faq-list').append('<div class="faq-config-item"><span class="faq-num">'+n+'</span><div class="faq-inputs">'
                +'<input type="text" name="faq_q[]" value="" placeholder="问题" class="layui-input faq-q">'
                +'<input type="text" name="faq_a[]" value="" placeholder="答案" class="layui-input faq-a">'
                +'<span class="faq-del" onclick="removeFaq(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>'
                +'</div></div>');
        }
        function removeFaq(el) {
            if ($('#faq-list .faq-config-item').length <= 1) { layer.msg('至少保留一个问题'); return; }
            $(el).closest('.faq-config-item').remove();
            updateFaqNumbers();
        }

        // ===== 自定义分类图标 =====
        function _esc(s) { return (s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
        var _ciIconList = [
            'ri-apps-line','ri-fire-line','ri-star-line','ri-heart-line','ri-shopping-cart-line',
            'ri-gift-line','ri-trophy-line','ri-medal-line','ri-vip-crown-line','ri-flashlight-line',
            'ri-rocket-line','ri-gamepad-line','ri-music-line','ri-movie-line','ri-camera-line',
            'ri-image-line','ri-palette-line','ri-brush-line','ri-pencil-line','ri-edit-line',
            'ri-book-line','ri-book-open-line','ri-newspaper-line','ri-file-text-line','ri-article-line',
            'ri-lightbulb-line','ri-magic-line','ri-sparkling-line','ri-cake-line','ri-cup-line',
            'ri-restaurant-line','ri-store-line','ri-shopping-bag-line','ri-t-shirt-line','ri-shirt-line',
            'ri-service-line','ri-customer-service-line','ri-headphone-line','ri-phone-line','ri-smartphone-line',
            'ri-computer-line','ri-macbook-line','ri-keyboard-line','ri-mouse-line','ri-hard-drive-line',
            'ri-database-line','ri-server-line','ri-cloud-line','ri-global-line','ri-earth-line',
            'ri-home-line','ri-building-line','ri-map-pin-line','ri-car-line','ri-plane-line',
            'ri-football-line','ri-basketball-line','ri-sword-line','ri-shield-line','ri-lock-line',
            'ri-key-line','ri-settings-line','ri-tools-line','ri-hammer-line','ri-leaf-line',
            'ri-flower-line','ri-sun-line','ri-moon-line','ri-thunder-flash-line','ri-drop-line',
            'ri-money-cny-line','ri-coin-line','ri-wallet-line','ri-bank-card-line','ri-exchange-line',
            'ri-coupon-line','ri-price-tag-line','ri-discount-percent-line','ri-vip-diamond-line','ri-reward-line',
            'ri-barcode-line','ri-qr-code-line','ri-user-line','ri-group-line','ri-team-line',
            'ri-robot-line','ri-bug-line','ri-code-line','ri-terminal-line','ri-plug-line'
        ];
        var _ciPickTarget = null;
        var _ciPresetColors = ['#2196F3','#f44336','#FF9800','#4CAF50','#9C27B0','#E91E63','#00BCD4','#FF5722','#795548','#607D8B'];
        function ciPickRi(btn) {
            _ciPickTarget = btn;
            var $wrap = $(btn).closest('.ci-ri-wrap');
            var currentIcon  = $wrap.find('.ci-ri-val').val() || '';
            var currentColor = $wrap.find('.ci-ri-color-val').val() || '';
            var _selColor = currentColor;
            // 建立弹窗 HTML
            var html = '<div style="padding:12px;">';
            // 颜色选择区
            html += '<div style="font-size:12px;color:#888;margin-bottom:6px;">图标颜色</div>';
            html += '<div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:12px;">';
            for (var p = 0; p < _ciPresetColors.length; p++) {
                var pc = _ciPresetColors[p];
                var ring = pc === currentColor ? 'box-shadow:0 0 0 2.5px '+pc+',0 0 0 4px #fff,0 0 0 5.5px '+pc+';' : '';
                html += '<div class="ci-cs" data-color="'+pc+'" style="width:24px;height:24px;border-radius:50%;background:'+pc+';cursor:pointer;flex-shrink:0;transition:all 0.15s;'+ring+'"></div>';
            }
            var defRing = !currentColor ? 'box-shadow:0 0 0 2px #999,0 0 0 4px #fff,0 0 0 5.5px #999;' : '';
            html += '<div class="ci-cs" data-color="" style="width:24px;height:24px;border-radius:50%;background:conic-gradient(#ddd 90deg,#fff 90deg 180deg,#ddd 180deg 270deg,#fff 270deg);border:1px solid #ccc;cursor:pointer;flex-shrink:0;transition:all 0.15s;'+defRing+'" title="默认主题色"></div>';
            html += '<div style="width:24px;height:24px;border-radius:50%;overflow:hidden;flex-shrink:0;cursor:pointer;border:1.5px dashed #bbb;position:relative;background:conic-gradient(red,orange,yellow,green,blue,violet,red);" title="自定义颜色">';
            html += '<input type="color" class="ci-custom-color" value="'+(currentColor||'#2196F3')+'" style="position:absolute;top:-6px;left:-6px;width:200%;height:200%;border:none;padding:0;cursor:pointer;opacity:0;"></div>';
            html += '</div>';
            // 图标选择网格
            html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;max-height:300px;overflow-y:auto;">';
            for (var i = 0; i < _ciIconList.length; i++) {
                var ic = _ciIconList[i];
                var sel = ic === currentIcon ? 'background:#e3f2fd;border-color:#2196F3;' : '';
                var lbl = ic.replace(/^ri-/,'').replace(/-(line|fill)$/,'');
                html += '<div class="ci-icon-opt" data-icon="'+ic+'" style="display:flex;flex-direction:column;align-items:center;padding:7px 4px;cursor:pointer;border:1px solid #eee;border-radius:6px;transition:all 0.15s;'+sel+'"><i class="'+ic+'" style="font-size:22px;color:#444;margin-bottom:3px;"></i><span style="font-size:9px;color:#aaa;word-break:break-all;text-align:center;line-height:1.2;">'+lbl+'</span></div>';
            }
            html += '</div><div style="margin-top:10px;text-align:center;display:flex;gap:8px;justify-content:center;">';
            html += '<button class="layui-btn layui-btn-sm ci-apply-color"><i class="ri-palette-line"></i> 仅更新颜色</button>';
            html += '<button class="layui-btn layui-btn-sm layui-btn-danger ci-clear-ri"><i class="ri-delete-bin-line"></i> 清除图标</button>';
            html += '</div></div>';
            layer.open({ type:1, title:'选择 Remix 图标', area:['520px','560px'], content:html,
                success: function(layero, index) {
                    // 预设颜色色块
                    $(layero).find('.ci-cs').on('click', function() {
                        _selColor = $(this).attr('data-color');
                        $(layero).find('.ci-cs').css('box-shadow','');
                        if (_selColor) $(this).css('box-shadow','0 0 0 2.5px '+_selColor+',0 0 0 4px #fff,0 0 0 5.5px '+_selColor);
                        else $(this).css('box-shadow','0 0 0 2px #999,0 0 0 4px #fff,0 0 0 5.5px #999');
                    });
                    // 自定义颜色输入
                    $(layero).find('.ci-custom-color').on('input change', function() {
                        _selColor = $(this).val();
                        $(layero).find('.ci-cs').css('box-shadow','');
                        // 更新幻圆背景为当前选色
                        $(this).closest('div').css('background', _selColor);
                    });
                    // 图标鼠标悬浮效果
                    $(layero).find('.ci-icon-opt').on('mouseenter', function() {
                        if ($(this).attr('data-icon') !== currentIcon) $(this).css({'background':'#f5f5f5','border-color':'#1E9FFF'});
                    }).on('mouseleave', function() {
                        if ($(this).attr('data-icon') !== currentIcon) $(this).css({'background':'','border-color':'#eee'});
                    }).on('click', function() { _ciApplyRi($(this).attr('data-icon'), _selColor); layer.close(index); });
                    // 仅更新颜色
                    $(layero).find('.ci-apply-color').on('click', function() {
                        var ex = $wrap.find('.ci-ri-val').val();
                        if (!ex) { layer.msg('请先选择图标'); return; }
                        _ciApplyRi(ex, _selColor); layer.close(index);
                    });
                    // 清除
                    $(layero).find('.ci-clear-ri').on('click', function() { _ciApplyRi('', ''); layer.close(index); });
                }
            });
        }
        function _ciApplyRi(iconClass, color) {
            if (!_ciPickTarget) return;
            var $btn = $(_ciPickTarget);
            var $wrap = $btn.closest('.ci-ri-wrap');
            $wrap.find('.ci-ri-val').val(iconClass);
            $wrap.find('.ci-ri-color-val').val(color || '');
            if (iconClass) {
                $btn.find('i').attr('class', iconClass).css('color', color || '');
                $btn.addClass('ci-has-icon');
            } else {
                $btn.find('i').attr('class', 'ri-add-line ci-ri-ph').css('color', '');
                $btn.removeClass('ci-has-icon');
            }
            _ciPickTarget = null;
        }
        function _ciItemHtml(n, img, ri, color, name, url, newtab) {
            var riColorStyle = (ri && color) ? ' style="color:'+_esc(color)+'"' : '';
            var checked = newtab === 'y' ? 'checked' : '';
            return '<div class="ci-config-item">'
                +'<span class="ci-drag" title="拖拽排序">⠿</span>'
                +'<span class="ci-num">'+n+'</span>'
                +'<div class="ci-inputs">'
                +'<div class="ci-img-wrap">'
                +'<input type="text" name="ci_img[]" value="'+_esc(img)+'" placeholder="图标图片URL" class="layui-input ci-img">'
                +'<button type="button" class="layui-btn layui-btn-xs layui-btn-warm ci-upload-btn" onclick="ciUpload(this)">上传</button>'
                +'</div>'
                +'<div class="ci-ri-wrap">'
                +'<button type="button" class="ci-ri-btn'+(ri?' ci-has-icon':'')+' " onclick="ciPickRi(this)" title="点击选择 Remix 图标">'
                +'<i class="'+(ri?_esc(ri):'ri-add-line ci-ri-ph')+'"'+riColorStyle+'></i></button>'
                +'<input type="hidden" name="ci_ri[]" value="'+_esc(ri)+'" class="ci-ri-val">'
                +'<input type="hidden" name="ci_ri_color[]" value="'+_esc(color)+'" class="ci-ri-color-val">'
                +'</div>'
                +'<input type="text" name="ci_name[]" value="'+_esc(name)+'" placeholder="显示名称" class="layui-input ci-name">'
                +'<div class="ci-url-wrap">'
                +'<input type="text" name="ci_url[]" value="'+_esc(url)+'" placeholder="点击跳转链接（留空=默认筛选）" class="layui-input ci-url">'
                +'<button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>'
                +'</div>'
                +'<div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">'
                +'<input type="checkbox" lay-skin="primary" lay-filter="ci_newtab_dummy" title="新窗口" '+checked+'>'
                +'<input type="hidden" name="ci_newtab[]" value="'+_esc(newtab||'n')+'" class="ci-newtab-val">'
                +'</div>'
                +'<span class="ci-del" onclick="removeCi(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>'
                +'</div></div>';
        }
        function updateCiNumbers() {
            $('#ci-list .ci-config-item').each(function(i){ $(this).find('.ci-num').text(i+1); });
        }
        function addCi(img, ri, color, name, url, newtab) {
            var n = $('#ci-list .ci-config-item').length + 1;
            $('#ci-list').append(_ciItemHtml(n, img||'', ri||'', color||'', name||'', url||'', newtab||'n'));
            layui.form.render('checkbox');
        }
        function removeCi(el) {
            $(el).closest('.ci-config-item').remove();
            updateCiNumbers();
        }
        function clearAllCi() {
            if (!$('#ci-list .ci-config-item').length) { layer.msg('列表已空'); return; }
            layer.confirm('确定清空所有自定义图标？', function(idx){
                $('#ci-list').empty();
                layer.close(idx);
            });
        }
        function syncSysCategories() {
            if (!_sysCategories || !_sysCategories.length) { layer.msg('暂无系统商品分类'); return; }
            var msg = $('#ci-list .ci-config-item').length
                ? '将用系统分类覆盖现有列表，确定继续？'
                : '将同步 '+_sysCategories.length+' 个系统分类，确定继续？';
            layer.confirm(msg, function(idx){
                layer.close(idx);
                $('#ci-list').empty();
                $.each(_sysCategories, function(i, s){
                    if (s.img) {
                        addCi(s.img, '', '', s.name, s.url || '', 'n');
                    } else if (s.ri) {
                        addCi('', s.ri, '', s.name, s.url || '', 'n');
                    } else {
                        addCi('', '', '', s.name, s.url || '', 'n');
                    }
                });
                layer.msg('已同步 '+_sysCategories.length+' 个分类，跳转链接已自动填入，可按需修改');
            });
        }

        // ================= 顶部导航配置 =================
        var _navQuickTarget = null;
        var _navQuickState = { type: 'goods_sort', keyword: '' };
        function _navQuickTabs() {
            return [
                { key: 'goods_sort', text: '商品分类' },
                { key: 'blog_sort', text: '文章分类' },
                { key: 'article', text: '文章' },
                { key: 'goods', text: '商品详情' },
                { key: 'page', text: '页面' }
            ];
        }
        function _navQuickPanelHtml() {
            var html = '<div class="nav-quick-panel">';
            html += '<div class="nav-quick-tip"><i class="ri-lightbulb-flash-line"></i><span>点击下方项目后，会自动回填当前这一行的导航名称和跳转链接，填完后仍可继续修改。</span></div>';
            html += '<div class="nav-quick-tabs">';
            $.each(_navQuickTabs(), function(_, tab) {
                html += '<span class="nav-quick-tab' + (tab.key === _navQuickState.type ? ' active' : '') + '" data-type="' + tab.key + '">' + tab.text + '</span>';
            });
            html += '</div>';
            html += '<div class="nav-quick-search"><i class="ri-search-line"></i><input type="text" class="layui-input nav-quick-keyword" placeholder="输入名称或链接关键词筛选"></div>';
            html += '<div class="nav-quick-list"></div>';
            html += '</div>';
            return html;
        }
        function _renderNavQuickList(layero) {
            var items = _navQuickSources[_navQuickState.type] || [];
            var keyword = $.trim(_navQuickState.keyword || '').toLowerCase();
            if (keyword) {
                items = $.grep(items, function(item) {
                    var haystack = ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.url || '')).toLowerCase();
                    return haystack.indexOf(keyword) !== -1;
                });
            }
            var html = '';
            if (!items.length) {
                html = '<div class="nav-quick-empty"><i class="ri-inbox-line"></i><span>当前没有可选项目</span></div>';
            } else {
                $.each(items, function(_, item) {
                    html += '<div class="nav-quick-item" data-name="' + _esc(item.name || '') + '" data-url="' + _esc(item.url || '') + '">';
                    html += '<div class="nav-quick-item-title">' + _esc(item.label || item.name || '') + '</div>';
                    html += '<div class="nav-quick-item-meta"><i class="ri-link"></i>' + _esc(item.url || '') + '</div>';
                    html += '<i class="ri-check-line nav-quick-item-icon"></i>';
                    html += '</div>';
                });
            }
            $(layero).find('.nav-quick-tab').removeClass('active').filter('[data-type="' + _navQuickState.type + '"]').addClass('active');
            $(layero).find('.nav-quick-list').html(html);
        }
        function openNavQuickPicker(btn) {
            _navQuickTarget = $(btn).closest('.ci-config-item, .bn-config-item');
            _navQuickState = { type: 'goods_sort', keyword: '' };
            var area = window.innerWidth < 768 ? ['96%', '80%'] : ['760px', '560px'];
            layer.open({
                type: 1,
                title: '快捷选择导航链接',
                area: area,
                content: _navQuickPanelHtml(),
                success: function(layero, index) {
                    var $layer = $(layero);
                    _renderNavQuickList(layero);
                    $layer.find('.nav-quick-keyword').on('input', function() {
                        _navQuickState.keyword = $(this).val() || '';
                        _renderNavQuickList(layero);
                    });
                    $layer.on('click', '.nav-quick-tab', function() {
                        _navQuickState.type = $(this).attr('data-type') || 'goods_sort';
                        _navQuickState.keyword = '';
                        $layer.find('.nav-quick-keyword').val('');
                        _renderNavQuickList(layero);
                    });
                    $layer.on('click', '.nav-quick-item', function() {
                        if (!_navQuickTarget || !_navQuickTarget.length) {
                            return;
                        }
                        var name = $(this).attr('data-name') || '';
                        var url = $(this).attr('data-url') || '';
                        
                        if (_navQuickTarget.hasClass('bn-config-item')) {
                            var $urlInput = _navQuickTarget.find('.bn-url');
                            if ($urlInput.length) { $urlInput.val(url); }
                        } else {
                            var $nameInput = _navQuickTarget.find('.ci-name');
                            var $urlInput = _navQuickTarget.find('.ci-url');
                            if ($nameInput.length) { $nameInput.val(name); }
                            if ($urlInput.length) { $urlInput.val(url); }
                        }
                        layer.close(index);
                    });
                },
                end: function() {
                    _navQuickTarget = null;
                }
            });
        }
        function _navItemHtml(n, name, url, ri, color, newtab) {
            var riColorStyle = (ri && color) ? ' style="color:'+_esc(color)+'"' : '';
            var hasIconCls = ri ? ' ci-has-icon' : '';
            var riCls = ri ? _esc(ri) : 'ri-add-line ci-ri-ph';
            var checked = newtab === 'y' ? 'checked' : '';
            return '<div class="ci-config-item">'
                +'<span class="ci-drag" title="拖拽排序">⠿</span>'
                +'<span class="ci-num">'+n+'</span>'
                +'<div class="ci-inputs">'
                +'<div class="ci-ri-wrap">'
                +'<button type="button" class="ci-ri-btn '+hasIconCls+'" onclick="ciPickRi(this)" title="点击选择 Remix 图标"><i class="'+riCls+'"'+riColorStyle+'></i></button>'
                +'<input type="hidden" name="nav_ri[]" value="'+_esc(ri)+'" class="ci-ri-val">'
                +'<input type="hidden" name="nav_ri_color[]" value="'+_esc(color)+'" class="ci-ri-color-val">'
                +'</div>'
                +'<input type="text" name="nav_name[]" value="'+_esc(name)+'" placeholder="导航名称" class="layui-input ci-name" style="min-width:100px;">'
                +'<div class="ci-url-wrap">'
                +'<input type="text" name="nav_url[]" value="'+_esc(url)+'" placeholder="跳转链接（支持手填或右侧快捷选择）" class="layui-input ci-url">'
                +'<button type="button" class="layui-btn layui-btn-primary ci-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>'
                +'</div>'
                +'<div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">'
                +'<input type="checkbox" lay-skin="primary" lay-filter="nav_newtab_dummy" title="新窗口" '+checked+'>'
                +'<input type="hidden" name="nav_newtab[]" value="'+_esc(newtab||'n')+'" class="nav-newtab-val">'
                +'</div>'
                +'<span class="ci-del" onclick="removeNav(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>'
                +'</div></div>';
        }
        function updateNavNumbers() {
            $('#nav-list .ci-config-item').each(function(i){ $(this).find('.ci-num').text(i+1); });
        }
        function addNav(name, url, ri, color, newtab) {
            var n = $('#nav-list .ci-config-item').length + 1;
            $('#nav-list').append(_navItemHtml(n, name||'', url||'', ri||'', color||'', newtab||'n'));
            layui.form.render('checkbox');
        }
        function removeNav(el) {
            $(el).closest('.ci-config-item').remove();
            updateNavNumbers();
        }
        function clearAllNav() {
            if (!$('#nav-list .ci-config-item').length) { layer.msg('列表已空'); return; }
            layer.confirm('确定清空所有导航？', function(idx){
                $('#nav-list').empty();
                layer.close(idx);
            });
        }
        function syncSysNav() {
            if (!_legacyNavItems || !_legacyNavItems.length) { layer.msg('旧导航管理中暂无可同步的数据'); return; }
            var msg = $('#nav-list .ci-config-item').length
                ? '将用旧导航管理中的数据覆盖当前列表，确定继续？'
                : '将同步 '+_legacyNavItems.length+' 个旧导航项目，确定继续？';
            layer.confirm(msg, function(idx){
                layer.close(idx);
                $('#nav-list').empty();
                $.each(_legacyNavItems, function(i, nav){
                    addNav(nav.name || '', nav.url || '', nav.ri || '', nav.ri_color || '', nav.newtab || 'n');
                });
                updateNavNumbers();
                layer.msg('已同步 '+_legacyNavItems.length+' 个旧导航项目');
            });
        }
        function applyShopHeaderPreset(gradient, activeColor, activeBg, titleColor, subtitleColor) {
            $('#shop_header_bg').val(gradient || '');
            $('#shop_nav_active_color').val(activeColor || '');
            $('#shop_nav_active_bg').val(activeBg || '');
            $('#shop_title_color').val(titleColor || '');
            $('#shop_subtitle_color').val(subtitleColor || '');
            if (/^#[0-9A-Fa-f]{6}$/.test(titleColor || '')) $('#shop_title_picker').val(titleColor);
            if (/^#[0-9A-Fa-f]{6}$/.test(subtitleColor || '')) $('#shop_subtitle_picker').val(subtitleColor);
            if (/^#[0-9A-Fa-f]{6}$/.test(activeColor || '')) $('#shop_nav_color_picker').val(activeColor);
            if (/^#[0-9A-Fa-f]{6}$/.test(activeBg || '')) $('#shop_nav_bg_picker').val(activeBg);
        }

        // ================= 拖拽排序 (复用) =================
        var _ciDragSrc = null;
        $(document).on('mousedown', '.ci-drag', function() {
            $(this).closest('.ci-config-item').attr('draggable', 'true');
        });
        $(document).on('mouseup', '.ci-config-item', function() {
            $(this).attr('draggable', 'false');
        });
        $(document).on('dragstart', '.ci-config-item', function(e) {
            _ciDragSrc = this;
            $(this).addClass('ci-dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('text/plain', '');
        });
        $(document).on('dragend', '.ci-config-item', function() {
            $(this).attr('draggable', 'false').removeClass('ci-dragging');
            $('#ci-list .ci-config-item, #nav-list .ci-config-item').removeClass('ci-drag-over');
        });
        $(document).on('dragover', '#ci-list .ci-config-item, #nav-list .ci-config-item', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            if (this !== _ciDragSrc) {
                $('#ci-list .ci-config-item, #nav-list .ci-config-item').removeClass('ci-drag-over');
                $(this).addClass('ci-drag-over');
            }
            return false;
        });
        $(document).on('dragleave', '#ci-list .ci-config-item, #nav-list .ci-config-item', function() {
            $(this).removeClass('ci-drag-over');
        });
        $(document).on('drop', '#ci-list .ci-config-item, #nav-list .ci-config-item', function(e) {
            e.preventDefault(); e.stopPropagation();
            if (_ciDragSrc && this !== _ciDragSrc) {
                var $drag = $(_ciDragSrc), $target = $(this);
                if ($drag.parent()[0] === $target.parent()[0]) {
                    if ($drag.index() < $target.index()) $drag.insertAfter($target);
                    else $drag.insertBefore($target);
                    if ($target.parent().attr('id') === 'nav-list') updateNavNumbers();
                    else updateCiNumbers();
                }
            }
            $('#ci-list .ci-config-item, #nav-list .ci-config-item').removeClass('ci-drag-over');
            return false;
        });

        // ===== 分类图标上传（共享文件选择器）=====
        var _ciUploadTarget = null;
        function ciUpload(btn) {
            _ciUploadTarget = $(btn).closest('.ci-img-wrap').find('.ci-img');
            $('#ci-file-global').val('').trigger('click');
        }
        $(document).on('change', '#ci-file-global', function(){
            var file = this.files[0];
            if (!file) return;
            var fd = new FormData();
            fd.append('image', file);
            var loadIdx = layer.load(2);
            $.ajax({
                url: '../admin/article.php?action=upload_cover',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res){
                    layer.close(loadIdx);
                    if (res.code === 0 && _ciUploadTarget) {
                        var src = typeof res.data === 'string' ? res.data : (res.data.src || res.data.url || '');
                        _ciUploadTarget.val(src);
                        layer.msg('上传成功');
                    } else {
                        layer.msg(res.msg || '上传失败');
                    }
                },
                error: function(){ layer.close(loadIdx); layer.msg('上传失败'); }
            });
        });

        // ===== Layui 初始化 =====
        layui.use(['form', 'upload', 'element'], function(){
            var form   = layui.form;
            var upload = layui.upload;
            var element = layui.element;

            function updateGoodsListRadioTitles(layout) {
                var currentLayout = layout || $('input[name="goods_list_layout"]:checked').val() || 'grid';
                if (currentLayout === 'list') {
                    $('input[name="goods_list_columns_pc"][value="2"]').attr('title', '2个');
                    $('input[name="goods_list_columns_pc"][value="3"]').attr('title', '3个（推荐）');
                    $('input[name="goods_list_columns_pc"][value="4"]').attr('title', '4个');
                    $('input[name="goods_list_columns_pc"][value="5"]').attr('title', '5个');
                    $('input[name="goods_list_columns_pc"][value="6"]').attr('title', '6个');
                    $('input[name="goods_list_columns_mobile"][value="1"]').attr('title', '1个（推荐）');
                    $('input[name="goods_list_columns_mobile"][value="2"]').attr('title', '2个');
                } else {
                    $('input[name="goods_list_columns_pc"][value="2"]').attr('title', '2个（图大）');
                    $('input[name="goods_list_columns_pc"][value="3"]').attr('title', '3个');
                    $('input[name="goods_list_columns_pc"][value="4"]').attr('title', '4个');
                    $('input[name="goods_list_columns_pc"][value="5"]').attr('title', '5个（推荐）');
                    $('input[name="goods_list_columns_pc"][value="6"]').attr('title', '6个（图小）');
                    $('input[name="goods_list_columns_mobile"][value="1"]').attr('title', '1个');
                    $('input[name="goods_list_columns_mobile"][value="2"]').attr('title', '2个（推荐）');
                }
            }

            function updateGoodsListTips(layout, isUserSwitch) {
                var currentLayout = layout || $('input[name="goods_list_layout"]:checked').val() || 'grid';
                updateGoodsListRadioTitles(currentLayout);
                if (currentLayout === 'list') {
                    $('input[name="goods_list_columns_pc"]').each(function(){
                        var value = parseInt($(this).val(), 10) || 0;
                        $(this).prop('disabled', value > 3);
                    });
                    $('input[name="goods_list_columns_mobile"]').each(function(){
                        $(this).prop('disabled', $(this).val() !== '1');
                    });

                    var currentPc = $('input[name="goods_list_columns_pc"]:checked').val();
                    if (parseInt(currentPc, 10) > 3 || !currentPc) {
                        $('input[name="goods_list_columns_pc"][value="3"]').prop('checked', true);
                    }
                    $('input[name="goods_list_columns_mobile"][value="1"]').prop('checked', true);

                    $('#goods_list_layout_tip').text('仅适用于非单页购买模式。当前为横向卡片布局：PC端仍会结合每行卡片数展示多个横向卡片，手机端固定为每行 1 个。');
                    $('#goods_list_columns_pc_tip').text('非单页购买模式下，PC端首页商品列表每行显示几个商品卡片。当前为横向卡片布局，为保证美观，PC端每行卡片数最大不超过 3 个，默认推荐 3 个。');
                    $('#goods_list_columns_mobile_tip').text('非单页购买模式下，手机端首页商品列表每行显示几个商品卡片。当前为横向卡片布局，手机端默认且固定每行只显示 1 个。');
                } else {
                    $('input[name="goods_list_columns_pc"]').prop('disabled', false);
                    $('input[name="goods_list_columns_mobile"]').each(function(){
                        $(this).prop('disabled', $(this).val() !== '2');
                    });
                    if (isUserSwitch) {
                        $('input[name="goods_list_columns_pc"][value="5"]').prop('checked', true);
                    }
                    $('input[name="goods_list_columns_mobile"][value="2"]').prop('checked', true);

                    $('#goods_list_layout_tip').text('仅适用于非单页购买模式。系统会根据你选择的布局，结合 PC端每行卡片数 和 手机端每行卡片数 来渲染商品列表；当前为竖向卡片布局。');
                    $('#goods_list_columns_pc_tip').text('非单页购买模式下，PC端首页商品列表每行显示几个商品卡片。当前为竖向卡片布局，默认推荐 5 个。');
                    $('#goods_list_columns_mobile_tip').text('非单页购买模式下，手机端首页商品列表每行显示几个商品卡片。当前为竖向卡片布局，仅支持每行显示 2 个。');
                }

                form.render('radio');
            }

            function updateSinglePageModeState(forceState) {
                var isEnabled = typeof forceState === 'boolean' ? forceState : $('input[name="single_page_mode"]').prop('checked');
                $('#single_page_mode_panel').toggleClass('is-disabled', !isEnabled);
                $('#single_page_mode_status')
                    .toggleClass('is-off', !isEnabled)
                    .html(isEnabled
                        ? '当前已开启单页购买模式：下方设置会直接作用于首页折叠展开的购买区域。'
                        : '当前未开启单页购买模式：下方设置暂时不会在前台生效，但你仍可提前配置，后续开启后会立即按这里的设置显示。');
            }

            function updateBannerShowState(forceState) {
                var isEnabled = typeof forceState === 'boolean' ? forceState : $('input[name="banner_show"]').prop('checked');
                $('#banner_show_panel').toggleClass('is-disabled', !isEnabled);
                $('#banner_show_status')
                    .toggleClass('is-off', !isEnabled)
                    .html(isEnabled
                        ? '当前已开启首页轮播：下方轮播图列表、尺寸与动画设置会直接在前台生效。'
                        : '当前未开启首页轮播：下方轮播设置暂不在前台显示，但你可以先把图片和参数配置好，后续开启后会立即生效。');
            }

            function updateCategoryShowState(forceState) {
                var isEnabled = typeof forceState === 'boolean' ? forceState : $('input[name="category_show"]').prop('checked');
                $('#category_show_panel').toggleClass('is-disabled', !isEnabled);
                $('#category_show_status')
                    .toggleClass('is-off', !isEnabled)
                    .html(isEnabled
                        ? '当前已开启分类模块：下方列数、滑动方式和自定义图标设置会直接作用于首页分类区域。'
                        : '当前未开启分类模块：下方分类设置暂不在首页显示，但你仍可提前配置，后续开启后会立即按当前设置生效。');
            }

            updateGoodsListTips(null, false);
            updateSinglePageModeState();
            updateBannerShowState();
            updateCategoryShowState();
            form.on('radio(goods_list_layout)', function(data){
                updateGoodsListTips(data.value, true);
            });
            form.on('switch(single_page_mode_switch)', function(data){
                updateSinglePageModeState(data.elem.checked);
            });
            form.on('switch(mobile_nav_switch)', function(data){
                var $switches = $('input[lay-filter="mobile_nav_switch"]');
                var onCount = $switches.filter(':checked').length;
                if (onCount > 3) {
                    data.elem.checked = false;
                    form.render('checkbox');
                    layer.msg('手机端顶部最多同时开启 3 个按钮', {icon: 0, anim: 6});
                }
            });
            form.on('switch(banner_show_switch)', function(data){
                updateBannerShowState(data.elem.checked);
            });
            form.on('switch(category_show_switch)', function(data){
                updateCategoryShowState(data.elem.checked);
            });
            form.on('checkbox(nav_newtab_dummy)', function(data){
                $(data.elem).siblings('.nav-newtab-val').val(data.elem.checked ? 'y' : 'n');
            });
            form.on('checkbox(bn_newtab_dummy)', function(data){
                $(data.elem).siblings('.bn-newtab-val').val(data.elem.checked ? 'y' : 'n');
            });
            form.on('checkbox(ci_newtab_dummy)', function(data){
                $(data.elem).siblings('.ci-newtab-val').val(data.elem.checked ? 'y' : 'n');
            });
            $('#shop_title_picker').on('input change', function(){ $('#shop_title_color').val(this.value); });
            $('#shop_subtitle_picker').on('input change', function(){ $('#shop_subtitle_color').val(this.value); });
            $('#shop_nav_color_picker').on('input change', function(){ $('#shop_nav_active_color').val(this.value); });
            $('#shop_nav_bg_picker').on('input change', function(){ $('#shop_nav_active_bg').val(this.value); });
            $(document).on('click', '.shop-gradient-preset', function(){
                applyShopHeaderPreset($(this).data('gradient'), $(this).data('active'), $(this).data('activebg'), $(this).data('title'), $(this).data('subtitle'));
            });

            // 购买按钮样式自定义输入框联动
            form.on('radio(card_buy_style)', function(data){
                $('input[name="card_buy_text"]').prop('disabled', data.value !== 'btn_text');
            });

            // 悬浮帮助图标上传
            upload.render({
                elem: '#upload_float_help_icon',
                url: '../admin/article.php?action=upload_cover',
                field: 'image',
                accept: 'images',
                exts: 'png|gif|jpg|jpeg|webp',
                done: function(res){
                    if(res.code == 0){
                        var src = typeof res.data === 'string' ? res.data : (res.data.src || res.data.url || '');
                        $('#float_help_icon').val(src);
                        $('#float_help_icon_preview').html('<img src="'+src+'" style="max-width:100%;max-height:100%;">');
                        layer.msg('上传成功');
                    } else { layer.msg(res.msg || '上传失败'); }
                },
                error: function(){ layer.msg('上传失败'); }
            });

            // 表单提交
            form.on('submit(submit)', function(){
                $.ajax({
                    url: '?tpl=default&action=setting_ajax',
                    type: 'POST',
                    data: $('#form').serialize(),
                    dataType: 'json',
                    success: function(res){
                        if(res.code && res.code != 0 && res.code != 200) return layer.msg(res.msg || '保存失败');
                        var frameIndex = parent.layer.getFrameIndex(window.name);
                        if (frameIndex !== undefined && frameIndex !== null && frameIndex !== '') {
                            parent.layer.close(frameIndex);
                        }
                        parent.layer.msg('已保存配置');
                        if(window.parent.table) window.parent.table.reload();
                    },
                    error: function(xhr){
                        var msg = '请求失败';
                        try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                        layer.msg(msg);
                    }
                });
                return false;
            });

            // 颜色选择器同步
            $('#theme_primary_picker').on('input change', function(){ $('#theme_primary').val($(this).val()); });
            $('#theme_primary').on('input change', function(){ var v=$(this).val(); if(/^#[0-9A-Fa-f]{6}$/.test(v)) $('#theme_primary_picker').val(v); });
            $('#theme_price_picker').on('input change', function(){ $('#theme_price').val($(this).val()); });
            $('#theme_price').on('input change', function(){ var v=$(this).val(); if(/^#[0-9A-Fa-f]{6}$/.test(v)) $('#theme_price_picker').val(v); });
            $('#theme_button_picker').on('input change', function(){ $('#theme_button').val($(this).val()); });
            $('#theme_button').on('input change', function(){ var v=$(this).val(); if(/^#[0-9A-Fa-f]{6}$/.test(v)) $('#theme_button_picker').val(v); });
            $('#theme_accent_picker').on('input change', function(){ $('#theme_accent').val($(this).val()); });
            $('#theme_accent').on('input change', function(){ var v=$(this).val(); if(/^#[0-9A-Fa-f]{6}$/.test(v)) $('#theme_accent_picker').val(v); });

            // 预设主题点击
            $('.theme-preset').click(function(){
                var p=$(this).data('primary'), pr=$(this).data('price'), b=$(this).data('button'), a=$(this).data('accent');
                $('#theme_primary').val(p); $('#theme_primary_picker').val(p);
                $('#theme_price').val(pr); $('#theme_price_picker').val(pr);
                $('#theme_button').val(b); $('#theme_button_picker').val(b);
                $('#theme_accent').val(a); $('#theme_accent_picker').val(a);
                var hdr = $(this).data('header');
                if (hdr) { $('#shop_header_bg').val(hdr); }
                var nav = $(this).data('navcolor');
                if (nav) { $('#shop_nav_active_color').val(nav); if(/^#[0-9A-Fa-f]{6}$/.test(nav)) $('#shop_nav_color_picker').val(nav); }
                $('.theme-preset').removeClass('active'); $(this).addClass('active');
            });
        });

        function clearFloatHelpIcon() {
            $('#float_help_icon').val('');
            $('#float_help_icon_preview').html('<i class="ri-image-add-line" style="font-size:24px;color:#ccc;"></i>');
        }

        // ===== 轮播图管理 =====
        function _bnItemHtml() {
            var n = $('#bn-list .bn-config-item').length + 1;
            return '<div class="bn-config-item">' +
                '<span class="bn-num">'+n+'</span>' +
                '<div class="bn-inputs">' +
                '<div class="bn-img-cell">' +
                '<div class="bn-img-preview"></div>' +
                '<input type="text" name="bn_img[]" class="bn-img-input layui-input" value="" placeholder="图片地址（上传后自动填入）">' +
                '<button type="button" class="layui-btn layui-btn-xs layui-btn-warm bn-upload-btn"><i class="ri-upload-line"></i> 上传</button>' +
                '</div>' +
                '<div class="bn-url-wrap">' +
                '<input type="text" name="bn_url[]" value="" placeholder="点击跳转链接（选填）" class="layui-input bn-url">' +
                '<button type="button" class="layui-btn layui-btn-primary bn-nav-picker-btn" onclick="openNavQuickPicker(this)"><i class="ri-compasses-2-line"></i> 快捷选择</button>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:4px;margin-left:5px;flex-shrink:0;">' +
                '<input type="checkbox" lay-skin="primary" lay-filter="bn_newtab_dummy" title="新窗口" checked>' +
                '<input type="hidden" name="bn_newtab[]" value="y" class="bn-newtab-val">' +
                '</div>' +
                '<span class="bn-del" onclick="removeBn(this)" title="删除"><i class="layui-icon layui-icon-delete"></i></span>' +
                '</div></div>';
        }
        function addBn() {
            $('#bn-list').append(_bnItemHtml());
            var $last = $('#bn-list .bn-config-item').last();
            bindBnUpload($last.find('.bn-upload-btn')[0]);
            layui.form.render('checkbox');
        }
        function updateBnNumbers() {
            $('#bn-list .bn-config-item').each(function(i){ $(this).find('.bn-num').text(i+1); });
        }
        function removeBn(el) {
            $(el).closest('.bn-config-item').remove();
            updateBnNumbers();
        }
        function bindBnUpload(btn) {
            layui.use('upload', function(){
                layui.upload.render({
                    elem: btn,
                    url: '../admin/article.php?action=upload_cover',
                    field: 'image',
                    accept: 'images',
                    done: function(res) {
                        if (res.code == 0 || res.code == 200) {
                            var url = '';
                            if (typeof res.data === 'string') {
                                url = res.data;
                            } else if (res.data && typeof res.data === 'object') {
                                url = res.data.src || res.data.url || res.data.path || '';
                            }
                            var item = $(btn).closest('.bn-config-item');
                            item.find('.bn-img-input').val(url);
                            item.find('.bn-img-preview').css('background-image', 'url(' + url + ')');
                        } else { layui.layer.msg(res.msg || '上传失败'); }
                    }
                });
            });
        }
        // 绑定已有的上传按钮
        $('#bn-list .bn-upload-btn').each(function(){ bindBnUpload(this); });
        // 手动粘贴图片地址时同步更新预览图
        $(document).on('input', '.bn-img-input', function(){
            var url = $(this).val().trim();
            $(this).closest('.bn-config-item').find('.bn-img-preview').css('background-image', url ? 'url(' + url + ')' : 'none');
        });
    </script>
    <?php
}

/**
 * 保存模板配置
 * @param string $tpl 模板名称
 */
function plugin_setting($tpl) {
    $tpl = frontTplSettingKey($tpl);
    $tplOptions = TplOptions::getInstance();
    
    // ========== 主题配色 ==========
    $theme_primary = isset($_POST['theme_primary']) ? trim($_POST['theme_primary']) : '#2196F3';
    $theme_price = isset($_POST['theme_price']) ? trim($_POST['theme_price']) : '#ff6600';
    $theme_button = isset($_POST['theme_button']) ? trim($_POST['theme_button']) : '#2f69d9';
    $theme_accent = isset($_POST['theme_accent']) ? trim($_POST['theme_accent']) : '#ff9800';
    
    // ========== 基础设置 ==========
    $bg_video_url = isset($_POST['bg_video_url']) ? trim($_POST['bg_video_url']) : '';
    $bg_video_mobile_show = isset($_POST['bg_video_mobile_show']) ? 'y' : 'n';
    
    // ========== 轮播图设置 ==========
    $banner_show      = isset($_POST['banner_show']) ? 'y' : 'n';
    $banner_speed     = isset($_POST['banner_speed'])     ? max(500, (int)$_POST['banner_speed'])  : 3000;
    $banner_height    = isset($_POST['banner_height'])    ? max(120, min(600, (int)$_POST['banner_height'])) : 300;
    $banner_animation = (isset($_POST['banner_animation']) && in_array($_POST['banner_animation'], ['slide','fade'])) ? $_POST['banner_animation'] : 'slide';
    $banner_items = [];
    if (!empty($_POST['bn_img'])) {
        foreach ($_POST['bn_img'] as $i => $img) {
            $img = trim($img);
            if ($img === '') continue;
            $banner_items[] = [
                'img' => $img,
                'url' => isset($_POST['bn_url'][$i]) ? trim($_POST['bn_url'][$i]) : '',
                'newtab' => isset($_POST['bn_newtab'][$i]) ? trim($_POST['bn_newtab'][$i]) : 'y',
            ];
        }
    }
    // ========== 公告设置 ==========
    $roll_notice_mobile_show = isset($_POST['roll_notice_mobile_show']) ? 'y' : 'n';
    // ========== 首页设置 ==========
    $category_show = isset($_POST['category_show']) ? 'y' : 'n';
    $category_mobile_cols = isset($_POST['category_mobile_cols']) ? trim($_POST['category_mobile_cols']) : '4';
    $category_slide_mode = isset($_POST['category_slide_mode']) ? 'y' : 'n';
    $category_pc_slide_mode = isset($_POST['category_pc_slide_mode']) ? 'y' : 'n';
    $category_pc_cols = isset($_POST['category_pc_cols']) ? trim($_POST['category_pc_cols']) : '0';
    $category_mobile_rows = isset($_POST['category_mobile_rows']) ? trim($_POST['category_mobile_rows']) : '0';
    $custom_category_icons = [];
    if (!empty($_POST['ci_name'])) {
        foreach ($_POST['ci_name'] as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            $custom_category_icons[] = [
                'img'      => isset($_POST['ci_img'][$i])       ? trim($_POST['ci_img'][$i])       : '',
                'ri'       => isset($_POST['ci_ri'][$i])        ? trim($_POST['ci_ri'][$i])        : '',
                'ri_color' => isset($_POST['ci_ri_color'][$i])  ? trim($_POST['ci_ri_color'][$i])  : '',
                'name'     => $name,
                'url'      => isset($_POST['ci_url'][$i])       ? trim($_POST['ci_url'][$i])       : '',
                'newtab'   => isset($_POST['ci_newtab'][$i])    ? trim($_POST['ci_newtab'][$i])    : 'n',
            ];
        }
    }
    $goods_list_style = isset($_POST['goods_list_style']) ? trim($_POST['goods_list_style']) : 'card';
    $goods_list_layout = isset($_POST['goods_list_layout']) && $_POST['goods_list_layout'] === 'list' ? 'list' : 'grid';
    $goods_list_columns_pc = isset($_POST['goods_list_columns_pc']) && in_array($_POST['goods_list_columns_pc'], ['2', '3', '4', '5', '6']) ? trim($_POST['goods_list_columns_pc']) : '4';
    if ($goods_list_layout === 'list') {
        $goods_list_columns_pc = (string)min(3, max(2, (int)$goods_list_columns_pc));
        $goods_list_columns_mobile = '1';
    } else {
        $goods_list_columns_mobile = '2';
    }
    $goods_list_columns = $goods_list_columns_pc;
    $list_per_page = isset($_POST['list_per_page']) ? max(4, min(100, (int)$_POST['list_per_page'])) : 12;
    $single_page_mode = isset($_POST['single_page_mode']) ? 'y' : 'n';
    $pay_type = isset($_POST['pay_type']) ? intval($_POST['pay_type']) : 2;
    $normal_stock_show = isset($_POST['normal_stock_show']) ? 'y' : 'n';
    $normal_sales_show = isset($_POST['normal_sales_show']) ? 'y' : 'n';
    $normal_des_show   = isset($_POST['normal_des_show'])   ? 'y' : 'n';
    // ========== 商品卡片扩展设置 ==========
    $card_soldout_show = isset($_POST['card_soldout_show']) ? 'y' : 'n';
    $card_type_show = isset($_POST['card_type_show']) ? 'y' : 'n';
    $card_buy_style = isset($_POST['card_buy_style']) ? trim($_POST['card_buy_style']) : 'icon_cart';
    $card_buy_text = isset($_POST['card_buy_text']) ? trim($_POST['card_buy_text']) : '购买';
    $normal_float_help_show = isset($_POST['normal_float_help_show']) ? 'y' : 'n';
    $float_help_top = isset($_POST['float_help_top']) ? max(10, min(90, (int)$_POST['float_help_top'])) : 60;
    $float_help_icon = isset($_POST['float_help_icon']) ? trim($_POST['float_help_icon']) : '';
    
    // ========== 商品详情页设置 ==========
    $detail_cover_show = isset($_POST['detail_cover_show']) ? 'y' : 'n';
    $detail_title_show = isset($_POST['detail_title_show']) ? 'y' : 'n';
    $detail_sales_show = isset($_POST['detail_sales_show']) ? 'y' : 'n';
    $detail_stock_show = isset($_POST['detail_stock_show']) ? 'y' : 'n';
    $detail_price_show = isset($_POST['detail_price_show']) ? 'y' : 'n';
    
    // ========== 单页购买模式设置 ==========
    $single_sales_show = isset($_POST['single_sales_show']) ? 'y' : 'n';
    $single_stock_show = isset($_POST['single_stock_show']) ? 'y' : 'n';
    $single_price_show = isset($_POST['single_price_show']) ? 'y' : 'n';
    $single_float_help_show = isset($_POST['single_float_help_show']) ? 'y' : 'n';
    
    // ========== 商品详情页悬浮图标 ==========
    $detail_float_help_show = isset($_POST['detail_float_help_show']) ? 'y' : 'n';
    
    // ========== 头部设置 ==========
    $header_menu_show = isset($_POST['header_menu_show']) ? 'y' : 'n';
    $header_search_show = isset($_POST['header_search_show']) ? 'y' : 'n';
    $header_user_show = isset($_POST['header_user_show']) ? 'y' : 'n';
    $header_order_show = isset($_POST['header_order_show']) ? 'y' : 'n';
    $header_help_show = isset($_POST['header_help_show']) ? 'y' : 'n';
    $shop_header_bg = isset($_POST['shop_header_bg']) ? trim($_POST['shop_header_bg']) : '';
    $shop_title_color = isset($_POST['shop_title_color']) ? trim($_POST['shop_title_color']) : '';
    $shop_subtitle_color = isset($_POST['shop_subtitle_color']) ? trim($_POST['shop_subtitle_color']) : '';
    $shop_nav_active_color = isset($_POST['shop_nav_active_color']) ? trim($_POST['shop_nav_active_color']) : '';
    $shop_nav_active_bg = isset($_POST['shop_nav_active_bg']) ? trim($_POST['shop_nav_active_bg']) : '';
    
    // ========== 头部设置（移动端） ==========
    $mobile_menu_show = isset($_POST['mobile_menu_show']) ? 'y' : 'n';
    $mobile_search_show = isset($_POST['mobile_search_show']) ? 'y' : 'n';
    $mobile_user_show = isset($_POST['mobile_user_show']) ? 'y' : 'n';
    $mobile_help_show = isset($_POST['mobile_help_show']) ? 'y' : 'n';
    $_mobile_nav_on = ($mobile_menu_show === 'y') + ($mobile_search_show === 'y') + ($mobile_user_show === 'y') + ($mobile_help_show === 'y');
    if ($_mobile_nav_on > 3) {
        $mobile_help_show = 'n';
    }
    
    // 顶部导航列表
    $nav_items = [];
    if (!empty($_POST['nav_name'])) {
        foreach ($_POST['nav_name'] as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;
            $nav_items[] = [
                'name'     => $name,
                'url'      => isset($_POST['nav_url'][$i])      ? trim($_POST['nav_url'][$i])      : '',
                'ri'       => isset($_POST['nav_ri'][$i])       ? trim($_POST['nav_ri'][$i])       : '',
                'ri_color' => isset($_POST['nav_ri_color'][$i]) ? trim($_POST['nav_ri_color'][$i]) : '',
                'newtab'   => isset($_POST['nav_newtab'][$i])   ? trim($_POST['nav_newtab'][$i])   : 'n',
            ];
        }
    }
    
    // ========== 底部设置 ==========
    $footer_show = isset($_POST['footer_show']) ? 'y' : 'n';
    
    // ========== 买家帮助页面 ==========
    $service_qq = isset($_POST['service_qq']) ? trim($_POST['service_qq']) : '';
    $service_wechat = isset($_POST['service_wechat']) ? trim($_POST['service_wechat']) : '';
    $after_sale_notice = isset($_POST['after_sale_notice']) ? trim($_POST['after_sale_notice']) : '';
    $contact_presale_url = isset($_POST['contact_presale_url']) ? trim($_POST['contact_presale_url']) : '';
    $contact_aftersale_url = isset($_POST['contact_aftersale_url']) ? trim($_POST['contact_aftersale_url']) : '';
    
    // 常见问题
    $faq_list = [];
    if (isset($_POST['faq_q']) && isset($_POST['faq_a'])) {
        $questions = $_POST['faq_q'];
        $answers = $_POST['faq_a'];
        for ($i = 0; $i < count($questions); $i++) {
            $q = trim($questions[$i]);
            $a = trim($answers[$i]);
            if (!empty($q)) {
                $faq_list[] = ['q' => $q, 'a' => $a];
            }
        }
    }
    
    // 构建保存数据
    $data = [
        ['template' => $tpl, 'name' => 'theme_primary', 'depend' => '', 'data' => serialize($theme_primary)],
        ['template' => $tpl, 'name' => 'theme_price', 'depend' => '', 'data' => serialize($theme_price)],
        ['template' => $tpl, 'name' => 'theme_button', 'depend' => '', 'data' => serialize($theme_button)],
        ['template' => $tpl, 'name' => 'theme_accent', 'depend' => '', 'data' => serialize($theme_accent)],
        ['template' => $tpl, 'name' => 'bg_video_url', 'depend' => '', 'data' => serialize($bg_video_url)],
        ['template' => $tpl, 'name' => 'bg_video_mobile_show', 'depend' => '', 'data' => serialize($bg_video_mobile_show)],
        ['template' => $tpl, 'name' => 'banner_show', 'depend' => '', 'data' => serialize($banner_show)],
        ['template' => $tpl, 'name' => 'banner_items', 'depend' => '', 'data' => serialize($banner_items)],
        ['template' => $tpl, 'name' => 'banner_speed', 'depend' => '', 'data' => serialize($banner_speed)],
        ['template' => $tpl, 'name' => 'banner_height', 'depend' => '', 'data' => serialize($banner_height)],
        ['template' => $tpl, 'name' => 'banner_animation', 'depend' => '', 'data' => serialize($banner_animation)],
        ['template' => $tpl, 'name' => 'roll_notice_mobile_show', 'depend' => '', 'data' => serialize($roll_notice_mobile_show)],
        ['template' => $tpl, 'name' => 'category_show', 'depend' => '', 'data' => serialize($category_show)],
        ['template' => $tpl, 'name' => 'category_mobile_cols', 'depend' => '', 'data' => serialize($category_mobile_cols)],
        ['template' => $tpl, 'name' => 'category_slide_mode', 'depend' => '', 'data' => serialize($category_slide_mode)],
        ['template' => $tpl, 'name' => 'category_pc_slide_mode', 'depend' => '', 'data' => serialize($category_pc_slide_mode)],
        ['template' => $tpl, 'name' => 'category_pc_cols', 'depend' => '', 'data' => serialize($category_pc_cols)],
        ['template' => $tpl, 'name' => 'category_mobile_rows', 'depend' => '', 'data' => serialize($category_mobile_rows)],
        ['template' => $tpl, 'name' => 'custom_category_icons', 'depend' => '', 'data' => serialize($custom_category_icons)],
        ['template' => $tpl, 'name' => 'goods_list_style', 'depend' => '', 'data' => serialize($goods_list_style)],
        ['template' => $tpl, 'name' => 'goods_list_columns', 'depend' => '', 'data' => serialize($goods_list_columns)],
        ['template' => $tpl, 'name' => 'goods_list_columns_pc', 'depend' => '', 'data' => serialize($goods_list_columns_pc)],
        ['template' => $tpl, 'name' => 'goods_list_columns_mobile', 'depend' => '', 'data' => serialize($goods_list_columns_mobile)],
        ['template' => $tpl, 'name' => 'goods_list_layout', 'depend' => '', 'data' => serialize($goods_list_layout)],
        ['template' => $tpl, 'name' => 'list_per_page', 'depend' => '', 'data' => serialize($list_per_page)],
        ['template' => $tpl, 'name' => 'single_page_mode', 'depend' => '', 'data' => serialize($single_page_mode)],
        ['template' => $tpl, 'name' => 'pay_type', 'depend' => '', 'data' => serialize($pay_type)],
        ['template' => $tpl, 'name' => 'normal_stock_show', 'depend' => '', 'data' => serialize($normal_stock_show)],
        ['template' => $tpl, 'name' => 'normal_sales_show', 'depend' => '', 'data' => serialize($normal_sales_show)],
        ['template' => $tpl, 'name' => 'normal_des_show',   'depend' => '', 'data' => serialize($normal_des_show)],
        ['template' => $tpl, 'name' => 'card_soldout_show', 'depend' => '', 'data' => serialize($card_soldout_show)],
        ['template' => $tpl, 'name' => 'card_type_show', 'depend' => '', 'data' => serialize($card_type_show)],
        ['template' => $tpl, 'name' => 'card_buy_style', 'depend' => '', 'data' => serialize($card_buy_style)],
        ['template' => $tpl, 'name' => 'card_buy_text', 'depend' => '', 'data' => serialize($card_buy_text)],
        ['template' => $tpl, 'name' => 'normal_float_help_show', 'depend' => '', 'data' => serialize($normal_float_help_show)],
        ['template' => $tpl, 'name' => 'float_help_top', 'depend' => '', 'data' => serialize($float_help_top)],
        ['template' => $tpl, 'name' => 'float_help_icon', 'depend' => '', 'data' => serialize($float_help_icon)],
        ['template' => $tpl, 'name' => 'detail_cover_show', 'depend' => '', 'data' => serialize($detail_cover_show)],
        ['template' => $tpl, 'name' => 'detail_title_show', 'depend' => '', 'data' => serialize($detail_title_show)],
        ['template' => $tpl, 'name' => 'detail_sales_show', 'depend' => '', 'data' => serialize($detail_sales_show)],
        ['template' => $tpl, 'name' => 'detail_stock_show', 'depend' => '', 'data' => serialize($detail_stock_show)],
        ['template' => $tpl, 'name' => 'detail_price_show', 'depend' => '', 'data' => serialize($detail_price_show)],
        ['template' => $tpl, 'name' => 'single_sales_show', 'depend' => '', 'data' => serialize($single_sales_show)],
        ['template' => $tpl, 'name' => 'single_stock_show', 'depend' => '', 'data' => serialize($single_stock_show)],
        ['template' => $tpl, 'name' => 'single_price_show', 'depend' => '', 'data' => serialize($single_price_show)],
        ['template' => $tpl, 'name' => 'single_float_help_show', 'depend' => '', 'data' => serialize($single_float_help_show)],
        ['template' => $tpl, 'name' => 'detail_float_help_show', 'depend' => '', 'data' => serialize($detail_float_help_show)],
        ['template' => $tpl, 'name' => 'header_menu_show', 'depend' => '', 'data' => serialize($header_menu_show)],
        ['template' => $tpl, 'name' => 'header_search_show', 'depend' => '', 'data' => serialize($header_search_show)],
        ['template' => $tpl, 'name' => 'header_user_show', 'depend' => '', 'data' => serialize($header_user_show)],
        ['template' => $tpl, 'name' => 'header_order_show', 'depend' => '', 'data' => serialize($header_order_show)],
        ['template' => $tpl, 'name' => 'header_help_show', 'depend' => '', 'data' => serialize($header_help_show)],
        ['template' => $tpl, 'name' => 'shop_header_bg', 'depend' => '', 'data' => serialize($shop_header_bg)],
        ['template' => $tpl, 'name' => 'shop_title_color', 'depend' => '', 'data' => serialize($shop_title_color)],
        ['template' => $tpl, 'name' => 'shop_subtitle_color', 'depend' => '', 'data' => serialize($shop_subtitle_color)],
        ['template' => $tpl, 'name' => 'shop_nav_active_color', 'depend' => '', 'data' => serialize($shop_nav_active_color)],
        ['template' => $tpl, 'name' => 'shop_nav_active_bg', 'depend' => '', 'data' => serialize($shop_nav_active_bg)],
        ['template' => $tpl, 'name' => 'nav_items', 'depend' => '', 'data' => serialize($nav_items)],
        ['template' => $tpl, 'name' => 'mobile_menu_show', 'depend' => '', 'data' => serialize($mobile_menu_show)],
        ['template' => $tpl, 'name' => 'mobile_search_show', 'depend' => '', 'data' => serialize($mobile_search_show)],
        ['template' => $tpl, 'name' => 'mobile_user_show', 'depend' => '', 'data' => serialize($mobile_user_show)],
        ['template' => $tpl, 'name' => 'mobile_help_show', 'depend' => '', 'data' => serialize($mobile_help_show)],
        ['template' => $tpl, 'name' => 'footer_show', 'depend' => '', 'data' => serialize($footer_show)],
        ['template' => $tpl, 'name' => 'service_qq', 'depend' => '', 'data' => serialize($service_qq)],
        ['template' => $tpl, 'name' => 'service_wechat', 'depend' => '', 'data' => serialize($service_wechat)],
        ['template' => $tpl, 'name' => 'after_sale_notice', 'depend' => '', 'data' => serialize($after_sale_notice)],
        ['template' => $tpl, 'name' => 'contact_presale_url', 'depend' => '', 'data' => serialize($contact_presale_url)],
        ['template' => $tpl, 'name' => 'contact_aftersale_url', 'depend' => '', 'data' => serialize($contact_aftersale_url)],
        ['template' => $tpl, 'name' => 'faq_list', 'depend' => '', 'data' => serialize($faq_list)],
    ];
    
    $tplOptions->insert('data', $data, true);
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $isStationSave = substr($script, -18) === '/user/template.php';
    if (!$isStationSave) {
        Option::updateOption('shop_header_bg', $shop_header_bg);
        Option::updateOption('shop_title_color', $shop_title_color);
        Option::updateOption('shop_subtitle_color', $shop_subtitle_color);
        Option::updateOption('shop_nav_active_color', $shop_nav_active_color);
        Option::updateOption('shop_nav_active_bg', $shop_nav_active_bg);
        Cache::getInstance()->updateCache('options');
    }
    
    Output::ok();
}

