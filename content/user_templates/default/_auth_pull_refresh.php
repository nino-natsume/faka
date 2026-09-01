<?php
/**
 * 移动端认证页（登录/注册/找回密码）下拉刷新组件
 * 用法: 在 </body> 前 include 本文件
 */
defined('DC_ROOT') || exit('access denied!');
?>
<div class="m-pull-indicator" id="mPullIndicator" aria-hidden="true">
    <i class="ri-refresh-line"></i>
</div>
<style>
html,body{overscroll-behavior-y:none;}
.m-pull-indicator{
    position:fixed;
    top:calc(env(safe-area-inset-top,0px) + 8px);
    left:50%;
    width:42px;height:42px;
    border-radius:999px;
    background:rgba(255,255,255,.96);
    color:var(--c-primary,#2196F3);
    box-shadow:0 8px 24px rgba(15,23,42,.14);
    border:1px solid rgba(226,232,240,.96);
    display:flex;align-items:center;justify-content:center;
    z-index:1005;
    opacity:0;pointer-events:none;
    transform:translate3d(-50%,-22px,0) scale(.8);
    transition:opacity .18s ease,transform .18s cubic-bezier(.22,.61,.36,1);
    will-change:transform,opacity;
}
.m-pull-indicator i{
    display:flex;align-items:center;justify-content:center;
    width:20px;height:20px;font-size:20px;line-height:1;font-style:normal;
}
.m-pull-indicator i::before{
    display:block;line-height:1;transition:transform .2s ease;
    transform-origin:center center;will-change:transform;
}
.m-pull-indicator.is-visible{opacity:1;}
.m-pull-indicator.is-ready i::before{transform:rotate(180deg);}
.m-pull-indicator.is-refreshing{opacity:1;}
.m-pull-indicator.is-refreshing i::before{animation:mPullSpin .75s linear infinite;}
@keyframes mPullSpin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
</style>
<script>
(function(){
    if(window.__mPullRefreshBound)return;
    window.__mPullRefreshBound=true;
    var ind=document.getElementById('mPullIndicator');
    if(!ind)return;
    var startY=0,startX=0,pullDist=0,pulling=false,refreshing=false;
    var startBuf=30,trigDist=110,maxDist=136,holdDist=68;
    var scrollEl=document.querySelector('.m-auth-page')||document.documentElement;
    function scrollTop(){return scrollEl.scrollTop||0;}
    function ease(d){if(d<=0)return 0;if(d<=130)return d*.42;return Math.min(maxDist,54.6+(d-130)*.34);}
    function setProgress(d,r){
        var p=Math.max(0,Math.min(1,d/trigDist));
        var ty=-22+Math.min(r?56:84,d*.72);
        var sc=r?1:(.8+p*.2);
        ind.style.transform='translate3d(-50%,'+ty.toFixed(1)+'px,0) scale('+sc.toFixed(3)+')';
        ind.style.opacity=p>0?Math.min(1,.24+p*.76).toFixed(3):'';
    }
    function reset(){
        pullDist=0;pulling=false;
        ind.classList.remove('is-visible','is-ready','is-refreshing');
        ind.style.transform='';ind.style.opacity='';
    }
    function isScroll(t){
        var el=t;
        while(el&&el!==document.body&&el!==document.documentElement){
            if(el===scrollEl){el=el.parentElement;continue;}
            var ov=el.style.overflowY||window.getComputedStyle(el).overflowY;
            if((ov==='auto'||ov==='scroll')&&el.scrollHeight>el.clientHeight+1)return true;
            el=el.parentElement;
        }
        return false;
    }
    document.addEventListener('touchstart',function(e){
        if(refreshing||e.touches.length!==1)return;
        if(window.getComputedStyle(document.body).position==='fixed')return;
        if(isScroll(e.target))return;
        if(scrollTop()>0){pulling=false;return;}
        startY=e.touches[0].clientY;startX=e.touches[0].clientX;pullDist=0;pulling=true;
    },{passive:true});
    document.addEventListener('touchmove',function(e){
        if(!pulling||refreshing||e.touches.length!==1)return;
        var dy=e.touches[0].clientY-startY,dx=e.touches[0].clientX-startX;
        if(Math.abs(dx)>Math.abs(dy)&&pullDist===0)return;
        if(dy<=0){if(pullDist>0)reset();return;}
        if(scrollTop()>0){reset();return;}
        var eff=dy-startBuf;
        if(eff<=0){ind.classList.remove('is-visible','is-ready');ind.style.transform='';ind.style.opacity='';e.preventDefault();return;}
        pullDist=ease(eff);
        ind.classList.add('is-visible');
        ind.classList.toggle('is-ready',pullDist>=trigDist);
        setProgress(pullDist,false);
        e.preventDefault();
    },{passive:false});
    document.addEventListener('touchend',function(){
        if(!pulling||refreshing)return;
        pulling=false;
        if(pullDist>=trigDist){
            refreshing=true;
            ind.classList.remove('is-ready');
            void ind.offsetWidth;
            ind.classList.add('is-visible','is-refreshing');
            setProgress(holdDist,true);
            setTimeout(function(){location.reload();},650);
            return;
        }
        reset();
    },{passive:true});
    document.addEventListener('touchcancel',function(){if(!refreshing)reset();},{passive:true});
})();
</script>
