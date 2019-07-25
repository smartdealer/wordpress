(SmartDealer = {
    config: {
        debug: true
    },
    data: {},
    events: [],
    addEvent: function (a) {
        this.events.push(a);
    },
    setComplete: function (a) {
        this.data.callback = a;
    },
    loadComplete: function () {
        this.debug('complete');
        if (typeof this.data.callback === 'function') {
            this.data.callback(jQuery);
        }
    },
    debug: function (a) {
        if (a && this.config.debug && typeof console === 'object') {
            console.log(a);
        }
    },
    loadEvents: function () {
        this.debug('load events');
        for (var x in this.events) {
            fn = this.events[x];
            if (typeof fn === 'function') {
                fn();
            }
        }
    },
    loadImage: function (el, fn) {
        src = el.getAttribute('data-src');
        el.src = src;
        el.onload = function () {
            this.removeAttribute('style');
        };
    },
    elementInViewport: function (el) {
        var rect = el.getBoundingClientRect()

        return (
            rect.top >= 0
            && rect.left >= 0
            && rect.top <= (window.innerHeight || document.documentElement.clientHeight)
        );
    },
    loadLazy: function () {
        this.debug('lazy load images');
        var $q = function (q, res) {
            if (document.querySelectorAll) {
                res = document.querySelectorAll(q);
            } else {
                var d = document
                    , a = d.styleSheets[0] || d.createStyleSheet();
                a.addRule(q, 'f:b');
                for (var l = d.all, b = 0, c = [], f = l.length; b < f; b++)
                    l[b].currentStyle.f && c.push(l[b]);
                a.removeRule(0);
                res = c;
            }
            return res;
        }, addEventListener = function (evt, fn) {
            window.addEventListener ? this.addEventListener(evt, fn, false) : (window.attachEvent) ? this.attachEvent('on' + evt, fn) : this['on' + evt] = fn;
        }, _has = function (obj, key) {
            return Object.prototype.hasOwnProperty.call(obj, key);
        };
        var images = new Array(), query = $q('img.lazy'), processScroll = function () {
            for (var i = 0; i < images.length; i++) {
                if (SmartDealer.elementInViewport(images[i])) {
                    SmartDealer.loadImage(images[i], function () {
                        images.splice(i, i);
                    });
                }
            };
        };
        for (var i = 0; i < query.length; i++) {
            images.push(query[i]);
        };
        processScroll();
        addEventListener('scroll', processScroll);
    },
    init: function () {
        this.debug('init class');
        window.onload = function () {
            SmartDealer.loadEvents();
            SmartDealer.loadComplete();
        }

    }
}).init();