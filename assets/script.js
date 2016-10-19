/*eslint
comma-spacing: 2,
dot-notation: [2, {"allowKeywords": true}],
eqeqeq: 2,
indent: 2,
key-spacing: [2, {"beforeColon": false, "afterColon": true}],
no-console: 0,
no-empty-function: 2,
no-empty: ["error", { "allowEmptyCatch": true }],
no-eval: 2,
no-extend-native: 0,
no-inner-declarations: 2,
no-loop-func: 2,
no-mixed-spaces-and-tabs: 2,
no-multi-spaces: 2,
no-new-func: 0,
no-new: 0,
no-shadow: 2,
no-trailing-spaces: "error",
no-undef: 0,
no-underscore-dangle: 0,
no-unused-vars: 0,
no-use-before-define: 2,
quotes: [2, "double"],
semi: 2,
space-before-blocks: 2,
space-before-function-paren: [2, {"anonymous": "always", "named": "never"}],
strict: [2, "function"]*/

// Detect DOM change https://developers.google.com/web/updates/2012/02/Detect-DOM-changes-with-Mutation-Observers

(function (factory) {
    "use strict";

    // Initialize

    // ES6 UMD http://jsrocks.org/2014/07/a-new-syntax-for-modules-in-es6/
    if (typeof define === "function" && define.amd) {
        // AMD
        define(["as"], factory);
    } else if (typeof module === "object" && module.exports) {
        // Node.js, CommonJS
        module.exports = factory();
    } else {
        // Browser globals
        window.as = factory();
    }
}(function () {
    "use strict";

    var w = window,
        d = document,
        n = navigator,
        h = history,
        l = location,
        ui = {
            html: d.documentElement,
            wrapper: d.getElementById("wrapper"),

            // Expandable navigation
            bar: d.getElementById("bar"),
            collapse: d.getElementById("collapse"),
            focusin: d.getElementById("focusin"),
            focusout: d.getElementById("focusout"),
            reset: d.getElementById("reset"),

            // Top navigation and content
            nav: d.getElementById("nav"),
            status: d.getElementById("status"),
            output: d.getElementById("output")
        },
        has = {
            // classList supported since IE10
            classList: "classList" in ui.html,
            // DOM 2 spec: element.click() defined only for HTMLInputElement http://www.w3.org/TR/DOM-Level-2-HTML/ecma-script-binding.html
            click: "click" in ui.html,
            // DNT (Do Not Track)
            dnt: n.doNotTrack === "1" || w.doNotTrack === "1" || n.msDoNotTrack === "1",
            // Error handler for Ajax requests
            error: {
                e: null
            },
            // addEventListener supported since IE9
            eventListener: !!d.addEventListener,
            // Pointer Events vs touch vs click
            // https://bugs.chromium.org/p/chromium/issues/detail?id=152149
            // http://www.stucox.com/blog/you-cant-detect-a-touchscreen/
            pointer: n.pointerEnabled ? "pointerdown" : (n.maxTouchPoints > 0 || w.matchMedia && w.matchMedia("(pointer: coarse)").matches || "ontouchstart" in w ? "touchstart" : "mousedown"),
            valid: function (fn) {
                // V8 optimized try-catch http://stackoverflow.com/questions/19727905/in-javascript-is-it-expensive-to-use-try-catch-blocks-even-if-an-exception-is-n
                try {
                    return fn();
                } catch (e) {
                    this.error.e = e;
                    return this.error;
                }
            }
        },
        api = { // Readable API
            // String, semantic versioning http://semver.org (MAJOR.MINOR.PATCH)
            version: "5.1",

            // Number (maximal width of device adaptation)
            viewportWidth: 720,

            // String (Google Analytics ID "UA-XXXX-Y")
            analytics: undefined,

            // Boolean (user agent DNT)
            dnt: has.dnt,

            // String (Google Analytics domain)
            domain: undefined,

            // String (project root)
            origin: (function () {
                var currentScript = d.currentScript || (function () {
                        var script = d.getElementsByTagName("script");
                        return script[script.length - 1];
                    }()),
                    origin = currentScript.src.split("#")[1] || "/ajax-seo";

                if (origin === "/") {
                    return l.origin;
                } else {
                    return decodeURIComponent(d.URL).replace(new RegExp("(" + origin + ")(.*)$"), "$1");
                }
            }()),

            // String (current page URL) http://jsperf.com/document-url-vs-window-location-href/2
            url: decodeURIComponent(d.URL),

            // String (current page title)
            title: d.title,

            // Element or null (the focused DOM Element based on as.url)
            activeElement: (function () {
                var arr = d.querySelectorAll ? d.querySelectorAll("[href]:not([target=_blank])") : [],
                    url = decodeURIComponent(d.URL).toUpperCase(),
                    i;

                for (i = 0; i < arr.length; i += 1) {
                    if (arr[i].href.toUpperCase() === url) {
                        // Normalize strings to uppercase https://msdn.microsoft.com/en-us/library/bb386042.aspx
                        return arr[i];
                    }
                }

                return null;
            }()),

            // Boolean (detect if shown error page)
            error: undefined
        },
        console = w.console || {
            error: function () {
                return;
            }
        },
        evnt = {},
        statusTimer,
        client, // XMLHttpRequest
        root;

    if (!has.eventListener) {
        // Stop here IE8
        api.error = "Browser missing EventListener support";

        console.error(api.error, "http://caniuse.com/#feat=addeventlistener");
        return api;
    } else if (!api.dnt && api.analytics) {
        // Google Analytics
        // Respect DNT (Do Not Track)
        evnt.analytics = {
            listener: function (flag) {
                flag = flag === true ? "addEventListener" : "removeEventListener";

                ui.analytics[flag]("load", evnt.analytics.load);
                ui.analytics[flag]("error", evnt.analytics.listener);
                ui.analytics[flag]("readystatechange", evnt.analytics.readystatechange);

                if (!flag) {
                    ui.analytics.removeAttribute("id");
                }
            },
            load: function () {
                // Disabling cookies https://developers.google.com/analytics/devguides/collection/analyticsjs/cookies-user-id#disabling_cookies
                ga("create", api.analytics, api.domain, {
                    storage: "none",
                    clientId: localStorage.gaClientId
                });

                if (!localStorage.gaClientId) {
                    ga(function (tracker) {
                        localStorage.gaClientId = tracker.get("clientId");
                    });
                }

                ga("send", "pageview");

                evnt.analytics.listener();
            },
            readystatechange: function () {
                if (ui.analytics.readyState === "complete" || ui.analytics.readyState === "loaded") {
                    if (typeof ga === "function") {
                        evnt.analytics.load();
                    } else {
                        evnt.analytics.listener();
                    }
                }
            },
            timestamp: +new Date + ""
        };

        ui.analytics = d.createElement("script");
        ui.analytics.src = "//www.google-analytics.com/analytics.js";
        ui.analytics.id = evnt.analytics.timestamp;

        d.body.appendChild(ui.analytics);

        ui.analytics = d.getElementById(evnt.analytics.timestamp);

        if (ui.analytics) {
            evnt.analytics.listener(true);
        }
    }

    if (!has.classList && Element.prototype) {
        // classList polyfill for IE9 https://gist.github.com/devongovett/1381839
        Object.defineProperty(Element.prototype, "classList", {
            get: function () {
                var self = this;

                function classlist() {
                    return self.className.split(/\s+/);
                }
                function update(fn) {
                    return function (value) {
                        var classes = classlist(),
                            index = classes.indexOf(value);

                        fn(classes, index, value);
                        self.className = classes.join(" ");
                    };
                }

                return {
                    add: update(function (classes, index, value) {
                        ~index || classes.push(value);
                    }),
                    remove: update(function (classes, index) {
                        ~index && classes.splice(index, 1);
                    }),
                    item: function (index) {
                        return classlist()[index] || null;
                    },
                    toggle: update(function (classes, index, value) {
                        ~index ? classes.splice(index, 1) : classes.push(value);
                    }),
                    contains: function (value) {
                        return !!~classlist().indexOf(value);
                    }
                };
            }
        });
    }

    if (ui.wrapper && ui.bar && ui.collapse && ui.focusin && ui.focusout && ui.reset && ui.nav && ui.output) {
        // EventListener and CSS media query supported in IE9

        // Convert NodeList to Array http://jsperf.com/convert-nodelist-to-array
        // https://davidwalsh.name/array-from http://toddmotto.com/a-comprehensive-dive-into-nodelists-arrays-converting-nodelists-and-understanding-the-dom/
        // ES6 Array spread operator [...ui.nav.querySelectorAll("a")]
        ui.nodeList = ui.nav && Array.from && Array.from(ui.nav.querySelectorAll("a")) || [].slice.call(ui.nav.querySelectorAll("a"));
        has.touch = has.pointer === "touchstart";

        ui.closest = function (el, selector) {
            if (!el || !selector) {
                return null;
            } else if (el.closest) {
                // http://jsperf.com/native-vs-jquery-closest/3
                // Native element.closest(selectors) standard https://dom.spec.whatwg.org/#dom-element-closest and https://developer.mozilla.org/en-US/docs/Web/API/Element.closest similar to $(selector).closest(selector), supported on Chrome 40 http://blog.chromium.org/2014/12/chrome-40-beta-powerful-offline-and.html
                return el.closest(selector);
            }

            var matches = el.matches || el.webkitMatchesSelector || el.msMatchesSelector;

            while (el && el.nodeType === 1) {
                if (matches.call(el, selector)) {
                    return el;
                } else {
                    el = el.parentNode;
                }
            }

            return null;
        };
        ui.anchor = function (el) {
            if (el) {
                if (el.tagName !== "A") {
                    el = ui.closest(el, "a[href]");
                }

                return el && el.tagName === "A" && el.href && el.target !== "_blank" ? el : null;

            } else {
                return null;
            }
        };

        evnt.nav = {
            expand: function () {
                // Perf http://jsperf.com/document-body-parentelement
                ui.html.classList.add("noscroll");
                ui.status.classList.add("expand");
            },
            toggleReal: function (e) {
                if (ui.status.classList.contains("expand")) {
                    // preventDefault is required, otherwise when focused element, click will colapse and expand
                    e.preventDefault();

                    evnt.nav.preventPassFocus = true;

                    ui.html.classList.remove("noscroll");
                    ui.collapse.setAttribute("tabindex", 0);

                    setTimeout(function () {
                        ui.focusout.setAttribute("tabindex", 0);
                        ui.collapse.focus();
                        ui.status.classList.remove("expand");
                    }, 10);
                } else if (e.type === "touchstart") {
                    e.preventDefault();
                    evnt.nav.expand();
                } else {
                    setTimeout(function () {
                        // Old Webkit compatibility
                        if (d.activeElement !== e.target) {
                            e.target.focus();
                        }
                    }, 0);
                }
            },
            focus: function (e) {
                if (!ui.status.classList.contains("expand")) {
                    e.target.blur();

                    ui.nav.scrollTop = 0;
                    evnt.nav.expand();

                    ui.focusin.setAttribute("tabindex", 0);
                    ui.focusout.removeAttribute("tabindex");

                    setTimeout(function () {
                        ui.focusin.focus();
                    }, 10);
                }
            },
            disable: function (e) {
                e.target.removeAttribute("tabindex");
            },
            collapse: function (e) {
                var pointerdown = e && (e.type === "pointerdown" || e.type === "mousedown"),
                    el;

                if (pointerdown && e.target === ui.nav && ui.nav.clientWidth <= e.clientX) {
                    // Prevent collapse onclick on scrollbar
                    e.preventDefault();
                } else if (!pointerdown || e.which === 1) {
                    // Collapse only by mouse left click (not mousewheel or right click)
                    if (pointerdown) {
                        el = ui.anchor(e.target);

                        if (el) {
                            el.click();
                        }
                    }

                    ui.html.classList.remove("noscroll");
                    ui.status.classList.remove("expand");

                    setTimeout(function () {
                        evnt.nav.preventPassFocus = true;
                        ui.focusout.setAttribute("tabindex", 0);
                    }, 10);
                }
            },
            collapseTab: function (e) {
                if (!e.shiftKey && (e.key === "Tab" || e.keyCode === 9)) {
                    evnt.nav.collapse(e);

                    setTimeout(function () {
                        ui.collapse.setAttribute("tabindex", 0);

                        setTimeout(function () {
                            ui.collapse.focus();
                        }, 10);
                    }, 0);
                }
            },
            keydown: function (e) {
                if (e.target === ui.bar && (e.key === "Enter" || e.keyCode === 13)) {
                    evnt.nav.toggleReal(e);
                }
                if ((e.target === ui.focusout ? !e.shiftKey : e.shiftKey) && (e.key === "Tab" || e.keyCode === 9)) {
                    evnt.nav.collapse(e);

                    if (e.target === ui.focusout && !e.shiftKey) {
                        ui.collapse.setAttribute("tabindex", 0);

                        setTimeout(function () {
                            ui.collapse.focus();
                        }, 10);
                    }
                }
            },
            passFocus: function (e) {
                if (evnt.nav.preventPassFocus) {
                    delete evnt.nav.preventPassFocus;
                } else if (!ui.status.classList.contains("expand")) {
                    ui.focusout.focus();
                    evnt.nav.disable(e);
                }
            },
            init: function () {
                var self = evnt.nav;

                if (ui.wrapper.offsetWidth <= api.viewportWidth ? !self.events : self.events) {
                    self.events = !self.events;
                    self.listener = self.events ? "addEventListener" : "removeEventListener";

                    ui.bar[self.listener](has.pointer, self.toggleReal, true);

                    if (!has.touch) {
                        ui.bar[self.listener]("focus", self.focus, true);
                        ui.bar[self.listener]("keydown", self.keydown, true);
                        ui.focusin[self.listener]("blur", self.disable, true);

                        if (ui.nodeList) {
                            ui.nodeList[ui.nodeList.length - 1][self.listener]("keydown", self.collapseTab, true);
                        }

                        ui.focusout[self.listener]("focus", self.expand, true);
                        ui.focusout[self.listener]("blur", self.disable, true);
                        ui.focusout[self.listener]("keydown", self.keydown, true);
                        ui.collapse[self.listener]("focus", self.passFocus, true);
                        ui.collapse[self.listener]("blur", self.disable, true);
                        ui.reset[self.listener]("blur", self.disable, true);
                        ui.nav[self.listener](has.pointer, self.collapse, true);
                    } else {
                        ui.nav[self.listener]("click", self.collapse, true);
                    }

                    ui.reset[self.listener](has.pointer, self.collapse, true);
                }
            }
        };

        evnt.nav.init();

        w.addEventListener("resize", function () {
            if (evnt.nav.timeoutScale) {
                clearTimeout(evnt.nav.timeoutScale);
            }

            // Don't execute too often
            evnt.nav.timeoutScale = setTimeout(evnt.nav.init, 100);
        }, true);
    } else {
        api.error = "Missing HTML Elements";

        console.error(api.error, "https://github.com/laukstein/ajax-seo");
        return api;
    }

    if (!h.pushState) {
        // Stop here IE10 and Android 4.3
        api.error = "Browser missing History API support";

        console.error(api.error, "http://caniuse.com/#feat=history");
        return api;
    }

    root = {
        filter: function (srt, noLowerCase) {
            if (srt) {
                // Remove hash from URL http://jsperf.com/url-replace-vs-match/2
                srt = decodeURIComponent(srt).replace(/#.*$/, "");
                return noLowerCase ? srt : srt.toLowerCase();
            }
        },
        reset: function () {
            if (statusTimer) {
                clearTimeout(statusTimer);
            }
            if (ui.status && ui.status.classList.contains("status-start")) {
                ui.status.classList.add("status-done");
            }
        },
        click: function (el) {
            if (el) {
                if (has.click) {
                    el.click();
                } else {
                    // Old Webkit legacy, alternative https://developer.mozilla.org/en-US/docs/Web/API/Document/elementsFromPoint
                    var evt = d.createEvent("MouseEvents");

                    evt.initEvent("click", true, true);
                    el.dispatchEvent(evt);
                }
            }
        },
        nav: {
            nodeList: ui.nodeList,
            // Element or null
            activeElement: function () {
                if (root.nav.nodeList) {
                    var i;

                    // Loop performance https://www.youtube.com/watch?v=taaEzHI9xyY#t=3042 http://www.impressivewebs.com/javascript-for-loop/ http://jsperf.com/array-length-vs-cached/19
                    for (i = 0; i < root.nav.nodeList.length; i += 1) {
                        if (root.filter(root.nav.nodeList[i].href) === api.url) {
                            return root.nav.nodeList[i];
                        }
                    }
                }

                return null;
            }
        },
        update: function (data, track, activeElement) {
            if (data) {
                if (!api.dnt && api.analytics && typeof ga === "function") {
                    // Track Ajax page requests
                    ga("send", "pageview", {
                        page: api.url
                    });
                }

                if (!track) {
                    client.abort();
                } else {
                    root.reset();
                }

                if (root.nav.nodeList) {
                    ui.focus = ui.nav.querySelector(".focus");
                    ui.active = ui.nav.querySelector(".active");
                    ui.error = ui.nav.querySelector(".error");

                    if (ui.focus) {
                        ui.focus.classList.remove("focus");
                    }
                    if (ui.active) {
                        ui.active.classList.remove("active");
                    }
                    if (ui.error) {
                        ui.error.classList.remove("error");
                    }
                }

                api.url = root.filter(d.URL);
                api.activeElement = activeElement || root.nav.activeElement();

                if (api.activeElement) {
                    api.activeElement.focus();
                    api.activeElement.classList.add(api.error ? "error" : "active");

                    if (api.error) {
                        api.activeElement.classList.add("x-error");
                    }
                }

                if (api.error) {
                    ui.status.classList.add("error");
                    ui.status.classList.add("status-error");
                } else {
                    ui.status.classList.remove("error");
                    ui.status.classList.remove("status-error");
                }

                d.title = api.title = data.title;

                // Fixing scrollTop with Document.scrollingElement https://dev.opera.com/articles/fixing-the-scrolltop-bug/ http://dev.w3.org/csswg/cssom-view/#dom-document-scrollingelement
                var scrollingElement = d.scrollingElement || ui.html.scrollTop || d.body;
                scrollingElement.scrollTop = 0;

                ui.output.innerHTML = data.content;

                if (l.hash) {
                    l.replace(api.url + l.hash);
                }

                delete root.inprogress;
            }
        },
        retry: false,
        popstate: function (e) {
            var state = e.state,
                activeElement;

            root.reset();

            root.retry = !state;
            api.error = state && state.error || false;

            if (!state && e.srcElement.location.pathname !== e.target.location.pathname) {
                // retry, prevented on hash change https://gist.github.com/mahemoff/1591495
                api.url = root.filter(d.URL);
                activeElement = root.nav.activeElement();

                root.click(activeElement);
            }

            // Chrome bug: XMLHttpRequest error avoids first popstate cache and recreates XMLHttpRequest (perhaps https://bugs.chromium.org/p/chromium/issues/detail?id=371549 will fix it)
            // 1. Fire XMLHttpRequest by clicking on different links till some of links returns an error
            // 2. Navigate history back - Chrome will recreate XMLHttpRequest for the first h.go -1. History -2, -3, etc. will return from cache accurately. Firefox correctly returns all from cache.
            root.update(state, false, activeElement);
        },
        loadstart: function () {
            if (ui.status) {
                ui.status.classList.remove("status-done");
                ui.status.classList.remove("status-start");

                if (statusTimer) {
                    clearTimeout(statusTimer);
                }

                statusTimer = setTimeout(function () {
                    // Will be avoided if content already in cache
                    ui.status.classList.add("status-start");
                }, 0);
            }
        },
        callback: function (data) {
            api.error = data.error || false;
            api.activeElement = root.nav.activeElement() || api.activeElement;

            h.replaceState(data, data.title, null);
            root.update(data, true, api.activeElement);
        },
        load: function () {
            var response = this.response;
            response = has.valid(function () {
                return JSON.parse(response);
            });

            root.callback(response === has.error ? {
                error: true,
                title: "Server error",
                content: "<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href=" + api.url + ">reload</a>" + (api.url === api.origin ? "" : " or head to <a href=" + api.origin + ">home page</a>") + "."
            } : response);
        },
        resetStatus: function (e) {
            if (ui.status) {
                if (ui.status.classList.contains("status-error") && (!e || !api.error)) {
                    ui.status.classList.remove("status-error");
                }
                if (ui.status.classList.contains("status-done")) {
                    ui.status.classList.remove("status-start");
                    ui.status.classList.remove("status-done");
                }
            }
        },
        listener: function (e) {
            if (e) {
                var el = ui.anchor(e.target),
                    patt = new RegExp("^" + api.origin + "($|#|/.{1,}).*", "i"),
                    url = {};

                // Run script only if has a link and matches "api.origin"
                if (!el || !patt.test(el.href.replace(/\/$/, ""))) {
                    // Outside API scope, missing "href" or has "_blank"
                    return;
                }

                setTimeout(function () {
                    if (el !== d.activeElement) {
                        el.focus();
                    }
                }, 0);

                if (el.href.toLowerCase() === api.url.toLowerCase()) {
                    // Is same URL
                    e.preventDefault();
                    return;
                }

                // Lowercase URL
                // Remove multiple trailing slashes except to protocol
                // Remove trailing slash from URL end
                api.url = el.href.toLowerCase().replace(/(\/)+(?=\1)/g, "").replace(/(^https?:(\/))/, "$1/").replace(/\/$/, "");
                url.attr = root.filter(api.url, true);
                url.url = decodeURIComponent(d.URL);
                url.address = root.filter(url.url);

                if (url.attr === url.address && api.url.indexOf("#") > -1) {
                    // Same link with + hash
                    setTimeout(function () {
                        h.replaceState({
                            error: api.error,
                            title: api.title,
                            content: ui.output.innerHTML
                        }, d.title, decodeURIComponent(api.url));
                    }, 0);

                    return;
                }

                e.preventDefault();

                if (evnt.nav.events && ui.status.classList.contains("expand")) {
                    evnt.nav.collapse();
                    ui.reset.setAttribute("tabindex", 0);

                    setTimeout(function () {
                        ui.reset.focus();
                    }, 10);
                }

                api.activeElement = el;
                api.activeNav = el.parentNode === ui.nav;

                if (!root.retry && api.activeNav) {
                    api.error = api.activeElement.classList.contains("x-error");
                }

                // Node.textContent performs faster that Node.innerText http://www.kellegous.com/j/2013/02/27/innertext-vs-textcontent/
                api.title = api.activeElement.textContent;

                if (api.error && url.address === url.url) {
                    h.replaceState(null, api.title, api.url);
                } else if (api.url !== url.url) {
                    h.pushState(null, api.title, api.url);
                }

                if (!api.error && !root.retry && (url.attr === url.address) || api.activeNav && api.activeElement.classList.contains("focus")) {
                    // Avoid API retry on same link if has not error status
                    return;
                }

                d.title = api.title;

                root.resetStatus();

                if (root.nav.nodeList) {
                    if (api.error && api.activeNav) {
                        api.activeElement.classList.remove("x-error");
                        api.activeElement.classList.remove("error");
                    }

                    ui.focus = ui.nav.querySelector(".focus");

                    if (ui.focus) {
                        ui.focus.classList.remove("focus");
                    }
                }
                if (api.activeNav) {
                    api.activeElement.classList.add("focus");
                }
                if (root.inprogress) {
                    // Stop awaiting requests
                    client.abort();

                    if (w.stop) {
                        w.stop();
                    } else if (d.execCommand) {
                        d.execCommand("Stop", false);
                    }
                }

                client.open("GET", api.origin + "/api" + url.attr.replace(new RegExp("^" + api.origin, "i"), ""));

                if (api.error) {
                    // Avoid cache http://stackoverflow.com/questions/1046966/whats-the-difference-between-cache-control-max-age-0-and-no-cache
                    // Firefox bug https://bugzilla.mozilla.org/show_bug.cgi?id=706806 https://bugzilla.mozilla.org/show_bug.cgi?id=428916 https://bugzilla.mozilla.org/show_bug.cgi?id=443098
                    // client.setRequestHeader("Cache-Control", "no-cache");
                    client.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
                }

                root.inprogress = true;

                client.send();
            }
        },
        init: function () {
            if (ui.status) {
                // Loading status reset
                ui.status.addEventListener("transitionend", root.resetStatus, true);
            }

            setTimeout(function () {
                // Old Webkit initial run popstate bug https://bugs.chromium.org/p/chromium/issues/detail?id=63040, fixed on Chrome 34
                // Chrome popstate bug with hashchange: multiple clicks on same hash URL will save lots of history
                // Chrome repeatedly repeated same hash URL history/popstate by onclick on same URL https://bugs.chromium.org/p/chromium/issues/detail?id=371549 http://jsbin.com/371549/1
                // http://jsperf.com/onpopstate-vs-addeventlistener
                w.onpopstate = root.popstate;
            }, 150);

            // Initial popstate state
            h.replaceState({
                error: api.error,
                title: api.title,
                content: ui.output.innerHTML
            }, api.title, api.url);

            // XMLHttpRequest https://xhr.spec.whatwg.org
            client = new XMLHttpRequest();
            // // IE11: SCRIPT5022: SyntaxError
            // client.open("GET", null);
            // // IE11: SCRIPT5022: InvalidStateError https://connect.microsoft.com/IE/feedback/details/794808
            // client.responseType = "json";
            // // would loop 4 times
            // client.addEventListener("readystatechange", root.callback, true);
            client.addEventListener("loadstart", root.loadstart, true);
            client.addEventListener("load", root.load, true);
            client.addEventListener("abort", root.reset, true);

            // http://jsperf.com/addeventlistener-usecapture-true-vs-false
            ui.html.addEventListener("click", root.listener, true);
        }
    };

    if (!api.analytics) {
        delete api.analytics;
    }
    if (!api.domain) {
        delete api.domain;
    }

    // Apply events
    root.init();

    api.error = ui.status && ui.status.classList.contains("status-error");

    // Return readable API
    return api;
}));
