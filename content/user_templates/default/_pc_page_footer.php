<?php
/**
 * PC 页面底部信息（版权 + 备案号）
 * 在各 PC 页面模板末尾 include 此文件
 */
defined('DC_ROOT') || exit('access denied!');
$_pf_footer_info = Option::get('footer_info');
$_pf_icp = Option::get('icp');
if (!empty($_pf_footer_info) || !empty($_pf_icp)):
?>
<div class="page-footer-info">
    <?php if (!empty($_pf_footer_info)): ?><span><?= $_pf_footer_info ?></span><?php endif; ?>
    <?php if (!empty($_pf_footer_info) && !empty($_pf_icp)): ?><span class="pfi-dot">·</span><?php endif; ?>
    <?php if (!empty($_pf_icp)): ?><a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener"><?= htmlspecialchars($_pf_icp) ?></a><?php endif; ?>
</div>
<?php endif; ?>
