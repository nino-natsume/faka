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
    $res = $db->fetch_all("select id, sku from {$db_prefix}goods_once where id in ({$ids})");
    $sale = Input::getStrVar('sale', 'n');
    try {
        $db->beginTransaction();
        $db->query("DELETE FROM {$db_prefix}goods_once WHERE id IN ({$ids})");
        if($sale == 'n'){
            $ids = explode(",", $ids);
            $ids_count = count($ids);
            foreach($res as $val){
                $db->query("UPDATE `{$db_prefix}goods` SET `stock` = stock - 1 WHERE `id` = {$goods_id}");
                $db->query("UPDATE `{$db_prefix}skus` SET `stock` = stock - 1 WHERE `goods_id` = {$goods_id} and `sku` = '{$val['sku']}'");
            }
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

//output::error('test');
$goods_id = Input::postIntVar('goods_id');
$sku = Input::postStrVar('sku', '0');
$batch_no = Input::postStrVar('batch_no', null);
$content = Input::postStrVar('content');
$content = array_filter(explode("\n", $content));
if(empty($content)){
    output::error('请输入卡密');
}
$goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
if($goods['is_sku'] == 'y') {
    if (empty($sku)) {
        output::error('请选择商品规格');
    }
}
try {
    $db->beginTransaction();
    foreach($content as $val){
        $db->query("INSERT INTO `{$db_prefix}goods_once` (`goods_id`, `sku`, `batch_no`,`content`, `create_time`) VALUES ({$goods_id}, '{$sku}', '{$batch_no}','{$val}', {$timestamp})");
    }
    $count = count($content);
    $db->query("UPDATE `{$db_prefix}skus` SET `stock` = stock + {$count} WHERE `goods_id` = {$goods_id} and `sku` = '{$sku}'");
    $db->query("UPDATE `{$db_prefix}goods` SET `stock` = stock + {$count} WHERE `id` = {$goods_id}");
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

$sku_list = [];
foreach($skus as $key => $val){
    $sku_list[$key]['sku_name'] = '';
    $sku_list[$key]['stock_count'] = $val['stock_count'];
    if($val['sku'] == 0){
        continue;
    }
    $sku_list[$key]['goods_id'] = $val['goods_id'];
    $sku_list[$key]['sku'] = $val['sku'];
    $sku_list[$key]['sku_name'] = '';
    $s = explode('-', $val['sku']);
    foreach($sku as $v){
        foreach($s as $sv){
            if($v['id'] == $sv){
                $sku_list[$key]['sku_name'] .= $v['name'] . "；";
            }
        }
    }
}
?>
<?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_once&action=add_ajax" id="form">
        <div style="padding: 25px;" id="open-box">

            <?php if($goods['is_sku'] == 'y'): ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">请选择商品规格</label>
                    <div class="layui-input-block">
                        <select class="layui-input" name="sku">
                            <option value="">商品规格</option>
                            <?php foreach($sku_list as $val): ?>
                                <option value="<?= $val['sku'] ?>"><?= $val['sku_name'] ?> (<?= $val['stock_count'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="layui-form-item">
                <label class="layui-form-label">批次号</label>
                <div class="layui-input-block">
                    <input type="text" name="batch_no" class="layui-input" value="" />
                    <span class="form-tips">主要用于搜索、导出时的筛选</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">卡密内容</label>
                <div class="layui-input-block">
                    <textarea rows="8" class="layui-textarea" name="content"></textarea>
                    <span class="form-tips">一行一个卡密，添加多个请使用回车键换行</span>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $goods_id ?>" name="goods_id"/>
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
                            return layer.msg(e.msg)
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
    $stock_id = Input::postIntVar('stock_id');
    $batch_no = Input::postStrVar('batch_no', null);
    $content = Input::postStrVar('content');

    $db->query("UPDATE `{$db_prefix}goods_once` SET `batch_no` = '{$batch_no}', `content` = '{$content}', `update_time` = {$timestamp} WHERE `id` = {$stock_id}");

    output::ok();
    ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'edit'): ?>
    <?php
    $goods_id = Input::getIntVar('goods_id');
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    $stock_id = Input::getIntVar('stock_id');
    $stock = $db->once_fetch_array("select * from {$db_prefix}goods_once where id = {$stock_id}");
//    d($goods_id); die;
    ?>
    <?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_once&action=edit_ajax" id="form">
        <div style="padding: 25px;" id="open-box">



            <div class="layui-form-item">
                <label class="layui-form-label">批次号</label>
                <div class="layui-input-block">
                    <input type="text" name="batch_no" class="layui-input" value="<?= $stock['batch_no'] ?>" />
                    <span class="form-tips">主要用于搜索、导出时的筛选</span>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">卡密内容</label>
                <div class="layui-input-block">
                    <textarea rows="8" class="layui-textarea" name="content"><?= $stock['content'] ?></textarea>
                </div>
            </div>
            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $stock_id ?>" name="stock_id"/>
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
                            return layer.msg(e.msg)
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
<?php if(User::isAdmin() && $action == 'export_ajax'): ?>
    <?php

    $goods_id = Input::postStrVar('goods_id');
    $sku = Input::postStrVar('sku');
    $export_range = Input::postStrVar('export_range');
    $export_num = Input::postIntVar('export_num');
    $batch_no = Input::postStrVar('batch_no');
    $is_delete = Input::postStrVar('is_delete');

    $start_time = Input::postStrVar('start_time', null);
    $end_time = Input::postStrVar('end_time', null);

    if($export_range == 'time'){
        if(empty($start_time) || empty($end_time)){
            output::error('请选择卡密添加时间');
        }
        if($start_time > $end_time){
            output::error('开始时间不能大于截止时间');
        }
    }


    $where = ' and sale_time is null';
    if($sku != 0 && !empty($sku)){
        $where .= " and sku='{$sku}'";
    }
    if($export_range == 'time'){
        $start_time .= ' 00:00:00';
        $end_time .= ' 23:59:59';
        $where .= " and create_time BETWEEN UNIX_TIMESTAMP('{$start_time}') and UNIX_TIMESTAMP('{$end_time}')";
    }
    if($export_range == 'batch_no'){
        if(empty($batch_no)){
            $where .= " and batch_no is null";
        }else{
            $where .= " and batch_no = '{$batch_no}'";
        }

    }

    $stock = $db->fetch_all("select * from {$db_prefix}goods_once where goods_id={$goods_id} {$where} order by id asc;");

    if(empty($stock)){
        output::error('无库存');
    }

    $sku_value = $db->fetch_all("select * from {$db_prefix}sku_value");

    $data = [];
    foreach($stock as $val){
        if($val['sku'] == 0){
            if($export_range == 'num'){
                if(!empty($data['默认规格']) && count($data['默认规格']) >= $export_num){
                    continue;
                }
            }
            $data['默认规格'][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }else{
            $temp = explode('-', $val['sku']);
            $sku_name = "";
            foreach($temp as $v){
                foreach($sku_value as $sv){
                    if($v == $sv['id']){
                        $sku_name .= $sv['name'] . "；";
                    }
                }
            }

            if($export_range == 'num'){
                if(!empty($data[$sku_name]) && count($data[$sku_name]) >= $export_num){
                    continue;
                }
            }

            $data[$sku_name][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }
    }
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    // 删除卡密
    try {
        $db->beginTransaction();
        if($is_delete == 1){
            foreach($data as $val){
                $ids = [];
                foreach($val as $v){
                    $ids[] = $v['id'];
                }
                $ids_count = count($ids);
                $ids = implode(',', $ids);
                $db->query("delete from {$db_prefix}goods_once where id in({$ids})");
                Log::info('导出并删除一次性卡密，数量：' . $ids_count);
                $db->query("UPDATE `{$db_prefix}goods` SET `stock` = stock - {$ids_count} WHERE `id` = {$goods_id}");
                $db->query("UPDATE `{$db_prefix}skus` SET `stock` = stock - {$ids_count} WHERE `goods_id` = {$goods_id} and `sku` = '{$v['sku']}'");
            }
        }

        $filename = "导出卡密_{$timestamp}_{$goods_id}.txt";
        $saveDir = DC_ROOT . '/content/em_temp/';
        // 保存库存导出记录
        $sql = "INSERT INTO `{$db_prefix}stock_export_log` (`filename`, `goods_id`, `create_time`) VALUES ('{$filename}', {$goods_id}, {$timestamp})";
        $db->query($sql);

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    $filePath = $saveDir . $filename;
    // 循环数组并写入文件
    $fileHandle = fopen($filePath, 'w');
    if ($fileHandle) {
        // 遍历数组，将每个卡密写入文件
        foreach ($data as $key => $val) {
            fwrite($fileHandle, "---- " . $key . "\n");
            foreach ($val as $v) {
                fwrite($fileHandle, $v['content'] . "\n");
            }
        }
        fclose($fileHandle);


        // 生成下载地址
        output::ok(DC_URL . 'admin/download.php?filename=' . $filename);
    } else {
        output::error('文件权限不足，请设置网站目录权限为755');
    }


    output::ok();
    ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'export_sales_ajax'): ?>
    <?php

    $goods_id = Input::postStrVar('goods_id');
    $sku = Input::postStrVar('sku');
    $export_range = Input::postStrVar('export_range');
    $export_num = Input::postIntVar('export_num');
    $batch_no = Input::postStrVar('batch_no');
    $is_delete = Input::postStrVar('is_delete');

    $start_time = Input::postStrVar('start_time', null);
    $end_time = Input::postStrVar('end_time', null);

    if($export_range == 'time'){
        if(empty($start_time) || empty($end_time)){
            output::error('请选择卡密添加时间');
        }
        if($start_time > $end_time){
            output::error('开始时间不能大于截止时间');
        }
    }


    $where = ' and sale_time is not null';
    if($sku != 0 && !empty($sku)){
        $where .= " and sku='{$sku}'";
    }
    if($export_range == 'time'){
        $start_time .= ' 00:00:00';
        $end_time .= ' 23:59:59';
        $where .= " and create_time BETWEEN UNIX_TIMESTAMP('{$start_time}') and UNIX_TIMESTAMP('{$end_time}')";
    }
    if($export_range == 'batch_no'){
        if(empty($batch_no)){
            $where .= " and batch_no is null";
        }else{
            $where .= " and batch_no = '{$batch_no}'";
        }

    }

    $stock = $db->fetch_all("select * from {$db_prefix}goods_once where goods_id={$goods_id} {$where} order by id asc;");

    if(empty($stock)){
        output::error('未查询到售出卡密');
    }

    $sku_value = $db->fetch_all("select * from {$db_prefix}sku_value");

    $data = [];
    foreach($stock as $val){
        if($val['sku'] == 0){
            if($export_range == 'num'){
                if(!empty($data['默认规格']) && count($data['默认规格']) >= $export_num){
                    continue;
                }
            }
            $data['默认规格'][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }else{
            $temp = explode('-', $val['sku']);
            $sku_name = "";
            foreach($temp as $v){
                foreach($sku_value as $sv){
                    if($v == $sv['id']){
                        $sku_name .= $sv['name'] . "；";
                    }
                }
            }

            if($export_range == 'num'){
                if(!empty($data[$sku_name]) && count($data[$sku_name]) >= $export_num){
                    continue;
                }
            }

            $data[$sku_name][] = [
                'content' => $val['content'],
                'id' => $val['id'],
                'sku' => $val['sku'],
                'goods_id' => $val['goods_id']
            ];
        }
    }
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    // 删除卡密
    try {
        $db->beginTransaction();
        if($is_delete == 1){
            foreach($data as $val){
                $ids = [];
                foreach($val as $v){
                    $ids[] = $v['id'];
                }
                $ids_count = count($ids);
                $ids = implode(',', $ids);
                $db->query("delete from {$db_prefix}goods_once where id in({$ids})");
                Log::info('导出并删除一次性已售出卡密，数量：' . $ids_count);
                
            }
        }

        $filename = "导出已售出卡密_{$timestamp}_{$goods_id}.txt";
        $saveDir = DC_ROOT . '/content/em_temp/';
        // 保存库存导出记录
        $sql = "INSERT INTO `{$db_prefix}stock_export_log` (`filename`, `goods_id`, `create_time`) VALUES ('{$filename}', {$goods_id}, {$timestamp})";
        $db->query($sql);

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        output::error($e->getMessage());
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    $filePath = $saveDir . $filename;
    // 循环数组并写入文件
    $fileHandle = fopen($filePath, 'w');
    if ($fileHandle) {
        // 遍历数组，将每个卡密写入文件
        foreach ($data as $key => $val) {
            fwrite($fileHandle, "---- " . $key . "\n");
            foreach ($val as $v) {
                fwrite($fileHandle, $v['content'] . "\n");
            }
        }
        fclose($fileHandle);


        // 生成下载地址
        output::ok(DC_URL . 'admin/download.php?filename=' . $filename);
    } else {
        output::error('文件权限不足，请设置网站目录权限为755');
    }


    output::ok();
    ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'export'): ?>
    <?php
    $goods_id = Input::getIntVar('goods_id');
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    ?>
    <?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
        #export_num_input, #export_time_input, #export_batch_no_input{
            display: none;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_once&action=export_ajax" id="form">
        <div style="padding: 25px;" id="open-box">
            <div class="layui-form-item">
                <label class="layui-form-label">导出范围</label>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="" title="全部导出" checked>
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="num" title="导出指定数量">
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="time" title="按添加时间导出">
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="batch_no" title="按批次号导出">
                </div>
            </div>
            <div class="layui-form-item" id="export_num_input">
                <label class="layui-form-label">导出卡密数量</label>
                <div class="layui-input-block">
                    <input type="number" name="export_num" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item" id="export_batch_no_input">
                <label class="layui-form-label">批次号</label>
                <div class="layui-input-block">
                    <input type="text" name="batch_no" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item" id="export_time_input">
                <label class="layui-form-label">卡密添加时间</label>
                <div class="layui-input-inline">
                    <input name="start_time" type="text" class="layui-input" id="start-laydate-type-datetime" placeholder="开始时间">
                </div>
                <div class="layui-input-inline">
                    <input name="end_time" type="text" class="layui-input" id="end-laydate-type-datetime" placeholder="截止时间">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">导出后操作</label>
                <div class="layui-input-block">
                    <input type="radio" name="is_delete" value="0" title="仅导出" checked>
                </div>
                <div class="layui-input-block">
                    <input type="radio" name="is_delete" value="1" title="导出并删除">
                </div>
            </div>

            <div style="width: 100%; height: 80px;"></div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $goods_id ?>" name="goods_id"/>
        </div>
        <div style="width: 100%; height: 50px;"></div>
        <div class="" id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">执行</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>



    <script>
        layui.use(['table'], function(){
            var $ = layui.$;
            var laydate = layui.laydate;
            var form = layui.form;
            form.on('submit(submit)', function(data){
                var load = layer.load(2);
                var field = data.field; // 获取表单全部字段值
                var url = $('#form').attr('action');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: field,
                    dataType: "json",
                    success: function (e) {
                        layer.close(load);
                        if(e.code == 400){
                            return layer.msg(e.msg)
                        }

                        parent.layer.close('export')
                        parent.layer.msg('已导出');
                        window.parent.ws_table.reload();
                        window.open(e.data)
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                        layer.close(load);
                    }
                });
                return false; // 阻止默认 form 跳转
            });

            form.on('radio(export_range)', function(data){
                var elem = data.elem; // 获得 radio 原始 DOM 对象
                var value = elem.value; // 获得 radio 值
                var othis = data.othis; // 获得 radio 元素被替换后的 jQuery 对象
                if(value == ''){
                    $('#export_num_input').hide();
                    $('#export_time_input').hide();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'num'){
                    $('#export_num_input').show();
                    $('#export_time_input').hide();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'time'){
                    $('#export_num_input').hide();
                    $('#export_time_input').show();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'batch_no'){
                    $('#export_batch_no_input').show();
                    $('#export_num_input').hide();
                    $('#export_time_input').hide();
                }
            });

            // 日期时间选择器
            laydate.render({
                elem: '#start-laydate-type-datetime',
                type: 'date'
            });
            laydate.render({
                elem: '#end-laydate-type-datetime',
                type: 'date'
            });

        })
        var maxHeight = $(window.parent).innerHeight() * 0.75;



        // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
        $("#open-box").css({
            "max-height": maxHeight + "px", // 单位必须加 px
            "overflow-y": "auto" // 内容超过 max-height 时显示垂直滚动条
        });
    </script>
    <?php include View::getAdmView('open_foot'); ?>
<?php endif; ?>
<?php if(User::isAdmin() && $action == 'export_sales'): ?>
    <?php
    $goods_id = Input::getIntVar('goods_id');
    $goods = $db->once_fetch_array("select * from {$db_prefix}goods where id = {$goods_id}");
    ?>
    <?php include View::getAdmView('open_head'); ?>
    <style>
        body{
            overflow: hidden;
        }
        #export_num_input, #export_time_input, #export_batch_no_input{
            display: none;
        }
    </style>


    <form class="layui-form " action="/?plugin=goods_once&action=export_sales_ajax" id="form">
        <div style="padding: 25px;" id="open-box">
            <div class="layui-form-item">
                <label class="layui-form-label">导出范围</label>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="" title="全部导出" checked>
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="num" title="导出指定数量">
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="time" title="按添加时间导出">
                </div>
                <div class="layui-input-block">
                    <input lay-filter="export_range" type="radio" name="export_range" value="batch_no" title="按批次号导出">
                </div>
            </div>
            <div class="layui-form-item" id="export_num_input">
                <label class="layui-form-label">导出卡密数量</label>
                <div class="layui-input-block">
                    <input type="number" name="export_num" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item" id="export_batch_no_input">
                <label class="layui-form-label">批次号</label>
                <div class="layui-input-block">
                    <input type="text" name="batch_no" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-form-item" id="export_time_input">
                <label class="layui-form-label">卡密添加时间</label>
                <div class="layui-input-inline">
                    <input name="start_time" type="text" class="layui-input" id="start-laydate-type-datetime" placeholder="开始时间">
                </div>
                <div class="layui-input-inline">
                    <input name="end_time" type="text" class="layui-input" id="end-laydate-type-datetime" placeholder="截止时间">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">导出后操作</label>
                <div class="layui-input-block">
                    <input type="radio" name="is_delete" value="0" title="仅导出" checked>
                </div>
                <div class="layui-input-block">
                    <input type="radio" name="is_delete" value="1" title="导出并删除">
                </div>
            </div>

            <div style="width: 100%; height: 80px;"></div>

            <input name="token" id="token" value="<?= LoginAuth::genToken() ?>" type="hidden"/>
            <input type="hidden" value="<?= $goods_id ?>" name="goods_id"/>
        </div>
        <div style="width: 100%; height: 50px;"></div>
        <div class="" id="form-btn">
            <div class="layui-input-block" style="margin: 0 auto;">
                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">执行</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>



    <script>
        layui.use(['table'], function(){
            var $ = layui.$;
            var laydate = layui.laydate;
            var form = layui.form;
            form.on('submit(submit)', function(data){
                var load = layer.load(2);
                var field = data.field; // 获取表单全部字段值
                var url = $('#form').attr('action');
                $.ajax({
                    type: "POST",
                    url: url,
                    data: field,
                    dataType: "json",
                    success: function (e) {
                        layer.close(load);
                        if(e.code == 400){
                            return layer.msg(e.msg)
                        }

                        parent.layer.close('export')
                        parent.layer.msg('已导出');
                        window.parent.ws_table.reload();
                        window.open(e.data)
                    },
                    error: function (xhr) {
                        layer.msg(JSON.parse(xhr.responseText).msg);
                        layer.close(load);
                    }
                });
                return false; // 阻止默认 form 跳转
            });

            form.on('radio(export_range)', function(data){
                var elem = data.elem; // 获得 radio 原始 DOM 对象
                var value = elem.value; // 获得 radio 值
                var othis = data.othis; // 获得 radio 元素被替换后的 jQuery 对象
                if(value == ''){
                    $('#export_num_input').hide();
                    $('#export_time_input').hide();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'num'){
                    $('#export_num_input').show();
                    $('#export_time_input').hide();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'time'){
                    $('#export_num_input').hide();
                    $('#export_time_input').show();
                    $('#export_batch_no_input').hide();
                }
                if(value == 'batch_no'){
                    $('#export_batch_no_input').show();
                    $('#export_num_input').hide();
                    $('#export_time_input').hide();
                }
            });

            // 日期时间选择器
            laydate.render({
                elem: '#start-laydate-type-datetime',
                type: 'date'
            });
            laydate.render({
                elem: '#end-laydate-type-datetime',
                type: 'date'
            });

        })
        var maxHeight = $(window.parent).innerHeight() * 0.75;



        // 2. 为 #open-box 设置 max-height，同时添加溢出滚动
        $("#open-box").css({
            "max-height": maxHeight + "px", // 单位必须加 px
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
$sku = Input::getStrVar('sku', '0');
$where = "where goods_id = {$goods_id}";
if(!empty($sku)){
    $where .= " and sku = '{$sku}'";
}
$where .= $sale == 'y' ? " and sale_time is not null" : " and sale_time is null";
if(!empty($keyword)){
    $where .= " and (batch_no like '%{$keyword}%' or content like '%{$keyword}%')";
}
//echo "select * from {$db_prefix}goods_once {$where} limit {$limit_start}, {$page_count}";die;
$list = $db->fetch_all("select * from {$db_prefix}goods_once {$where} order by id desc limit {$limit_start}, {$page_count}");

$sku_value = $db->fetch_all("select * from {$db_prefix}sku_value");
foreach($list as $key => $val){
    $list[$key]['sku_name'] = '';
    $list[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
    $list[$key]['sale_time'] = date('Y-m-d H:i:s', $val['sale_time']);
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

$count = $db->once_fetch_array("select count(id) kami_count from {$db_prefix}goods_once {$where}")['kami_count'];
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
                    <input id="search-keyword" type="text" value="" name="keyword" placeholder="卡密内容/批次号" lay-affix="clear" class="layui-input">
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
        <button type="button" class="layui-btn layui-bg-blue" lay-event="export">导出</button>
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
            url: '/?plugin=goods_once&action=index&sale=n&goods_id=<?= $goods_id ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
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
                {field:'batch_no', title:'批次号', minWwidth: 150, align: 'center'},
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
                        url: '/?plugin=goods_once&action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                layer.msg(e.msg);
                            }else{
                                layer.msg('已删除');
                                table.reload(id);
                            }

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
                    content: '/?plugin=goods_once&action=add&goods_id=<?= $goods_id ?>',
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
                    content: '/?plugin=goods_once&action=export&sale=n&goods_id=<?= $goods_id ?>',
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
                        url: '/?plugin=goods_once&action=del',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: data.id, goods_id: "<?= $goods_id ?>", token: '<?= LoginAuth::genToken() ?>' },
                        success: function(e) {
                            if(e.code == 400){
                                layer.msg(e.msg);
                            }else{
                                layer.msg('已删除');
                                table.reload(id);
                            }
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
                    content: '/?plugin=goods_once&action=edit&goods_id=<?= $goods_id ?>&stock_id=' + data.id,
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
                        <input id="search-keyword" type="text" value="" name="keyword" placeholder="卡密内容/批次号" lay-affix="clear" class="layui-input">
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
           <button type="button" class="layui-btn layui-bg-blue" lay-event="export">导出</button>
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
                url: '/?plugin=goods_once&action=index&sale=y&goods_id=<?= $goods_id ?>', // 此处为静态模拟数据，实际使用时需换成真实接口
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
                    {field:'batch_no', title:'批次号', minWwidth: 150, align: 'center'},
                    {field:'create_time', title:'添加时间', minWwidth: 150, align: 'center'},
                    {field:'sale_time', title:'出售时间', minWwidth: 150, align: 'center'},
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
                            url: '/?plugin=goods_once&action=del&sale=y',
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
                    content: '/?plugin=goods_once&action=export_sales&sale=y&goods_id=<?= $goods_id ?>',
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
                            url: '/?plugin=goods_once&action=del&sale=y',
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
                        content: '/?plugin=goods_once&action=edit&stock_id=' + data.id,
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