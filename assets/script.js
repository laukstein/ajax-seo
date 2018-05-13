"use strict";

(function (w) {
    var d = document,
        n = navigator,
        h = history,
        l = location,
        ui = {
            bar: d.getElementById("bar"),
            collapse: d.getElementById("collapse"),
            focusin: d.getElementById("focusin"),
            focusout: d.getElementById("focusout"),
            html: d.documentElement,
            nav: d.getElementById("nav"),
            output: d.getElementById("output"),
            reset: d.getElementById("reset"),
            status: d.getElementById("status"),
            wrapper: d.getElementById("wrapper")
        },
        has = {
            // classList supported since IE10
            classList: "classList" in ui.html,
            // DOM 2 spec: element.click() defined only for HTMLInputElement
            // http://www.w3.org/TR/DOM-Level-2-HTML/ecma-script-binding.html
            click: "click" in ui.html,
            // DNT (Do Not Track)
            dnt: n.doNotTrack === "1" || w.doNotTrack === "1" || n.msDoNotTrack === "1",
            // Error handler for Ajax requests
            error: {e: null},
            // addEventListener supported since IE9
            eventListener: !!d.addEventListener,
            eventListenerOptions: (function () {
                // Resource http://tonsky.me/blog/chrome-intervention/
                // Spec issue https://github.com/whatwg/dom/issues/491
                var supports = false;

                try {
                    d.addEventListener && addEventListener("test", null, {
                        get passive() {
                            supports = true;
                        }
                    });
                } catch (e) {}

                return supports;
            }()),
            // Pointer vs touch vs click event
            // https://bugs.chromium.org/p/chromium/issues/detail?id=152149
            // http://www.stucox.com/blog/you-cant-detect-a-touchscreen/
            pointer: w.PointerEvent ? "pointerdown" : n.maxTouchPoints > 0 || (w.matchMedia ?
                w.matchMedia("(pointer: coarse)").matches : "ontouchstart" in w) ? "touchstart" : "mousedown",
            valid: function (fn) {
                // V8 optimized try-catch http://stackoverflow.com/questions/19727905
                try {
                    return fn();
                } catch (e) {
                    this.error.e = e;

                    return this.error;
                }
            }
        },
        // Readable API
        api = {
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

            // String (Google Analytics ID "UA-XXXX-Y")
            // analytics: undefined,

            // Boolean (respect user agent DNT)
            dnt: false,

            // String (Google Analytics domain)
            // domain: undefined,

            // Boolean (detect if shown error page)
            // error: undefined,

            // String (project root)
            origin: (function () {
                var currentScript = d.currentScript || (function () {
                        var script = d.getElementsByTagName("script");

                        return script[script.length - 1];
                    }()),
                    origin = currentScript.src.split("#")[1] || "/ajax-seo";

                if (origin === "/") {
                    return l.origin;
                }

                return decodeURIComponent(d.URL).replace(new RegExp("(" + origin + ")(.*)$"), "$1");
            }()),

            // String (current page title)
            title: d.title,

            // String (current page URL) http://jsperf.com/document-url-vs-window-location-href/2
            url: decodeURIComponent(d.URL),

            // String, semantic versioning http://semver.org (MAJOR.MINOR.PATCH)
            version: "5.4.0",

            // Number (maximal width of device adaptation)
            viewportWidth: 720
        },
        console = w.console || {
            error: function () {
                return arguments;
            }
        },
        event = {},
        statusTimer,
        // XMLHttpRequest
        client,
        root;

    if (!has.eventListener) {
        // Stop here IE8
        api.error = "Browser missing EventListener support";

        console.error(api.error, "http://caniuse.com/#feat=addeventlistener");

        return api;
    } else if (api.analytics && (!has.dnt || !api.dnt)) {
        try {
            // Safari Private Browsing doesn't support localStorage
            localStorage.localStorage = "1";
            delete localStorage.localStorage;
        } catch (e) {
            if (w.localStorage) {
                // Required for Safari Private Browsing
                delete w.localStorage;
            }

            w.localStorage = {};
        }

        // Google Analytics
        // Respect DNT (Do Not Track)
        event.analytics = {
            listener: function (flag) {
                flag = flag === true ? "addEventListener" : "removeEventListener";

                ui.analytics[flag]("load", event.analytics.load);
                ui.analytics[flag]("error", event.analytics.listener);
                ui.analytics[flag]("readystatechange", event.analytics.readystatechange);

                if (!flag) {
                    ui.analytics.removeAttribute("id");
                }
            },
            load: function () {
                if (typeof w.ga === "function") {
                    ga("create", api.analytics, api.domain, {
                        // Anonymize IP https://support.google.com/analytics/answer/2763052
                        anonymizeIp: true,

                        // No-cookie https://developers.google.com/analytics/devguides/collection/analyticsjs/cookies-user-id
                        clientId: localStorage.gaClientId,
                        storage: "none"
                    });

                    if (!localStorage.gaClientId) {
                        ga(function (tracker) {
                            localStorage.gaClientId = tracker.get("clientId");
                        });
                    }

                    event.analytics.listener();
                    event.analytics.track();
                }
            },
            readystatechange: function () {
                if (ui.analytics.readyState === "complete" || ui.analytics.readyState === "loaded") {
                    if (typeof w.ga === "function") {
                        event.analytics.load();
                    } else {
                        event.analytics.listener();
                    }
                }
            },
            timestamp: +new Date + "",
            track: function () {
                if (typeof w.ga === "function") {
                    // Page tracking https://developers.google.com/analytics/devguides/collection/analyticsjs/pages
                    ga("send", {
                        hitType: "pageview",
                        title: d.title,
                        page: location.pathname
                    });
                }
            }
        };

        ui.analytics = d.createElement("script");
        ui.analytics.src = "https://www.google-analytics.com/analytics.js";
        ui.analytics.id = event.analytics.timestamp;

        d.body.appendChild(ui.analytics);

        ui.analytics = d.getElementById(event.analytics.timestamp);

        if (ui.analytics) {
            event.analytics.listener(true);
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
                    contains: function (value) {
                        return !!~classlist().indexOf(value);
                    },
                    item: function (index) {
                        return classlist()[index] || null;
                    },
                    remove: update(function (classes, index) {
                        ~index && classes.splice(index, 1);
                    }),
                    toggle: update(function (classes, index, value) {
                        ~index ? classes.splice(index, 1) : classes.push(value);
                    })
                };
            }
        });
    }

    if (ui.wrapper && ui.bar && ui.collapse && ui.focusin && ui.focusout && ui.reset && ui.nav && ui.output) {
        // EventListener and CSS media query supported in IE9

        // Convert NodeList to Array http://jsperf.com/convert-nodelist-to-array
        // https://davidwalsh.name/array-from
        // http://toddmotto.com/a-comprehensive-dive-into-nodelists-arrays-converting-
        //     nodelists-and-understanding-the-dom/
        // ES6 Array spread operator [...ui.nav.querySelectorAll("a")]
        ui.nodeList = ui.nav && ui.nav.querySelectorAll("a");
        ui.nodeList = ui.nodeList && (Array.from && Array.from(ui.nodeList) || [].slice.call(ui.nodeList));
        has.touch = has.pointer === "touchstart";

        ui.closest = function (el, selector) {
            if (!el || !selector) {
                return null;
            } else if (el.closest) {
                // http://jsperf.com/native-vs-jquery-closest/3
                // Native element.closest(selectors) standard https://dom.spec.whatwg.org/#dom-element-closest
                // https://developer.mozilla.org/en-US/docs/Web/API/Element.closest
                // similar to $(selector).closest(selector)
                // supported on Chrome 40 http://blog.chromium.org/2014/12/chrome-40-beta-powerful-offline-and.html
                return el.closest(selector);
            }

            var matches = el.matches || el.webkitMatchesSelector || el.msMatchesSelector;

            while (el && el.nodeType === 1) {
                if (matches.call(el, selector)) {
                    return el;
                }

                el = el.parentNode;
            }

            return null;
        };
        ui.anchor = function (el) {
            if (el) {
                if (el.tagName !== "A") {
                    el = ui.closest(el, "a[href]");
                }

                return el && el.tagName === "A" && el.href && el.target !== "_blank" ? el : null;
            }

            return null;
        };

        event.nav = {
            expand: function () {
                // Perf http://jsperf.com/document-body-parentelement
                ui.html.classList.add("noscroll");
                ui.status.classList.add("expand");
            },
            toggleReal: function (e) {
                if (ui.status.classList.contains("expand")) {
                    if (e.type !== "touchstart" || !has.eventListenerOptions) {
                        // preventDefault is required, otherwise when focused element, click will colapse and expand
                        e.preventDefault();
                    }

                    event.nav.preventPassFocus = true;

                    ui.html.classList.remove("noscroll");
                    ui.collapse.setAttribute("tabindex", 0);

                    setTimeout(function () {
                        ui.focusout.setAttribute("tabindex", 0);
                        ui.collapse.focus({preventScroll: true});
                        ui.status.classList.remove("expand");
                    }, 10);
                } else if (e.type === "touchstart") {
                    if (!has.eventListenerOptions) {
                        // Unable to preventDefault inside passive event listener invocation
                        e.preventDefault();
                    }

                    event.nav.expand();
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
                    event.nav.expand();

                    ui.focusin.setAttribute("tabindex", 0);
                    ui.focusout.removeAttribute("tabindex");

                    setTimeout(function () {
                        ui.focusin.focus({preventScroll: true});
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
                        event.nav.preventPassFocus = true;
                        ui.focusout.setAttribute("tabindex", 0);
                    }, 10);
                }
            },
            collapseTab: function (e) {
                if (!e.shiftKey && (e.key === "Tab" || e.keyCode === 9)) {
                    event.nav.collapse(e);

                    setTimeout(function () {
                        ui.collapse.setAttribute("tabindex", 0);

                        setTimeout(function () {
                            ui.collapse.focus({preventScroll: true});
                        }, 10);
                    }, 0);
                }
            },
            keydown: function (e) {
                if (e.target === ui.bar && (e.key === "Enter" || e.keyCode === 13)) {
                    event.nav.toggleReal(e);
                }
                if ((e.target === ui.focusout ? !e.shiftKey : e.shiftKey) && (e.key === "Tab" || e.keyCode === 9)) {
                    event.nav.collapse(e);

                    if (e.target === ui.focusout && !e.shiftKey) {
                        ui.collapse.setAttribute("tabindex", 0);

                        setTimeout(function () {
                            ui.collapse.focus({preventScroll: true});
                        }, 10);
                    }
                }
            },
            passFocus: function (e) {
                if (event.nav.preventPassFocus) {
                    delete event.nav.preventPassFocus;
                } else if (!ui.status.classList.contains("expand")) {
                    ui.focusout.focus({preventScroll: true});
                    event.nav.disable(e);
                }
            },
            init: function (eventName) {
                var self = event.nav;

                if (eventName && eventName !== has.pointer || (ui.wrapper.offsetWidth <= api.viewportWidth ? !self.events : self.events)) {
                    self.events = !self.events;
                    self.listener = self.events ? "addEventListener" : "removeEventListener";
                    self.options = has.pointer === "touchstart" && has.eventListenerOptions ? {passive: true} : true;

                    ui.bar[self.listener](has.pointer, self.toggleReal, self.options);

                    if (self.events) {
                        ui.focusout.setAttribute("tabindex", 0);
                    } else {
                        ui.focusout.removeAttribute("tabindex");
                    }
                    if (has.touch) {
                        ui.nav[self.listener]("click", self.collapse, true);
                    } else {
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
                        ui.nav[self.listener](has.pointer, self.collapse, self.options);
                    }

                    ui.reset[self.listener](has.pointer, self.collapse, self.options);

                    if (eventName) {
                        has.pointer = eventName;
                        has.touch = has.pointer === "touchstart";
                        event.nav.init();
                    }
                }
            }
        };

        event.nav.init();

        if (has.pointer !== "pointerdown" && w.matchMedia) {
            w.matchMedia("(pointer: coarse)").addListener(function (e) {
                event.nav.init(e.matches ? "touchstart" : "mousedown");
            });
        }

        w.addEventListener("resize", function () {
            if (event.nav.timeoutScale) {
                clearTimeout(event.nav.timeoutScale);
            }

            // Don't execute too often
            event.nav.timeoutScale = setTimeout(event.nav.init, 100);
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
        callback: function (data) {
            api.error = data.error || false;
            api.activeElement = root.nav.activeElement() || api.activeElement;

            h.replaceState(data, data.title, null);
            root.update(data, true, api.activeElement);
        },
        click: function (el) {
            var evt;

            if (el) {
                if (has.click) {
                    el.click();
                } else {
                    // Old Webkit legacy, alternative
                    // https://developer.mozilla.org/en-US/docs/Web/API/Document/elementsFromPoint
                    evt = d.createEvent("MouseEvents");

                    evt.initEvent("click", true, true);
                    el.dispatchEvent(evt);
                }
            }
        },
        filter: function (srt, noLowerCase) {
            if (srt) {
                // Remove hash from URL http://jsperf.com/url-replace-vs-match/2
                srt = decodeURIComponent(srt).replace(/#.*$/, "");

                return noLowerCase ? srt : srt.toLowerCase();
            }
        },
        init: function () {
            if (ui.status) {
                // Loading status reset
                ui.status.addEventListener("transitionend", root.resetStatus, true);
            }

            setTimeout(function () {
                // Old Webkit initial run popstate bug
                // https://bugs.chromium.org/p/chromium/issues/detail?id=63040, fixed on Chrome 34
                //
                // Chrome popstate bug with hashchange: multiple clicks on same hash URL will save lots of history
                // Chrome repeatedly repeated same hash URL history/popstate by onclick on same URL
                // https://bugs.chromium.org/p/chromium/issues/detail?id=371549 http://jsbin.com/371549/1
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
        },
        listener: function (e) {
            var url = {},
                patt,
                el;

            if (e) {
                el = ui.anchor(e.target);
                patt = new RegExp("^" + api.origin + "($|#|/.{1,}).*", "i");

                // Run script only if has a link and matches "api.origin"
                if (!el || !patt.test(el.href.replace(/\/$/, ""))) {
                    // Outside API scope, missing "href" or has "_blank"
                    return;
                }

                setTimeout(function () {
                    if (el !== d.activeElement) {
                        el.focus({preventScroll: true});
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
                api.url = el.href.toLowerCase()
                    .replace(/(\/)+(?=\1)/g, "")
                    .replace(/(^https?:(\/))/, "$1/")
                    .replace(/\/$/, "");
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

                if (event.nav.events && ui.status.classList.contains("expand")) {
                    event.nav.collapse();
                    ui.reset.setAttribute("tabindex", 0);

                    setTimeout(function () {
                        ui.reset.focus({preventScroll: true});
                    }, 10);
                }

                api.activeElement = el;
                api.activeNav = el.parentNode === ui.nav;

                if (!root.retry && api.activeNav) {
                    api.error = api.activeElement.classList.contains("x-error");
                }

                // Node.textContent performs faster that Node.innerText
                // http://www.kellegous.com/j/2013/02/27/innertext-vs-textcontent/
                api.title = api.activeElement.textContent;

                if (api.error && url.address === url.url) {
                    h.replaceState(null, api.title, api.url);
                } else if (api.url !== url.url) {
                    h.pushState(null, api.title, api.url);
                }

                if (!api.error && !root.retry && (url.attr === url.address) ||
                    api.activeNav && api.activeElement.classList.contains("focus")) {
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
                    // Avoid cache http://stackoverflow.com/questions/1046966
                    // Firefox bug
                    // https://bugzilla.mozilla.org/show_bug.cgi?id=706806
                    // https://bugzilla.mozilla.org/show_bug.cgi?id=428916
                    // https://bugzilla.mozilla.org/show_bug.cgi?id=443098
                    // client.setRequestHeader("Cache-Control", "no-cache");
                    client.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
                }

                root.inprogress = true;

                client.send();
            }
        },
        load: function () {
            var response = this.response;

            response = has.valid(function () {
                return JSON.parse(response);
            });

            root.callback(response === has.error ? {
                error: true,
                title: "Server error",
                content: "<h1>Whoops...</h1><p>Experienced server error. Try to <a class=x-error href=" +
                    api.url + ">reload</a>" + (api.url === api.origin ? "" :
                    " or head to <a href=" + api.origin + ">home page</a>") + "."
            } : response);
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
        nav: {
            // Element or null
            activeElement: function () {
                var i;

                if (root.nav.nodeList) {
                    // Loop performance https://www.youtube.com/watch?v=taaEzHI9xyY#t=3042
                    // http://www.impressivewebs.com/javascript-for-loop/ http://jsperf.com/array-length-vs-cached/19
                    for (i = 0; i < root.nav.nodeList.length; i += 1) {
                        if (root.filter(root.nav.nodeList[i].href) === api.url) {
                            return root.nav.nodeList[i];
                        }
                    }
                }

                return null;
            },
            nodeList: ui.nodeList
        },
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

            // Chrome bug: XMLHttpRequest error avoids first popstate cache and recreates XMLHttpRequest
            // (perhaps https://bugs.chromium.org/p/chromium/issues/detail?id=371549 will fix it)
            // 1. Fire XMLHttpRequest by clicking on different links till some of links returns an error
            // 2. Navigate history back - Chrome will recreate XMLHttpRequest for the first h.go -1.
            // History -2, -3, etc. will return from cache accurately. Firefox correctly returns all from cache.
            root.update(state, false, activeElement);
        },
        reset: function () {
            if (statusTimer) {
                clearTimeout(statusTimer);
            }
            if (ui.status && ui.status.classList.contains("status-start")) {
                ui.status.classList.add("status-done");
            }
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
        retry: false,
        update: function (data, track, activeElement) {
            if (data) {
                if (track) {
                    root.reset();
                } else {
                    client.abort();
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
                    api.activeElement.focus({preventScroll: true});
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

                var scrollingElement = d.scrollingElement || ui.html.scrollTop || d.body;

                // Fixing scrollTop with Document.scrollingElement
                // https://dev.opera.com/articles/fixing-the-scrolltop-bug/
                // http://dev.w3.org/csswg/cssom-view/#dom-document-scrollingelement
                scrollingElement.scrollTop = 0;

                ui.output.innerHTML = data.content;

                if (l.hash) {
                    l.replace(api.url + l.hash);
                }
                if (event.analytics) {
                    event.analytics.track();
                }

                delete root.inprogress;
            }
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
    w.as = api;
}(this));
