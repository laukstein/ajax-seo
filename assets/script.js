// Resources
// ---------
// https://www.igvita.com/2014/05/20/script-injected-async-scripts-considered-harmful/
// jsonp http://mathiasbynens.be/notes/xhr-responsetype-json
// http://jsperf.com/localstorage-versus-browser-cache-json-performance
//
// Benchmarks
// ----------
// http://jsperf.com/fn-expression-vs-statement/
// Fastest results:
// var a, b, c;
// function fn() {}
// (function () {}()) Crockford recommendation https://www.youtube.com/watch?v=taaEzHI9xyY#t=2127

/*jslint devel:true, browser:true */
/*global ga: false */
(function (w, d, h, l, g) { // IIFE http://gregfranko.com/blog/i-love-my-iife/
    'use strict';

    w.toggle = function (focusOut) { // http://youmightnotneedjquery.com/#toggle_class
        var className = 'expand',
            status = d.getElementById('status'),
            classes,
            existingIndex;

        console.log('------------------ toggle');
        if (status.classList) {
            status.classList.toggle(className);
        } else {
            classes = status.className.split(' ');
            existingIndex = classes.indexOf(className);

            if (existingIndex >= 0) {
                classes.splice(existingIndex, 1);
            } else {
                classes.push(className);
            }
            status.className = classes.join(' ');
        }
        if (!!focusOut) {
            d.getElementById('focusout').focus();
        }
    };
    w.through = function (e) { /* IE10 legacy, pass through layers http://www.vinylfox.com/forwarding-mouse-events-through-layers/ */
        d.elementFromPoint(e.clientX, e.clientY).focus();
    };

    w[g] = function () {
        w[g].q = w[g].q || [];
        w[g].q.push(arguments);
    };
    ga('create', 'UA-XXXX-Y', 'auto');
    ga('send', 'pageview');

    if (!h.pushState) { // Browser legacy, stop here if does not support History API
        throw new Error('History API not supported');
    }

    var // cached
        client   = new XMLHttpRequest(), // http://xhr.spec.whatwg.org/ https://dvcs.w3.org/hg/xhr/raw-file/tip/Overview.html
        xstatus  = d.getElementById('status'),
        output   = d.getElementById('output'),
        hasError = false,
        statusRetry = false,
        statusName,
        statusLanded,
        statusTimer,
        i,
        url,
        uri,
        me2,

        host = l.host,
        currentScript = d.currentScript || (function () {
            var script = d.getElementsByTagName('script');
            return script[script.length - 1];
        }()),
        path = currentScript.src.split('#')[1] || '/ajax-seo/',
        selector = '.x',
        nav = d.querySelector('nav'),
        x   = nav ? nav.querySelectorAll(selector) : null,
        me;

    function currentURL() {
        return d.URL; // http://jsperf.com/document-url-vs-window-location-href/2
    }
    h.replaceState({'title': d.title, 'content': output.innerHTML}, d.title, currentURL()); // Initial popstate state, fixed on Chrome 34

    function resetStatus() {
        console.log('------------------ resetStatus');
        if (statusTimer) { clearTimeout(statusTimer); }
        if (xstatus.classList.contains('status-start')) { xstatus.classList.add('status-done'); }
    }
    function filterURL(url) {
        return url.split('#')[0]; // http://jsperf.com/url-replace-vs-match/2
    }
    function update(data, statusName, track) {
        console.log('------------------ update: ' + data + ', ' + statusName + ', ' + track + '. data.statusName: ' + data.statusName);

        // Google Universal Analytics tracking
        ga('send', 'pageview', { page: decodeURI(l.pathname) });

        if (!track) {
            client.abort();
        } else {
            resetStatus();
        }

        d.title = data.title;
        output.innerHTML = data.content;

        if (nav.querySelector('.x.focus')) { nav.querySelector('.x.focus').classList.remove('focus'); }
        if (nav.querySelector('.x.active')) { nav.querySelector('.x.active').classList.remove('active'); }
        if (nav.querySelector('.x.error')) { nav.querySelector('.x.error').classList.remove('error'); }

        url = filterURL(currentURL());

        // Browser cached focus bug workaround - leaves menu focused onclick /history and navigating history back http://tjvantoll.com/2013/08/30/bugs-with-document-activeelement-in-internet-explorer/
        if (d.activeElement && d.activeElement.nodeName.toLowerCase() !== 'body') { d.activeElement.blur(); }

        for (i = 0; i < x.length; i += 1) { // https://www.youtube.com/watch?v=taaEzHI9xyY#t=3042, http://www.impressivewebs.com/javascript-for-loop/, http://jsperf.com/array-length-vs-cached/19
            me2 = x[i];
            if (filterURL(me2.href) === url) {
                me2.focus(); // IE9 issue
                if (data.statusName) { statusName = 'error'; }
                me2.classList.add(statusName);
                if (statusName === 'error') {
                    me2.classList.remove('active');
                    me2.classList.add('x-error');
                    break;
                }
                me2.classList.remove('x-error');
                me2.classList.remove('error');
                break;
            }
        }

        // if (client.getResponseHeader('Connection') == null && client.getResponseHeader('Keep-Alive') == null) return;
        // if (track) if (typeof ga !== 'undefined') ga('send', 'pageview', {page: decodeURI(l.pathname)});

        console.log('END==========================');
    }
    function click(el) {
        if (el === null) { return; }
        try {
            // DOM 2 spec: click() defined only for HTMLInputElement http://www.w3.org/TR/DOM-Level-2-HTML/ecma-script-binding.html
            el.click();
        } catch (e) {
            // Old Webkit legacy
            var evt = d.createEvent('MouseEvents');
            evt.initEvent('click', true, true);
            el.dispatchEvent(evt);
        }
    }
    function popstate(e) {
        console.log('==========================POPSTATE');

        if (url !== undefined && url.indexOf('#') > -1) { console.log('stop: hasHash, POPSTATE'); return; }

        hasError    = false;
        statusRetry = false;

        resetStatus();

        var state = e.state;

        if (!state) {
            console.log('------------------ retry'); // and set @ hasError=true (in cache: ' + hasError + ')');
            // hasError    = true;
            statusRetry = true;
            url = filterURL(currentURL());
            for (i = 0; i < x.length; i += 1) { if (filterURL(x[i].href) === url) { return click(x[i]); } }
        }

        statusName = state.status;
        if (statusName === undefined) {
            hasError = true;
        }
        statusName = statusName !== undefined ? statusName : 'active';
        console.log('@ hasError: '  + hasError + ', e.state.status: ' + state.status);
        if (hasError && statusName === 'active') { hasError = false; }
        console.log('------------------ popstate: update(' + state + ', ' + statusName + ', ' + false + ')');
        update(state, statusName, false); // Chrome bug: XMLHttpRequest error avoids first popstate cache and recreates XMLHttpRequest (perhaps ttps://code.google.com/p/chromium/issues/detail?id=371549will fix the issue)
                                            // 1. Fire XMLHttpRequest by clicking on different links till some of links returns an error
                                            // 2. Navigate history back - Chrome will recreate XMLHttpRequest for the first h.go -1. History -2, -3, etc. will return from cache accurately. Firefox correctly returns all from cache.
    }
    setTimeout(function () { // Webkit initial popstate bug https://code.google.com/p/chromium/issues/detail?id=63040, fixed on Chrome 34
        // Chrome popstate bug with hashchange: multiple clicks on same hash URL will save lots of history
        // Chrome repeatedly repeated same hash URL history/popstate by onclick on same URL https://code.google.com/p/chromium/issues/detail?id=371549 http://jsbin.com/371549/1
        w.onpopstate = popstate; // http://jsperf.com/onpopstate-vs-addeventlistener
    }, 100);
    function closest(el, selector) { // http://jsperf.com/native-vs-jquery-closest
        // console.log('------------------ closest');
        // Element.matches() supported in Firefox 34, based on Gecko 34, will ship in November 2014 https://developer.mozilla.org/en-US/Firefox/Releases/34
        var matches = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;
        while (el && el.nodeType === 1) {
            if (matches.call(el, selector)) { return el; }
            el = el.parentNode;
        }
        return null;
    }
    function listener(e) {
        console.log('==========================LISTENER');

        if (closest(e.target, '.bar') || closest(e.target, '.handler')) { return; } // Fire toggle()
        if (!closest(e.target, 'nav ' + selector) && xstatus.classList.contains('expand')) { console.log('#status remove .expand'); return xstatus.classList.remove('expand'); }

        me = closest(e.target, selector);
        console.log(me);

        if (me === null || host !== me.host) { return; }

        url = me.getAttribute('href');
        if (url.indexOf('//') === 0 || url.indexOf('http') === 0) { // http://jsperf.com/startswith-prototype-vs-fn-vs-normal
            url = url.split(host).pop(); // Get URL after path http://jsperf.com/remove-all-string-before
            if (url.indexOf(path) !== 0) { return; }
        }
        if ((url[0] === '/' && url.indexOf(path) !== 0) || url.indexOf('#') > -1) { console.log('stop: surfing ourside API scope or requred hash URL in same link'); return; }

        e.preventDefault();

        var urlAttr   = filterURL(me.href),
            urlAddess = filterURL(currentURL()),
            title = me.innerText || me.textContent;  // innerText is not standardised and supported by Firefox, http://www.kellegous.com/j/2013/02/27/innertext-vs-textcontent/ http://stackoverflow.com/questions/1359469/innertext-works-in-ie-but-not-in-firefox http://jsperf.com/textcontent-and-innertext/2

        if (!statusRetry) { hasError = me.classList.contains('x-error'); }

        // Bug navigating h.back() after surfing from /ajax-seo/ to #hash and /ajax-seo/
        // if (me.href !== currentURL() && currentURL().indexOf('#') > -1) { h.pushState(null, title, me.href); console.log('stop: pushState ' + me.href + ' && !== ' + currentURL()); return; }
        if ((!hasError && !statusRetry && urlAttr === urlAddess) || me.classList.contains('focus')) { console.log('stop: refreshing the same URL'); return; }
        if (xstatus.hasAttribute('class')) { console.log('#status removeAttribute class'); xstatus.removeAttribute('class'); }
        if (url.indexOf(path) === 0) { url = url.slice(path.length); }

        // d.title = title; ////////////////////////////////////// need to workaroud it
        uri = path + url;

        // Chrome bug (also Chrome 37.0.2004.0 canary): hangs on error page (perhaps https://code.google.com/p/chromium/issues/detail?id=371549 will fix the issue, testcase http://jsbin.com/371549):
        //   click on link that returns error
        //   click on it till it returns 200
        //   navigate back
        //   click again on link that once had error
        //   - it retuns error while expected 200
        if (hasError && urlAddess === currentURL()) {
            h.replaceState(null, title, uri);
            console.log('------------------ listener: replaceState: ' + title + ' (placeholder), ' + uri);
        } else {
            h.pushState(null, title, uri);
            console.log('------------------ listener: pushState: ' + title + ' (placeholder), ' + uri);
        }
        if (nav.querySelector('.x.focus')) { nav.querySelector('.x.focus').classList.remove('focus'); }

        title = me.innerText || me.textContent;
        console.log(title);
        me.classList.remove('error');
        me.classList.add('focus');

        h.replaceState(null, title, uri);
        console.log('------------------ listener: replaceState with real title: ' + title + ', ' + uri);
        if (statusLanded) { clearTimeout(statusLanded); }
        statusLanded = setTimeout(function () {
            d.title = title;
        }, 3);

        client.abort(); // IE 11 issue: client.send() new request doesn't cancel unfinished earlier request
        client.open('GET', path + 'api' + (url.length > 0 ? '/' : '') + url); // decodeURIComponent(url) http://jsperf.com/decodeuri-vs-decodeuricomponent
        if (hasError) { // Avoid cache http://stackoverflow.com/questions/1046966/whats-the-difference-between-cache-control-max-age-0-and-no-cache
            // client.setRequestHeader('Cache-Control', 'no-cache'); // Firefox bug https://bugzilla.mozilla.org/show_bug.cgi?id=706806, https://bugzilla.mozilla.org/show_bug.cgi?id=428916, https://bugzilla.mozilla.org/show_bug.cgi?id=443098 : does not retry new XMLHttpRequest, but returns cache
            // client.setRequestHeader('If-Modified-Since', 'Sat, 1 Jan 2000 00:00:00 GMT'); // Chrome will avoid cache forever, works fine on Firefox
            console.log('Add header If-Modified-Since, because @ hasError: ' + hasError);
            client.setRequestHeader('If-Modified-Since', 'Sat, 1 Jan 2000 00:00:00 GMT');
        }
        client.send();
    }
    d.addEventListener(w.hasOwnProperty('ontouchstart') ? 'touchstart' : 'click', listener, true); // http://jsperf.com/addeventlistener-usecapture-true-vs-false

    xstatus.addEventListener('transitionend', function (e) {
        if (e.target.classList.contains('status-done')) {
            e.target.classList.remove('status-start');
            e.target.classList.remove('status-done');
            console.log('#status remove class status-start, status-done');
        }
    }, true);

    function loadstart() {
        console.log('------------------ loadstart');
        statusTimer = setTimeout(function () { // Will be avoided if content already in cache
            xstatus.classList.add('status-start');
        }, 100);
    }
    function callback(data, statusName) {
        console.log('------------------ callback: ' + data + ', ' + statusName);
        hasError = statusName === 'active' ? false : true;
        console.log('set @ hasError=' + hasError);
        h.replaceState(data, data.title, uri);
        update(data, statusName, true);
    }
    function load() {
        /*jshint validthis: true */
        console.log('------------------ load');
        resetStatus();
        statusName = this.status;
        if (statusLanded) { clearTimeout(statusLanded); }
        if (!statusName) { return; }
        if (statusName === 200) {
            try {
                callback(JSON.parse(this.response), 'active');
            } catch (e) {
                console.log('JSON.parse error: ' + e.message);
                callback({'statusName': 'error', 'title': 'Server error', 'content': '<h1>Oops...</h1><p>Sorry, experienced server error. Try to <a class="x x-error" href=' + url + '>reload</a> the page or head to <a class=x href=' + path + '>home</a>.'}, 'error');
            }
            return;
        }
        callback({'statusName': 'error', 'title': 'Page not found', 'content': '<h1>Oops...</h1><p>Sorry, this page hasn\'t been found. Try to <a class="x x-error" href=' + url + '>reload</a> the page or head to <a class=x href=' + path + '>home</a>.'}, 'error');
    }
    // client.open('GET', null); // IE11: SCRIPT5022: SyntaxError
    // client.responseType = 'json'; // IE11: SCRIPT5022: InvalidStateError https://connect.microsoft.com/IE/feedback/details/794808
    // client.onreadystatechange = callback; // would loop 4 times
    client.onloadstart = loadstart;
    client.onload = load;
    client.onabort = resetStatus;
}(window, document, history, location, 'ga'));