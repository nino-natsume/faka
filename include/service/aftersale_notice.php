<?php

/**
 * Service: AftersaleNotice
 * 售后通知服务 - 处理售后邮件和Webhook通知
 */

class AftersaleNotice {

    /**
     * 发送售后申请通知
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @return bool
     */
    public static function sendAftersaleNotification($aftersaleData, $orderData) {
        $emailSent = false;
        $webhookSent = false;
        
        // 发送邮件通知
        $emailSent = self::sendAftersaleEmail($aftersaleData, $orderData);
        
        // 发送Webhook通知
        $webhookSent = self::sendAftersaleWebhook($aftersaleData, $orderData);
        
        return $emailSent || $webhookSent;
    }

    /**
     * 发送售后邮件通知
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @return bool
     */
    private static function sendAftersaleEmail($aftersaleData, $orderData) {
        // 检查邮件配置
        if (!self::isEmailConfigured()) {
            return false;
        }
        
        $notifyEmail = Option::get('aftersale_notify_email');
        if (empty($notifyEmail) || !checkMail($notifyEmail)) {
            return false;
        }
        
        // 构建邮件内容
        $title = "【售后通知】新的售后申请 - 订单号：" . $aftersaleData['out_trade_no'];
        $content = self::buildEmailContent($aftersaleData, $orderData);
        
        try {
            return Notice::sendMail($notifyEmail, $title, $content);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 发送售后Webhook通知
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @return bool
     */
    private static function sendAftersaleWebhook($aftersaleData, $orderData) {
        $webhookUrl = Option::get('aftersale_webhook_url');
        if (empty($webhookUrl)) {
            return false;
        }
        
        // 构建Webhook数据
        $webhookData = self::buildWebhookData($aftersaleData, $orderData, $webhookUrl);
        
        try {
            return self::sendWebhookRequest($webhookUrl, $webhookData);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 构建邮件内容
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @return string
     */
    private static function buildEmailContent($aftersaleData, $orderData) {
        $typeMap = [
            'cant_use' => '不会使用',
            'invalid' => '无效商品',
            'fraud' => '欺诈骗钱',
            'kami_error' => '卡密错误',
            'other' => '其他问题'
        ];
        
        $typeText = $typeMap[$aftersaleData['type']] ?? $aftersaleData['type'];
        $createTime = date('Y-m-d H:i:s', $aftersaleData['create_time']);
        
        $content = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>";
        $content .= "<h2 style='color: #e74c3c;'>新的售后申请</h2>";
        $content .= "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        $content .= "<p><strong>订单号：</strong>" . htmlspecialchars($aftersaleData['out_trade_no']) . "</p>";
        $content .= "<p><strong>商品名称：</strong>" . htmlspecialchars($aftersaleData['goods_title']) . "</p>";
        $content .= "<p><strong>售后类型：</strong>" . htmlspecialchars($typeText) . "</p>";
        $content .= "<p><strong>问题描述：</strong>" . nl2br(htmlspecialchars($aftersaleData['reason'])) . "</p>";
        $content .= "<p><strong>联系方式：</strong>" . htmlspecialchars($aftersaleData['contact']) . "</p>";
        $content .= "<p><strong>申请时间：</strong>" . $createTime . "</p>";
        $content .= "</div>";
        
        // 添加图片信息
        if (!empty($aftersaleData['images'])) {
            $images = json_decode($aftersaleData['images'], true);
            if (is_array($images) && !empty($images)) {
                $content .= "<p><strong>补充图片：</strong></p>";
                $content .= "<div style='margin: 10px 0;'>";
                foreach ($images as $image) {
                    $content .= "<p>• " . htmlspecialchars($image) . "</p>";
                }
                $content .= "</div>";
            }
        }
        
        $content .= "<div style='margin-top: 20px; padding: 10px; background: #e8f4fd; border-radius: 5px;'>";
        $content .= "<p style='margin: 0; color: #2c3e50;'><strong>提醒：</strong>请及时处理用户的售后申请，保持良好的客户服务体验。</p>";
        $content .= "</div>";
        $content .= "</div>";
        
        return $content;
    }

    /**
     * 构建Webhook数据
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @param string $webhookUrl Webhook URL
     * @return array
     */
    private static function buildWebhookData($aftersaleData, $orderData, $webhookUrl = '') {
        $typeMap = [
            'cant_use' => '不会使用',
            'invalid' => '无效商品',
            'fraud' => '欺诈骗钱',
            'kami_error' => '卡密错误',
            'other' => '其他问题'
        ];
        
        $images = [];
        if (!empty($aftersaleData['images'])) {
            $decoded = json_decode($aftersaleData['images'], true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }
        
        // 检测是否是企业微信Webhook
        if (strpos($webhookUrl, 'qyapi.weixin.qq.com') !== false) {
            return self::buildWechatWorkWebhookData($aftersaleData, $orderData, $typeMap, $images);
        }
        
        // 默认格式
        return [
            'event' => 'aftersale_submitted',
            'timestamp' => time(),
            'data' => [
                'aftersale_id' => $aftersaleData['id'] ?? null,
                'order_id' => $aftersaleData['order_id'],
                'order_list_id' => $aftersaleData['order_list_id'],
                'out_trade_no' => $aftersaleData['out_trade_no'],
                'goods_title' => $aftersaleData['goods_title'],
                'type' => $aftersaleData['type'],
                'type_text' => $typeMap[$aftersaleData['type']] ?? $aftersaleData['type'],
                'reason' => $aftersaleData['reason'],
                'contact' => $aftersaleData['contact'],
                'images' => $images,
                'status' => $aftersaleData['status'],
                'create_time' => $aftersaleData['create_time'],
                'create_time_text' => date('Y-m-d H:i:s', $aftersaleData['create_time'])
            ]
        ];
    }
    
    /**
     * 构建企业微信Webhook数据
     * @param array $aftersaleData 售后申请数据
     * @param array $orderData 订单数据
     * @param array $typeMap 类型映射
     * @param array $images 图片数组
     * @return array
     */
    private static function buildWechatWorkWebhookData($aftersaleData, $orderData, $typeMap, $images) {
        $typeText = $typeMap[$aftersaleData['type']] ?? $aftersaleData['type'];
        $createTime = date('Y-m-d H:i:s', $aftersaleData['create_time']);
        
        // 构建markdown格式的消息内容
        $content = "## 🔔 新的售后申请\n\n";
        $content .= "**订单号：** `{$aftersaleData['out_trade_no']}`\n\n";
        $content .= "**商品名称：** {$aftersaleData['goods_title']}\n\n";
        $content .= "**售后类型：** <font color=\"warning\">{$typeText}</font>\n\n";
        $content .= "**问题描述：** {$aftersaleData['reason']}\n\n";
        $content .= "**联系方式：** {$aftersaleData['contact']}\n\n";
        $content .= "**申请时间：** {$createTime}\n\n";
        
        if (!empty($images)) {
            $content .= "**补充图片：**\n";
            foreach ($images as $image) {
                $content .= "- {$image}\n";
            }
            $content .= "\n";
        }
        
        $content .= "> 请及时处理用户的售后申请，保持良好的客户服务体验。";
        
        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $content
            ]
        ];
    }

    /**
     * 发送Webhook请求
     * @param string $url Webhook URL
     * @param array $data 数据
     * @return bool
     */
    private static function sendWebhookRequest($url, $data) {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // 优先使用cURL
        if (function_exists('curl_init')) {
            return self::sendWebhookWithCurl($url, $jsonData);
        }
        
        // 备用方案：使用file_get_contents
        return self::sendWebhookWithFileGetContents($url, $jsonData);
    }
    
    /**
     * 使用cURL发送Webhook请求
     * @param string $url Webhook URL
     * @param string $jsonData JSON数据
     * @return bool
     */
    private static function sendWebhookWithCurl($url, $jsonData) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: DCSHOP-AftersaleNotice/1.0',
                'Content-Length: ' . strlen($jsonData)
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,  // 禁用SSL证书验证
            CURLOPT_SSL_VERIFYHOST => false,  // 禁用SSL主机验证
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // 记录调试信息（可选）
        if (!empty($error)) {
            error_log("Webhook cURL Error: " . $error);
        }
        
        // 检查HTTP状态码
        return $httpCode >= 200 && $httpCode < 300;
    }
    
    /**
     * 使用file_get_contents发送Webhook请求（备用方案）
     * @param string $url Webhook URL
     * @param string $jsonData JSON数据
     * @return bool
     */
    private static function sendWebhookWithFileGetContents($url, $jsonData) {
        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'User-Agent: DCSHOP-AftersaleNotice/1.0',
                    'Content-Length: ' . strlen($jsonData)
                ],
                'method' => 'POST',
                'content' => $jsonData,
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        // 检查HTTP响应码
        if (isset($http_response_header)) {
            $statusLine = $http_response_header[0];
            if (strpos($statusLine, '200') !== false || 
                strpos($statusLine, '201') !== false || 
                strpos($statusLine, '202') !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查邮件配置是否完整
     * @return bool
     */
    private static function isEmailConfigured() {
        $smtpMail = Option::get('smtp_mail');
        $smtpPw = Option::get('smtp_pw');
        $smtpServer = Option::get('smtp_server');
        $smtpPort = Option::get('smtp_port');
        
        return !empty($smtpMail) && !empty($smtpPw) && !empty($smtpServer) && !empty($smtpPort);
    }
}