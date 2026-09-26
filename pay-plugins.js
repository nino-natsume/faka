// ============================================================
// 支付插件注册表 — 对齐原版 App\Service\Pay::getPlugins() 的返回形状
//
// 原版在 PHP 里扫插件目录、读各插件的 Config/Info.php + Config/Submit.php，
// 配置值写在插件目录的配置文件里。Workers 没有可写文件系统、也不能在运行期
// 加载代码，所以这里把「插件元数据 + 配置表单定义」固化成静态注册表，
// 插件的可变配置值则存 D1 的 acg_pay_config（每 handle 多配置档）。
//
// 字段形状与原版一致，前端原版 controller/pay/plugin.js 可直接消费：
//   {
//     id:     '<handle>',                    // 支付接口的 handle 指向它
//     info:   { name, version, author, description, options: {} },
//     submit: [ 表单字段... ],                // 数组形态，免 eval
//   }
//   submit 的每个字段: { type, name, title, tips?, placeholder?, required?, value? }
//   type 取值见 common/js/component/form.js: input/password/number/textarea/
//                    select/radio/checkbox/switch/date/editor/...
//
// 新增插件 = 往 PAY_PLUGINS 里加一条 + 在 api.js 的下单分支里实现对应协议。
// 配置项的键名要和下单时读取的键名一致（见 api.js 的 epayTrade）。
// ============================================================

export const PAY_PLUGINS = [
  {
    id: '#system',
    info: {
      name: '余额支付',
      version: '1.0.0',
      author: '系统内置',
      description: '使用站内余额结算，不经过任何第三方网关，下单即时到账',
      options: { '站内结算': '无需第三方', '实时到账': '下单即完成', '零手续费': '不额外扣费' },
    },
    // 余额由系统直接扣，不需要任何配置
    submit: [],
    system: true,
  },
  {
    id: 'Epay',
    info: {
      name: '易支付',
      version: '1.0.0',
      author: '彩虹云',
      description: '对接易支付（彩虹云）聚合网关，一套商户号走支付宝、微信、QQ 钱包等通道',
      options: { '聚合网关': '一次对接多通道', 'MD5签名': '标准易支付协议', '302跳转': '无需本站收银台' },
    },
    submit: [
      {
        type: 'input',
        name: 'gateway',
        title: '网关地址',
        tips: '易支付网关根地址，结尾不要带斜杠，例如 https://pay.example.com',
        placeholder: 'https://pay.example.com',
        required: true,
        regex: { value: '^https?://[^\\s/]+(?:/[^\\s]*)?$', message: '网关地址必须是 http(s) 开头、不含空格的地址' },
      },
      {
        type: 'input',
        name: 'pid',
        title: '商户 PID',
        tips: '易支付分配给你的商户号',
        placeholder: '1000',
        required: true,
      },
      {
        type: 'password',
        name: 'key',
        title: '商户密钥',
        tips: '用于 MD5 签名，保存后不再明文展示',
        placeholder: '商户密钥',
        required: true,
      },
    ],
  },
];

const BY_HANDLE = new Map(PAY_PLUGINS.map((p) => [p.id, p]));

// 取插件定义；未注册的 handle 返回 null（调用方据此判定「支付插件不存在」）
export function findPayPlugin(handle) {
  return BY_HANDLE.get(String(handle ?? '')) || null;
}

// 已注册的 handle 集合，保存支付接口时用它代替「读插件目录」做存在性校验
export function isPayPluginHandle(handle) {
  return BY_HANDLE.has(String(handle ?? ''));
}
