// IIFE http://gregfranko.com/blog/i-love-my-iife/
// Crockford recommendation function () {}()) https://www.youtube.com/watch?v=taaEzHI9xyY#t=2127
// void function () {}();
void function (root, factory) {
    "use strict";

    /*global module, define */

    if (typeof module === "object" && typeof module.exports === "object") {
        // Node.js, CommonJS
        module.exports = factory();
    } else if (typeof define === "function" && define.amd) {
        // AMD. Register as an anonymous module.
        define(["as"], factory);
    } else {
        // Browser globals
        root.as = factory();
    }
}(this, function () {
    "use strict";

    /*global window, document, history, location, ga, setTimeout, clearTimeout, XMLHttpRequest*/

    // cached
    var w = window,
        d = document,
        h = history,
        l = location,
        has = {
            // classList supported since IE10
            classList: "classList" in document.createElement("_"),
            // addEventListener supported since IE9
            eventListener: d.addEventListener ? true : false
        },
        layout = {
            // Expandable navigation
            bar: d.getElementById("bar"),
            focusin: d.getElementById("focusin"),
            focusout: d.getElementById("focusout"),
            reset: d.getElementById("reset"),

            // Top navigation and content
            nav: d.getElementById("nav"),
            status: d.getElementById("status"),
            output: d.getElementById("output")
        },
        nav = {
            toggleClassName: function (el, className) {
                if (el) {
                    if (has.classList) {
                        el.classList.toggle(className);
                    } else {
                        var classes = el.className.split(" "),
                            existingIndex = classes.indexOf(className);

                        if (existingIndex >= 0) {
                            classes.splice(existingIndex, 1);
                        } else {
                            classes.push(className);
                        }

                        el.className = classes.join(" ");
                    }
                }
            },
            toFocus: function () {
                if (d.activeElement !== layout.focusin) {
                    // Old Webkit fix
                    nav.toToggle(true);
                }
            },
            toToggle: function (toExpand) {
                // Perf http://jsperf.com/document-body-parentelement
                nav.toggleClassName(d.body.parentElement, "noscroll");
                nav.toggleClassName(layout.status, "expand");

                if (toExpand && layout.focusin) {
                    setTimeout(function () {
                        // Fix twice fired focus on Firefox
                        layout.focusin.focus();
                    }, 0);
                }
            }
        },
        // Compare API with flowplayer().conf https://flowplayer.org/
        as = {
            // Number
            version: 4,

            // String "UA-XXXX-Y"
            analytics: undefined,

            // String
            path: (function () {
                var currentScript = d.currentScript || (function () {
                        var script = d.getElementsByTagName("script");

                        return script[script.length - 1];
                    }()),
                    path = currentScript.src.split("#")[1] || "/ajax-seo",
                    re = new RegExp("(" + path + ")(.*)$");

                return d.URL.replace(re, "$1");
            }()),

            // String, http://jsperf.com/document-url-vs-window-location-href/2
            url: d.URL,

            // String
            title: d.title,

            // Element or null
            activeElement: (function () {
                var arr = d.querySelectorAll ? d.querySelectorAll("[href]") : [],
                    i;
                for (i = 0; i < arr.length; i += 1) {
                    if (arr[i].href === d.URL) {
                        return arr[i];
                    }
                }
                return null;
            }()),

            // Boolean
            // Issue: Set true if first page load returns 404 error
            error: false
        },
        statusLanded,
        statusTimer,
        client,
        root;

    if (as.analytics) {
        // Google Analytics, run also on legacy browsers
        w.ga = function () {
            ga.q = ga.q || [];

            ga.q.push(arguments);
        };
        ga("create", as.analytics, "auto");
        ga("send", "pageview");
    }
    if (layout.bar && has.eventListener) {
        // addEventListener and CSS media query supported since IE9
        layout.bar.addEventListener("focus", function () {
            nav.toToggle(true);
        }, true);

        layout.bar.addEventListener("click", nav.toFocus, true);

        if (layout.nav) {
            layout.nav.addEventListener("click", nav.toToggle, true);
        }
        if (layout.focusout) {
            layout.focusout.addEventListener("focus", nav.toToggle, true);
        }
        if (layout.reset) {
            layout.reset.addEventListener("click", nav.toToggle, true);
        }
    }
    if (!h.pushState || !has.classList || !has.eventListener) {
        // Stop here IE10 and Android 4.3 http://caniuse.com/#feat=history
        // Browser legacy, stop here if does not support History API
        throw new Error("Browser legacy: History API not supported");
    }
    if (!layout.output) {
        throw new Error("Layout issue: missing elements");
    }

    root = {
        filter: function (srt) {
            // Remove all after hash in URL, http://jsperf.com/url-replace-vs-match/2
            return srt ? srt.replace(/#.*$/, "").toLowerCase() : undefined;
        },
        reset: function () {
            if (statusTimer) {
                clearTimeout(statusTimer);
            }
            if (layout.status && layout.status.classList.contains("status-start")) {
                layout.status.classList.add("status-done");
            }
        },
        click: function (el) {
            if (!el) {
                return;
            }
            try {
                // DOM 2 spec: click() defined only for HTMLInputElement http://www.w3.org/TR/DOM-Level-2-HTML/ecma-script-binding.html
                el.click();
            } catch (e) {
                // Old Webkit legacy
                var evt = d.createEvent("MouseEvents");

                evt.initEvent("click", true, true);
                el.dispatchEvent(evt);
            }
        },
        nav: {
            // Array
            // Convert NodeList to Array, perf http://jsperf.com/convert-nodelist-to-array
            // Array.from(selector) ECMAScript 6 http://toddmotto.com/a-comprehensive-dive-into-nodelists-arrays-converting-nodelists-and-understanding-the-dom/
            nodeList: layout.nav ? (Array.from ? Array.from("a") : [].slice.call(layout.nav.querySelectorAll("a"))) : null,
            // Element or null
            activeElement: function() {
                var i;
                if (root.nav.nodeList) {
                    // Loop performance https://www.youtube.com/watch?v=taaEzHI9xyY#t=3042, http://www.impressivewebs.com/javascript-for-loop/, http://jsperf.com/array-length-vs-cached/19
                    for (i = 0; i < root.nav.nodeList.length; i += 1) {
                        if (root.filter(root.nav.nodeList[i].href) === as.url) {
                            return root.nav.nodeList[i];
                        }
                    }
                }
                return null;
            }
        },
        update: function (data, status, track, activeElement) {
            if (as.analytics) {
                // Google Universal Analytics tracking
                ga("send", "pageview", {page: decodeURI(l.pathname)});
            }
            if (!track) {
                client.abort();
            } else {
                root.reset();
            }
            if (root.nav.nodeList) {
                layout.focus = layout.nav.querySelector(".focus");
                layout.active = layout.nav.querySelector(".active");
                layout.error = layout.nav.querySelector(".error");

                if (layout.focus) {
                    layout.focus.classList.remove("focus");
                }
                if (layout.active) {
                    layout.active.classList.remove("active");
                }
                if (layout.error) {
                    layout.error.classList.remove("error");
                }
            }

            // Browser cached focus bug workaround - leaves menu focused onclick /history and navigating history back http://tjvantoll.com/2013/08/30/bugs-with-document-activeelement-in-internet-explorer/
            if (d.activeElement && d.activeElement.tagName === "BODY") {
                d.activeElement.blur();
            }

            as.url = root.filter(d.URL);
            activeElement = activeElement || root.nav.activeElement();

            if (activeElement) {
                activeElement.focus();
                activeElement.classList.add(status);

                if (status === "error") {
                    activeElement.classList.add("x-error");
                }
            }

            d.title = as.title = data.title;
            d.body.scrollTop = 0;
            layout.output.innerHTML = data.content;

            if (l.hash) {
                // CSS :target fix
                h.replaceState({
                    title: as.title,
                    content: layout.output.innerHTML
                }, as.title, as.url);

                l.replace(as.url + l.hash);
            }
        },
        retry: false,
        popstate: function (e) {
            if (l.hash && root.filter(as.url) === root.filter(d.URL) || as.url && as.url.indexOf("#") > -1) {
                // stop: same URL with hash
                return;
            }

            as.error = false;
            root.retry = false;

            root.reset();

            var state = e.state,
                status = state && state.status ? state.status : "active",
                activeElement;

            if (!state) {
                // retry
                root.retry = true;
                as.url = root.filter(d.URL);
                activeElement = root.nav.activeElement();

                root.click(activeElement);
            }
            if (status === "error") {
                as.error = true;
            }
            if (as.error && status === "active") {
                as.error = false;
            }

            // Chrome bug: XMLHttpRequest error avoids first popstate cache and recreates XMLHttpRequest (perhaps ttps://code.google.com/p/chromium/issues/detail?id=371549will fix the issue)
            // 1. Fire XMLHttpRequest by clicking on different links till some of links returns an error
            // 2. Navigate history back - Chrome will recreate XMLHttpRequest for the first h.go -1. History -2, -3, etc. will return from cache accurately. Firefox correctly returns all from cache.
            root.update(state, status, false, activeElement);
        },
        loadstart: function () {
            if (layout.status) {
                statusTimer = setTimeout(function () {
                    // Will be avoided if content already in cache
                    layout.status.classList.add("status-start");
                }, 100);
            }
        },
        callback: function (data, status) {
            as.error = status === "active" ? false : true;

            h.replaceState(data, data.title, null);
            root.update(data, status, true);
        },
        load: function () {
            root.reset();

            var status = this.status;

            if (statusLanded) {
                clearTimeout(statusLanded);
            }
            if (!status) {
                return;
            }
            if (status === 200) {
                try {
                    root.callback(JSON.parse(this.response), "active");
                } catch (e) {
                    root.callback({
                        status: "error",
                        title: "Server error",
                        content: "<h1>Oops...</h1><p>Sorry, experienced server error. Try to <a class=x-error href=" + as.url + ">reload</a> the page or head to <a href=" + as.path + ">home</a>."
                    }, "error");
                }
                return;
            }
            root.callback({
                status: "error",
                title: "Page not found",
                content: "<h1>Oops...</h1><p>Sorry, this page hasn't been found. Try to <a class=x-error href=" + as.url + ">reload</a> the page or head to <a href=" + as.path + ">home</a>."
            }, "error");
        },
        closest: function (el, selector) {
            // http://jsperf.com/native-vs-jquery-closest
            if (!el || !selector) {
                return null;
            }
            if (el.closest) {
                // Native element.closest(selectors) standard https://dom.spec.whatwg.org/#dom-element-closest and https://developer.mozilla.org/en-US/docs/Web/API/Element.closest similar to $(selector).closest(selector), supported on Chrome 40 http://blog.chromium.org/2014/12/chrome-40-beta-powerful-offline-and.html
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
        },
        listener: function (e) {
            if (!e) {
                return;
            }

            var el = e.target,
                patt = new RegExp("^" + as.path + "($|#|/.{1,}).*", "i"),
                url = {};

            // Run script only if has link and matches "as.path" root
            if (!el) {
                return;
            } else if (el.tagName !== "A") {
                el = root.closest(el, "a[href]");
            }
            if (!el || !(el.tagName === "A" && el.hasAttribute("href")) || !patt.test(el.href)) {
                // Stop: outside API scope
                return;
            }

            // Lowercase URL
            // Remove multiple trailing slashes except to protocol
            // Remove trailing slash from URL end
            as.url = el.href.toLowerCase().replace(/(\/)+(?=\1)/g, "").replace(/(^https?:(\/))/, "$1/").replace(/\/$/, "");
            url.attr = root.filter(as.url);
            url.address = root.filter(d.URL);

            if (url.attr === url.address && as.url.indexOf("#") > -1) {
                // stop: same link with hash
                return;
            }

            e.preventDefault();
            el.blur();

            as.activeElement = el;

            if (!root.retry) {
                as.error = as.activeElement.classList.contains("x-error");
            }

            // innerText is not standardised and not either supported on Firefox, http://www.kellegous.com/j/2013/02/27/innertext-vs-textcontent/ http://stackoverflow.com/questions/1359469/innertext-works-in-ie-but-not-in-firefox http://jsperf.com/textcontent-and-innertext/2
            as.title = as.activeElement.innerText || as.activeElement.textContent;

            // Chrome bug (also Chrome 37.0.2004.0 canary): hangs on error page (perhaps https://code.google.com/p/chromium/issues/detail?id=371549 will fix the issue, testcase http://jsbin.com/371549):
            //   click on link that returns error
            //   click on it till it returns 200
            //   navigate back
            //   click again on link that once had error
            //   - it retuns error while expected 200
            if (as.error && url.address === d.URL) {
                h.replaceState(null, as.title, as.url);
            } else if (!as.error && as.url !== d.URL) {
                h.pushState(null, as.title, as.url);
            }
            if (!as.error && !root.retry && (url.attr === url.address) || as.activeElement.classList.contains("focus")) {
                // Avoid API retry on same link if has not error status
                return;
            }
            if (statusLanded) {
                clearTimeout(statusLanded);
            }

            statusLanded = setTimeout(function () {
                d.title = as.title;
            }, 3);

            if (layout.status) {
                // IE11 doesn't support multiple classes https://connect.microsoft.com/IE/Feedback/Details/920755
                layout.status.classList.remove("status-start");
                layout.status.classList.remove("status-done");
            }
            if (root.nav.nodeList) {
                if (as.error) {
                    as.activeElement.classList.remove("x-error");
                    as.activeElement.classList.remove("error");
                }

                layout.focus = layout.nav.querySelector(".focus");

                if (layout.focus) {
                    layout.focus.classList.remove("focus");
                }
            }
            as.activeElement.classList.add("focus");

            // IE11 issue: client.send() new request doesn"t cancel unfinished earlier request
            client.abort();

            // decodeURIComponent(as.url) http://jsperf.com/decodeuri-vs-decodeuricomponent
            client.open("GET", as.path + "/api" + as.url.replace(new RegExp("^" + as.path, "i"), ""));

            if (as.error) {
                // Avoid cache http://stackoverflow.com/questions/1046966/whats-the-difference-between-cache-control-max-age-0-and-no-cache
                // Firefox bug https://bugzilla.mozilla.org/show_bug.cgi?id=706806, https://bugzilla.mozilla.org/show_bug.cgi?id=428916, https://bugzilla.mozilla.org/show_bug.cgi?id=443098
                // client.setRequestHeader("Cache-Control", "no-cache");
                client.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
            }
            client.send();
        },
        resetStatus: function (e) {
            var el = e.target;

            if (el.contains("status-done")) {
                el.classList.remove("status-start");
                el.clacachessList.remove("status-done");
            }
        },
        init: function () {
            // UI: status reset
            if (layout.status) {
                layout.status.addEventListener("transitionend", root.resetStatus, true);
            }

            setTimeout(function () {
                // Webkit initial popstate bug https://code.google.com/p/chromium/issues/detail?id=63040, fixed on Chrome 34
                // Chrome popstate bug with hashchange: multiple clicks on same hash URL will save lots of history
                // Chrome repeatedly repeated same hash URL history/popstate by onclick on same URL https://code.google.com/p/chromium/issues/detail?id=371549 http://jsbin.com/371549/1
                // http://jsperf.com/onpopstate-vs-addeventlistener
                w.onpopstate = root.popstate;
            }, 100);

            // Initial popstate state, fixed on Chrome 34
            h.replaceState({
                title: as.title,
                content: layout.output.innerHTML
            }, as.title, as.url);

            // http://xhr.spec.whatwg.org/ https://dvcs.w3.org/hg/xhr/raw-file/tip/Overview.html
            client = new XMLHttpRequest();
            // // IE11: SCRIPT5022: SyntaxError
            // client.open("GET", null);
            // // IE11: SCRIPT5022: InvalidStateError https://connect.microsoft.com/IE/feedback/details/794808
            // client.responseType = "json";
            // // would loop 4 times
            // client.onreadystatechange = root.callback;
            client.onloadstart = root.loadstart;
            client.onload = root.load;
            client.onabort = root.reset;

            // http://jsperf.com/addeventlistener-usecapture-true-vs-false
            d.addEventListener(w.hasOwnProperty("ontouchstart") ? "touchstart" : "click", root.listener, true);
        }
    };

    // Apply events
    root.init();

    // Return readable API
    return as;
});
