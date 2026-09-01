"use strict"

/**
 * jqurey 的动画扩展“缓进缓出”
 */
jQuery.extend(jQuery.easing, {
    easeInOut: function (x, t, b, c, d) {
        if ((t /= d / 2) < 1) return c / 2 * t * t + b
        return -c / 2 * ((--t) * (t - 2) - 1) + b
    }
})

var myBlog = {
    /**
     * 初始化
     */
    init: function () {
        this.tocAnalyse()  // toc目录生成
        this.readProgressInit()  // 文章阅读进度条
        this.shareInit()  // 文章分享/复制/二维码
        this.codeCopyInit()  // 代码块复制
        this.lazyLoadInit()  // 图片懒加载
        this.backToTopInit()  // 返回顶部
        this.mobileReadToolbarInit()  // 移动端阅读工具栏
        this.searchModalInit()  // 搜索弹层
        if ($("#comment-info").length === 0) {  // 大屏幕登录状态，评论框下两角变圆角
            $(".commentform #comment").css("height", "140px")
                .css('border-radius', '10px')
        }
        $("#commentform").attr("onsubmit", "return myBlog.comSubmitTip()")  // 评论提交在表单验证未通过的情况下是不能提交的
    },
    /**
     * 回复
     */
    toggleCommentInput: function ($t) {
        var $ele, getpid, $com_board;
        $ele = $t.closest(".comment-infos");
        getpid = String($t.closest(".comment").attr("id") || "0");
        $com_board = $("#comment-post");

        if (!/^\d+$/.test(getpid) || getpid === "0") {
            return;
        }

        if ($("#comment-pid").val() !== getpid) {
            $ele.after($com_board);
            $("#comment-pid").attr("value", getpid);
            $("#comments").addClass("com-bottom");
            $("#comment").focus();
        } else {
            $("#comment-pid").attr("value", "0");
            $("#comments").append($("#comment-post")).removeClass("com-bottom");
        }
    },

    /**
     * 手机点击展开导航按钮
     */
    navToggle: function ($t) {
        var $navbar = $("#navbarResponsive")
        var $board = $navbar.closest(".blog-header-menu-board")
        var isOpen = !$navbar.hasClass('is-open')
        $navbar.toggleClass('is-open', isOpen)
        $board.toggleClass('is-open', isOpen)
        $t.toggleClass('is-open', isOpen).attr('aria-expanded', isOpen ? 'true' : 'false')
    },
    /**
     * 定位大屏状态下的导航下拉框位置
     */
    calMargin: function ($t) {
        if (window.outerWidth < 992) return
        var $childMenu, menuWidth, count

        menuWidth = 135  // 大屏幕端的子导航下拉框宽度(px)，可根据需要修改
        count = ($t.outerWidth() - menuWidth) / 2 + "px"
        $childMenu = $t.siblings('.dropdown-menus')

        $childMenu.css("width", menuWidth + "px")
            .css("margin-left", count)
    },
    /**
     * 提交评论前对表单的验证
     */
    comTip: '', comSubmitTip: function (value) {
        if (value === 'judge') {
            let mailReg = /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/

            let mail = $('#info_m').val()
            let name = $('#info_n').val()
            let comment = $('#comment').val()

            if (typeof name !== "undefined" && $.trim(name || '') === '') {
                this.comTip = "请填写昵称！"
            } else if ($.trim(comment || '') === '') {
                this.comTip = "请填写评论内容！"
            } else if (typeof mail !== "undefined" && mail !== '' && !mailReg.test(mail)) {
                this.comTip = "邮箱格式错误！"
            } else {
                this.comTip = ''
            }
        } else {
            this.comSubmitTip('judge')
            if (this.comTip !== '') {
                alert(this.comTip)
                return false
            } else {
                return true
            }
        }
    },
    /**
     * 显示(隐藏)验证码模态窗
     */
    viewModal: function () {
        var $modal, $lock
        $modal = $(".modal-dialog,.lock-screen")
        $lock = $(".lock-screen")

        $('body,html').toggleClass('scroll-fix')
        $modal.fadeToggle()
        $("input[name='imgcode']").attr("autocomplete", "off")
    },
    /**
     * 搜索弹层
     */
    searchStorageKey: 'blogSearchHistory',
    searchModalInit: function () {
        if (!$('#blogSearchModal').length) return

        var self = this
        this.searchRenderHistory()
        $(document)
            .off('.blogSearch')
            .on('click.blogSearch', '[data-search-action]', function (e) {
                var action = $(this).attr('data-search-action')
                if (action === 'open' || action === 'close') {
                    e.preventDefault()
                }
                if (action === 'open') {
                    self.searchOpen()
                } else if (action === 'close') {
                    self.searchClose()
                }
            })
            .on('click.blogSearch', '.blog-search-history-item', function (e) {
                e.preventDefault()
                var keyword = $(this).attr('data-keyword') || $(this).text()
                $('#blogSearchInput').val(keyword)
                $('#blogSearchForm').trigger('submit')
            })
            .on('submit.blogSearch', '#blogSearchForm', function (e) {
                var $input = $('#blogSearchInput')
                var keyword = $.trim($input.val() || '')
                if (keyword === '') {
                    e.preventDefault()
                    self.shareToast('请输入搜索关键词')
                    $input.focus()
                    return false
                }
                $input.val(keyword)
                self.searchSaveHistory(keyword)
                return true
            })
            .on('keydown.blogSearch', function (e) {
                var target = e.target
                var tagName = target && target.tagName ? target.tagName.toLowerCase() : ''
                var isTyping = tagName === 'input' || tagName === 'textarea' || tagName === 'select' || (target && target.isContentEditable)
                if (e.key === 'Escape' || e.keyCode === 27) {
                    self.searchClose()
                    return
                }
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault()
                    self.searchOpen()
                    return
                }
                if (!isTyping && e.key === '/') {
                    e.preventDefault()
                    self.searchOpen()
                }
            })
    },
    searchOpen: function () {
        var $modal = $('#blogSearchModal')
        if (!$modal.length) return
        $modal.addClass('show').attr('aria-hidden', 'false')
        $('body,html').addClass('scroll-fix')
        this.searchRenderHistory()
        setTimeout(function () {
            $('#blogSearchInput').focus().select()
        }, 80)
    },
    searchClose: function () {
        $('#blogSearchModal').removeClass('show').attr('aria-hidden', 'true')
        $('body,html').removeClass('scroll-fix')
    },
    searchGetHistory: function () {
        try {
            var raw = localStorage.getItem(this.searchStorageKey)
            var list = raw ? JSON.parse(raw) : []
            return $.isArray(list) ? list : []
        } catch (e) {
            return []
        }
    },
    searchSaveHistory: function (keyword) {
        keyword = $.trim(keyword || '')
        if (!keyword) return
        var list = this.searchGetHistory().filter(function (item) {
            return item !== keyword
        })
        list.unshift(keyword)
        list = list.slice(0, 6)
        try {
            localStorage.setItem(this.searchStorageKey, JSON.stringify(list))
        } catch (e) {}
        this.searchRenderHistory(list)
    },
    searchRenderHistory: function (list) {
        var $box = $('#blogSearchHistory')
        if (!$box.length) return
        list = list || this.searchGetHistory()
        if (!list.length) {
            $box.html('<div class="blog-search-history-empty"><i class="ri-time-line"></i>暂无搜索历史</div>')
            return
        }
        var html = '<div class="blog-search-history-title"><i class="ri-history-line"></i>最近搜索</div><div class="blog-search-history-list">'
        for (var i = 0; i < list.length; i++) {
            html += '<button type="button" class="blog-search-history-item" data-keyword="' + this.tocEscape(list[i]) + '">' + this.tocEscape(list[i]) + '</button>'
        }
        html += '</div>'
        $box.html(html)
    },
    /**
     * 点击刷新验证码
     */
    captchaRefresh: function ($t) {
        var timestamp = new Date().getTime()
        var blogUrl = $("#blog_url").val();

        $t.attr("src", blogUrl + "/include/lib/checkcode.php?" + timestamp)
    },
    /**
     * 图片在点击时，将略缩图转化为原图
     */
    toggleImgSrc: function ($t) {
        var sourceSrc = $t.parent().attr('sourcesrc')
        if (!sourceSrc) return
        $t.addClass('zoomFocus')
        $t.attr('src2', $t.attr('src'))
        $t.attr('src', sourceSrc)
    },
    /**
     * 归档下拉框的值被改变，跳转到相应日期文章的链接
     */
    jumpLink: function ($t) {
        $(window).attr("location", $t.val())
    },
    /**
     * 文章阅读进度条
     *
     * 只在文章详情页存在 #dcshopEchoLog / #emlogEchoLog 时启用。
     */
    readProgressRaf: null,
    readProgressArticle: function () {
        var $article = $('#dcshopEchoLog')
        return $article.length ? $article : $('#emlogEchoLog')
    },
    readProgressInit: function () {
        var $article = this.readProgressArticle()
        if (!$article.length) return

        $('.blog-reading-progress').remove()
        $('body').prepend('<div class="blog-reading-progress" id="blogReadingProgress"><div class="blog-reading-progress-bar" role="progressbar" aria-label="阅读进度" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div></div>')

        var self = this
        var update = function () {
            if (self.readProgressRaf) return
            var runner = function () {
                self.readProgressRaf = null
                self.readProgressUpdate($article)
            }
            self.readProgressRaf = window.requestAnimationFrame ? window.requestAnimationFrame(runner) : setTimeout(runner, 16)
        }

        $(window).off('.blogReadProgress').on('scroll.blogReadProgress resize.blogReadProgress orientationchange.blogReadProgress', update)
        $article.find('img').on('load.blogReadProgress', update)
        update()
        setTimeout(update, 300)
        setTimeout(update, 1000)
    },
    readProgressUpdate: function ($article) {
        if (!$article || !$article.length) return

        var $bar = $('.blog-reading-progress-bar')
        if (!$bar.length) return

        var scrollTop = $(window).scrollTop()
        var viewportHeight = $(window).height()
        var articleTop = $article.offset().top
        var articleHeight = Math.max($article.outerHeight(true), viewportHeight)
        var start = Math.max(0, articleTop - 90)
        var end = articleTop + articleHeight - viewportHeight * 0.6
        var total = Math.max(1, end - start)
        var progress = ((scrollTop - start) / total) * 100

        progress = Math.max(0, Math.min(100, progress))
        $bar.css('transform', 'scaleX(' + (progress / 100) + ')')
            .attr('aria-valuenow', Math.round(progress))
        $('#blogReadingProgress').toggleClass('is-started', progress > 0).toggleClass('is-complete', progress >= 99.5)
        this.mobileReadToolbarUpdateProgress(progress)
    },
    /**
     * toc 效果
     *
     * 文章目录会优先识别 #dcshopEchoLog，并兼容旧模板的 #emlogEchoLog。
     * 默认自动读取正文中的 h2/h3；文章中写 [toc] 或 <!--[toc]--> 时会强制显示目录。
     */
    tocFlag: /(?:\[toc\]|<!--\s*\[toc\]\s*-->)/i,
    tocFlagReplace: /(?:\[toc\]|<!--\s*\[toc\]\s*-->)/ig,
    tocArray: [],
    tocEscape: function (str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (s) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s]
        })
    },
    tocSlug: function (text, index) {
        var slug = String(text || '').toLowerCase()
            .replace(/[^\w\u4e00-\u9fa5-]+/g, '-')
            .replace(/^-+|-+$/g, '')
        return slug || ('heading-' + index)
    },
    tocArticle: function () {
        var $article = $('#dcshopEchoLog')
        return $article.length ? $article : $('#emlogEchoLog')
    },
    tocSetArray: function ($article, force) {
        var self = this
        var usedIds = {}
        var $titles = $article.find('h2,h3').filter(function () {
            return $.trim($(this).text()) !== ''
        })

        if (!$titles.length && force) {
            $titles = $article.find('h1,h2,h3,h4,h5,h6').filter(function () {
                return $.trim($(this).text()) !== ''
            })
        }

        this.tocArray = []
        $titles.each(function (i) {
            var $title = $(this)
            var text = $.trim($title.text())
            var level = parseInt(this.tagName.substring(1), 10) || 2
            var baseId = $.trim($title.attr('id') || '')

            if (!baseId) {
                baseId = 'dc-toc-' + self.tocSlug(text, i + 1)
            }

            baseId = baseId.replace(/\s+/g, '-')
            var id = baseId
            var n = 2
            while (usedIds[id]) {
                id = baseId + '-' + n
                n++
            }
            usedIds[id] = true
            $title.attr('id', id).attr('toc-date', 'title')
            self.tocArray.push({
                id: id,
                level: level,
                text: text
            })
        })

        return this.tocArray
    },
    /**
     * toc 分析（toc 效果程序的入口）
     */
    tocAnalyse: function () {
        var $article = this.tocArticle()
        if (!$article.length) return

        $('.toc-con,.toc-mobile-btn,.toc-mask').remove()
        $(window).off('.blogToc')
        $(document).off('.blogToc')

        var articleHtml = $article.html()
        var forceToc = this.tocFlag.test(articleHtml)
        if (forceToc) {
            $article.html(articleHtml.replace(this.tocFlagReplace, ''))
        }

        var data = this.tocSetArray($article, forceToc)
        if (!data.length || (!forceToc && data.length < 2)) return

        this.tocRender()
        this.tocBindEvents()
        this.tocUpdateActive()
    },
    /**
     * toc 目录渲染
     */
    tocRender: function () {
        var data = this.tocArray
        var tocHtml = '<button type="button" class="toc-mobile-btn" id="tocMobileBtn" aria-label="打开文章目录"><i class="ri-list-check-2"></i><span>目录</span></button>'
        tocHtml += '<div class="toc-mask" id="tocMask"></div>'
        tocHtml += '<nav class="toc-con" id="toc-con" aria-label="文章目录">'
        tocHtml += '<div class="toc-head"><span><i class="ri-list-check-2"></i>文章目录</span><button type="button" class="close-toc" id="closeToc" aria-label="关闭目录">×</button></div>'
        tocHtml += '<div class="toc-body"><ul>'

        for (var i = 0; i < data.length; i++) {
            var item = data[i]
            tocHtml += '<li class="toc-level-' + item.level + '">'
            tocHtml += '<a href="#' + this.tocEscape(item.id) + '" class="toc-item" data-target="' + this.tocEscape(item.id) + '" title="' + this.tocEscape(item.text) + '">' + this.tocEscape(item.text) + '</a>'
            tocHtml += '</li>'
        }

        tocHtml += '</ul></div></nav>'
        $('body').append(tocHtml)
    },
    tocBindEvents: function () {
        var self = this
        $(window).on('scroll.blogToc resize.blogToc', function () {
            self.tocUpdateActive()
            if (window.outerWidth > 1274) {
                self.tocClose()
            }
        })

        $(document)
            .on('click.blogToc', '#tocMobileBtn', function (e) {
                e.preventDefault()
                self.tocOpenMobile()
            })
            .on('click.blogToc', '#tocMask,#closeToc', function (e) {
                e.preventDefault()
                self.tocClose()
            })
            .on('click.blogToc', '.toc-item', function (e) {
                e.preventDefault()
                self.tocScrollTo($(this).attr('data-target'))
            })
    },
    tocUpdateActive: function () {
        if (!this.tocArray.length) return

        var scrollTop = $(window).scrollTop() + 96
        var currentId = this.tocArray[0].id

        for (var i = 0; i < this.tocArray.length; i++) {
            var el = document.getElementById(this.tocArray[i].id)
            if (el && $(el).offset().top <= scrollTop) {
                currentId = this.tocArray[i].id
            }
        }

        $('.toc-item').removeClass('active')
        $('.toc-item').filter(function () {
            return $(this).attr('data-target') === currentId
        }).addClass('active')
    },
    tocScrollTo: function (id) {
        var el = document.getElementById(id)
        if (!el) return

        var top = Math.max(0, $(el).offset().top - 78)
        $('body,html').animate({scrollTop: top}, 300)
        this.tocClose()
    },
    tocOpenMobile: function () {
        $('.toc-con').addClass('toc-open')
        $('.toc-mask').addClass('show')
        $('body,html').addClass('scroll-fix')
    },
    /**
     * 兼容旧调用：打开移动端目录
     */
    tocMobileSet: function () {
        this.tocOpenMobile()
    },
    /**
     * 关闭移动端目录
     */
    tocClose: function () {
        $('.toc-con').removeClass('toc-open')
        $('.toc-mask').removeClass('show')
        $('body,html').removeClass('scroll-fix')
    },
    /**
     * 文章分享、复制链接、二维码
     */
    shareInit: function () {
        if (!$('#blogPostShare').length) return

        var self = this
        $(document).off('.blogShare')
            .on('click.blogShare', '[data-share-action]', function (e) {
                var action = $(this).attr('data-share-action')
                if (action === 'native' || action === 'copy' || action === 'qrcode' || action === 'close-qrcode') {
                    e.preventDefault()
                }
                self.shareHandle(action)
            })
            .on('keyup.blogShare', function (e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    self.shareCloseQr()
                }
            })
    },
    shareData: function () {
        var $box = $('#blogPostShare')
        var title = $.trim($box.attr('data-title') || document.title || '')
        var url = $.trim($box.attr('data-url') || window.location.href)
        try {
            url = new URL(url, window.location.href).href
        } catch (e) {}
        return {title: title, url: url}
    },
    shareHandle: function (action) {
        if (action === 'native') {
            this.shareNative()
        } else if (action === 'copy') {
            this.shareCopy()
        } else if (action === 'qrcode') {
            this.shareOpenQr()
        } else if (action === 'close-qrcode') {
            this.shareCloseQr()
        }
    },
    shareNative: function () {
        var data = this.shareData()
        var self = this
        if (navigator.share) {
            navigator.share({
                title: data.title,
                text: data.title,
                url: data.url
            }).catch(function () {})
            return
        }
        this.shareCopy(function () {
            self.shareToast('当前浏览器不支持系统分享，已复制链接')
        })
    },
    shareCopy: function (callback) {
        var data = this.shareData()
        var self = this
        var done = function () {
            if (typeof callback === 'function') {
                callback()
            } else {
                self.shareToast('文章链接已复制')
            }
        }
        var fail = function () {
            self.shareToast('复制失败，请手动复制')
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(data.url).then(done).catch(function () {
                self.shareCopyFallback(data.url, done, fail)
            })
        } else {
            this.shareCopyFallback(data.url, done, fail)
        }
    },
    shareCopyFallback: function (text, done, fail) {
        var textarea = document.createElement('textarea')
        textarea.value = text
        textarea.setAttribute('readonly', 'readonly')
        textarea.style.position = 'fixed'
        textarea.style.left = '-9999px'
        textarea.style.top = '0'
        document.body.appendChild(textarea)
        textarea.select()
        textarea.setSelectionRange(0, textarea.value.length)
        try {
            if (document.execCommand('copy')) {
                done()
            } else {
                fail()
            }
        } catch (e) {
            fail()
        }
        document.body.removeChild(textarea)
    },
    shareOpenQr: function () {
        var data = this.shareData()
        var $modal = $('#blogShareQrModal')
        var box = document.getElementById('blogShareQrcode')
        if (!$modal.length || !box) return

        $modal.addClass('show').attr('aria-hidden', 'false')
        $('body,html').addClass('scroll-fix')
        $('.blog-share-qrcode-url').val(data.url)

        if (typeof QRCode === 'undefined') {
            box.innerHTML = '<div class="blog-share-qrcode-error">二维码组件加载失败，请复制链接分享</div>'
            return
        }

        box.innerHTML = ''
        new QRCode(box, {
            text: data.url,
            width: 200,
            height: 200,
            colorDark: '#111111',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        })
    },
    shareCloseQr: function () {
        $('#blogShareQrModal').removeClass('show').attr('aria-hidden', 'true')
        $('body,html').removeClass('scroll-fix')
    },
    shareToast: function (text) {
        $('.blog-share-toast').remove()
        var $toast = $('<div class="blog-share-toast"></div>').text(text)
        $('body').append($toast)
        setTimeout(function () {
            $toast.addClass('show')
        }, 10)
        setTimeout(function () {
            $toast.removeClass('show')
            setTimeout(function () {
                $toast.remove()
            }, 220)
        }, 1800)
    },
    /**
     * 代码块复制
     */
    codeCopyInit: function () {
        var self = this
        $('.markdown pre').each(function () {
            var $pre = $(this)
            if ($pre.find('.blog-code-copy').length) return

            var $code = $pre.find('code').first()
            var className = $code.attr('class') || ''
            var langMatch = className.match(/(?:language|lang)-([a-z0-9_+#.-]+)/i)
            var lang = langMatch ? langMatch[1].toUpperCase() : ''

            $pre.addClass('blog-code-pre')
            if (lang) {
                $pre.append($('<span class="blog-code-lang"></span>').text(lang))
            }
            $pre.append('<button type="button" class="blog-code-copy" aria-label="复制代码"><i class="ri-file-copy-line"></i><span>复制</span></button>')
        })

        $(document).off('click.blogCodeCopy', '.blog-code-copy').on('click.blogCodeCopy', '.blog-code-copy', function (e) {
            e.preventDefault()
            var $btn = $(this)
            var $pre = $btn.closest('pre')
            var $code = $pre.find('code').first()
            var text = $code.length ? $code.text() : $pre.clone().children('.blog-code-copy,.blog-code-lang').remove().end().text()
            var done = function () {
                $btn.addClass('copied').find('span').text('已复制')
                self.shareToast('代码已复制')
                setTimeout(function () {
                    $btn.removeClass('copied').find('span').text('复制')
                }, 1600)
            }
            var fail = function () {
                $btn.addClass('failed').find('span').text('失败')
                self.shareToast('复制失败，请手动复制')
                setTimeout(function () {
                    $btn.removeClass('failed').find('span').text('复制')
                }, 1600)
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(function () {
                    self.shareCopyFallback(text, done, fail)
                })
            } else {
                self.shareCopyFallback(text, done, fail)
            }
        })
    },
    /**
     * 图片懒加载
     */
    lazyObserver: null,
    lazyLoadInit: function () {
        var self = this
        var $imgs = $('.markdown img,.log-cover img,.loglist-cover img,.blog-related-cover img')
        if (!$imgs.length) return

        $imgs.each(function () {
            var $img = $(this)
            if (!$img.attr('loading')) $img.attr('loading', 'lazy')
            if (!$img.attr('decoding')) $img.attr('decoding', 'async')
            $img.addClass('blog-lazy-image')
            if (this.complete && this.naturalWidth > 0) {
                $img.addClass('is-loaded')
            }
        })

        $imgs.off('.blogLazy')
            .on('load.blogLazy', function () {
                $(this).addClass('is-loaded').removeClass('is-error')
            })
            .on('error.blogLazy', function () {
                $(this).addClass('is-loaded is-error')
            })

        if ('IntersectionObserver' in window) {
            if (this.lazyObserver) {
                this.lazyObserver.disconnect()
            }
            this.lazyObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return
                    var img = entry.target
                    var $img = $(img)
                    var dataSrc = $img.attr('data-src') || $img.attr('data-original')
                    if (dataSrc && $img.attr('src') !== dataSrc) {
                        $img.attr('src', dataSrc)
                    }
                    $img.addClass('is-observed')
                    self.lazyObserver.unobserve(img)
                })
            }, {rootMargin: '180px 0px'})

            $imgs.each(function () {
                if (!this.complete) {
                    self.lazyObserver.observe(this)
                } else {
                    $(this).addClass('is-loaded')
                }
            })
        } else {
            $imgs.addClass('is-observed is-loaded')
        }
    },
    /**
     * 返回顶部
     */
    backToTopRaf: null,
    backToTopInit: function () {
        if (!$('#blogBackToTop').length) {
            $('body').append('<button type="button" class="blog-back-to-top" id="blogBackToTop" aria-label="返回顶部" title="返回顶部"><i class="ri-arrow-up-line"></i></button>')
        }

        var self = this
        $(document).off('click.blogBackToTop', '#blogBackToTop').on('click.blogBackToTop', '#blogBackToTop', function (e) {
            e.preventDefault()
            $('body,html').animate({scrollTop: 0}, 360)
        })

        var update = function () {
            if (self.backToTopRaf) return
            var runner = function () {
                self.backToTopRaf = null
                self.backToTopUpdate()
            }
            self.backToTopRaf = window.requestAnimationFrame ? window.requestAnimationFrame(runner) : setTimeout(runner, 16)
        }
        $(window).off('.blogBackToTop').on('scroll.blogBackToTop resize.blogBackToTop', update)
        update()
    },
    backToTopUpdate: function () {
        $('#blogBackToTop').toggleClass('show', $(window).scrollTop() > 360)
    },
    /**
     * 移动端阅读工具栏
     */
    mobileReadToolbarRaf: null,
    mobileReadToolbarInit: function () {
        var $article = this.readProgressArticle()
        if (!$article.length) return

        var hasToc = this.tocArray && this.tocArray.length > 0
        $('#blogMobileToolbar').remove()
        $('body').addClass('blog-mobile-toolbar-ready').append(
            '<div class="blog-mobile-toolbar" id="blogMobileToolbar" role="toolbar" aria-label="移动端阅读工具栏">' +
            '<span class="blog-mobile-toolbar-progress" aria-hidden="true"><i></i></span>' +
            '<button type="button" class="blog-mobile-toolbar-btn' + (hasToc ? '' : ' is-disabled') + '" data-mobile-toolbar-action="toc" aria-label="打开目录" aria-disabled="' + (hasToc ? 'false' : 'true') + '"><i class="ri-list-check-2"></i><span>目录</span></button>' +
            '<button type="button" class="blog-mobile-toolbar-btn" data-mobile-toolbar-action="comment" aria-label="查看评论"><i class="ri-chat-3-line"></i><span>评论</span></button>' +
            '<button type="button" class="blog-mobile-toolbar-btn" data-mobile-toolbar-action="share" aria-label="分享文章"><i class="ri-share-forward-line"></i><span>分享</span></button>' +
            '<button type="button" class="blog-mobile-toolbar-btn" data-mobile-toolbar-action="top" aria-label="返回顶部"><i class="ri-arrow-up-line"></i><span>顶部</span></button>' +
            '</div>'
        )

        var self = this
        $(document).off('click.blogMobileToolbar', '[data-mobile-toolbar-action]').on('click.blogMobileToolbar', '[data-mobile-toolbar-action]', function (e) {
            e.preventDefault()
            self.mobileReadToolbarHandle($(this).attr('data-mobile-toolbar-action'))
        })

        var update = function () {
            if (self.mobileReadToolbarRaf) return
            var runner = function () {
                self.mobileReadToolbarRaf = null
                self.mobileReadToolbarUpdate()
            }
            self.mobileReadToolbarRaf = window.requestAnimationFrame ? window.requestAnimationFrame(runner) : setTimeout(runner, 16)
        }
        $(window).off('.blogMobileToolbar').on('scroll.blogMobileToolbar resize.blogMobileToolbar orientationchange.blogMobileToolbar', update)
        update()
    },
    mobileReadToolbarHandle: function (action) {
        if (action === 'toc') {
            if (this.tocArray && this.tocArray.length) {
                this.tocOpenMobile()
            } else {
                this.shareToast('当前文章暂无目录')
            }
            return
        }
        if (action === 'comment') {
            var $target = $('#comments')
            if (!$target.length) $target = $('#comment-post')
            if (!$target.length) $target = $('.comment-header').first()
            if ($target.length) {
                $('body,html').animate({scrollTop: Math.max(0, $target.offset().top - 78)}, 320)
            } else {
                this.shareToast('当前文章暂无评论区域')
            }
            return
        }
        if (action === 'share') {
            this.shareNative()
            return
        }
        if (action === 'top') {
            $('body,html').animate({scrollTop: 0}, 360)
        }
    },
    mobileReadToolbarUpdate: function () {
        var $toolbar = $('#blogMobileToolbar')
        if (!$toolbar.length) return
        var scrollTop = $(window).scrollTop()
        $toolbar.toggleClass('show', scrollTop > 120)
        $toolbar.find('[data-mobile-toolbar-action="top"]').toggleClass('active', scrollTop > 360)
    },
    mobileReadToolbarUpdateProgress: function (progress) {
        var $toolbar = $('#blogMobileToolbar')
        if (!$toolbar.length) return
        progress = Math.max(0, Math.min(100, Number(progress) || 0))
        $toolbar.find('.blog-mobile-toolbar-progress i').css('width', progress + '%')
    }

}

/**
 * 事件监听
 */
$(document).ready(function () {
    myBlog.init()

    $(document).off('click.blogCommentReply', '.com-reply').on('click.blogCommentReply', '.com-reply', function () {
        myBlog.toggleCommentInput($(this))
    })

    $(".blog-header-toggle").click(function () {
        myBlog.navToggle($(this))
    })

    $(".has-down").mouseenter(function () {
        myBlog.calMargin($(this))
    })

    $("#captcha").click(function () {
        myBlog.captchaRefresh($(this))
    })

    $('#comment_submit[type="button"], #close-modal').click(function () {
        myBlog.comSubmitTip('judge')
        if (myBlog.comSubmitTip()) {  // 在显示评论的验证码模态框前，先校验一下评论区内容
            myBlog.viewModal()
        }
    })

    $(".form-control").blur(function () {
        myBlog.comSubmitTip('judge')
    })

    $(".markdown img").click(function () {
        myBlog.toggleImgSrc($(this))
    })

    $("#archive").change(function () {
        myBlog.jumpLink($(this))
    })

    $("#closeToc").click(function () {
        myBlog.tocClose()
    })

    // 切换夜间模式主题
    const toggleButton = document.getElementById('theme-toggle')
    const themeMedia = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null

    function getStoredTheme() {
        try {
            const savedTheme = localStorage.getItem('theme') || ''
            return (savedTheme === 'dark' || savedTheme === 'light') ? savedTheme : ''
        } catch (e) {
            return ''
        }
    }

    function getSystemTheme() {
        return themeMedia && themeMedia.matches ? 'dark' : 'light'
    }

    function updateThemeButton(theme) {
        if (!toggleButton) return
        const isDark = theme === 'dark'
        const icon = toggleButton.querySelector('i')
        const text = toggleButton.querySelector('.blog-theme-toggle-text')
        toggleButton.setAttribute('aria-pressed', isDark ? 'true' : 'false')
        toggleButton.setAttribute('aria-label', isDark ? '切换日间模式' : '切换夜间模式')
        toggleButton.setAttribute('title', isDark ? '切换日间模式' : '切换夜间模式')
        if (icon) {
            icon.className = isDark ? 'ri-sun-line' : 'ri-moon-line'
        }
        if (text) {
            text.innerText = isDark ? '白日' : '夜读'
        }
    }

    function applyTheme(theme, remember) {
        const targetTheme = theme === 'dark' ? 'dark' : 'light'
        document.documentElement.setAttribute('data-theme', targetTheme)
        document.documentElement.style.colorScheme = targetTheme
        updateThemeButton(targetTheme)
        if (remember) {
            try {
                localStorage.setItem('theme', targetTheme)
            } catch (e) {}
        }
    }

    applyTheme(getStoredTheme() || getSystemTheme(), false)

    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark', true)
        })
    }

    if (themeMedia) {
        const systemThemeChange = function () {
            if (!getStoredTheme()) {
                applyTheme(getSystemTheme(), false)
            }
        }
        if (themeMedia.addEventListener) {
            themeMedia.addEventListener('change', systemThemeChange)
        } else if (themeMedia.addListener) {
            themeMedia.addListener(systemThemeChange)
        }
    }
})