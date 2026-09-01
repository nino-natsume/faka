<?php
defined('DC_ROOT') || exit('access denied!');
$action = Input::getStrVar('action');
$db = Database::getInstance();
$db_prefix = DB_PREFIX;
$timestamp = time();
?>
<?php if(User::isAdmin() && $action == 'del'): ?>
    <?php
    $ids = Input::postStrVar('ids');
    $goods_id = Input::postIntVar('goods_id');
    $res = $db->fetch_all("select id, sku from {$db_prefix}goods_general where id in ({$ids})");
    $sale = Input::getStrVar('sale', 'n');
    $skus = $db->fetch_all("select * from {$db_prefix}skus where goods_id = {$goods_id}");
//    output::error('test');
    try {
        $db->beginTransaction();
        if($sale == 'n'){
            $db->query("DELETE FROM {$db_prefix}goods_general WHERE id IN ({$ids})");
            foreach($res as $val){
                foreach($skus as $v){
                    if($v['sku'] == $val['sku']){
                        $db->query("UPDATE `{$db_prefix}goods` SET `stock` = stock - {$v['stock']} WHERE `id` = {$goods_id}");
                    }
                }
                $db->query("UPDATE `{$db_prefix}skus` SET `stock` = 0 WHERE `goods_id` = {$goods_id} and `sku` = '{$val['sku']}'");
            }
        }else{
            $db->query("DELETE FROM {$db_prefix}goods_general_sale WHERE id IN ({$ids})");
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }
    output::ok();
    ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'add_ajax'): ?>
<?php

$goods_id = Input::postIntVar('goods_id');
$sku = Input::postStrVar('sku', '0');
$content = Input::postStrVar('content');
$num = Input::postIntVar('num');
if(empty($content)){
    output::error('请输入卡密');
}
if(empty($num) || $num <= 0){
    output::error('请输入可用数量');
}
$goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
if($goods['is_sku'] == 'y') {
    if (empty($sku)) {
        output::error('请选择商品规格');
    }
    $skus_result = $db->once_fetch_array("select * from {$db_prefix}skus where goods_id = {$goods_id} and sku = '{$sku}'");
}
$exists_template = $db->once_fetch_array("select id from {$db_prefix}goods_general where goods_id = {$goods_id} and sku = '{$sku}' order by id asc");
if(!empty($exists_template)){
    output::error('该规格已存在模板卡密，请直接在列表中编辑卡密内容或可用次数');
}

//output::error('测试');
try {
    $db->beginTransaction();

    $db->query("delete from {$db_prefix}goods_general where goods_id = {$goods_id} and sku = '{$sku}'");
    $db->query("INSERT INTO `{$db_prefix}goods_general` (`goods_id`, `sku`, `content`, `create_time`) VALUES ({$goods_id}, '{$sku}','{$content}', {$timestamp})");

    $db->query("UPDATE `{$db_prefix}skus` SET `stock` = {$num} WHERE `goods_id` = {$goods_id} and `sku` = '{$sku}'");
    if($goods['is_sku'] == 'y'){
        $num = $goods['stock'] - $skus_result['stock'] + $num;
    }
    $db->query("UPDATE `{$db_prefix}goods` SET `stock` = {$num} WHERE `id` = {$goods_id}");

    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    output::error($e->getMessage());
}
output::ok();
?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'add'): ?>
<?php
$goods_id = Input::getIntVar('goods_id');
$goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
$skus = $db->fetch_all("select goods_id, sku, stock stock_count from {$db_prefix}skus where goods_id={$goods_id}");
$sku = $db->fetch_all("select * from {$db_prefix}sku_value");
$template_list = $db->fetch_all("select id, sku from {$db_prefix}goods_general where goods_id = {$goods_id} order by id asc");
$template_sku_map = [];
foreach($template_list as $val){
    if(!isset($template_sku_map[(string)$val['sku']])){
        $template_sku_map[(string)$val['sku']] = (int)$val['id'];
    }
}

$sku_list = [];
$available_sku_count = 0;
foreach($skus as $val){
    if($val['sku'] == 0){
        continue;
    }
    $item = [];
    $item['goods_id'] = $val['goods_id'];
    $item['sku'] = $val['sku'];
    $item['sku_name'] = '';
    $item['stock_count'] = $val['stock_count'];
    $item['is_exists'] = isset($template_sku_map[(string)$val['sku']]);
    if(!$item['is_exists']){
        $available_sku_count++;
    }
    $s = explode('-', $val['sku']);
    foreach($sku as $v){
        foreach($s as $sv){
            if($v['id'] == $sv){
                $item['sku_name'] .= $v['name'] . "；";
            }
        }
    }
    $sku_list[] = $item;
}
$all_template_locked = $goods['is_sku'] == 'y' ? $available_sku_count === 0 : isset($template_sku_map['0']);
$has_existing_template = !empty($template_sku_map);
?>
<?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_general&action=add_ajax" id="form">
        <div style="padding: 25px;" id="open-box">

            <?php if($all_template_locked): ?>
                <div style="margin-bottom: 15px; padding: 10px 12px; border: 1px solid #ffd591; border-radius: 6px; background: #fff7e6; color: #d46b08; line-height: 1.8;">当前商品的模板卡密已配置完成，请返回库存列表使用“编辑卡密”修改内容或可用次数。</div>
            <?php elseif($has_existing_template): ?>
                <div style="margin-bottom: 15px; padding: 10px 12px; border: 1px solid #91d5ff; border-radius: 6px; background: #e6f7ff; color: #0958d9; line-height: 1.8;">已配置模板的规格不可重复新增，请选择未配置规格；如需调整内容或次数，请返回库存列表编辑。</div>
            <?php endif; ?>

            <?php if($goods['is_sku'] == 'y'): ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">请选择商品规格</label>
                    <div class="layui-input-block">
                        <select class="layui-input" name="sku">
                            <option value="">商品规格</option>
                            <?php foreach($sku_list as $val): ?>
                                <option value="<?= $val['sku'] ?>" <?= !empty($val['is_exists']) ? 'disabled' : '' ?>><?= $val['sku_name'] ?> (<?= $val['stock_count'] ?>)<?= !empty($val['is_exists']) ? '【已配置，请编辑】' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="layui-form-item">
                <label class="layui-form-label">可用次数</label>
                <div class="layui-input-block">
                    <input type="number" name="num" class="layui-input" value="" <?= $all_template_locked ? 'disabled' : '' ?> />
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">卡密内容</label>
                <div class="layui-input-block">
                    <textarea rows="8" class="layui-textarea" name="content" <?= $all_template_locked ? 'disabled' : '' ?>></textarea>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $goods_id ?>" name="goods_id"/>
        </div>
        <div style="width: 100%; height: 50px;"></div>
        <div class="" id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit" <?= $all_template_locked ? 'disabled' : '' ?>>立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary" <?= $all_template_locked ? 'disabled' : '' ?>>重置</button>
            </div>
        </div>
    </form>



    <script>
        layui.use(['table'], function(){
            var $ = layui.$;
            var form = layui.form;
            form.on('submit(submit)', function(data){
                var field = data.field; // 获取表单全部字段值
                var url = $('#form').attr('action');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: field,
                    dataType: "json",
                    success: function (e) {
                        if(!e || e.code != 0){
                            return layer.msg((e && e.msg) || '操作失败');
                        }
                        parent.layer.close('add')
                        parent.layer.msg('商品已添加');
                        window.parent.ws_table.reload();
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                    }
                });
                return false; // 阻止默认 form 跳转
            });



        })
        var maxHeight = $(window.parent).innerHeight() * 0.75;
        var minHeight = $(window.parent).innerHeight() * 0.5;



        // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
        $("#open-box").css({
            "max-height": maxHeight + "px", // 单位必须加 px
            "min-height": minHeight + "px", // 单位必须加 px
            "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
        });
    </script>
    <?php include View::getAdmView('open_foot'); ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'edit_ajax'): ?>
<?php
    $goods_id = Input::postIntVar('goods_id');
    $sku = Input::postStrVar('sku', '0');
    $content = Input::postStrVar('content');
    $num = Input::postIntVar('num');
    $type = Input::postStrVar('type');
    $stock_id = Input::postIntVar('stock_id');
    if(empty($content)){
        output::error('请输入卡密');
    }
    if((empty($num) || $num <= 0) && $type == 'ws'){
        output::error('请输入可用数量');
    }
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    if($goods['is_sku'] == 'y' && $type == 'ws') {
        if (empty($sku)) {
            output::error('请选择商品规格');
        }
        $skus_result = $db->once_fetch_array("select * from {$db_prefix}skus where goods_id = {$goods_id} and sku = '{$sku}'");
    }

//output::error('测试');
    try {
        $db->beginTransaction();
        if($type == 'ws'){
            $db->query("delete from {$db_prefix}goods_general where goods_id = {$goods_id} and sku = '{$sku}'");
            $db->query("INSERT INTO `{$db_prefix}goods_general` (`goods_id`, `sku`, `content`, `create_time`) VALUES ({$goods_id}, '{$sku}','{$content}', {$timestamp})");

            $db->query("UPDATE `{$db_prefix}skus` SET `stock` = {$num} WHERE `goods_id` = {$goods_id} and `sku` = '{$sku}'");
            if($goods['is_sku'] == 'y'){
                $num = $goods['stock'] - $skus_result['stock'] + $num;
            }
            $db->query("UPDATE `{$db_prefix}goods` SET `stock` = {$num} WHERE `id` = {$goods_id}");
        }else{
            $db->query("UPDATE `{$db_prefix}goods_general_sale` SET `content` = '{$content}' WHERE `id` = {$stock_id}");
        }


        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }
    output::ok();
?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'edit'): ?>
    <?php
    $goods_id = Input::getIntVar('goods_id');
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    $stock_id = Input::getIntVar('stock_id');
    $type = Input::getStrVar('type', 'ws');
    if($type == 'ws'){
        $stock = $db->once_fetch_array("select * from {$db_prefix}goods_general where id = {$stock_id}");
    }else{
        $stock = $db->once_fetch_array("select * from {$db_prefix}goods_general_sale where id = {$stock_id}");
    }

    $skus = $db->once_fetch_array("select * from {$db_prefix}skus where goods_id = {$goods_id} and sku = '{$stock['sku']}'");
//    d($skus);die;
    ?>
    <?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_general&action=edit_ajax" id="form">
        <div style="padding: 25px;" id="open-box">

            <?php if($type == 'ws'): ?>
            <div class="layui-form-item">
                <label class="layui-form-label">可用次数</label>
                <div class="layui-input-block">
                    <input type="number" name="num" class="layui-input" value="<?= $skus['stock'] ?>" />
                </div>
            </div>
            <?php endif; ?>
            <div class="layui-form-item">
                <label class="layui-form-label">卡密内容</label>
                <div class="layui-input-block">
                    <textarea rows="8" class="layui-textarea" name="content"><?= $stock['content'] ?></textarea>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $stock_id ?>" name="stock_id"/>
            <input type="hidden" value="<?= $stock['sku'] ?>" name="sku" />
            <input type="hidden" value="<?= $stock['goods_id'] ?>" name="goods_id" />
            <input type="hidden" value="<?= $type ?>" name="type" />
        </div>
        <div style="width: 100%; height: 50px;"></div>
        <div class="" id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>



    <script>
        layui.use(['table'], function(){
            var $ = layui.$;
            var form = layui.form;
            form.on('submit(submit)', function(data){
                var field = data.field; // 获取表单全部字段值
                var url = $('#form').attr('action');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: field,
                    dataType: "json",
                    success: function (e) {
                        if(e.code == 400){
                            return layer.msg(e.msg);
                        }
                        parent.layer.close('edit')
                        parent.layer.msg('商品已保存');
                        window.parent.ws_table.reload();
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                    }
                });
                return false; // 阻止默认 form 跳转
            });



        })
        var maxHeight = $(window.parent).innerHeight() * 0.75;
        var minHeight = $(window.parent).innerHeight() * 0.5;



        // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
        $("#open-box").css({
            "max-height": maxHeight + "px", // 单位必须加 px
            "min-height": minHeight + "px", // 单位必须加 px
            "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
        });
    </script>
    <?php include View::getAdmView('open_foot'); ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'index'): ?>
<?php
$goods_id = Input::getIntVar('goods_id');
$sale = Input::getStrVar('sale');
$page = Input::getIntVar('page');
$page_count = Input::getIntVar('limit');
$limit_start = ($page - 1) * $page_count;
$keyword = Input::getStrVar('keyword');
$sku = Input::getStrVar('sku');
$where = "where goods_id = {$goods_id}";
if(!empty($sku)){
    $where .= " and sku = '{$sku}'";
}
if(!empty($keyword)){
    $where .= " and content like '%{$keyword}%'";
}
if($sale == 'y'){
    $list = $db->fetch_all("select * from {$db_prefix}goods_general_sale {$where} order by id desc limit {$limit_start}, {$page_count}");
    $count = $db->once_fetch_array("select count(id) kami_count from {$db_prefix}goods_general_sale {$where}")['kami_count'];
}else{
    $list = $db->fetch_all("select * from {$db_prefix}goods_general {$where} order by id desc limit {$limit_start}, {$page_count}");
    $count = $db->once_fetch_array("select count(id) kami_count from {$db_prefix}goods_general {$where}")['kami_count'];
}

$skus = $db->fetch_all("select * from {$db_prefix}skus where goods_id = {$goods_id}");
$sku_value = $db->fetch_all("select * from {$db_prefix}sku_value");
//d($skus);die;
foreach($list as $key => $val){
    $list[$key]['sku_name'] = '';
    $list[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
    foreach($skus as $s){
        if($s['sku'] == $val['sku'] && $sale == 'n'){
            $list[$key]['num'] = $s['stock'];
        }
    }
    if($val['sku'] == 0){
        continue;
    }
    $s = explode('-', $val['sku']);
    foreach($sku_value as $v){
        foreach($s as $sv){
            if($v['id'] == $sv){
                $list[$key]['sku_name'] .= $v['name'] . "；";
            }
        }
    }
}


output::data($list, $count);
?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'index_ws'): ?>
<style>
    body{
        overflow: hidden;
    }
    .layui-table-view-1 .layui-table-body .layui-table tr .layui-table-cell{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1; /* 限制显示2行 */
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .layui-table-tool-temp{
        padding-right: 0;
    }
</style>
<div style="padding: 20px 10px;" id="open-box">

    <div class="layui-tabs" style="margin-bottom: 12px;" lay-options="{trigger: false}">
        <ul class="layui-tabs-header">
            <li class="layui-this"><a href="stock.php?action=index_ws&goods_id=<?= $goods_id ?>">未售出</a></li>
            <li><a href="stock.php?action=index_ys&goods_id=<?= $goods_id ?>">已售出</a></li>
        </ul>
    </div>
    <form class="layui-form" style="float: right;">
        <div class="layui-form-item">
            <div class="layui-inline">
                <?php if($goods['is_sku'] == 'y'): ?>
                    <div class="layui-input-inline layui-input-wrap">
                        <select name="sku" id="search-sku">
                            <option value="">商品规格</option>
                            <?php foreach($sku_list as $val): ?>
                                <option value="<?= $val['sku'] ?>"><?= $val['sku_name'] ?> (<?= $val['stock_count'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="layui-input-inline layui-input-wrap">
                    <input id="search-keyword" type="text" value="" name="keyword" placeholder="卡密内容" lay-affix="clear" class="layui-input">
                </div>
                <div class="layui-form-mid" style="padding: 0!important;">
                    <button class="layui-btn" lay-submit lay-filter="index-search">搜索</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </div>

    </form>

    <table class="layui-hide" id="stock_index_ws" lay-filter="stock_index_ws"></table>
</div>
<script type="text/html" id="toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh">
            <i class="fa fa-refresh" style=""></i>
        </button>
        <button type="button" class="layui-btn" lay-event="add">添加</button>
        <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">
            删除
        </button>
    </div>
</script>

<script type="text/html" id="stock">
    <div class="layui-clear-space">
        {{ d.stock }}
    </div>
</script>

<script type="text/html" id="operate">
    <div class="layui-clear-space">
        <!--        <a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="copy">复制</a>-->
        <a class="layui-btn" lay-event="edit">编辑</a>
        <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
    </div>
</script>


<script>
    layui.use(['table'], function(){
        var table = layui.table;
        var form = layui.form;
        // 创建渲染实例
        window.ws_table = table.render({
            elem: '#stock_index_ws',
            autoSort: false,
            url: '/?plugin=goods_general&action=index&sale=n&goods_id=<?= $goods_id ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
            toolbar: '#toolbar',
            limits: [10,20,30,50,100],
            page: true,
            lineStyle: 'height: 30px;',
            defaultToolbar: [],
            maxHeight : 'full-78',
            cols: [[
                {type: 'checkbox', fixed: 'left'},
                {field:'sku_name', title:'规格', align: 'center', minWidth: 100 },
                {field:'content', title:'卡密内容', align: 'center', minWidth: 200 },
                {field:'num', title:'可用数量', minWwidth: 150, align: 'center'},
                {field:'create_time', title:'添加时间', minWwidth: 150, align: 'center'},
                {fixed: 'right', title:'操作', templet: '#operate', minWidth: 210, align: 'center'}
            ]],

            error: function(res, msg){
                console.log(res, msg)
            }
        });
        <?php if($goods['is_sku'] == 'n'): ?>
        // 设置对应列的显示或隐藏
        table.hideCol('stock_index_ws', {
            field: 'sku_name', // 对应表头的 field 属性值
            hide: true // `true` or `false`
        });
        <?php endif; ?>

        // 搜索提交
        form.on('submit(index-search)', function(data){
            var field = data.field; // 获得表单字段
            // 执行搜索重载
            table.reload('stock_index_ws', {
                page: {
                    curr: 1 // 重新从第 1 页开始
                },
                where: field // 搜索的字段
            });
            return false; // 阻止默认 form 跳转
        });



        // 工具栏事件
        table.on('toolbar(stock_index_ws)', function(obj){
            var id = obj.config.id;
            var checkStatus = table.checkStatus(id);
            var othis = lay(this);
            if(obj.event == 'refresh'){
                table.reload(id);
            }
            if(obj.event == 'del'){
                var data = checkStatus.data;
                if(data.length == 0){
                    return false;
                }
                var ids = $.map(data, function(item) {
                    return item.id; // 提取每个对象的uid
                }).join(',');
                layer.confirm('确定要删除的数据？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '/?plugin=goods_general&action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg);
                            }
                            layer.msg('已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }
            if(obj.event == 'add'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'add',
                    title: '添加库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'layui-layer-molv',
                    content: '/?plugin=goods_general&action=add&goods_id=<?= $goods_id ?>',
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
            if(obj.event == 'export'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['500px', 'auto'];
                layer.open({
                    id: 'export',
                    title: '导出库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'layui-layer-molv',
                    content: '/?plugin=goods_general&action=export&sale=n&goods_id=<?= $goods_id ?>',
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
        });

        // 触发单元格工具事件
        table.on('tool(stock_index_ws)', function(obj){ // 双击 toolDouble
            var data = obj.data; // 获得当前行数据
            var id = obj.config.id;
            if(obj.event == 'del'){
                layer.confirm('确定删除？', {
                    btn: ['确认', '取消'], // 按钮
                    icon: 3,             // 图标，3表示问号
                    title: '温馨提示'
                }, function(index) {
                    layer.close(index); // 关闭对话框
                    $.ajax({
                        url: '/?plugin=goods_general&action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.id, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                return layer.msg(e.msg);
                            }
                            layer.msg('已删除');
                            table.reload(id);
                        },
                        error: function(err) {
                            layer.msg(err.responseJSON.msg);
                        }
                    });
                });
            }

            if(obj.event === 'edit'){
                let isMobile = window.innerWidth < 768;
                let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                layer.open({
                    id: 'edit',
                    title: '编辑库存',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'layui-layer-molv',
                    content: '/?plugin=goods_general&goods_id=<?= $goods_id ?>&action=edit&stock_id=' + data.id,
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标
                    }
                });
            }
            if(obj.event === 'stock'){
                let isMobile = window.innerWidth < 1200;
                let area = isMobile ? ['98%', 'auto']  : ['1000px', 'auto'];
                layer.open({
                    id: 'stock',
                    title: '库存管理',
                    type: 2,
                    area: area,
                    // skin: 'layui-layer-win10',
                    skin: 'layui-layer-molv',
                    content: 'stock.php?action=index&goods_id=' + data.uid,
                    fixed: false, // 不固定
                    maxmin: true,
                    shadeClose: true,
                    success: function(layero, index, that){
                        layer.iframeAuto(index); // 让 iframe 高度自适应
                        that.offset(); // 重新自适应弹层坐标

                    }
                });
            }
        });

        // 触发排序事件
        table.on('sort(stock_index_ws)', function(obj){
            console.log(obj.field); // 当前排序的字段名
            console.log(obj.type); // 当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
            console.log(this); // 当前排序的 th 对象

            // 尽管我们的 table 自带排序功能，但并没有请求服务端。
            // 有些时候，你可能需要根据当前排序的字段，重新向后端发送请求，从而实现服务端排序，如：
            table.reload('stock_index_ws', {
                initSort: obj, // 记录初始排序，如果不设的话，将无法标记表头的排序状态。
                where: { // 请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    field: obj.field, // 排序字段
                    order: obj.type // 排序方式
                }
            });
        });

        // 触发表格复选框选择
        table.on('checkbox(stock_index_ws)', function(obj){
            var id = obj.config.id;
            var checkData = table.checkStatus(id).data;
            console.log(checkData)
            if(checkData.length == 0){
                $('#toolbar-del').addClass('layui-btn-disabled');
            }else{
                $('#toolbar-del').removeClass('layui-btn-disabled');
            }
        });

        // 分页栏事件
        table.on('pagebar(stock_index_ws)', function(obj){
            alert()
            console.log(obj); // 查看对象所有成员
            console.log(obj.config); // 当前实例的配置信息
            console.log(obj.event); // 属性 lay-event 对应的值
        });


        // 表头自定义元素工具事件 --- 2.8.8+
        table.on('colTool(test)', function(obj){
            var event = obj.event;
            console.log(obj);
            if(event === 'email-tips'){
                layer.alert(layui.util.escape(JSON.stringify(obj.col)), {
                    title: '当前列属性选项'
                });
            }
        });


    });
</script>
<script>

    var maxHeight = $(window.parent).innerHeight() * 0.75;
    $("#open-box").css({
        "max-height": maxHeight + "px", // 单位必须加 px
        "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
    });
</script>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'index_ys'): ?>
    <style>
        body{
            overflow: hidden;
        }
        .layui-table-view-1 .layui-table-body .layui-table tr .layui-table-cell{
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1; /* 限制显示2行 */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .layui-table-tool-temp{
            padding-right: 0;
        }
    </style>
    <div style="padding: 20px 10px;" id="open-box">

        <div class="layui-tabs" style="margin-bottom: 12px;" lay-options="{trigger: false}">
            <ul class="layui-tabs-header">
                <li><a href="stock.php?action=index_ws&goods_id=<?= $goods_id ?>">未售出</a></li>
                <li class="layui-this"><a href="stock.php?action=index_ys&goods_id=<?= $goods_id ?>">已售出</a></li>
            </ul>
        </div>
        <form class="layui-form" style="float: right;">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <?php if($goods['is_sku'] == 'y'): ?>
                        <div class="layui-input-inline layui-input-wrap">
                            <select name="sku" id="search-sku">
                                <option value="">商品规格</option>
                                <?php foreach($sku_list as $val): ?>
                                    <option value="<?= $val['sku'] ?>"><?= $val['sku_name'] ?> (<?= $val['stock_count'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="layui-input-inline layui-input-wrap">
                        <input id="search-keyword" type="text" value="" name="keyword" placeholder="卡密内容" lay-affix="clear" class="layui-input">
                    </div>
                    <div class="layui-form-mid" style="padding: 0!important;">
                        <button class="layui-btn" lay-submit lay-filter="index-search">搜索</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </div>

        </form>

        <table class="layui-hide" id="stock_index_ws" lay-filter="stock_index_ws"></table>
    </div>
    <script type="text/html" id="toolbar">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-primary layui-border-green" lay-event="refresh">
                <i class="fa fa-refresh" style=""></i>
            </button>
<!--            <button type="button" class="layui-btn" lay-event="add">添加</button>-->
            <button id="toolbar-del" class="layui-btn  layui-bg-red layui-btn-disabled" lay-event="del">
                删除
            </button>
<!--            <button type="button" class="layui-btn layui-bg-blue" lay-event="export">导出</button>-->
        </div>
    </script>

    <script type="text/html" id="stock">
        <div class="layui-clear-space">
            {{ d.stock }}
        </div>
    </script>

    <script type="text/html" id="operate">
        <div class="layui-clear-space">
            <!--        <a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="copy">复制</a>-->
            <a class="layui-btn" lay-event="edit">编辑</a>
            <a class="layui-btn layui-bg-red" lay-event="del">删除</a>
        </div>
    </script>


    <script>
        layui.use(['table'], function(){
            var table = layui.table;
            var form = layui.form;
            // 创建渲染实例
            window.ws_table = table.render({
                elem: '#stock_index_ws',
                autoSort: false,
                url: '/?plugin=goods_general&action=index&sale=y&goods_id=<?= $goods_id ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
                toolbar: '#toolbar',
                limits: [10,20,30,50,100],
                page: true,
                lineStyle: 'height: 30px;',
                defaultToolbar: [],
                maxHeight : 'full-78',
                cols: [[
                    {type: 'checkbox', fixed: 'left'},
                    {field:'sku_name', title:'规格', align: 'center', minWidth: 100 },
                    {field:'content', title:'卡密内容', align: 'center', minWidth: 200 },
                    {field:'num', title:'数量', minWwidth: 150, align: 'center'},
                    {field:'create_time', title:'出售时间', minWwidth: 150, align: 'center'},
                    {fixed: 'right', title:'操作', templet: '#operate', minWidth: 210, align: 'center'}
                ]],

                error: function(res, msg){
                    console.log(res, msg)
                }
            });
            <?php if($goods['is_sku'] == 'n'): ?>
            // 设置对应列的显示或隐藏
            table.hideCol('stock_index_ws', {
                field: 'sku_name', // 对应表头的 field 属性值
                hide: true // `true` or `false`
            });
            <?php endif; ?>

            // 搜索提交
            form.on('submit(index-search)', function(data){
                var field = data.field; // 获得表单字段
                // 执行搜索重载
                table.reload('stock_index_ws', {
                    page: {
                        curr: 1 // 重新从第 1 页开始
                    },
                    where: field // 搜索的字段
                });
                return false; // 阻止默认 form 跳转
            });



            // 工具栏事件
            table.on('toolbar(stock_index_ws)', function(obj){
                var id = obj.config.id;
                var checkStatus = table.checkStatus(id);
                var othis = lay(this);
                if(obj.event == 'refresh'){
                    table.reload(id);
                }
                if(obj.event == 'del'){
                    var data = checkStatus.data;
                    if(data.length == 0){
                        return false;
                    }
                    var ids = $.map(data, function(item) {
                        return item.id; // 提取每个对象的uid
                    }).join(',');
                    layer.confirm('确定要删除的数据？', {
                        btn: ['确认', '取消'], // 按钮
                        icon: 3,             // 图标，3表示问号
                        title: '温馨提示'
                    }, function(index) {
                        layer.close(index); // 关闭对话框
                        $.ajax({
                            url: '/?plugin=goods_general&action=del&sale=y',
                            type: 'POST',
                            dataType: 'json',
                            data: { ids: ids, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                            success: function(e) {
                                if(e.code == 400){
                                    return layer.msg(e.msg)
                                }
                                layer.msg('已删除');
                                table.reload(id);
                            },
                            error: function(err) {
                                layer.msg(err.responseJSON.msg);
                            }
                        });
                    });
                }
                if(obj.event == 'add'){
                    let isMobile = window.innerWidth < 768;
                    let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                    layer.open({
                        id: 'add',
                        title: '添加库存',
                        type: 2,
                        area: area,
                        // skin: 'layui-layer-win10',
                        skin: 'layui-layer-molv',
                        content: '/?plugin=goods_general&action=add&goods_id=<?= $goods_id ?>',
                        fixed: false, // 不固定
                        maxmin: true,
                        shadeClose: true,
                        success: function(layero, index, that){
                            layer.iframeAuto(index); // 让 iframe 高度自适应
                            that.offset(); // 重新自适应弹层坐标
                        }
                    });
                }


            });

            // 触发单元格工具事件
            table.on('tool(stock_index_ws)', function(obj){ // 双击 toolDouble
                var data = obj.data; // 获得当前行数据
                var id = obj.config.id;
                if(obj.event == 'del'){
                    layer.confirm('确定删除？', {
                        btn: ['确认', '取消'], // 按钮
                        icon: 3,             // 图标，3表示问号
                        title: '温馨提示'
                    }, function(index) {
                        layer.close(index); // 关闭对话框
                        $.ajax({
                            url: '/?plugin=goods_general&action=del&sale=y',
                            type: 'POST',
                            dataType: 'json',
                            data: { ids: data.id, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                            success: function(e) {
                                if(e.code == 400){
                                    return layer.msg(e.msg);
                                }
                                layer.msg('已删除');
                                table.reload(id);
                            },
                            error: function(err) {
                                layer.msg(err.responseJSON.msg);
                            }
                        });
                    });
                }

                if(obj.event === 'edit'){
                    let isMobile = window.innerWidth < 768;
                    let area = isMobile ? ['98%', 'auto']  : ['700px', 'auto'];
                    layer.open({
                        id: 'edit',
                        title: '编辑库存',
                        type: 2,
                        area: area,
                        // skin: 'layui-layer-win10',
                        skin: 'layui-layer-molv',
                        content: '/?plugin=goods_general&type=ys&action=edit&stock_id=' + data.id,
                        fixed: false, // 不固定
                        maxmin: true,
                        shadeClose: true,
                        success: function(layero, index, that){
                            layer.iframeAuto(index); // 让 iframe 高度自适应
                            that.offset(); // 重新自适应弹层坐标
                        }
                    });
                }
                if(obj.event === 'stock'){
                    let isMobile = window.innerWidth < 1200;
                    let area = isMobile ? ['98%', 'auto']  : ['1000px', 'auto'];
                    layer.open({
                        id: 'stock',
                        title: '库存管理',
                        type: 2,
                        area: area,
                        // skin: 'layui-layer-win10',
                        skin: 'layui-layer-molv',
                        content: 'stock.php?action=index&goods_id=' + data.uid,
                        fixed: false, // 不固定
                        maxmin: true,
                        shadeClose: true,
                        success: function(layero, index, that){
                            layer.iframeAuto(index); // 让 iframe 高度自适应
                            that.offset(); // 重新自适应弹层坐标

                        }
                    });
                }
            });

            // 触发排序事件
            table.on('sort(stock_index_ws)', function(obj){
                console.log(obj.field); // 当前排序的字段名
                console.log(obj.type); // 当前排序类型：desc（降序）、asc（升序）、null（空对象，默认排序）
                console.log(this); // 当前排序的 th 对象

                // 尽管我们的 table 自带排序功能，但并没有请求服务端。
                // 有些时候，你可能需要根据当前排序的字段，重新向后端发送请求，从而实现服务端排序，如：
                table.reload('stock_index_ws', {
                    initSort: obj, // 记录初始排序，如果不设的话，将无法标记表头的排序状态。
                    where: { // 请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                        field: obj.field, // 排序字段
                        order: obj.type // 排序方式
                    }
                });
            });

            // 触发表格复选框选择
            table.on('checkbox(stock_index_ws)', function(obj){
                var id = obj.config.id;
                var checkData = table.checkStatus(id).data;
                console.log(checkData)
                if(checkData.length == 0){
                    $('#toolbar-del').addClass('layui-btn-disabled');
                }else{
                    $('#toolbar-del').removeClass('layui-btn-disabled');
                }
            });

            // 分页栏事件
            table.on('pagebar(stock_index_ws)', function(obj){
                alert()
                console.log(obj); // 查看对象所有成员
                console.log(obj.config); // 当前实例的配置信息
                console.log(obj.event); // 属性 lay-event 对应的值
            });


            // 表头自定义元素工具事件 --- 2.8.8+
            table.on('colTool(test)', function(obj){
                var event = obj.event;
                console.log(obj);
                if(event === 'email-tips'){
                    layer.alert(layui.util.escape(JSON.stringify(obj.col)), {
                        title: '当前列属性选项'
                    });
                }
            });


        });
    </script>
    <script>

        var maxHeight = $(window.parent).innerHeight() * 0.75;
        $("#open-box").css({
            "max-height": maxHeight + "px", // 单位必须加 px
            "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
        });
    </script>
<?php endif; ?>
