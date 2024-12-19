(function ($) {
    "use strict";
    // Initialize global variable

    var LaStudio = {
        global: {},
        utils: {},
        component: {},
        core: {}
    }

    window.LaStudio = LaStudio;

    if (typeof window.LA_No_LoadLazyScripts === "undefined") {
        window.LA_No_LoadLazyScripts = false;
    }

    $.exists = function ($selector) {
        return ($selector.length > 0);
    };

    $.getCachedScript = function (url) {
        var options = {
            dataType: "script",
            cache: true,
            url: url
        };
        return $.ajax(options);
    };

    LaStudio.global.log = function (...args) {
        console.log(args);
    };

    LaStudio.utils.ajax_xhr = null; // helper for ajax

    LaStudio.utils.localCache = {
        /**
         * timeout for cache in seconds, default 5 mins
         * @type {number}
         */
        timeout: typeof la_theme_config.cache_ttl !== "undefined" && parseInt(la_theme_config.cache_ttl) > 0 ? parseInt(la_theme_config.cache_ttl) : (60 * 5),

        timeout2: 10 * 60,
        /**
         * @type {{_: number, data: {}}}
         **/
        data: {},
        remove: function (url) {
            delete LaStudio.utils.localCache.data[url];
        },
        exist: function (url, ignore_timeout) {
            if(ignore_timeout){
                return !!LaStudio.utils.localCache.data[url];
            }
            else{
                return !!LaStudio.utils.localCache.data[url] && ((Date.now() - LaStudio.utils.localCache.data[url]._) / 1000 < LaStudio.utils.localCache.timeout2);
            }
        },
        get: function (url) {
            LaStudio.global.log('Get cache for ' + url);
            return LaStudio.utils.localCache.data[url].data;
        },
        set: function (url, cachedData, callback) {
            LaStudio.utils.localCache.remove(url);
            LaStudio.utils.localCache.data[url] = {
                _: Date.now(),
                data: cachedData
            };
            if ("function" == typeof callback && "number" != typeof callback.nodeType) {
                callback(cachedData)
            }
        },
        addedStyles: {},
        addedScripts: {},
        addedAssetsPromises: [],
        ajaxPromises: {},
    };

    LaStudio.utils.hashCode = function (s) {
        var hash = 0;
        if (s.length == 0) return hash;

        for (var i = 0; i < s.length; i++) {
            var char = s.charCodeAt(i);
            hash = (hash << 5) - hash + char;
            hash = hash & hash; // Convert to 32bit integer
        }

        return Math.abs(hash);
    };

    LaStudio.utils.debounce = function ( func, delay ){
        let timeout;
        return function executedFunc(...args) {
            if (timeout) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(() => {
                func(...args);
                timeout = null;
            }, delay);
        };
    }

    LaStudio.global.isPageSpeed = function () {
        return (typeof navigator !== "undefined" && (/(lighthouse|gtmetrix)/i.test(navigator.userAgent.toLocaleLowerCase())));
    };

    LaStudio.global.calculateAspectRatioFit = function (srcWidth, srcHeight, maxWidth, maxHeight) {
        var ratio = Math.min(maxWidth / (Math.max(srcWidth, maxWidth)), maxHeight / (Math.max(srcHeight, maxHeight))),
            _n_w = srcWidth*ratio,
            _n_h = srcHeight*ratio;
        return {
            width: _n_w,
            height: _n_h
        }
    }

    LaStudio.global.hasClass = function (elm, cls) {
        return (' ' + elm.className + ' ').indexOf(' ' + cls + ' ') > -1;
    };

    LaStudio.global.isRTL = function () {
        return document.body.classList ? document.body.classList.contains('rtl') : /\brtl\b/g.test(document.body.className);
    };

    LaStudio.global.sanitizeSlug = function (text) {
        return text.toString().toLowerCase().replace(/\s+/g, '-') // Replace spaces with -
            .replace(/[^\w\-]+/g, '') // Remove all non-word chars
            .replace(/\-\-+/g, '-') // Replace multiple - with single -
            .replace(/^-+/, '') // Trim - from start of text
            .replace(/-+$/, '');
    };

    LaStudio.global.isCookieEnable = function () {
        if (navigator.cookieEnabled) return true;
        document.cookie = "cookietest=1";
        var ret = document.cookie.indexOf("cookietest=") != -1;
        document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
        return ret;
    };

    LaStudio.global.parseVideo = function (url) {
        // - Supported YouTube URL formats:
        //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
        //   - http://youtu.be/My2FRPA3Gf8
        //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
        // - Supported Vimeo URL formats:
        //   - http://vimeo.com/25451551
        //   - https://vimeo.com/641193052/febd3489c9
        //   - https://player.vimeo.com/video/25451551
        //   - https://vimeo.com/channels/comedy/728921660
        //   - https://vimeo.com/groups/animation/videos/766071995
        // - Also supports relative URLs:
        //   - //player.vimeo.com/video/25451551
        var _playlist = LaStudio.global.getUrlParameter('playlist', url);

        url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

        if (RegExp.$3.indexOf('youtu') > -1) {
            if (_playlist) {
                return 'https://www.youtube.com/embed/' + RegExp.$6 + '?autoplay=1&playlist=' + _playlist + '&loop=1&rel=0&iv_load_policy3';
            }
            return 'https://www.youtube.com/embed/' + RegExp.$6 + '?autoplay=1&loop=1&rel=0&iv_load_policy3';
        }
        else if (RegExp.$3.indexOf('vimeo') > -1) {
            var vimeo_url = '';
            if(RegExp.$2.indexOf('player') > -1){
                vimeo_url = 'https://player.vimeo.com/video/' + RegExp.$6 + '?autoplay=1&loop=1&title=0&byline=0&portrait=0';
            }
            else{
                url.match(/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/)|(showcase\/[0-9]+\/video\/))?([0-9]+)(\/[A-z0-9]+)?/);
                vimeo_url = 'https://player.vimeo.com/video/' + RegExp.$6 + '?autoplay=1&loop=1&title=0&byline=0&portrait=0';
                if( RegExp.$7 ){
                    vimeo_url = LaStudio.global.addQueryArg(vimeo_url, 'h', RegExp.$7.replace('/', ''));
                }
            }
            var hparam = LaStudio.global.getUrlParameter('h',vimeo_url);
            if(hparam){
                vimeo_url = LaStudio.global.addQueryArg(vimeo_url, 'h', hparam);
            }
            if(window.innerWidth < 1200){
                vimeo_url = LaStudio.global.addQueryArg(vimeo_url, 'muted', '1');
            }
            url = vimeo_url;
        }

        return url;
    };

    LaStudio.global.getBrowseInformation = function () {
        var name, version, platform_name, _tmp;
        var ua = navigator.userAgent.toLowerCase(),
            platform = navigator.platform.toLowerCase(),
            UA = ua.match(/(opera|ie|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/) || [null, 'unknown', '0'];

        function getInternetExplorerVersion() {
            var rv = -1,
                ua2,
                re2;

            if (navigator.appName == 'Microsoft Internet Explorer') {
                ua2 = navigator.userAgent;
                re2 = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                if (re2.exec(ua2) != null) rv = parseFloat(RegExp.$1);
            } else if (navigator.appName == 'Netscape') {
                ua2 = navigator.userAgent;
                re2 = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
                if (re2.exec(ua2) != null) rv = parseFloat(RegExp.$1);
            }

            return rv;
        }

        _tmp = getInternetExplorerVersion();

        if (_tmp != -1) {
            name = 'ie';
            version = _tmp;
        } else {
            name = UA[1] == 'version' ? UA[3] : UA[1];
            version = UA[2].split('.')[0];
        }

        platform_name = ua.match(/ip(?:ad|od|hone)/) ? 'ios' : (ua.match(/(?:webos|android)/) || platform.match(/mac|win|linux/) || ['other'])[0];
        return {
            name: name,
            version: version,
            platform: platform_name
        };
    };

    LaStudio.global.setBrowserInformation = function () {
        var information = LaStudio.global.getBrowseInformation();

        if ('ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0) {
            document.documentElement.classList.add('touchevents');
        } else {
            document.documentElement.classList.add('no-touchevents');
        }

        document.documentElement.classList.add(information.name);
        document.documentElement.classList.add(information.name + information.version);
        document.documentElement.classList.add('platform-' + information.platform);

        function setScrollbarWidth(){
            var _scrollbarwidth = window.innerWidth - document.documentElement.clientWidth;
            document.documentElement.style.setProperty('--scrollbar-width-o', _scrollbarwidth + 'px');
            if(_scrollbarwidth == 0){
                if(information.platform == 'mac'){
                    _scrollbarwidth = 15;
                }
                else if(information.platform == 'ios'){
                    _scrollbarwidth = 10;
                }
            }
            document.documentElement.style.setProperty('--scrollbar-width', _scrollbarwidth + 'px');
        }
        setScrollbarWidth();

        $(window).on('resize', setScrollbarWidth );
    };

    LaStudio.global.isIELower16 = function () {
        var information = LaStudio.global.getBrowseInformation();
        return information.name == 'ie' && parseInt(information.version) < 16;
    };

    LaStudio.global.getRandomID = function () {
        var text = "",
            char = "abcdefghijklmnopqrstuvwxyz",
            num = "0123456789",
            i;

        for (i = 0; i < 5; i++) {
            text += char.charAt(Math.floor(Math.random() * char.length));
        }

        for (i = 0; i < 5; i++) {
            text += num.charAt(Math.floor(Math.random() * num.length));
        }

        return text;
    }

    LaStudio.global.getAdminBarHeight = function () {
        return document.getElementById('wpadminbar') && window.innerWidth > 600 ? 32 : 0
    }

    LaStudio.global.addQueryArg = function (url, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = url.indexOf('?') !== -1 ? "&" : "?";

        if (url.match(re)) {
            return url.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return url + separator + key + "=" + value;
        }
    }

    LaStudio.global.getUrlParameter = function (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    };

    LaStudio.global.removeURLParameter = function (url, parameter) {
        var urlparts = url.split('?');

        if (urlparts.length >= 2) {
            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g); //reverse iteration as may be destructive

            for (var i = pars.length; i-- > 0;) {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    };

    LaStudio.global.parseQueryString = function (query) {
        var urlparts = query.split("?");
        var query_string = {};

        if (urlparts.length >= 2) {
            var vars = urlparts[1].split("&");

            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                var key = decodeURIComponent(pair[0]);
                var value = decodeURIComponent(pair[1]); // If first entry with this name

                if (typeof query_string[key] === "undefined") {
                    query_string[key] = decodeURIComponent(value); // If second entry with this name
                } else if (typeof query_string[key] === "string") {
                    var arr = [query_string[key], decodeURIComponent(value)];
                    query_string[key] = arr; // If third or later entry with this name
                } else {
                    query_string[key].push(decodeURIComponent(value));
                }
            }
        }

        return query_string;
    };

    /**
     *
     * @param {Object} options
     */
    LaStudio.global.AjaxHandler = function (options){
        /**
         * General default settings
         *
         * @type {Object}
         */
        let self = this,
            settings = {
                'handlerId': '',
                'action': '',
                'cache': false,
                'processData': true,
                'url': '',
                'async': false,
                'beforeSendCallback': function () { },
                'errorCallback': function () { },
                'successCallback': function () { },
                'completeCallback': function () { },
                'handlerSettings': {}
            }

        /**
         * Checking options, settings and options merging
         *
         */
        if (options) {
            $.extend(settings, options);
        }

        /**
         * Set handler settings from localized global variable
         *
         * @type {Object} example, require: nonce, ajax_url, data_type, type, action
         */
        self.handlerSettings = settings.handlerSettings;

        /**
         * Ajax request instance
         *
         * @type {Object}
         */
        self.ajaxRequest = null;

        /**
         * Ajax processing state
         *
         * @type {Boolean}
         */
        self.ajaxProcessing = false;

        /**
         * Set ajax request data
         *
         * @type {Object}
         */
        self.data = {
            'action': settings.action || 'lastudio_theme_ajax',
            '_nonce': self.handlerSettings.nonce,
            'actions': {}
        };

        /**
         * Check ajax url is empty
         */
        if ('' === settings.url) {
            // Check public request
            settings.url = self.handlerSettings.ajax_url;
        }

        /**
         * Init ajax request
         *
         */
        self.send = function () {

            self.ajaxProcessing = true;

            self.ajaxRequest = $.ajax({
                type: self.handlerSettings.type,
                url: settings.url,
                data: self.data,
                cache: settings.cache,
                dataType: self.handlerSettings.data_type,
                processData: settings.processData,
                beforeSend: function (jqXHR, ajaxSettings) {
                    if (null !== self.ajaxRequest && !settings.async) {
                        self.ajaxRequest.abort();
                    }

                    if (settings.beforeSendCallback && 'function' === typeof (settings.beforeSendCallback)) {
                        settings.beforeSendCallback(jqXHR, ajaxSettings);
                    }
                }
            })
                .done(function (data, textStatus, jqXHR){
                    self.ajaxProcessing = false;
                    $(document).trigger({
                        type: 'LaStudioTheme:AjaxHandler:success',
                        response: data,
                        jqXHR: jqXHR,
                        textStatus: textStatus
                    });
                    if (settings.successCallback && 'function' === typeof (settings.successCallback)) {
                        settings.successCallback(data, textStatus, jqXHR);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown){
                    $(document).trigger({
                        type: 'LaStudioTheme:AjaxHandler:error',
                        jqXHR: jqXHR,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    if (settings.errorCallback && 'function' === typeof (settings.errorCallback)) {
                        settings.errorCallback(jqXHR, textStatus, errorThrown);
                    }
                })
                .always(function(jqXHR, textStatus, errorThrown) {
                    $(document).trigger({
                        type: 'LaStudioTheme:AjaxHandler:complete',
                        jqXHR: jqXHR,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    if (settings.completeCallback && 'function' === typeof (settings.completeCallback)) {
                        settings.completeCallback(jqXHR, textStatus, errorThrown);
                    }
                });
        };

        /**
         * Send data ajax request
         *
         * @param  {Object} data User data
         *
         */
        self.sendData = function (data) {
            const _key = settings.handlerId;
            self.data.actions = JSON.stringify({
                [_key]: {
                    'action' : settings.handlerId,
                    'data' : data || {}
                }
            })
            self.send();
        };
    }

})(jQuery);

(function ($) {
    var _loadedDependencies = [],
        _inQueue = {};
    $('body').on('lastudio-prepare-object-fit', function (e, $elm) {
        LaStudio.global.log('run fix object-fit');
        var objectFits = $('.figure__object_fit:not(.custom-object-fit) img', $elm);
        objectFits.each(function () {
            var $container = $(this).closest('.figure__object_fit'),
                imgUrl = $(this).prop('src');

            if (imgUrl) {
                $container.css('backgroundImage', 'url(' + imgUrl + ')').addClass('custom-object-fit');
            }
        });
    });

    if (LaStudio.global.isIELower16()) {
        $('<div class="unsupported-browser"><div><h3>' + la_theme_config.i18n.unsupported_browser.title + '</h3><p>' + la_theme_config.i18n.unsupported_browser.desc + '</p></div></div>').prependTo($('body'));
        $('body').on('lastudio-object-fit', function (e) {
            LaStudio.global.log('run fix object-fit');
            var objectFits = $('.figure__object_fit:not(.custom-object-fit) img');
            objectFits.each(function () {
                var $container = $(this).closest('.figure__object_fit'),
                    imgUrl = $(this).prop('src');

                if (imgUrl) {
                    $container.css('backgroundImage', 'url(' + imgUrl + ')').addClass('custom-object-fit');
                }
            });
        });
    }

    LaStudio.core.initAll = function ($scope) {
        var $el = $scope.find('.js-el'),
            $components = $el.filter('[data-la_component]'),
            component = null;

        if ($scope.find('.variations_form').length) {
            $(document).trigger('reinit_la_swatches');
        }

        if ($components.length <= 0) {
            return;
        } // initialize  component


        var init_component = function (name, el) {
            var $el = $(el);
            if ($el.data('init-' + name)) return;

            if (typeof LaStudio.component[name] !== 'function') {
                LaStudio.global.log('[LaStudio Component ' + name + '] ---- init error');
            } else {
                component = new LaStudio.component[name](el);
                component.init();
                $el.data('init-' + name, true);
                LaStudio.global.log('[LaStudio Component ' + name + '] ---- init success', $el);
                LaStudio.global.eventManager.publish('LaStudio:component_inited', [name, el]);
            }
        };

        if (!LaStudio.global.isPageSpeed()) {
            $components.each(function () {
                var self = this,
                    names = $(this).data('la_component');

                if (typeof names === 'string') {
                    var _name = names;
                    init_component(_name, self);
                } else {
                    names.forEach(function (name) {
                        init_component(name, self);
                    });
                }
            });
        } else {
            LaStudio.global.LazyLoad($components, {
                rootMargin: '200px',
                load: function (comp) {
                    var comp_name = $(comp).data('la_component');

                    if ('string' === typeof comp_name) {
                        init_component(comp_name, comp);
                    } else {
                        comp_name.forEach(function (name) {
                            init_component(name, comp);
                        });
                    }
                }
            }).observe();
        }

        $('body').trigger('lastudio-fix-ios-limit-image-resource').trigger('lastudio-lazy-images-load').trigger('jetpack-lazy-images-load').trigger('lastudio-object-fit');
    };

    LaStudio.global.loadScriptAsync = function (script, uri, callback, async) {
        if (LaStudio.utils.localCache.addedScripts.hasOwnProperty(script)) {
            return script;
        }

        LaStudio.utils.localCache.addedScripts[script] = uri;
        return new Promise(function (resolve, reject) {
            var tag = document.createElement('script');
            tag.src = uri;
            tag.async = async;

            tag.onload = function () {
                resolve(script);

                if ("function" == typeof callback && "number" != typeof callback.nodeType) {
                    callback();
                }
            };

            tag.onerror = function (){
                reject(`Can not load javascript file "${uri}"`);
                if ("function" == typeof callback && "number" != typeof callback.nodeType) {
                    callback();
                }
            }
            document.head.appendChild(tag);
        });
    };

    LaStudio.global.loadScriptAsyncSequence = function (scripts, callback) {
        scripts.forEach(function (item, idx) {
            LaStudio.global.loadScriptAsync(item[0], item[1], idx + 1 == scripts.length ? callback : '', item[2]);
        });
    };

    LaStudio.global.loadDependencies = function (dependencies, callback) {
        var _callback = callback || function () { };

        if (!dependencies) {
            _callback();

            return;
        }

        var newDeps = dependencies.map(function (dep) {
            if (_loadedDependencies.indexOf(dep) === -1) {
                if (typeof _inQueue[dep] === 'undefined') {
                    return dep;
                } else {
                    _inQueue[dep].push(_callback);

                    return true;
                }
            } else {
                return false;
            }
        });

        if (newDeps[0] === true) {
            return;
        }

        if (newDeps[0] === false) {
            _callback();

            return;
        }

        var queue = newDeps.map(function (script) {
            _inQueue[script] = [_callback];
            return $.getCachedScript(script);
        }); // Callbacks invoking

        var onLoad = function onLoad() {
            var index = 0;
            newDeps.map(function (loaded) {
                index++;

                _inQueue[loaded].forEach(function (callback) {
                    if (index == newDeps.length) {
                        LaStudio.global.log('loaded js: ' + loaded);
                        callback();
                    }
                });

                delete _inQueue[loaded];

                _loadedDependencies.push(loaded);
            });
        }; // Run callbacks when promise is resolved


        $.when.apply(null, queue).done(onLoad);
    };

    LaStudio.global.loadJsFile = function (name) {
        return la_theme_config.js_path + name + (la_theme_config.js_min ? '.min.js' : '.js');
    };

    LaStudio.global.ShowMessageBox = function (html, exClass, needAutoClose, callback) {
        if (typeof LaStudio.utils.timeOutMessageBox === "undefined") {
            LaStudio.utils.timeOutMessageBox = null;
        }

        var $content = $('<div class="la-global-message"></div>').html(html);

        var show_popup = function () {
            if ($.featherlight.close() !== undefined) {
                $.featherlight.close();
            }
            $.featherlight($content, {
                persist: 'shared',
                type: 'jquery',
                background: '<div class="featherlight featherlight-loading"><div class="featherlight-outer"><button class="featherlight-close-icon featherlight-close" aria-label="Close"><i class="lastudioicon-e-remove"></i></button><div class="featherlight-content"><div class="featherlight-inner"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div><div class="custom-featherlight-overlay"></div></div>',
                beforeOpen: function (evt) {
                    $('body').addClass(exClass);
                    if(needAutoClose){
                        clearTimeout(LaStudio.utils.timeOutMessageBox);
                    }
                },
                afterOpen: function (evt) {
                    if(needAutoClose){
                        LaStudio.utils.timeOutMessageBox = setTimeout(function () {
                            $.featherlight.close();
                        }, 20 * 1000);
                    }
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                afterClose: function (evt) {
                    $('body').removeClass(exClass);
                    if(needAutoClose){
                        clearTimeout(LaStudio.utils.timeOutMessageBox);
                    }
                }
            });
        };

        if ($.isFunction($.fn.featherlight)) {
            show_popup();
        } else {
            LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('featherlight')], show_popup);
        }
    };

})(jQuery); // Initialize Event Manager


(function ($) {
    'use strict';

    LaStudio.global.eventManager = {};

    LaStudio.global.eventManager.subscribe = function (evt, func) {
        $(this).on(evt, func);
    };

    LaStudio.global.eventManager.unsubscribe = function (evt, func) {
        $(this).off(evt, func);
    };

    LaStudio.global.eventManager.publish = function (evt, params) {
        $(this).trigger(evt, params);
    };
})(jQuery); // Initialize Lazyload


(function ($) {
    "use strict";

    var defaultConfig = {
        rootMargin: '50px',
        threshold: 0,
        load: function load(element) {
            var base_src = element.getAttribute('data-src') || element.getAttribute('data-lazy') || element.getAttribute('data-lazy-src') || element.getAttribute('data-lazy-original'),
                base_srcset = element.getAttribute('data-src') || element.getAttribute('data-lazy-srcset'),
                base_sizes = element.getAttribute('data-sizes') || element.getAttribute('data-lazy-sizes');

            if (base_src) {
                element.src = base_src;
            }

            if (base_srcset) {
                element.srcset = base_srcset;
            }

            if (base_sizes) {
                element.sizes = base_sizes;
            }

            if (element.getAttribute('data-background-image')) {
                element.style.backgroundImage = 'url("' + element.getAttribute('data-background-image') + '")';
            }

            element.setAttribute('data-element-loaded', true);

            if ($(element).hasClass('jetpack-lazy-image')) {
                $(element).addClass('jetpack-lazy-image--handled');
            }
        },
        complete: function ($elm) {
            // this function will be activated when element has been loaded
        }
    };

    function markAsLoaded(element) {
        element.setAttribute('data-element-loaded', true);
    }

    var isLoaded = function isLoaded(element) {
        return element.getAttribute('data-element-loaded') === 'true';
    };

    var onIntersection = function onIntersection(load) {
        return function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.intersectionRatio > 0) {
                    observer.unobserve(entry.target);

                    if (!isLoaded(entry.target)) {
                        load(entry.target);
                        markAsLoaded(entry.target);
                    }
                }
            });
        };
    };

    LaStudio.global.LazyLoad = function () {
        var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

        var _defaultConfig$option = $.extend({}, defaultConfig, options),
            rootMargin = _defaultConfig$option.rootMargin,
            threshold = _defaultConfig$option.threshold,
            load = _defaultConfig$option.load,
            complete = _defaultConfig$option.complete; // // If initialized, then disconnect the observer

        var observer = void 0;

        if ("IntersectionObserver" in window) {
            observer = new IntersectionObserver(onIntersection(load), {
                rootMargin: rootMargin,
                threshold: threshold
            });
        }
        return {
            observe: function observe() {
                if (!$.exists(selector)) {
                    return;
                }
                for (var i = 0; i < selector.length; i++) {
                    if (isLoaded(selector[i])) {
                        continue;
                    }
                    if (observer) {
                        observer.observe(selector[i]);
                        continue;
                    }
                    load(selector[i]);
                    markAsLoaded(selector[i]);
                }

                complete(selector);
            }
        };
    };

    LaStudio.global.makeImageAsLoaded = function (elm) {
        if (!isLoaded(elm)) {
            defaultConfig.load(elm);
            markAsLoaded(elm);
            $(elm).removeClass('lazyload');
        }
    };

    $('body').on('lastudio-lazy-images-load', function () {
        var $elm = $('.la-lazyload-image:not([data-element-loaded="true"]), img[data-lazy-src]:not([data-element-loaded="true"]), img[data-lazy-original]:not([data-element-loaded="true"])');
        LaStudio.global.LazyLoad($elm, {
            rootMargin: '50px'
        }).observe();
        var jetpackLazyImagesLoadEvent;
        try {
            jetpackLazyImagesLoadEvent = new Event('jetpack-lazy-images-load', {
                bubbles: true,
                cancelable: true
            });
        } catch (e) {
            jetpackLazyImagesLoadEvent = document.createEvent('Event');
            jetpackLazyImagesLoadEvent.initEvent('jetpack-lazy-images-load', true, true);
        }

        $('body').get(0).dispatchEvent(jetpackLazyImagesLoadEvent);
    });
})(jQuery); // Initialize Component


(function ($) {
    'use strict';

    var $window = $(window),
        $document = $(document),
        $htmlbody = $('html,body'),
        $body = $('body');

    LaStudio.component.SVGAnimation = function (el) {
        var $this = $(el),
            _settings = $this.data(),
            _type = _settings.type ? _settings.type : 'delayed',
            _duration = _settings.duration ? _settings.duration : 100,
            _options = {
                type: _type,
                duration: _duration
            },
            $svg = $this.find('svg');

        var setup_vivus = function () {
            var _vivus = new Vivus($svg[0], _options);

            if (_settings.hover) {
                if (_settings.hoveron) {
                    $(_settings.hoveron).on('mouseenter', function () {
                        _vivus.stop().reset().play(2);
                    }).on('mouseleave', function () {
                        _vivus.finish();
                    });
                } else {
                    $this.on('mouseenter', function () {
                        _vivus.stop().reset().play(2);
                    }).on('mouseleave', function () {
                        _vivus.finish();
                    });
                }
            }
        };

        return {
            init: function () {
                if (typeof Vivus === 'undefined') {
                    LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('vivus')], setup_vivus);
                } else {
                    setup_vivus();
                }
            }
        };
    };

    LaStudio.component.CountDownTimer = function (el) {
        var $scope = $(el);
        var timeInterval,
            $coutdown = $scope.find('[data-due-date]'),
            endTime = new Date($coutdown.data('due-date') * 1000),
            elements = {
                days: $coutdown.find('[data-value="days"]'),
                hours: $coutdown.find('[data-value="hours"]'),
                minutes: $coutdown.find('[data-value="minutes"]'),
                seconds: $coutdown.find('[data-value="seconds"]')
            };

        LaStudio.component.CountDownTimer.updateClock = function () {
            var timeRemaining = LaStudio.component.CountDownTimer.getTimeRemaining(endTime);
            $.each(timeRemaining.parts, function (timePart) {
                var $element = elements[timePart];

                if ($element.length) {
                    $element.html(this);
                }
            });

            if (timeRemaining.total <= 0) {
                clearInterval(timeInterval);
            }
        };

        LaStudio.component.CountDownTimer.initClock = function () {
            LaStudio.component.CountDownTimer.updateClock();
            timeInterval = setInterval(LaStudio.component.CountDownTimer.updateClock, 1000);
        };

        LaStudio.component.CountDownTimer.splitNum = function (num) {
            var num = num.toString(),
                arr = [],
                reult = '';

            if (1 === num.length) {
                num = 0 + num;
            }

            arr = num.match(/\d{1}/g);
            $.each(arr, function (index, val) {
                reult += '<span class="lastudio-countdown-timer__digit">' + val + '</span>';
            });
            return reult;
        };

        LaStudio.component.CountDownTimer.getTimeRemaining = function (endTime) {
            var timeRemaining = endTime - new Date(),
                seconds = Math.floor(timeRemaining / 1000 % 60),
                minutes = Math.floor(timeRemaining / 1000 / 60 % 60),
                hours = Math.floor(timeRemaining / (1000 * 60 * 60) % 24),
                days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));

            if (days < 0 || hours < 0 || minutes < 0) {
                seconds = minutes = hours = days = 0;
            }

            return {
                total: timeRemaining,
                parts: {
                    days: LaStudio.component.CountDownTimer.splitNum(days),
                    hours: LaStudio.component.CountDownTimer.splitNum(hours),
                    minutes: LaStudio.component.CountDownTimer.splitNum(minutes),
                    seconds: LaStudio.component.CountDownTimer.splitNum(seconds)
                }
            };
        };

        LaStudio.component.CountDownTimer.initClock();
        return {
            init: function () {
                LaStudio.component.CountDownTimer.initClock();
            }
        };
    };

    LaStudio.core.InstanceSearch = function ($modal) {
        if ($modal.hasClass('has-init')) {
            return;
        }

        $modal.addClass('has-init');

        var build_result_html = function(){
            return '<div class="search-results"><div class="lakit-css-loader"></div><div class="results-container"></div><div class="view-more-results text-center"><a href="#" class="button search-results-button">'+la_theme_config.i18n.global.search_view_more+'</a></div></div>';
        }

        var xhr = null,
            term = '',
            searchCache = {},
            $form = $modal.find('form.lakit-search__form'),
            $search = $form.find('input.lakit-search__field'),
            post_type = $form.find('input[name=post_type]').val(),
            $button,
            $results,
            search_type = 'minimal';

        $('<button type="reset" class="lakit-search__submit search-reset"><span class="lakit-search__submit-icon lakit-blocks-icon"><i class="lastudioicon-e-remove"></i></span></button>').insertAfter($form.find('.lakit-search__submit'));

        if($modal.find('.lakit-search__popup--full-screen').length){
            search_type = 'normal';
            $modal.find('.lakit-search__popup--full-screen').append(build_result_html());
        }
        else{
            if($modal.find('.lakit-search__popup-content').length){
                $modal.find('.lakit-search__popup-content').append(build_result_html());
            }
            else{
                $modal.find('.lakit-search').append(build_result_html());
            }
        }

        $results = $modal.find('.search-results');
        $button = $modal.find('.search-results-button');

        var delaySearch = function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        }();

        $modal.on('keyup', '.lakit-search__field', function (e) {
            var valid = false;

            if (typeof e.which === 'undefined') {
                valid = true;
            } else if (typeof e.which === 'number' && e.which > 0) {
                valid = !e.ctrlKey && !e.metaKey && !e.altKey;
            }

            if (!valid) {
                return;
            }

            if (xhr) {
                xhr.abort();
            }
            delaySearch(function () {
                search(true);
            }, 400);
        }).on('change', '.product-cats input', function () {
            if (xhr) {
                xhr.abort();
            }

            search(false);
        }).on('change', 'select', function () {
            if (xhr) {
                xhr.abort();
            }

            search(false);
        }).on('click', '.search-reset', function () {
            if (xhr) {
                xhr.abort();
            }
            term = '';
            $modal.addClass('reset');
            $results.find('.results-container, .view-more-results').slideUp(function () {
                $modal.removeClass('searching searched found-products found-no-product invalid-length reset');
            });
        }).on('focusout', '.lakit-search__field', function () {
            if ($(this).val().length < 2) {
                $results.find('.results-container, .view-more-results').slideUp(function () {
                    $modal.removeClass('searching searched found-products found-no-product invalid-length');
                });
            }
        }).on('focus', '.lakit-search__field', function () {
            if ($modal.hasClass('found-products')) {
                $results.find('.results-container,.view-more-results').slideDown(200);
            }
        });

        /**
         * Private function for searching products
         */

        /**
         *
         * @param itemObj { image: htmlObj, title : htmlObj, price: htmlObj, desc: htmlObj }
         * @returns {string}
         */
        function build_item_result( itemObj ){
            var _price = ( itemObj.price != '' ? `<div class="search_result__item-price">${itemObj.price}</div>` : ''),
                _desc = ( itemObj.desc != '' ? `<div class="search_result__item-desc">${itemObj.desc}</div>` : ''),
                _image = ( itemObj.image != '' ? `<div class="search_result__item-image">${itemObj.image}</div>` : '');
            return `<div class="search_result__item">${_image}<div class="search_result__item-content"><div class="search_result__item-title">${itemObj.title}</div>${_price}${_desc}</div></div>`;
        }

        function render_result_html($items, type){
            var _html = '';
            $items.each(function(){
                var itemObj = {
                    image: '',
                    desc: '',
                    title: '',
                    price: ''
                };
                if(type == 'product'){
                    itemObj.price = $(this).find('.price').html() || '';
                    itemObj.desc = $(this).find('.item--excerpt').html() || '';
                    if($(this).find('.product_item--title').length){
                        itemObj.image = $(this).find('.product_item--thumbnail-holder').html() || '';
                        itemObj.title = $(this).find('.product_item--title').html() || '';
                    }
                    else{
                        var itemLink = $(this).find('.woocommerce-loop-product__link').attr('href');
                        itemObj.image = '<a href="'+itemLink+'">'+$(this).find('figure').html()+'</a>';
                        itemObj.title = '<a href="'+itemLink+'">'+$(this).find('.woocommerce-loop-product__title').html()+'</a>';
                    }
                }
                else{
                    itemObj.image = $(this).find('.post-thumbnail, .post_item-thumbnail').html() || '';
                    itemObj.title = $(this).find('.lakit-posts__title,.entry-title').html() || '';
                    itemObj.desc = $(this).find('.item--excerpt,.entry-excerpt').html() || '';
                }
                _html += build_item_result(itemObj);
            })
            return _html;
        }

        function search(typing) {
            var keyword = $search.val(),
                $category = $form.find('.product-cats input:checked'),
                category = $category.length ? $category.val() : $form.find('select').length ? $form.find('select').val() : '',
                key = keyword + '[' + category + ']';

            if (term === keyword && typing) {
                return;
            }

            term = keyword;

            if (keyword.length < 2) {
                $modal.removeClass('searching found-products found-no-product').addClass('invalid-length');
                return;
            }

            var url = $form.attr('action') + '?' + $form.serialize() + '&la_doing_ajax=true';
            $button.removeClass('fadeInUp');
            $('.view-more-results', $results).slideUp(10);
            $modal.removeClass('found-products found-no-product').addClass('searching');

            if (key in searchCache) {
                showResult(searchCache[key]);
            } else {
                xhr = $.get(url, function (response) {
                    var $content = $('.site-main .site-content--default, .elementor.elementor-location-archive', response);

                    if ('product' === post_type) {
                        var $result_selector = $('.lakit-products[data-widget_current_query] ul.products', $content);

                        if($result_selector.length){
                            var $products = $result_selector;

                            if ($products.length) {
                                searchCache[key] = {
                                    found: true,
                                    items: render_result_html($products.children('li'), 'product'),
                                    url: LaStudio.global.removeURLParameter(url, 'la_doing_ajax')
                                }
                            }
                            else {
                                // Cache
                                searchCache[key] = {
                                    found: false,
                                    text: $('.woocommerce-info', $content).length ? $('.woocommerce-info', $content).text() : la_theme_config.i18n.global.search_not_found
                                }
                            }
                        }

                        else{
                            // Cache
                            searchCache[key] = {
                                found: false,
                                text: la_theme_config.i18n.global.search_not_found
                            }
                        }
                    }
                    else {

                        var $result_selector = $('div[data-widget_current_query="yes"]', $content);

                        if($result_selector.length){
                            var $posts = $( $result_selector.find($result_selector.data('item_selector')) );

                            if ($posts.length) {
                                searchCache[key] = {
                                    found: true,
                                    items: render_result_html($posts, 'post'),
                                    url: LaStudio.global.removeURLParameter(url, 'la_doing_ajax')
                                };
                            }
                            else {
                                searchCache[key] = {
                                    found: false,
                                    text: la_theme_config.i18n.global.search_not_found
                                };
                            }
                        }
                        else{
                            searchCache[key] = {
                                found: false,
                                text: la_theme_config.i18n.global.search_not_found
                            };
                        }
                    }

                    showResult(searchCache[key]);
                    $modal.addClass('searched');
                    xhr = null;
                }, 'html');
            }
        }

        /**
         * Private function for showing the search result
         *
         * @param result
         */
        function showResult(result) {
            var extraClass = 'product' === post_type ? 'woocommerce' : 'la-post-grid';
            $modal.removeClass('searching');

            if (result.found) {

                $modal.addClass('found-products');

                var $results_grid = $('<div class="search_result_grid"></div>');

                if (search_type == 'minimal') {
                    $results.addClass('is-minimal-result');
                    $results_grid.addClass('is-minimal-result');
                }
                $results_grid.append(result.items);

                $results.find('.results-container').addClass(extraClass).html($results_grid.get(0).outerHTML);
                $('body').trigger('lastudio-fix-ios-limit-image-resource').trigger('lastudio-lazy-images-load').trigger('jetpack-lazy-images-load').trigger('lastudio-object-fit');
                LaStudio.core.initAll($results); // Add animation class

                $('.search_result__item', $results).each(function ( idx ){
                    $(this).css('animation-delay', idx * 100 + 'ms');
                    $(this).addClass('fadeInUp animated');
                })

                $button.attr('href', result.url).css('animation-delay', $('.search_result__item', $results).length * 100 + 'ms').addClass('fadeInUp animated');
                $results.find('.results-container, .view-more-results').slideDown(300, function () {
                    $modal.removeClass('invalid-length');
                });
            }
            else {
                $modal.addClass('found-no-product');
                $results.find('.results-container').removeClass(extraClass).html($('<div class="not-found text-center" />').text(result.text));
                $button.attr('href', '#');
                $results.find('.view-more-results').slideUp(300);
                $results.find('.results-container').slideDown(300, function () {
                    $modal.removeClass('invalid-length');
                });
            }

            $modal.addClass('searched');
        }
    };

    LaStudio.core.ElementClickEvent = function () {
        $document
            .on('click', function (e){
                if( $(e.target).closest('.lakit-ajax-searchform').length ) {
                    return;
                }
                $('.lakit-ajax-searchform .results-container').hide();
                $('.lakit-ajax-searchform .view-more-results').hide();
            })
            .on('LaStudio:Component:Popup:Close', function (e) {
                e.preventDefault();
                try {
                    $.featherlight.close();
                } catch (e) {
                }
            })
            .on('click', '.la-popup:not(.elementor-widget):not([data-gallery-id]), .la-popup.elementor-widget a, a[lapopup], div[lapopup] a, a[data-lapopup], div[data-lapopup] a', function (e) {
                e.preventDefault();
                var $that = $(this),
                    _href = LaStudio.global.parseVideo($that.attr('href')),
                    typeMapping = {
                        'image': /\.(png|jp?g|gif|tiff?|bmp|svg|webp)(\?\S*)?$/i,
                        'inline': /^[#.]\w/,
                        'html': /^\s*<[\w!][^<]*>/,
                        'elementor_image': /\.(png|jpe?g|gif|svg|webp)(\?\S*)?$/i
                    };

                var _type = 'iframe';

                if (_href.match(typeMapping.image)) {
                    _type = 'image';
                } else if (_href.match(typeMapping.inline)) {
                    _type = 'jquery';
                } else if (_href.match(typeMapping.html)) {
                    _type = 'html';
                } else {
                    _type = 'iframe';
                }

                var extra_class = $that.data('component_name') || '';
                extra_class += ' featherlight--inline';

                if (_href.match(typeMapping.elementor_image) && typeof elementorFrontend !== "undefined" && elementorFrontend.getKitSettings("global_image_lightbox")) {
                    return;
                }

                var init_auto_popup = function () {

                    if($that.data('isOpen')){
                        $.featherlight.close();
                        return;
                    }
                    $.featherlight.close();
                    $.featherlight(_href, {
                        type: _type,
                        persist: 'shared',
                        background: '<div class="featherlight featherlight-loading"><div class="featherlight-outer"><button class="featherlight-close-icon featherlight-close" aria-label="Close"><i class="lastudioicon-e-remove"></i></button><div class="featherlight-content"><div class="featherlight-inner"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div><div class="custom-featherlight-overlay"></div></div>',
                        beforeClose: function (evt) {
                            $that.data('isOpen', false);
                            $document.trigger('LaStudio:Component:Popup:beforeClose', [this, $that]);
                            $body.removeClass(extra_class);
                            if (_type == 'jquery' && $(_href).length > 0) {
                                var _temp_id = _href.replace('#', '#__tmp__');

                                $(_href).insertBefore($(_temp_id));
                                $(_temp_id).remove();
                            }
                        },
                        beforeOpen: function (evt) {
                            $that.data('isOpen', true);
                            $document.trigger('LaStudio:Component:Popup:beforeOpen', [this, $that]);
                            $body.addClass(extra_class);
                            if (_type == 'jquery' && $(_href).length > 0) {
                                const _temp_id = _href.replace('#', '__tmp__');
                                $('<div id="' + _temp_id + '" class="featherlight__placeholder"></div>').insertBefore($(_href));
                                const $eRoot = $(_href).closest('.elementor[data-elementor-id]');
                                if($eRoot.length){
                                    $(this.target).removeClass('elementor-element');
                                    this.$instance.addClass( $eRoot.get(0).className )
                                }
                            }
                        },
                        afterOpen: function (){
                            $document.trigger('LaStudio:Component:Popup:afterOpen', [this, $that]);

                            if (_type == 'jquery' && $(_href).length > 0) {
                                const $eRoot = $(_href.replace('#', '#__tmp__')).closest('.elementor[data-elementor-id]');
                                if($eRoot.length){
                                    $(this.target).addClass('elementor-element');
                                }
                            }
                        },
                        iframeAllow: "autoplay",
                        iframeAllowfullscreen: "1"
                    });
                };

                if ($.isFunction($.fn.featherlight)) {
                    init_auto_popup();
                } else {
                    LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('featherlight')], init_auto_popup);
                }
            })
            .on('click', '.la-inline-popup', function (e) {
                e.preventDefault();

                var _this = $(this);

                var $popup = $(_this.data('href') || _this.attr('href'));
                var extra_class = _this.data('component_name') || '';
                extra_class += ' featherlight--inline';

                var init_auto_popup = function () {

                    $.featherlight.close();

                    $.featherlight($popup, {
                        // persist: 'shared',
                        // type: 'jquery',
                        background: '<div class="featherlight featherlight-loading"><div class="featherlight-outer"><button class="featherlight-close-icon featherlight-close" aria-label="Close"><i class="lastudioicon-e-remove"></i></button><div class="featherlight-content"><div class="featherlight-inner"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div><div class="custom-featherlight-overlay"></div></div>',
                        beforeOpen: function (evt) {
                            $body.addClass(extra_class);
                        },
                        afterClose: function (evt) {
                            $body.removeClass(extra_class);
                        }
                    });
                };

                if ($.isFunction($.fn.featherlight)) {
                    init_auto_popup();
                } else {
                    LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('featherlight')], init_auto_popup);
                }
            })
            .on('click', '.custom-lighcase-btn-close, .popup-button-continue, .custom-lightcase-overlay, .custom-featherlight-close, .btn-close-newsletter-popup, .lakit-popup--close', function (e) {
                e.preventDefault();
                $document.trigger('LaStudio:Component:Popup:Close');
            })
            .on('click', '.elementor-lakit-portfolio.enable-pf-lightbox .lakit-posts__inner-box', function (e){
                var $this = $(this),
                    tpl = '',
                    imgSize = {};

                if( $('.post-thumbnail', $this).length > 0 ){
                    e.preventDefault();
                    tpl += '<div class="lakit-ppc--img">' + $('.post-thumbnail', $this).html() + '</div>';
                    tpl += '<div class="lakit-ppc--content">' + $('.lakit-posts__inner-content-inner', $this).html() + '</div>';

                    imgSize = LaStudio.global.calculateAspectRatioFit($('.post-thumbnail img', $this).attr('width'), $('.post-thumbnail img', $this).attr('height'), (window.innerWidth * 0.8), (window.innerHeight * 0.8))
                }
                else{
                    return true;
                }

                var $content = $('<div class="lakit-ppc"></div>').html(tpl);

                var show_popup = function () {
                    if ($.featherlight.close() !== undefined) {
                        $.featherlight.close();
                    }

                    $.featherlight($content, {
                        persist: 'shared',
                        type: 'jquery',
                        background: '<div class="featherlight featherlight-loading"><div class="featherlight-outer"><button class="featherlight-close-icon featherlight-close" aria-label="Close"><i class="lastudioicon-e-remove"></i></button><div class="featherlight-content"><div class="featherlight-inner"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div><div class="custom-featherlight-overlay"></div></div>',
                        beforeOpen: function (evt) {
                            $('body').addClass('body-pf-gallery');
                        },
                        afterOpen: function (evt){
                            $('.lakit-ppc').css({
                                width: imgSize.width,
                                '--imageRatio': imgSize.width / imgSize.height
                            });
                        },
                        afterClose: function (evt) {
                            $('body').removeClass('body-pf-gallery');
                        }
                    });
                };

                if ($.isFunction($.fn.featherlight)) {
                    show_popup();
                } else {
                    LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('featherlight')], show_popup);
                }

            })
    };

    LaStudio.core.Blog = function ($sidebar_inner) {
        $sidebar_inner = $sidebar_inner || $('.widget-area');

        if($sidebar_inner.hasClass('--inited')){
            return;
        }

        if( $sidebar_inner.hasClass('elementor-widget') ){
            $sidebar_inner.addClass('--lakit-wp--widget');
        }

        $sidebar_inner.addClass('--inited');

        $('.widget_block > .widget-title:only-child', $sidebar_inner).each(function (){
            var $that = $(this),
                $parent = $that.closest('.widget_block'),
                $next = $parent.next('.widget_block');
            if($next.length && $next.find('.widget-title').length == 0){
                $that.prependTo($next);
            }
            $parent.remove();
        })

        $('.menu li a:empty', $sidebar_inner).each(function () {
            $(this).closest('li').remove();
        });
        $('.widget_pages > ul, .widget_archive > ul, .widget_categories > ul, .widget_product_categories > ul, .widget_meta > ul', $sidebar_inner).addClass('menu').closest('.widget').addClass('accordion-menu');
        $('.wc-block-product-categories.is-list > ul', $sidebar_inner).addClass('menu').closest('.widget').addClass('accordion-menu');
        $('.widget_nav_menu', $sidebar_inner).closest('.widget').addClass('accordion-menu');
        $('.widget_categories > ul li.cat-parent,.widget_product_categories li.cat-parent', $sidebar_inner).addClass('mm-item-has-sub');
        $('.menu li > ul', $sidebar_inner).each(function () {
            var $ul = $(this);
            if($ul.siblings('.narrow').length == 0){
                $ul.before('<span class="narrow"><i></i></span>');
            }
        });
        $('.accordion-menu li.menu-item-has-children > a,.menu li.mm-item-has-sub > a,.menu li > .narrow', $sidebar_inner).off('click');
        $('.accordion-menu li.menu-item-has-children > a,.menu li.mm-item-has-sub > a,.menu li > .narrow', $sidebar_inner).on('click', function (e){
            e.preventDefault();
            var $parent = $(this).parent();

            if ($parent.hasClass('open')) {
                $parent.removeClass('open');
                $parent.find('>ul').stop().slideUp();
            } else {
                $parent.addClass('open');
                $parent.find('>ul').stop().slideDown();
                $parent.siblings().removeClass('open').find('>ul').stop().slideUp();
            }
        })
        $('li.current-cat, li.current-cat-parent', $sidebar_inner).each(function(){
            $(this).addClass('open');
            $('>ul', $(this)).css('display','block');
        });

    };

    LaStudio.core.SitePreload = function () {
        var pbar = document.getElementById('wpadminbar');

        if (pbar) {
            pbar.classList.add('wpbar');
        }
        /** Back To Top **/

        $window.on('load scroll', function () {
            if ($window.scrollTop() > $window.height() + 100) {
                $('.backtotop-container').addClass('show');
                $('[data-backtotop]').addClass('show');
            } else {
                $('.backtotop-container').removeClass('show');
                $('[data-backtotop]').removeClass('show');
            }
        });

        $window.on('load resize', function (){
            if($window.height() < 650){
                if($('.lakit-cart .lakit-cart__list .product_list_widget').first().find('li').length > 3){
                    $body.addClass('l650')
                }
            }
            else{
                $body.removeClass('l650')
            }
        })

        $document.on('click', '.btn-backtotop, [data-backtotop] .elementor-button', function (e) {
            e.preventDefault();
            $htmlbody.animate({
                scrollTop: 0
            }, 800);
        });
        $body.on('lastudio-fix-ios-limit-image-resource', function () {
            if (!('matchMedia' in window)) {
                return;
            }

            if (window.matchMedia("(max-width: 1024px)").matches) {
                $('li.product_item.thumb-has-effect').each(function () {
                    $(this).removeClass('thumb-has-effect');
                    $(this).find('.p_img-second').remove();
                });
            }
        }).trigger('lastudio-fix-ios-limit-image-resource');
        $body.removeClass('site-loading');
        $window.on('beforeunload', function (e) {
            var browser_information = LaStudio.global.getBrowseInformation();
            if (browser_information.name != 'safari' && window.self === window.top) {
                if (typeof window['hack_beforeunload_time'] === "undefined" || typeof window['hack_beforeunload_time'] !== "undefined" && e.timeStamp - window['hack_beforeunload_time'] > 1000) {
                    $body.addClass('site-loading');
                }
            }
        });
        $document.on('click', 'a[href^="tel:"], a[href^="mailto:"], a[href^="callto"], a[href^="skype"], a[href^="whatsapp"], a.mail-link, a.noloading', function (e) {
            window['hack_beforeunload_time'] = parseInt(e.timeStamp);
        });
        $window.on('pageshow', function (e) {
            if (e.originalEvent.persisted) {
                $body.removeClass('site-loading body-loading');
            }
        });
        LaStudio.global.eventManager.subscribe('LaStudio:Component:LazyLoadImage', function (e, $container) {
            $container.find('.la-lazyload-image:not([data-element-loaded="true"]), img[data-lazy-src]:not([data-element-loaded="true"]), img[data-lazy-original]:not([data-element-loaded="true"])').each(function (idx, element) {
                LaStudio.global.makeImageAsLoaded(element);
            });
        });
        $('body').trigger('lastudio-fix-ios-limit-image-resource').trigger('lastudio-lazy-images-load').trigger('jetpack-lazy-images-load').trigger('lastudio-object-fit');
    };

    LaStudio.core.OnLoadEvent = function () {
        $body.removeClass('site-loading body-loading').addClass('body-loaded');
        try{
            Pace.stop();
        }catch (e) {

        }
        $('.force-active-object-fit').each(function () {
            $body.trigger('lastudio-prepare-object-fit', [$(this)]);
        });
    };

    LaStudio.core.CustomFunction = function () {
        $(document).on('click', 'button.site-nav-toggleicon', function (e){
            e.preventDefault();
            $('html').toggleClass('open-site-nav-default');
        });
        $('.div.gallery[class*="galleryid-"], .wp-block-gallery').each(function () {
            var _id = LaStudio.global.getRandomID();
            $(this).find('a').addClass('la-popup').attr('data-elementor-lightbox-slideshow', _id);
        });

        function init_wc_swatch() {
            var init_swatch_cb = function () {
                $('.variations_form').trigger('wc_variation_form');
            };
        }

        $(document).on('reinit_la_swatches', function () {
            $('.product_item .product_item--info').each(function () {
                $(this).closest('.product_item').css('--item_info', $(this).innerHeight() + 'px');
            });

            if ($body.hasClass('elementor-editor-active')) {
                return;
            }

            if ($('.variations_form').length && la_theme_config.has_wc == "1") {
                var $variations_form = $('.variations_form');

                var init_wc_swatch_default = function () {
                    $variations_form.each(function () {
                        $(this).wc_variation_form();

                        if ($(this).closest('.product_item')) {
                            $(this).closest('.product_item').css('--variations_form', $(this).innerHeight() + 'px');
                        }
                    });
                    init_wc_swatch();
                };

                if ($.fn.wc_variation_form) {
                    init_wc_swatch_default();
                }
                else {
                    if (typeof _wpUtilSettings === "undefined") {
                        window._wpUtilSettings = {
                            ajax: {
                                url: la_theme_config.ajax_url
                            }
                        };
                    }

                    if (typeof wc_add_to_cart_variation_params === "undefined") {
                        window.wc_add_to_cart_variation_params = la_theme_config.i18n.variation;
                    }

                    LaStudio.global.loadScriptAsyncSequence([
                        ['underscore-js', la_theme_config.wc_variation.underscore, true],
                        ['wp-util-js', la_theme_config.wc_variation.wp_util, false],
                        ['wc-add-to-cart-variation-js', la_theme_config.wc_variation.base, false]
                    ], init_wc_swatch_default);
                }
            }
        });

        $(document).on('click mouseenter', '[data-swiper_cdots]', function (e){
            e.preventDefault();
            // swiper_id, slide_index
            var _swiper, _slide_idx, _swiper_for;
            _swiper_for = $(this).attr('data-swiper_id');
            if($(_swiper_for).hasClass('swiper-container')){
                _swiper = $(_swiper_for).data('swiper');
            }
            else{
                _swiper = $(_swiper_for).find('.swiper-container').first().data('swiper');
            }
            if( _swiper !== "undefined" ){
                _slide_idx = parseInt($(this).attr('slide_idx') || 0);
                $('[data-swiper_cdots][data-swiper_id="'+_swiper_for+'"]').removeClass('active-swiper-cdots');
                $(this).addClass('active-swiper-cdots');
                try {
                    _swiper.slideTo(_slide_idx);
                }catch (ex){}
            }
        });

        const wishlist_compare_count = {
            'wishlist': 0,
            'compare': 0
        }
        try {
            if (Cookies.get('ciri_wishlist') !== 'undefined') {
                const _cW = JSON.parse(Cookies.get('ciri_wishlist'));
                wishlist_compare_count.wishlist = _cW[0].posts.length
            }
            if (Cookies.get('ciri_compare') !== 'undefined') {
                const _cC = JSON.parse(Cookies.get('ciri_compare'));
                wishlist_compare_count.compare = _cC[0].posts.length
            }
        } catch (ex) { }

        $('.elementor-element.lakit-wishlist-trigger .elementor-icon, a[data-action="lakit_wishlist"]').each(function (){
            if( $(this).find('.la-wishlist-count').length == 0){
                $(this).append('<span class="la-wishlist-count">'+wishlist_compare_count.wishlist+'</span>')
            }
            else{
                $(this).find('.la-wishlist-count').html(wishlist_compare_count.wishlist)
            }
        })
        $('.elementor-element.lakit-compare-trigger .elementor-icon, a[data-action="lakit_compare"]').each(function (){
            if( $(this).find('.la-compare-count').length == 0){
                $(this).append('<span class="la-compare-count">'+wishlist_compare_count.compare+'</span>')
            }
            else{
                $(this).find('.la-compare-count').html(wishlist_compare_count.compare)
            }
        });

        $(document).on('click', '.lakit-cart-trigger a.menu-item-link', function (e){
            e.preventDefault();
            $('.lakit-cart').addClass('lakit-cart-open');
        })
    }

    LaStudio.core.elementorFrontendInit = function ( $container, reinit_trigger ){
        if(reinit_trigger){
            $(window).trigger('elementor/frontend/init');
        }
        $container.removeClass('need-reinit-js');
        $container.find('[data-element_type]').each(function () {
            var $this = $(this),
                elementType = $this.data('element_type');

            if (!elementType) {
                return;
            }

            try {
                if ('widget' === elementType) {
                    elementType = $this.data('widget_type');
                    window.elementorFrontend.hooks.doAction('frontend/element_ready/widget', $this, $);
                }

                window.elementorFrontend.hooks.doAction('frontend/element_ready/global', $this, $);
                window.elementorFrontend.hooks.doAction('frontend/element_ready/' + elementType, $this, $);

            } catch (err) {
                console.log(err);
                $this.remove();
                return false;
            }
        });
    }

    LaStudio.core.DomLoadEvent = function () {

        $window.on('elementor/frontend/init', function () {
            if (typeof elementorFrontend !== "undefined" && typeof elementorFrontend.hooks !== "undefined") {
                elementorFrontend.hooks.addAction('frontend/element_ready/toggle.default', function ($scope) {
                    if ($scope.hasClass('toggle__active-first')) {
                        $scope.find('.elementor-toggle-item:first-child .elementor-tab-title').trigger('click');
                    }
                }, 20);

                elementorFrontend.hooks.addAction( 'frontend/element_ready/widget', function( $scope ) {
                    if( $scope.is('[class*="elementor-widget-wp-widget"]') ){
                        if( $('body').hasClass('elementor-editor-active') ){
                            $scope.removeClass('--lakit-wp--widget --inited')
                        }
                        LaStudio.core.Blog($scope);
                    }
                } );

            }
        });
        function LoadLazyScripts() {
            if (!LaStudio.global.isPageSpeed()) {
                $('body').addClass('dom-completely-loaded body-completely-loaded');
            }
            if (la_theme_config.has_wc && typeof LaStudioWooCommerce === "undefined") {
                LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('woocommerce')], function () {
                    LaStudioWooCommerce.AutoInit();
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            LoadLazyScripts();
            if (la_theme_config.has_wc && typeof LaStudioWooCommerce !== "undefined") {
                LaStudioWooCommerce.AutoInit();
            }
        })

    }

    LaStudio.core.OpenNewsletterPopup = function ($popup, callback) {
        var extra_class = 'open-newsletter-popup';

        var show_popup = function () {
            $.featherlight($popup, {
                persist: 'shared',
                type: 'jquery',
                background: '<div class="featherlight featherlight-loading"><div class="featherlight-outer"><button class="featherlight-close-icon featherlight-close" aria-label="Close"><i class="lastudioicon-e-remove"></i></button><div class="featherlight-content"><div class="featherlight-inner"><div class="la-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div><div class="custom-featherlight-overlay"></div></div>',
                beforeClose: function (evt) {
                    var _temp_id = '#__tmp__' + $popup.attr('id');

                    $popup.insertBefore($(_temp_id));
                    $(_temp_id).remove();
                },
                beforeOpen: function () {
                    var _temp_id = '__tmp__' + $popup.attr('id');

                    $('<div id="' + _temp_id + '" class="featherlight__placeholder"></div>').insertBefore($popup);
                    $body.addClass(extra_class);
                },
                afterOpen: function (evt) {
                    LaStudio.core.initAll($('.featherlight-content'));
                },
                afterClose: function (evt) {
                    if (typeof callback === 'function') {
                        callback();
                    }

                    $body.removeClass(extra_class);
                }
            });
        };

        if ($.isFunction($.fn.featherlight)) {
            show_popup();
        } else {
            LaStudio.global.loadDependencies([LaStudio.global.loadJsFile('featherlight')], show_popup);
        }
    };

    LaStudio.component.NewsletterPopup = function (el) {
        var $popup = $(el),
            disable_on_mobile = parseInt($popup.attr('data-show-mobile') || 0),
            p_delay = parseInt($popup.attr('data-delay') || 2000),
            backtime = parseInt($popup.attr('data-back-time') || 1),
            waitfortrigger = parseInt($popup.attr('data-waitfortrigger') || 0);

        if (waitfortrigger == 1) {
            $(document).on('click touchend', '.elm-trigger-open-newsletter', function (e) {
                e.preventDefault();
                LaStudio.core.OpenNewsletterPopup($popup);
            });
        }

        return {
            init: function () {
                if (waitfortrigger != 1) {
                    if ($(window).width() < 767) {
                        if (disable_on_mobile) {
                            return;
                        }
                    }

                    try {
                        if (Cookies.get('ciri_dont_display_popup') == 'yes') {
                            return;
                        }
                    } catch (ex) {
                        LaStudio.global.log(ex);
                    }

                    $(window).load(function () {
                        setTimeout(function () {
                            LaStudio.core.OpenNewsletterPopup($popup, function () {
                                if ($('.cbo-dont-show-popup', $popup).length && $('.cbo-dont-show-popup', $popup).is(':checked')) {
                                    try {
                                        Cookies.set('ciri_dont_display_popup', 'yes', {
                                            expires: backtime,
                                            path: '/'
                                        });
                                    } catch (ex) {
                                    }
                                }
                            });
                        }, p_delay);
                    });
                }
            }
        };
    };
})(jQuery); // Kickoff all event


(function ($) {
    'use strict';

    LaStudio.global.setBrowserInformation();
    $(function () {
        $(document).trigger('LaStudio:Document:BeforeRunScript');
        LaStudio.core.SitePreload();
        $('.lakit-ajax-searchform').each(function () {
            LaStudio.core.InstanceSearch($(this));
        });
        LaStudio.core.initAll($(document));
        LaStudio.core.ElementClickEvent();
        /**
         * WooCommerce
         */
        LaStudio.core.CustomFunction();
        $(document).trigger('LaStudio:Document:AfterRunScript');

        $(document).on('lastudio-kit/carousel/init_success', function (e, data){
            LaStudio.core.initAll(data.swiperContainer);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        LaStudio.core.Blog();
        if($('.site-header--default').length){
            LaStudio.core.Blog($('.site-header--default'))
        }
    })

    window.addEventListener('load', LaStudio.core.OnLoadEvent);
    LaStudio.core.DomLoadEvent();
    $(document).trigger('LaStudio:Document:AfterInitAllScript');
})(jQuery);


(function ($) {
    'use strict';

    $(function () {
        $(document).on('LaStudio:Component:Popup:beforeOpen', function (evt, $instance){
            if($instance.type === "iframe"){
                var _pattern = /\.(mp4|m4p|m4v|mov|webm|ogg)(\?\S*)?$/i;
                if($instance.target.match(_pattern)){
                    var $videoEl = $('<video></video>');
                    $videoEl.attr('src', $instance.target);
                    var videoTagRef = $videoEl[0];
                    videoTagRef.addEventListener('loadedmetadata', function(e){
                        var _size = LaStudio.global.calculateAspectRatioFit(videoTagRef.videoWidth, videoTagRef.videoHeight, (window.innerWidth * 0.9), (window.innerHeight * 0.9))
                        $instance.$instance.addClass('featherlight--cvideo').css({
                            '--video-ratio': videoTagRef.videoHeight / videoTagRef.videoWidth,
                            '--video-naturalWidth': _size.width + 'px',
                            '--video-naturalHeight': _size.height + 'px',
                        });
                    });
                }
            }
        });
        $(document).on('LaStudio:Component:Popup:afterOpen', function (evt, $instance){
            if($instance.type === "iframe"){
                var _pattern = /\.(mp4|m4p|m4v|mov|webm|ogg)(\?\S*)?$/i;
                if($instance.target.match(_pattern)){
                    try{
                        var _doc = $instance.$content.get(0).contentDocument || $instance.$content.get(0).contentWindow.document;
                        _doc.querySelector('video').setAttribute('style', 'width: 100%');
                        $instance.$instance.addClass('featherlight--cvideo-internal');
                    }catch (ex){
                        $instance.$instance.addClass('featherlight--cvideo-external');
                    }
                }
            }
        });
    });

})(jQuery);

(function ($) {
    'use strict';
    $(function () {

        // $('.elementor-location-header >.elementor-top-section > .e-con-inner > .elementor-element.e-con, .elementor-location-header >.elementor-top-section > .elementor-element.e-con')
        //     .on('sticky:stick', function (e){
        //         let $parent = $(this).closest('.elementor-top-section');
        //         console.log($parent);
        //         console.log('bind')
        //     })
        //     .on('sticky:unstick', function (e){
        //         let $parent = $(this).closest('.elementor-top-section');
        //         console.log($parent);
        //         console.log('sticky:unstick')
        //     })

        $(document).on('click', '.elementor-widget[data-send2frm] .elementor-widget-container', function (e){
            e.preventDefault();
            var $parent = $(this).closest('.elementor-widget'),
                _field_name = $parent.attr('data-field'),
                _field_value = $parent.attr('data-value');

            $('.elementor-widget[data-field="'+_field_name+'"]').removeClass('selected');
            $parent.addClass('selected');
            $('[name="'+_field_name+'"]').val(_field_value);
        });

        $(document).on('lastudio-kit/carousel/init_success', function (e, data){
            LaStudio.core.initAll(data.swiperContainer);
        });

        $(document).on('click', '.custom-featherlight-overlay', function (e){
            try{
                $.featherlight.close()
            }catch (e) {}
        })

    });
})(jQuery);