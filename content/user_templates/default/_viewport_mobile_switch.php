<?php defined('DC_ROOT') || exit('access denied!'); ?>
<script>
(function(){
    var KEY = 'dc_default_user_mobile_app';
    var BP = 768;
    var path = window.location.pathname || '';
    if (!/\/user(?:\/|$)/i.test(path)) return;
    var doc = document;
    var root = doc.documentElement;
    var width = Math.min(window.innerWidth || root.clientWidth || 0, root.clientWidth || window.innerWidth || 0);
    if (!width) return;
    var shouldMobile = width <= BP;
    var currentMobile = <?= isMobile() ? 'true' : 'false' ?>;
    function setCookie(val) {
        doc.cookie = KEY + '=' + val + '; path=/; max-age=' + (val === '1' ? 2592000 : 0) + '; SameSite=Lax';
    }
    function removeParam(url, name) {
        url.searchParams.delete(name);
        var qs = url.searchParams.toString();
        return url.pathname + (qs ? '?' + qs : '') + url.hash;
    }
    if (shouldMobile && !currentMobile) {
        setCookie('1');
        var mobileUrl = new URL(window.location.href);
        mobileUrl.searchParams.set('__dc_user_mobile_app', '1');
        window.location.replace(mobileUrl.pathname + '?' + mobileUrl.searchParams.toString() + mobileUrl.hash);
        return;
    }
    if (!shouldMobile && currentMobile && /(?:^|;\s*)dc_default_user_mobile_app=1(?:;|$)/.test(doc.cookie)) {
        setCookie('0');
        window.location.replace(removeParam(new URL(window.location.href), '__dc_user_mobile_app'));
    }
})();
</script>
