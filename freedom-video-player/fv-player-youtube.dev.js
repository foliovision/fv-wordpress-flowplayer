/*global YT, FV_YT, fv_player_log, fv_player_track */

/*eslint no-inner-declarations: 0*/
/*eslint no-cond-assign: 0*/

/*
 * Moved in from FV Player Pro
 * For full comit history check foliovision/fv-player-pro/blob/517cb6ef122e507f6ba7744e591b3825a643abe4/beta/js/youtube.module.js
 */

if( fv_flowplayer_conf.youtube ) {
  /**
   * Copy of https://www.youtube.com/iframe_api with YT changed to FV_YT.
   *
   * Part where it loads scriptUrl was commented out.
   */
  /*var scriptUrl = 'https:\/\/www.youtube.com\/s\/player\/2b2385a0\/www-widgetapi.vflset\/www-widgetapi.js';
  try {
      var ttPolicy = window.trustedTypes.createPolicy("youtube-widget-api", {
          createScriptURL: function(x) {
              return x
          }
      });
      scriptUrl = ttPolicy.createScriptURL(scriptUrl)
  } catch (e) {}*/
  var FV_YT;
  if (!window["FV_YT"])
      FV_YT = {
          loading: 0,
          loaded: 0
      };
  var YTConfig;
  if (!window["YTConfig"])
      YTConfig = {
          "host": "https://www.youtube.com"
      };
  if (!FV_YT.loading) {
      FV_YT.loading = 1;
      (function() {
          var l = [];
          FV_YT.ready = function(f) {
              if (FV_YT.loaded)
                  f();
              else
                  l.push(f)
          }
          ;
          window.onYTReady = function() {
              FV_YT.loaded = 1;
              var i = 0;
              for (; i < l.length; i++)
                  try {
                      l[i]()
                  } catch (e) {}
          }
          ;
          FV_YT.setConfig = function(c) {
              var k;
              for (k in c)
                  if (c.hasOwnProperty(k))
                      YTConfig[k] = c[k]
          }
          ;
          /*var a = document.createElement("script");
          a.type = "text/javascript";
          a.id = "www-widgetapi-script";
          a.src = scriptUrl;
          a.async = true;
          var c = document.currentScript;
          if (c) {
              var n = c.nonce || c.getAttribute("nonce");
              if (n)
                  a.setAttribute("nonce", n)
          }
          var b = document.getElementsByTagName("script")[0];
          b.parentNode.insertBefore(a, b)*/
      }
      )()
  }
  ;

  /**
   * Copy of https://www.youtube.com/s/player/c6d7bdc9/www-widgetapi.vflset/www-widgetapi.js with YT changed to FV_YT.
   *
   * Commented out parts where it runs onYTReady(), onYouTubeIframeAPIReady(), onYouTubePlayerAPIReady()
   */
  (function() {
    'use strict';
    var n, ca = typeof Object.create == "function" ? Object.create : function(a) {
        function b() {}
        b.prototype = a;
        return new b
    }
    , p = typeof Object.defineProperties == "function" ? Object.defineProperty : function(a, b, c) {
        if (a == Array.prototype || a == Object.prototype)
            return a;
        a[b] = c.value;
        return a
    }
    ;
    function da(a) {
        a = ["object" == typeof globalThis && globalThis, a, "object" == typeof window && window, "object" == typeof self && self, "object" == typeof global && global];
        for (var b = 0; b < a.length; ++b) {
            var c = a[b];
            if (c && c.Math == Math)
                return c
        }
        throw Error("Cannot find global object");
    }
    var q = da(this);
    function r(a, b) {
        if (b)
            a: {
                var c = q;
                a = a.split(".");
                for (var d = 0; d < a.length - 1; d++) {
                    var k = a[d];
                    if (!(k in c))
                        break a;
                    c = c[k]
                }
                a = a[a.length - 1];
                d = c[a];
                b = b(d);
                b != d && b != null && p(c, a, {
                    configurable: !0,
                    writable: !0,
                    value: b
                })
            }
    }
    var t;
    if (typeof Object.setPrototypeOf == "function")
        t = Object.setPrototypeOf;
    else {
        var v;
        a: {
            var ea = {
                a: !0
            }
              , fa = {};
            try {
                fa.__proto__ = ea;
                v = fa.a;
                break a
            } catch (a) {}
            v = !1
        }
        t = v ? function(a, b) {
            a.__proto__ = b;
            if (a.__proto__ !== b)
                throw new TypeError(a + " is not extensible");
            return a
        }
        : null
    }
    var ha = t;
    function ia(a) {
        var b = 0;
        return function() {
            return b < a.length ? {
                done: !1,
                value: a[b++]
            } : {
                done: !0
            }
        }
    }
    function x(a) {
        var b = typeof Symbol != "undefined" && Symbol.iterator && a[Symbol.iterator];
        if (b)
            return b.call(a);
        if (typeof a.length == "number")
            return {
                next: ia(a)
            };
        throw Error(String(a) + " is not an iterable or ArrayLike");
    }
    function y() {
        this.j = !1;
        this.h = null;
        this.m = void 0;
        this.g = 1;
        this.A = this.l = 0;
        this.i = null
    }
    function z(a) {
        if (a.j)
            throw new TypeError("Generator is already running");
        a.j = !0
    }
    y.prototype.o = function(a) {
        this.m = a
    }
    ;
    function B(a, b) {
        a.i = {
            P: b,
            R: !0
        };
        a.g = a.l || a.A
    }
    y.prototype.return = function(a) {
        this.i = {
            return: a
        };
        this.g = this.A
    }
    ;
    function C(a, b, c) {
        a.g = c;
        return {
            value: b
        }
    }
    function ja(a) {
        this.g = new y;
        this.h = a
    }
    function ka(a, b) {
        z(a.g);
        var c = a.g.h;
        if (c)
            return D(a, "return"in c ? c["return"] : function(d) {
                return {
                    value: d,
                    done: !0
                }
            }
            , b, a.g.return);
        a.g.return(b);
        return E(a)
    }
    function D(a, b, c, d) {
        try {
            var k = b.call(a.g.h, c);
            if (!(k instanceof Object))
                throw new TypeError("Iterator result " + k + " is not an object");
            if (!k.done)
                return a.g.j = !1,
                k;
            var g = k.value
        } catch (f) {
            return a.g.h = null,
            B(a.g, f),
            E(a)
        }
        a.g.h = null;
        d.call(a.g, g);
        return E(a)
    }
    function E(a) {
        for (; a.g.g; )
            try {
                var b = a.h(a.g);
                if (b)
                    return a.g.j = !1,
                    {
                        value: b.value,
                        done: !1
                    }
            } catch (c) {
                a.g.m = void 0,
                B(a.g, c)
            }
        a.g.j = !1;
        if (a.g.i) {
            b = a.g.i;
            a.g.i = null;
            if (b.R)
                throw b.P;
            return {
                value: b.return,
                done: !0
            }
        }
        return {
            value: void 0,
            done: !0
        }
    }
    function la(a) {
        this.next = function(b) {
            z(a.g);
            a.g.h ? b = D(a, a.g.h.next, b, a.g.o) : (a.g.o(b),
            b = E(a));
            return b
        }
        ;
        this.throw = function(b) {
            z(a.g);
            a.g.h ? b = D(a, a.g.h["throw"], b, a.g.o) : (B(a.g, b),
            b = E(a));
            return b
        }
        ;
        this.return = function(b) {
            return ka(a, b)
        }
        ;
        this[Symbol.iterator] = function() {
            return this
        }
    }
    function ma(a) {
        function b(d) {
            return a.next(d)
        }
        function c(d) {
            return a.throw(d)
        }
        return new Promise(function(d, k) {
            function g(f) {
                f.done ? d(f.value) : Promise.resolve(f.value).then(b, c).then(g, k)
            }
            g(a.next())
        }
        )
    }
    function F(a) {
        return ma(new la(new ja(a)))
    }
    r("Symbol", function(a) {
        function b(g) {
            if (this instanceof b)
                throw new TypeError("Symbol is not a constructor");
            return new c(d + (g || "") + "_" + k++,g)
        }
        function c(g, f) {
            this.g = g;
            p(this, "description", {
                configurable: !0,
                writable: !0,
                value: f
            })
        }
        if (a)
            return a;
        c.prototype.toString = function() {
            return this.g
        }
        ;
        var d = "jscomp_symbol_" + (Math.random() * 1E9 >>> 0) + "_"
          , k = 0;
        return b
    });
    r("Symbol.iterator", function(a) {
        if (a)
            return a;
        a = Symbol("Symbol.iterator");
        p(Array.prototype, a, {
            configurable: !0,
            writable: !0,
            value: function() {
                return na(ia(this))
            }
        });
        return a
    });
    function na(a) {
        a = {
            next: a
        };
        a[Symbol.iterator] = function() {
            return this
        }
        ;
        return a
    }
    r("Promise", function(a) {
        function b(f) {
            this.h = 0;
            this.i = void 0;
            this.g = [];
            this.o = !1;
            var e = this.j();
            try {
                f(e.resolve, e.reject)
            } catch (h) {
                e.reject(h)
            }
        }
        function c() {
            this.g = null
        }
        function d(f) {
            return f instanceof b ? f : new b(function(e) {
                e(f)
            }
            )
        }
        if (a)
            return a;
        c.prototype.h = function(f) {
            if (this.g == null) {
                this.g = [];
                var e = this;
                this.i(function() {
                    e.l()
                })
            }
            this.g.push(f)
        }
        ;
        var k = q.setTimeout;
        c.prototype.i = function(f) {
            k(f, 0)
        }
        ;
        c.prototype.l = function() {
            for (; this.g && this.g.length; ) {
                var f = this.g;
                this.g = [];
                for (var e = 0; e < f.length; ++e) {
                    var h = f[e];
                    f[e] = null;
                    try {
                        h()
                    } catch (l) {
                        this.j(l)
                    }
                }
            }
            this.g = null
        }
        ;
        c.prototype.j = function(f) {
            this.i(function() {
                throw f;
            })
        }
        ;
        b.prototype.j = function() {
            function f(l) {
                return function(m) {
                    h || (h = !0,
                    l.call(e, m))
                }
            }
            var e = this
              , h = !1;
            return {
                resolve: f(this.K),
                reject: f(this.l)
            }
        }
        ;
        b.prototype.K = function(f) {
            if (f === this)
                this.l(new TypeError("A Promise cannot resolve to itself"));
            else if (f instanceof b)
                this.M(f);
            else {
                a: switch (typeof f) {
                case "object":
                    var e = f != null;
                    break a;
                case "function":
                    e = !0;
                    break a;
                default:
                    e = !1
                }
                e ? this.J(f) : this.m(f)
            }
        }
        ;
        b.prototype.J = function(f) {
            var e = void 0;
            try {
                e = f.then
            } catch (h) {
                this.l(h);
                return
            }
            typeof e == "function" ? this.N(e, f) : this.m(f)
        }
        ;
        b.prototype.l = function(f) {
            this.A(2, f)
        }
        ;
        b.prototype.m = function(f) {
            this.A(1, f)
        }
        ;
        b.prototype.A = function(f, e) {
            if (this.h != 0)
                throw Error("Cannot settle(" + f + ", " + e + "): Promise already settled in state" + this.h);
            this.h = f;
            this.i = e;
            this.h === 2 && this.L();
            this.C()
        }
        ;
        b.prototype.L = function() {
            var f = this;
            k(function() {
                if (f.I()) {
                    var e = q.console;
                    typeof e !== "undefined" && e.error(f.i)
                }
            }, 1)
        }
        ;
        b.prototype.I = function() {
            if (this.o)
                return !1;
            var f = q.CustomEvent
              , e = q.Event
              , h = q.dispatchEvent;
            if (typeof h === "undefined")
                return !0;
            typeof f === "function" ? f = new f("unhandledrejection",{
                cancelable: !0
            }) : typeof e === "function" ? f = new e("unhandledrejection",{
                cancelable: !0
            }) : (f = q.document.createEvent("CustomEvent"),
            f.initCustomEvent("unhandledrejection", !1, !0, f));
            f.promise = this;
            f.reason = this.i;
            return h(f)
        }
        ;
        b.prototype.C = function() {
            if (this.g != null) {
                for (var f = 0; f < this.g.length; ++f)
                    g.h(this.g[f]);
                this.g = null
            }
        }
        ;
        var g = new c;
        b.prototype.M = function(f) {
            var e = this.j();
            f.B(e.resolve, e.reject)
        }
        ;
        b.prototype.N = function(f, e) {
            var h = this.j();
            try {
                f.call(e, h.resolve, h.reject)
            } catch (l) {
                h.reject(l)
            }
        }
        ;
        b.prototype.then = function(f, e) {
            function h(w, A) {
                return typeof w == "function" ? function(aa) {
                    try {
                        l(w(aa))
                    } catch (ba) {
                        m(ba)
                    }
                }
                : A
            }
            var l, m, u = new b(function(w, A) {
                l = w;
                m = A
            }
            );
            this.B(h(f, l), h(e, m));
            return u
        }
        ;
        b.prototype.catch = function(f) {
            return this.then(void 0, f)
        }
        ;
        b.prototype.B = function(f, e) {
            function h() {
                switch (l.h) {
                case 1:
                    f(l.i);
                    break;
                case 2:
                    e(l.i);
                    break;
                default:
                    throw Error("Unexpected state: " + l.h);
                }
            }
            var l = this;
            this.g == null ? g.h(h) : this.g.push(h);
            this.o = !0
        }
        ;
        b.resolve = d;
        b.reject = function(f) {
            return new b(function(e, h) {
                h(f)
            }
            )
        }
        ;
        b.race = function(f) {
            return new b(function(e, h) {
                for (var l = x(f), m = l.next(); !m.done; m = l.next())
                    d(m.value).B(e, h)
            }
            )
        }
        ;
        b.all = function(f) {
            var e = x(f)
              , h = e.next();
            return h.done ? d([]) : new b(function(l, m) {
                function u(aa) {
                    return function(ba) {
                        w[aa] = ba;
                        A--;
                        A == 0 && l(w)
                    }
                }
                var w = []
                  , A = 0;
                do
                    w.push(void 0),
                    A++,
                    d(h.value).B(u(w.length - 1), m),
                    h = e.next();
                while (!h.done)
            }
            )
        }
        ;
        return b
    });
    function G(a, b) {
        return Object.prototype.hasOwnProperty.call(a, b)
    }
    var oa = typeof Object.assign == "function" ? Object.assign : function(a, b) {
        if (a == null)
            throw new TypeError("No nullish arg");
        a = Object(a);
        for (var c = 1; c < arguments.length; c++) {
            var d = arguments[c];
            if (d)
                for (var k in d)
                    G(d, k) && (a[k] = d[k])
        }
        return a
    }
    ;
    r("Object.assign", function(a) {
        return a || oa
    });
    r("Symbol.dispose", function(a) {
        return a ? a : Symbol("Symbol.dispose")
    });
    r("WeakMap", function(a) {
        function b(h) {
            this.g = (e += Math.random() + 1).toString();
            if (h) {
                h = x(h);
                for (var l; !(l = h.next()).done; )
                    l = l.value,
                    this.set(l[0], l[1])
            }
        }
        function c() {}
        function d(h) {
            var l = typeof h;
            return l === "object" && h !== null || l === "function"
        }
        function k(h) {
            if (!G(h, f)) {
                var l = new c;
                p(h, f, {
                    value: l
                })
            }
        }
        function g(h) {
            var l = Object[h];
            l && (Object[h] = function(m) {
                if (m instanceof c)
                    return m;
                Object.isExtensible(m) && k(m);
                return l(m)
            }
            )
        }
        if (function() {
            if (!a || !Object.seal)
                return !1;
            try {
                var h = Object.seal({})
                  , l = Object.seal({})
                  , m = new a([[h, 2], [l, 3]]);
                if (m.get(h) != 2 || m.get(l) != 3)
                    return !1;
                m.delete(h);
                m.set(l, 4);
                return !m.has(h) && m.get(l) == 4
            } catch (u) {
                return !1
            }
        }())
            return a;
        var f = "$jscomp_hidden_" + Math.random();
        g("freeze");
        g("preventExtensions");
        g("seal");
        var e = 0;
        b.prototype.set = function(h, l) {
            if (!d(h))
                throw Error("Invalid WeakMap key");
            k(h);
            if (!G(h, f))
                throw Error("WeakMap key fail: " + h);
            h[f][this.g] = l;
            return this
        }
        ;
        b.prototype.get = function(h) {
            return d(h) && G(h, f) ? h[f][this.g] : void 0
        }
        ;
        b.prototype.has = function(h) {
            return d(h) && G(h, f) && G(h[f], this.g)
        }
        ;
        b.prototype.delete = function(h) {
            return d(h) && G(h, f) && G(h[f], this.g) ? delete h[f][this.g] : !1
        }
        ;
        return b
    });
    r("Map", function(a) {
        function b() {
            var e = {};
            return e.previous = e.next = e.head = e
        }
        function c(e, h) {
            var l = e[1];
            return na(function() {
                if (l) {
                    for (; l.head != e[1]; )
                        l = l.previous;
                    for (; l.next != l.head; )
                        return l = l.next,
                        {
                            done: !1,
                            value: h(l)
                        };
                    l = null
                }
                return {
                    done: !0,
                    value: void 0
                }
            })
        }
        function d(e, h) {
            var l = h && typeof h;
            l == "object" || l == "function" ? g.has(h) ? l = g.get(h) : (l = "" + ++f,
            g.set(h, l)) : l = "p_" + h;
            var m = e[0][l];
            if (m && G(e[0], l))
                for (e = 0; e < m.length; e++) {
                    var u = m[e];
                    if (h !== h && u.key !== u.key || h === u.key)
                        return {
                            id: l,
                            list: m,
                            index: e,
                            entry: u
                        }
                }
            return {
                id: l,
                list: m,
                index: -1,
                entry: void 0
            }
        }
        function k(e) {
            this[0] = {};
            this[1] = b();
            this.size = 0;
            if (e) {
                e = x(e);
                for (var h; !(h = e.next()).done; )
                    h = h.value,
                    this.set(h[0], h[1])
            }
        }
        if (function() {
            if (!a || typeof a != "function" || !a.prototype.entries || typeof Object.seal != "function")
                return !1;
            try {
                var e = Object.seal({
                    x: 4
                })
                  , h = new a(x([[e, "s"]]));
                if (h.get(e) != "s" || h.size != 1 || h.get({
                    x: 4
                }) || h.set({
                    x: 4
                }, "t") != h || h.size != 2)
                    return !1;
                var l = h.entries()
                  , m = l.next();
                if (m.done || m.value[0] != e || m.value[1] != "s")
                    return !1;
                m = l.next();
                return m.done || m.value[0].x != 4 || m.value[1] != "t" || !l.next().done ? !1 : !0
            } catch (u) {
                return !1
            }
        }())
            return a;
        var g = new WeakMap;
        k.prototype.set = function(e, h) {
            e = e === 0 ? 0 : e;
            var l = d(this, e);
            l.list || (l.list = this[0][l.id] = []);
            l.entry ? l.entry.value = h : (l.entry = {
                next: this[1],
                previous: this[1].previous,
                head: this[1],
                key: e,
                value: h
            },
            l.list.push(l.entry),
            this[1].previous.next = l.entry,
            this[1].previous = l.entry,
            this.size++);
            return this
        }
        ;
        k.prototype.delete = function(e) {
            e = d(this, e);
            return e.entry && e.list ? (e.list.splice(e.index, 1),
            e.list.length || delete this[0][e.id],
            e.entry.previous.next = e.entry.next,
            e.entry.next.previous = e.entry.previous,
            e.entry.head = null,
            this.size--,
            !0) : !1
        }
        ;
        k.prototype.clear = function() {
            this[0] = {};
            this[1] = this[1].previous = b();
            this.size = 0
        }
        ;
        k.prototype.has = function(e) {
            return !!d(this, e).entry
        }
        ;
        k.prototype.get = function(e) {
            return (e = d(this, e).entry) && e.value
        }
        ;
        k.prototype.entries = function() {
            return c(this, function(e) {
                return [e.key, e.value]
            })
        }
        ;
        k.prototype.keys = function() {
            return c(this, function(e) {
                return e.key
            })
        }
        ;
        k.prototype.values = function() {
            return c(this, function(e) {
                return e.value
            })
        }
        ;
        k.prototype.forEach = function(e, h) {
            for (var l = this.entries(), m; !(m = l.next()).done; )
                m = m.value,
                e.call(h, m[1], m[0], this)
        }
        ;
        k.prototype[Symbol.iterator] = k.prototype.entries;
        var f = 0;
        return k
    });
    r("Set", function(a) {
        function b(c) {
            this.g = new Map;
            if (c) {
                c = x(c);
                for (var d; !(d = c.next()).done; )
                    this.add(d.value)
            }
            this.size = this.g.size
        }
        if (function() {
            if (!a || typeof a != "function" || !a.prototype.entries || typeof Object.seal != "function")
                return !1;
            try {
                var c = Object.seal({
                    x: 4
                })
                  , d = new a(x([c]));
                if (!d.has(c) || d.size != 1 || d.add(c) != d || d.size != 1 || d.add({
                    x: 4
                }) != d || d.size != 2)
                    return !1;
                var k = d.entries()
                  , g = k.next();
                if (g.done || g.value[0] != c || g.value[1] != c)
                    return !1;
                g = k.next();
                return g.done || g.value[0] == c || g.value[0].x != 4 || g.value[1] != g.value[0] ? !1 : k.next().done
            } catch (f) {
                return !1
            }
        }())
            return a;
        b.prototype.add = function(c) {
            c = c === 0 ? 0 : c;
            this.g.set(c, c);
            this.size = this.g.size;
            return this
        }
        ;
        b.prototype.delete = function(c) {
            c = this.g.delete(c);
            this.size = this.g.size;
            return c
        }
        ;
        b.prototype.clear = function() {
            this.g.clear();
            this.size = 0
        }
        ;
        b.prototype.has = function(c) {
            return this.g.has(c)
        }
        ;
        b.prototype.entries = function() {
            return this.g.entries()
        }
        ;
        b.prototype.values = function() {
            return this.g.values()
        }
        ;
        b.prototype.keys = b.prototype.values;
        b.prototype[Symbol.iterator] = b.prototype.values;
        b.prototype.forEach = function(c, d) {
            var k = this;
            this.g.forEach(function(g) {
                return c.call(d, g, g, k)
            })
        }
        ;
        return b
    });
    r("Array.prototype.find", function(a) {
        return a ? a : function(b, c) {
            a: {
                var d = this;
                d instanceof String && (d = String(d));
                for (var k = d.length, g = 0; g < k; g++) {
                    var f = d[g];
                    if (b.call(c, f, g, d)) {
                        b = f;
                        break a
                    }
                }
                b = void 0
            }
            return b
        }
    });
    r("Array.from", function(a) {
        return a ? a : function(b, c, d) {
            c = c != null ? c : function(e) {
                return e
            }
            ;
            var k = []
              , g = typeof Symbol != "undefined" && Symbol.iterator && b[Symbol.iterator];
            if (typeof g == "function") {
                b = g.call(b);
                for (var f = 0; !(g = b.next()).done; )
                    k.push(c.call(d, g.value, f++))
            } else
                for (g = b.length,
                f = 0; f < g; f++)
                    k.push(c.call(d, b[f], f));
            return k
        }
    });
    /*

 Copyright The Closure Library Authors.
 SPDX-License-Identifier: Apache-2.0
*/
    var H = this || self;
    function I(a) {
        var b = typeof a;
        return b == "object" && a != null || b == "function"
    }
    function pa(a) {
        return Object.prototype.hasOwnProperty.call(a, qa) && a[qa] || (a[qa] = ++ra)
    }
    var qa = "closure_uid_" + (Math.random() * 1E9 >>> 0)
      , ra = 0;
    function J(a, b) {
        a = a.split(".");
        for (var c = H, d; a.length && (d = a.shift()); )
            a.length || b === void 0 ? c[d] && c[d] !== Object.prototype[d] ? c = c[d] : c = c[d] = {} : c[d] = b
    }
    function sa(a, b) {
        function c() {}
        c.prototype = b.prototype;
        a.H = b.prototype;
        a.prototype = new c;
        a.prototype.constructor = a;
        a.Y = function(d, k, g) {
            for (var f = Array(arguments.length - 2), e = 2; e < arguments.length; e++)
                f[e - 2] = arguments[e];
            return b.prototype[k].apply(d, f)
        }
    }
    ;var ta = Array.prototype.indexOf ? function(a, b) {
        return Array.prototype.indexOf.call(a, b, void 0)
    }
    : function(a, b) {
        if (typeof a === "string")
            return typeof b !== "string" || b.length != 1 ? -1 : a.indexOf(b, 0);
        for (var c = 0; c < a.length; c++)
            if (c in a && a[c] === b)
                return c;
        return -1
    }
      , ua = Array.prototype.forEach ? function(a, b, c) {
        Array.prototype.forEach.call(a, b, c)
    }
    : function(a, b, c) {
        for (var d = a.length, k = typeof a === "string" ? a.split("") : a, g = 0; g < d; g++)
            g in k && b.call(c, k[g], g, a)
    }
    ;
    function va(a, b) {
        b = ta(a, b);
        b >= 0 && Array.prototype.splice.call(a, b, 1)
    }
    function wa(a) {
        return Array.prototype.concat.apply([], arguments)
    }
    function xa(a) {
        var b = a.length;
        if (b > 0) {
            for (var c = Array(b), d = 0; d < b; d++)
                c[d] = a[d];
            return c
        }
        return []
    }
    ;function ya(a, b) {
        this.i = a;
        this.j = b;
        this.h = 0;
        this.g = null
    }
    ya.prototype.get = function() {
        if (this.h > 0) {
            this.h--;
            var a = this.g;
            this.g = a.next;
            a.next = null
        } else
            a = this.i();
        return a
    }
    ;
    function za(a) {
        H.setTimeout(function() {
            throw a;
        }, 0)
    }
    ;function Aa() {
        this.h = this.g = null
    }
    Aa.prototype.add = function(a, b) {
        var c = Ba.get();
        c.set(a, b);
        this.h ? this.h.next = c : this.g = c;
        this.h = c
    }
    ;
    Aa.prototype.remove = function() {
        var a = null;
        this.g && (a = this.g,
        this.g = this.g.next,
        this.g || (this.h = null),
        a.next = null);
        return a
    }
    ;
    var Ba = new ya(function() {
        return new Ca
    }
    ,function(a) {
        return a.reset()
    }
    );
    function Ca() {
        this.next = this.scope = this.g = null
    }
    Ca.prototype.set = function(a, b) {
        this.g = a;
        this.scope = b;
        this.next = null
    }
    ;
    Ca.prototype.reset = function() {
        this.next = this.scope = this.g = null
    }
    ;
    var Da, Ea = !1, Fa = new Aa;
    function Ga(a) {
        Da || Ha();
        Ea || (Da(),
        Ea = !0);
        Fa.add(a, void 0)
    }
    function Ha() {
        var a = Promise.resolve(void 0);
        Da = function() {
            a.then(Ia)
        }
    }
    function Ia() {
        for (var a; a = Fa.remove(); ) {
            try {
                a.g.call(a.scope)
            } catch (c) {
                za(c)
            }
            var b = Ba;
            b.j(a);
            b.h < 100 && (b.h++,
            a.next = b.g,
            b.g = a)
        }
        Ea = !1
    }
    ;function K() {
        this.i = this.i;
        this.j = this.j
    }
    K.prototype.i = !1;
    K.prototype.dispose = function() {
        this.i || (this.i = !0,
        this.D())
    }
    ;
    K.prototype[Symbol.dispose] = function() {
        this.dispose()
    }
    ;
    K.prototype.addOnDisposeCallback = function(a, b) {
        this.i ? b !== void 0 ? a.call(b) : a() : (this.j || (this.j = []),
        b && (a = a.bind(b)),
        this.j.push(a))
    }
    ;
    K.prototype.D = function() {
        if (this.j)
            for (; this.j.length; )
                this.j.shift()()
    }
    ;
    function Ja(a) {
        var b = {}, c;
        for (c in a)
            b[c] = a[c];
        return b
    }
    ;var Ka = /&/g
      , La = /</g
      , Ma = />/g
      , Na = /"/g
      , Oa = /'/g
      , Pa = /\x00/g
      , Qa = /[\x00&<>"']/;
    /*

 Copyright Google LLC
 SPDX-License-Identifier: Apache-2.0
*/
    function L(a) {
        this.g = a
    }
    L.prototype.toString = function() {
        return this.g
    }
    ;
    var Ra = new L("about:invalid#zClosurez");
    function Sa(a) {
        this.S = a
    }
    function M(a) {
        return new Sa(function(b) {
            return b.substr(0, a.length + 1).toLowerCase() === a + ":"
        }
        )
    }
    var Ta = [M("data"), M("http"), M("https"), M("mailto"), M("ftp"), new Sa(function(a) {
        return /^[^:]*([/?#]|$)/.test(a)
    }
    )]
      , Ua = /^\s*(?!javascript:)(?:[\w+.-]+:|[^:/?#]*(?:[/?#]|$))/i;
    var Va = {
        X: 0,
        V: 1,
        W: 2,
        0: "FORMATTED_HTML_CONTENT",
        1: "EMBEDDED_INTERNAL_CONTENT",
        2: "EMBEDDED_TRUSTED_EXTERNAL_CONTENT"
    };
    function N(a, b) {
        b = Error.call(this, a + " cannot be used with intent " + Va[b]);
        this.message = b.message;
        "stack"in b && (this.stack = b.stack);
        this.type = a;
        this.name = "TypeCannotBeUsedWithIframeIntentError"
    }
    var O = Error;
    N.prototype = ca(O.prototype);
    N.prototype.constructor = N;
    if (ha)
        ha(N, O);
    else
        for (var P in O)
            if (P != "prototype")
                if (Object.defineProperties) {
                    var Wa = Object.getOwnPropertyDescriptor(O, P);
                    Wa && Object.defineProperty(N, P, Wa)
                } else
                    N[P] = O[P];
    N.H = O.prototype;
    function Xa(a) {
        Qa.test(a) && (a.indexOf("&") != -1 && (a = a.replace(Ka, "&amp;")),
        a.indexOf("<") != -1 && (a = a.replace(La, "&lt;")),
        a.indexOf(">") != -1 && (a = a.replace(Ma, "&gt;")),
        a.indexOf('"') != -1 && (a = a.replace(Na, "&quot;")),
        a.indexOf("'") != -1 && (a = a.replace(Oa, "&#39;")),
        a.indexOf("\x00") != -1 && (a = a.replace(Pa, "&#0;")));
        return a
    }
    ;var Ya, Q;
    a: {
        for (var Za = ["CLOSURE_FLAGS"], R = H, $a = 0; $a < Za.length; $a++)
            if (R = R[Za[$a]],
            R == null) {
                Q = null;
                break a
            }
        Q = R
    }
    var ab = Q && Q[610401301];
    Ya = ab != null ? ab : !1;
    function S() {
        var a = H.navigator;
        return a && (a = a.userAgent) ? a : ""
    }
    var T, bb = H.navigator;
    T = bb ? bb.userAgentData || null : null;
    function cb() {
        return Ya ? !!T && T.brands.length > 0 : !1
    }
    function db(a) {
        var b = {};
        a.forEach(function(c) {
            b[c[0]] = c[1]
        });
        return function(c) {
            return b[c.find(function(d) {
                return d in b
            })] || ""
        }
    }
    function eb() {
        for (var a = S(), b = RegExp("([A-Z][\\w ]+)/([^\\s]+)\\s*(?:\\((.*?)\\))?", "g"), c = [], d; d = b.exec(a); )
            c.push([d[1], d[2], d[3] || void 0]);
        a = db(c);
        if (cb())
            a: {
                if (Ya && T)
                    for (b = 0; b < T.brands.length; b++)
                        if ((c = T.brands[b].brand) && c.indexOf("Chromium") != -1) {
                            b = !0;
                            break a
                        }
                b = !1
            }
        else
            b = (S().indexOf("Chrome") != -1 || S().indexOf("CriOS") != -1) && (cb() || S().indexOf("Edge") == -1) || S().indexOf("Silk") != -1;
        return b ? a(["Chrome", "CriOS", "HeadlessChrome"]) : ""
    }
    function fb() {
        if (cb()) {
            var a = T.brands.find(function(b) {
                return b.brand === "Chromium"
            });
            if (!a || !a.version)
                return NaN;
            a = a.version.split(".")
        } else {
            a = eb();
            if (a === "")
                return NaN;
            a = a.split(".")
        }
        return a.length === 0 ? NaN : Number(a[0])
    }
    ;function U(a) {
        K.call(this);
        this.o = 1;
        this.l = [];
        this.m = 0;
        this.g = [];
        this.h = {};
        this.A = !!a
    }
    sa(U, K);
    n = U.prototype;
    n.subscribe = function(a, b, c) {
        var d = this.h[a];
        d || (d = this.h[a] = []);
        var k = this.o;
        this.g[k] = a;
        this.g[k + 1] = b;
        this.g[k + 2] = c;
        this.o = k + 3;
        d.push(k);
        return k
    }
    ;
    function gb(a, b, c) {
        var d = V;
        if (a = d.h[a]) {
            var k = d.g;
            (a = a.find(function(g) {
                return k[g + 1] == b && k[g + 2] == c
            })) && d.F(a)
        }
    }
    n.F = function(a) {
        var b = this.g[a];
        if (b) {
            var c = this.h[b];
            this.m != 0 ? (this.l.push(a),
            this.g[a + 1] = function() {}
            ) : (c && va(c, a),
            delete this.g[a],
            delete this.g[a + 1],
            delete this.g[a + 2])
        }
        return !!b
    }
    ;
    n.G = function(a, b) {
        var c = this.h[a];
        if (c) {
            var d = Array(arguments.length - 1), k = arguments.length, g;
            for (g = 1; g < k; g++)
                d[g - 1] = arguments[g];
            if (this.A)
                for (g = 0; g < c.length; g++)
                    k = c[g],
                    hb(this.g[k + 1], this.g[k + 2], d);
            else {
                this.m++;
                try {
                    for (g = 0,
                    k = c.length; g < k && !this.i; g++) {
                        var f = c[g];
                        this.g[f + 1].apply(this.g[f + 2], d)
                    }
                } finally {
                    if (this.m--,
                    this.l.length > 0 && this.m == 0)
                        for (; c = this.l.pop(); )
                            this.F(c)
                }
            }
            return g != 0
        }
        return !1
    }
    ;
    function hb(a, b, c) {
        Ga(function() {
            a.apply(b, c)
        })
    }
    n.clear = function(a) {
        if (a) {
            var b = this.h[a];
            b && (b.forEach(this.F, this),
            delete this.h[a])
        } else
            this.g.length = 0,
            this.h = {}
    }
    ;
    n.D = function() {
        U.H.D.call(this);
        this.clear();
        this.l.length = 0
    }
    ;
    var ib = RegExp("^(?:([^:/?#.]+):)?(?://(?:([^\\\\/?#]*)@)?([^\\\\/?#]*?)(?::([0-9]+))?(?=[\\\\/?#]|$))?([^?#]+)?(?:\\?([^#]*))?(?:#([\\s\\S]*))?$");
    function jb(a) {
        var b = a.match(ib);
        a = b[1];
        var c = b[2]
          , d = b[3];
        b = b[4];
        var k = "";
        a && (k += a + ":");
        d && (k += "//",
        c && (k += c + "@"),
        k += d,
        b && (k += ":" + b));
        return k
    }
    function kb(a, b, c) {
        if (Array.isArray(b))
            for (var d = 0; d < b.length; d++)
                kb(a, String(b[d]), c);
        else
            b != null && c.push(a + (b === "" ? "" : "=" + encodeURIComponent(String(b))))
    }
    var lb = /#|$/;
    var mb = ["https://www.google.com"];
    function nb() {
        var a = this;
        this.g = [];
        this.h = function() {
            Promise.all(a.g.map(function(b) {
                document.requestStorageAccessFor(b)
            })).then(function() {
                window.removeEventListener("click", a.h)
            })
        }
    }
    function ob() {
        return F(function(a) {
            var b = a.return;
            var c = fb() >= 119;
            return b.call(a, c && !!navigator.permissions && !!navigator.permissions.query && "requestStorageAccessFor"in document)
        })
    }
    function pb() {
        var a = new nb
          , b = ["https://www.youtube.com"];
        b = b === void 0 ? mb : b;
        F(function(c) {
            switch (c.g) {
            case 1:
                return C(c, ob(), 2);
            case 2:
                if (!c.m) {
                    c.g = 3;
                    break
                }
                return C(c, Promise.all(b.map(function(d) {
                    var k;
                    return F(function(g) {
                        if (g.g == 1)
                            return g.l = 2,
                            C(g, navigator.permissions.query({
                                name: "top-level-storage-access",
                                requestedOrigin: d
                            }), 4);
                        g.g != 2 ? (k = g.m,
                        k.state === "prompt" && a.g.push(d),
                        g.g = 0,
                        g.l = 0) : (g.l = 0,
                        g.i = null,
                        g.g = 0)
                    })
                })), 4);
            case 4:
                a.g.length > 0 && window.addEventListener("click", a.h);
            case 3:
                return c.return()
            }
        })
    }
    ;var W = {}
      , qb = []
      , V = new U
      , rb = {};
    function sb() {
        for (var a = x(qb), b = a.next(); !b.done; b = a.next())
            b = b.value,
            b()
    }
    function tb(a, b) {
        return a.tagName.toLowerCase().substring(0, 3) === "yt:" ? a.getAttribute(b) : a.dataset ? a.dataset[b] : a.getAttribute("data-" + b)
    }
    function ub(a) {
        V.G.apply(V, arguments)
    }
    ;function vb(a) {
        return (a.search("cue") === 0 || a.search("load") === 0) && a !== "loadModule"
    }
    function wb(a) {
        return a.search("get") === 0 || a.search("is") === 0
    }
    ;var xb = window;
    function X(a, b) {
        this.v = {};
        this.playerInfo = {};
        this.videoTitle = "";
        this.j = this.g = null;
        this.h = 0;
        this.m = !1;
        this.l = [];
        this.i = null;
        this.C = {};
        this.options = null;
        this.A = this.T.bind(this);
        if (!a)
            throw Error("YouTube player element ID required.");
        this.id = pa(this);
        b = Object.assign({
            title: "video player",
            videoId: "",
            width: 640,
            height: 360
        }, b || {});
        var c = document;
        if (a = typeof a === "string" ? c.getElementById(a) : a) {
            xb.yt_embedsEnableRsaforFromIframeApi && pb();
            c = a.tagName.toLowerCase() === "iframe";
            b.host || (b.host = c ? jb(a.src) : "https://www.youtube.com");
            this.options = b || {};
            b = [this.options, window.YTConfig || {}];
            for (var d = 0; d < b.length; d++)
                b[d].host && (b[d].host = b[d].host.toString().replace("http://", "https://"));
            if (!c) {
                b = document.createElement("iframe");
                c = a.attributes;
                d = 0;
                for (var k = c.length; d < k; d++) {
                    var g = c[d].value;
                    g != null && g !== "" && g !== "null" && b.setAttribute(c[d].name, g)
                }
                b.setAttribute("frameBorder", "0");
                b.setAttribute("allowfullscreen", "");
                b.setAttribute("allow", "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share");
                b.setAttribute("referrerPolicy", "strict-origin-when-cross-origin");
                b.setAttribute("title", "YouTube " + Y(this, "title"));
                (c = Y(this, "width")) && b.setAttribute("width", c.toString());
                (c = Y(this, "height")) && b.setAttribute("height", c.toString());
                this.j = a;
                (c = a.parentNode) && c.replaceChild(b, a);
                a = yb(this, b);
                c = "" + Y(this, "host") + zb(this) + "?";
                d = [];
                for (var f in a)
                    kb(f, a[f], d);
                f = c + d.join("&");
                if (xb.yt_embedsEnableIframeSrcWithIntent) {
                    var e = e === void 0 ? Ta : e;
                    a: if (e = e === void 0 ? Ta : e,
                    f instanceof L)
                        e = f;
                    else {
                        for (a = 0; a < e.length; ++a)
                            if (c = e[a],
                            c instanceof Sa && c.S(f)) {
                                e = new L(f);
                                break a
                            }
                        e = void 0
                    }
                    e = e || Ra;
                    b.removeAttribute("srcdoc");
                    f = "allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox allow-storage-access-by-user-activation".split(" ");
                    b.setAttribute("sandbox", "");
                    for (a = 0; a < f.length; a++)
                        b.sandbox.supports && !b.sandbox.supports(f[a]) || b.sandbox.add(f[a]);
                    if (e instanceof L)
                        if (e instanceof L)
                            e = e.g;
                        else
                            throw Error("");
                    else
                        e = Ua.test(e) ? e : void 0;
                    e !== void 0 && (b.src = e);
                    b.sandbox.add("allow-presentation", "allow-top-navigation")
                } else
                    b.src = f;
                a = b
            }
            this.g = a;
            this.g.id || (this.g.id = "widget" + pa(this.g));
            W[this.g.id] = this;
            if (window.postMessage) {
                this.i = new U;
                Ab(this);
                b = Y(this, "events");
                for (var h in b)
                    b.hasOwnProperty(h) && this.addEventListener(h, b[h]);
                for (var l in rb)
                    rb.hasOwnProperty(l) && Bb(this, l)
            }
        }
    }
    n = X.prototype;
    n.setSize = function(a, b) {
        this.g.width = a.toString();
        this.g.height = b.toString();
        return this
    }
    ;
    n.getIframe = function() {
        return this.g
    }
    ;
    n.addEventListener = function(a, b) {
        var c = b;
        typeof b === "string" && (c = function() {
            window[b].apply(window, arguments)
        }
        );
        if (!c)
            return this;
        this.i.subscribe(a, c);
        Cb(this, a);
        return this
    }
    ;
    function Bb(a, b) {
        b = b.split(".");
        if (b.length === 2) {
            var c = b[1];
            "player" === b[0] && Cb(a, c)
        }
    }
    n.destroy = function() {
        this.g && this.g.id && (W[this.g.id] = null);
        var a = this.i;
        a && typeof a.dispose == "function" && a.dispose();
        if (this.j) {
            a = this.j;
            var b = this.g
              , c = b.parentNode;
            c && c.replaceChild(a, b)
        } else
            (a = this.g) && a.parentNode && a.parentNode.removeChild(a);
        Z && (Z[this.id] = null);
        this.options = null;
        this.g && this.o && this.g.removeEventListener("load", this.o);
        this.j = this.g = null
    }
    ;
    function Db(a, b, c) {
        c = c || [];
        c = Array.prototype.slice.call(c);
        b = {
            event: "command",
            func: b,
            args: c
        };
        a.m ? a.sendMessage(b) : a.l.push(b)
    }
    n.T = function() {
        Eb(this) || clearInterval(this.h)
    }
    ;
    function Eb(a) {
        if (!a.g || !a.g.contentWindow)
            return !1;
        a.sendMessage({
            event: "listening"
        });
        return !0
    }
    function Ab(a) {
        Fb(a, a.id, String(Y(a, "host")));
        var b = Number(xb.yt_embedsWidgetPollIntervalMs) || 250;
        a.h = setInterval(a.A, b);
        a.g && (a.o = function() {
            clearInterval(a.h);
            a.h = setInterval(a.A, b)
        }
        ,
        a.g.addEventListener("load", a.o))
    }
    function Gb(a) {
        var b = a.getBoundingClientRect();
        a = Math.max(0, Math.min(b.bottom, window.innerHeight || document.documentElement.clientHeight) - Math.max(b.top, 0)) * Math.max(0, Math.min(b.right, window.innerWidth || document.documentElement.clientWidth) - Math.max(b.left, 0));
        a = (b = b.height * b.width) ? a / b : 0;
        return document.visibilityState === "hidden" || a < .5 ? 1 : a < .75 ? 2 : a < .85 ? 3 : a < .95 ? 4 : a < 1 ? 5 : 6
    }
    function Cb(a, b) {
        a.C[b] || (a.C[b] = !0,
        Db(a, "addEventListener", [b]))
    }
    n.sendMessage = function(a) {
        a.id = this.id;
        a.channel = "widget";
        a = JSON.stringify(a);
        var b = jb(this.g.src || "").replace("http:", "https:");
        if (this.g.contentWindow)
            try {
                this.g.contentWindow.postMessage(a, b)
            } catch (c) {
                if (c.name && c.name === "SyntaxError")
                    c.message && c.message.indexOf("target origin ''") > 0 || console && console.warn && console.warn(c);
                else
                    throw c;
            }
        else
            console && console.warn && console.warn("The YouTube player is not attached to the DOM. API calls should be made after the onReady event. See more: https://developers.google.com/youtube/iframe_api_reference#Events")
    }
    ;
    function zb(a) {
        if ((a = String(Y(a, "videoId"))) && (a.length !== 11 || !a.match(/^[a-zA-Z0-9\-_]+$/)))
            throw Error("Invalid video id");
        return "/embed/" + a
    }
    function yb(a, b) {
        var c = Y(a, "playerVars");
        c ? c = Ja(c) : c = {};
        window !== window.top && document.referrer && (c.widget_referrer = document.referrer.substring(0, 256));
        var d = Y(a, "embedConfig");
        if (d) {
            if (I(d))
                try {
                    d = JSON.stringify(d)
                } catch (k) {
                    console.error("Invalid embed config JSON", k)
                }
            c.embed_config = d
        }
        c.enablejsapi = window.postMessage ? 1 : 0;
        window.location.host && (c.origin = window.location.protocol + "//" + window.location.host);
        c.widgetid = a.id;
        window.location.href && ua(["debugjs", "debugcss"], function(k) {
            var g = window.location.href;
            var f = g.search(lb);
            b: {
                var e = 0;
                for (var h = k.length; (e = g.indexOf(k, e)) >= 0 && e < f; ) {
                    var l = g.charCodeAt(e - 1);
                    if (l == 38 || l == 63)
                        if (l = g.charCodeAt(e + h),
                        !l || l == 61 || l == 38 || l == 35)
                            break b;
                    e += h + 1
                }
                e = -1
            }
            if (e < 0)
                g = null;
            else {
                h = g.indexOf("&", e);
                if (h < 0 || h > f)
                    h = f;
                e += k.length + 1;
                g = decodeURIComponent(g.slice(e, h !== -1 ? h : 0).replace(/\+/g, " "))
            }
            g !== null && (c[k] = g)
        });
        window.location.href && (c.forigin = window.location.href);
        a = window.location.ancestorOrigins;
        c.aoriginsup = a === void 0 ? 0 : 1;
        a && a.length > 0 && (c.aorigins = Array.from(a).join(","));
        window.document.referrer && (c.gporigin = window.document.referrer);
        b && (c.vf = Gb(b));
        return c
    }
    function Hb(a, b) {
        if (I(b)) {
            for (var c in b)
                b.hasOwnProperty(c) && (a.playerInfo[c] = b[c]);
            a.playerInfo.hasOwnProperty("videoData") && (b = a.playerInfo.videoData,
            b.hasOwnProperty("title") && b.title ? (b = b.title,
            b !== a.videoTitle && (a.videoTitle = b,
            a.g.setAttribute("title", b))) : (a.videoTitle = "",
            a.g.setAttribute("title", "YouTube " + Y(a, "title"))))
        }
    }
    function Ib(a, b) {
        b = x(b);
        for (var c = b.next(), d = {}; !c.done; d = {
            u: void 0
        },
        c = b.next())
            d.u = c.value,
            a[d.u] || (d.u === "getCurrentTime" ? a[d.u] = function() {
                var k = this.playerInfo.currentTime;
                if (this.playerInfo.playerState === 1) {
                    var g = (Date.now() / 1E3 - this.playerInfo.currentTimeLastUpdated_) * this.playerInfo.playbackRate;
                    g > 0 && (k += Math.min(g, 1))
                }
                return k
            }
            : vb(d.u) ? a[d.u] = function(k) {
                return function() {
                    this.playerInfo = {};
                    this.v = {};
                    Db(this, k.u, arguments);
                    return this
                }
            }(d) : wb(d.u) ? a[d.u] = function(k) {
                return function() {
                    var g = k.u
                      , f = 0;
                    g.search("get") === 0 ? f = 3 : g.search("is") === 0 && (f = 2);
                    return this.playerInfo[g.charAt(f).toLowerCase() + g.substring(f + 1)]
                }
            }(d) : a[d.u] = function(k) {
                return function() {
                    Db(this, k.u, arguments);
                    return this
                }
            }(d))
    }
    n.getVideoEmbedCode = function() {
        var a = "" + Y(this, "host") + zb(this)
          , b = Number(Y(this, "width"))
          , c = Number(Y(this, "height"));
        if (isNaN(b) || isNaN(c))
            throw Error("Invalid width or height property");
        b = Math.floor(b);
        c = Math.floor(c);
        var d = this.videoTitle;
        a = Xa(a);
        d = Xa(d != null ? d : "YouTube video player");
        return '<iframe width="' + b + '" height="' + c + '" src="' + a + '" title="' + (d + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>')
    }
    ;
    n.getOptions = function(a) {
        return this.v.namespaces ? a ? this.v[a] ? this.v[a].options || [] : [] : this.v.namespaces || [] : []
    }
    ;
    n.getOption = function(a, b) {
        if (this.v.namespaces && a && b && this.v[a])
            return this.v[a][b]
    }
    ;
    function Y(a, b) {
        a = [a.options, window.YTConfig || {}];
        for (var c = 0; c < a.length; c++) {
            var d = a[c][b];
            if (d !== void 0)
                return d
        }
        return null
    }
    var Z = null
      , Jb = null;
    function Kb(a) {
        if (a.tagName.toLowerCase() !== "iframe") {
            var b = tb(a, "videoid");
            b && (b = {
                videoId: b,
                width: tb(a, "width"),
                height: tb(a, "height")
            },
            new X(a,b))
        }
    }
    function Fb(a, b, c) {
        Z || (Z = {},
        Jb = new Set,
        Lb.addEventListener("message", function(d) {
            a: if (Jb.has(d.origin)) {
                try {
                    var k = JSON.parse(d.data)
                } catch (e) {
                    break a
                }
                var g = Z[k.id];
                if (g && d.origin === g.O)
                    switch (d = g.U,
                    d.m = !0,
                    d.m && (ua(d.l, d.sendMessage, d),
                    d.l.length = 0),
                    g = k.event,
                    k = k.info,
                    g) {
                    case "apiInfoDelivery":
                        if (I(k))
                            for (var f in k)
                                k.hasOwnProperty(f) && (d.v[f] = k[f]);
                        break;
                    case "infoDelivery":
                        Hb(d, k);
                        break;
                    case "initialDelivery":
                        I(k) && (clearInterval(d.h),
                        d.playerInfo = {},
                        d.v = {},
                        Ib(d, k.apiInterface),
                        Hb(d, k));
                        break;
                    case "alreadyInitialized":
                        clearInterval(d.h);
                        break;
                    case "readyToListen":
                        Eb(d);
                        break;
                    default:
                        d.i.i || (f = {
                            target: d,
                            data: k
                        },
                        d.i.G(g, f),
                        ub("player." + g, f))
                    }
            }
        }));
        Z[b] = {
            U: a,
            O: c
        };
        Jb.add(c)
    }
    var Lb = window;
    J("FV_YT.PlayerState.UNSTARTED", -1);
    J("FV_YT.PlayerState.ENDED", 0);
    J("FV_YT.PlayerState.PLAYING", 1);
    J("FV_YT.PlayerState.PAUSED", 2);
    J("FV_YT.PlayerState.BUFFERING", 3);
    J("FV_YT.PlayerState.CUED", 5);
    J("FV_YT.get", function(a) {
        return W[a]
    });
    J("FV_YT.scan", sb);
    J("FV_YT.subscribe", function(a, b, c) {
        V.subscribe(a, b, c);
        rb[a] = !0;
        for (var d in W)
            W.hasOwnProperty(d) && Bb(W[d], a)
    });
    J("FV_YT.unsubscribe", function(a, b, c) {
        gb(a, b, c)
    });
    J("FV_YT.Player", X);
    X.prototype.destroy = X.prototype.destroy;
    X.prototype.setSize = X.prototype.setSize;
    X.prototype.getIframe = X.prototype.getIframe;
    X.prototype.addEventListener = X.prototype.addEventListener;
    X.prototype.getVideoEmbedCode = X.prototype.getVideoEmbedCode;
    X.prototype.getOptions = X.prototype.getOptions;
    X.prototype.getOption = X.prototype.getOption;
    qb.push(function(a) {
        var b = a;
        b || (b = document);
        a = xa(b.getElementsByTagName("yt:player"));
        b = xa((b || document).querySelectorAll(".yt-player"));
        ua(wa(a, b), Kb)
    });
    typeof YTConfig !== "undefined" && YTConfig.parsetags && YTConfig.parsetags !== "onload" || sb();
    // var Mb = H.onYTReady;
    // Mb && Mb();
    // var Nb = H.onYouTubeIframeAPIReady;
    // Nb && Nb();
    // var Ob = H.onYouTubePlayerAPIReady;
    // Ob && Ob();
}
).call(this);

}



if( typeof(flowplayer) != "undefined" ) {

  function fv_player_youtube_error( code ) {

    code = parseInt( code );

    switch( code ) {
      case 2:
        return "Invalid parameter value.";
      case 5:
        return 'HTML5 player error.';
      case 100:
        return "The video could not be found. It's either removed or private.";
      case 101:
      case 150:
        return "The video cannot be embedded."
      default:
        return 'Code: ' + code;
    }
  }

  function fv_player_pro_youtube_get_video_id( src ) {
    var aMatch;
    if( aMatch = src.match(/(?:\?|&)v=([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    if( aMatch = src.match(/youtu.be\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    if( aMatch = src.match(/(?:embed|live|shorts)\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    return false;
  }

  function fv_player_pro_youtube_addRemovableEventListener( player, eventName, cb ) {
    var callbackName = 'youtubeCallbackFunction' + Math.random().toString(36).substr(2, 7);
    window[callbackName] = cb;
    player.addEventListener(eventName, callbackName);

    return function () {
      window[callbackName] = function () {}; // make the callback inactive
      if( typeof(player.removeEventListener) != "undefined" ) {
        player.removeEventListener(eventName, callbackName);
      }
    };
  }

  function fv_player_pro_youtube_onReady(e) {
    //console.log('fv_player_pro_youtube_onReady');
    var root = jQuery(e.target.getIframe()).closest('.flowplayer');
    root.removeClass('is-loading');

    var api = root.data('flowplayer');
    api.loading = false;
    api.trigger('yt-ready');
    api.fv_yt_did_preload = true;

    //  signal to the other players that 1MB YouTube API base.js has loaded
    jQuery(document).trigger('fv-player-yt-api-loaded');

    // YouTube doesn't tell us if it's a live stream
    // but it seems when you check the duration in this moment
    // it gives 0 on live streams
    var duration = api.youtube.getDuration();
    if( duration == 0 ) {
      api.live = true;
      jQuery(root).addClass('is-live');

      // TODO: Problem is that when you use this in playlist
      // the next video will also behave like a live stream
      // but it appears to be a problem with Flowplayer in general
    }
  }


  function fv_player_pro_youtube_onStateChange(e) {
    //console.log('fv_player_pro_youtube_onStateChange',e.data);

    var root = jQuery(e.target.getIframe()).parents('.flowplayer');
    switch (e.data) {
      case -1:
        jQuery('.fp-splash',root).css('pointer-events','');
        root.addClass('is-loading');
        break;
      case FV_YT.PlayerState.PLAYING:
        var api = root.data('flowplayer');
        api.load();
        break;
      case FV_YT.PlayerState.BUFFERING:
        root.addClass('is-loading');
        // todo: put in placeholder splash screen as this event occurs if you use Video Link targetting a playlist item, but most of the time it triggers in onStateChange() already
        break;
    }
  }


  function fv_player_pro_youtube_onError(e) {
    var root = jQuery(e.target.getIframe()).parents('.flowplayer');
    var player = root.data('flowplayer');

    //  this is a copy of onError as we need to execute it for mobile preloaded player somehow...
    fv_player_log('FV Player Youtube onError for preloaded player',e);

    var src = player.video.index > 0 ? player.conf.playlist[player.video.index].sources[0].src : player.conf.clip.sources[0].src;

    fv_player_track( player, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", "YouTube video removed", src );

    setTimeout( function() {
      root.removeClass('is-splash'); //  we only do this for the preloaded player

      player.loading = false; //  we need to reset this to make sure user can pick another video in playlist
      root.removeClass('is-loading'); //  same as above
      if( player.conf.clip.sources.length > 1 ) {

        player.youtube.destroy();
        player.youtube = false;
        jQuery('.fvyoutube-engine',root).remove();
        jQuery('.fv-pf-yt-temp2',root).remove();
        jQuery(root).removeClass('is-ytios11');

        jQuery('.fp-ui',root).css('background-image','');
        jQuery('.fp-ui',root).append('<div class="wpfp_custom_popup fp-notice-load" style="height: 100%"><div class="wpfp_custom_popup_content">' + fv_flowplayer_translations.video_loaded + '</div></div>'); //  we show this so that we can capture the user click

        // TODO: Remove dead code
        jQuery('.fp-notice-load').one( 'click', function()  {
          jQuery('.fp-notice-load',root).remove();

          //var api = jQuery(root).data('flowplayer');
          player.trigger('error', [ player, { code: 4, video: player.video, custom_error: fv_player_youtube_error( e.data ) } ] );
        } );

      }

    });

  }


  function fv_player_pro_youtube_is_mobile() {
    // If it's the Facebook in-app browser or Messenger
    if( navigator.userAgent.match(/FBAN|FBAV|FB_IAB|FB4A|FBMD|FBBV|FBDV|FBSN|FBSV|FBSS|FBID|FBLC|FBOP|FBRV|FBSF|FBAN|FB4A|FBMD|FBAV|FBBV|FBDV|FBSN|FBSV|FBSS|FBID|FBLC|FBOP|FBRV|FBSF|FB_IAB/i) ) {
      jQuery('body').addClass( 'is-fv-player-fb-app' );
      return true;
    }

    // If it's Android, then it gets a special permission to play YouTube with sound! So we do not consider that a mobile
    // Include Safari (which means iPad too) as it won't let us unmute the video
    return !flowplayer.support.android && (
      !flowplayer.support.firstframe || flowplayer.support.iOS || flowplayer.support.browser.safari
    );
  }

  function fv_player_pro_youtube_is_old_android() {
    return flowplayer.support.android && flowplayer.support.android.version < 4.4;
  }

  function fv_player_pro_youtube_player_vars( video_id, root, events ) {
    var vars = {
      videoId: video_id,
      width: root.width,
      height: root.height,
      playerVars: {
        // seems we need this for mobile load, otherwise onReady calls playVideo()
        // but now we had to exclude Safari (which means iPad too) from it
        autoplay: 0,
        controls: !jQuery(root).hasClass('no-controlbar') && fv_player_pro_youtube_is_old_android() ? 1 : 0, //  todo: no interface if it's a video ad!
        disablekb: 1,
        enablejsapi: 1,
        fs: 0,
        html5: 1,
        iv_load_policy: 3,
        loop: 0, //T.loop,
        modestbranding: 1,
        origin: ( document.location.protocol == "https:" ) ? "https://" : "http://" + flowplayer.conf.hostname,
        playsinline: 1,
        rel: 0,
        showinfo: 0,
        showsearch: 0,
        start: 0,
        t0: 1,
        widget_referrer: window ? window.location.href : null // help with YouTube tracking
      }
    }

    if( !fv_flowplayer_conf.youtube_cookies ) {
      vars.host = 'https://www.youtube-nocookie.com';
    }

    if( events ) {
      vars.events = events;
    }
    return vars;
  }


  function fv_player_pro_youtube_preload( that, api, is_lightbox ) {
    var root = jQuery(that);
    if( !api ) api = root.data('flowplayer');

    if( api && api.conf.item && api.conf.item.sources[0].type == 'video/youtube' || api && api.conf.clip && api.conf.clip.sources[0].type == 'video/youtube' ) { // exp: not sury why api.conf.clip sometimes fails?!
      if( api.loading == true || api.youtube || api.video.index ) return; // don' preload if it's already loading, if YouTube API already exists or if it's about to advanced to some other playlist item in case that this function was triggered by ajaxComplete as Vimeo loading Ajax has succeeded

      //if( root.find('.fake-video') ) return; // don't preload if FV Player VAST has decided to put in bogus video tag for the video ad

      api.loading = true;
      root.addClass('is-loading');

      var common = flowplayer.common,
        video_id = api.conf.item ? fv_player_pro_youtube_get_video_id(api.conf.item.sources[0].src) : fv_player_pro_youtube_get_video_id(api.conf.clip.sources[0].src); // exp: not sury why api.conf.clip sometimes fails?!

      common.removeNode(common.findDirect("video", root)[0] || common.find(".fp-player > video", root)[0]);
      var wrapperTag = common.createElement("div");
      wrapperTag.className = 'fp-engine fvyoutube-engine';

      /**
       * FV Player loads YouTube as FV_YT to avoid conflicts with original YouTube Player API,
       * that might be loaded by some other script.
       *
       * But we have seen scripts like Plausible Tracking, that use mutation observer to find out
       * about new YouTube iframes and run new YT.Player() on them. When that happens our original
       * events for the API instance stop working on iPhone!
       *
       * So we were going to use YT.get() on the load function below to get the API instance for
       * the iframe. But I found that just adding the ID attribute to the iframe fixes the issue.
       * Perhaps YouTube player API does not remove the old events if it sees the iframe has the
       * ID attribute.
       */
      wrapperTag.id = 'fv-player-yt-wrapper-' + root.attr('id');

      common.prepend(common.find(".fp-player", root)[0], wrapperTag);

        //console.log('new YT preload');  //  probably shouldn't happen when used in lightbox

        // this is the event which lets the player load YouTube
        jQuery(document).one('fv-player-yt-api-loaded', function() {

          // only one player can enter the loading phase
          if( ( typeof(FV_YT) == "undefined" || typeof(FV_YT.Player) == "undefined" ) && window.fv_player_pro_yt_loading ) {
            return;
          }

          window.fv_player_pro_yt_loading = true;

          var intLoad = setInterval( function() {
            // somehow the loading indicator disappears, so we put it back
            api.loading = true;
            root.addClass('is-loading');

            if( typeof(FV_YT) == "undefined" || typeof(FV_YT.Player) == "undefined" ) {
              return;
            }

            clearInterval(intLoad);

            api.youtube = new FV_YT.Player(
              wrapperTag,
              fv_player_pro_youtube_player_vars(video_id, root)
            );

            jQuery('.fp-engine.fvyoutube-engine',root)[0].allowFullscreen = false;

            // splash needs to cover the iframe
            var splash = jQuery('.fp-splash',root);
            jQuery('.fp-ui',root).before( splash );
            splash.css('pointer-events','none');

            jQuery('.fp-ui',root).before('<div class="fv-pf-yt-temp2"></div>');
            if( flowplayer.support.iOS && flowplayer.support.iOS.version > 11 ) {
              jQuery(root).addClass('is-ytios11');
              jQuery(root).find('.fv-pf-yt-temp2').on('click', function(){
                api.toggle();
              });
            }

            api.fv_yt_onReady = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onReady',fv_player_pro_youtube_onReady);
            api.fv_yt_onStateChange = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onStateChange',fv_player_pro_youtube_onStateChange);
            api.fv_yt_onError = fv_player_pro_youtube_addRemovableEventListener(api.youtube,'onError',fv_player_pro_youtube_onError);

          }, 50 );
        });

        if ( !window.fv_player_pro_yt_load || is_lightbox ) {
          window.fv_player_pro_yt_load = true;
          jQuery(document).trigger('fv-player-yt-api-loaded');
        }

    }
  }


  (function () {

    var engineImpl = function(player, root) {

        function getVideoDeatils( youtube ) {
          var quality = youtube.getPlaybackQuality();

          var output = {
            seekable: true,
            src: youtube.getVideoUrl()
          };
          output.duration = youtube.getDuration();
          if( quality && typeof(aResolutions[quality]) != "undefined" ) {
            output.width = aResolutions[quality].width;
            output.height = aResolutions[quality].height;
            output.quality = quality;
            output.qualityLabel = aQuality.qualityLabels[quality];
            output.bitrate = aResolutions[quality].bitrate;
          }

          if( typeof(youtube.getVideoData) == 'function' ){
            var details = youtube.getVideoData();
            if( details.title ) {
              output.fv_title = 'YouTube: '+details.title+' ('+details.video_id+')';
              output.fv_title_clean = details.title;
            }
          }

          return output;
        }


        function onError(e) {
          fv_player_log('FV Player Youtube onError',e);

          var src = player.video.index > 0 ? player.conf.playlist[player.video.index].sources[0].src : player.conf.clip.sources[0].src;

          fv_player_track( player, false, "Video " + (root.hasClass('is-cva')?'Ad ':'') + "error", "YouTube video removed", src );

          // Unfortunately the player had to enter the ready state to get this far
          // So we act as if it's the splash state - means no controls
          root.addClass('is-splash');

          player.trigger('error', [ player, { code: 4, video: player.video, custom_message: 'Error: ' + fv_player_youtube_error( e.data ) } ] );

          /**
           * Go to next video if it's a playlist and if there are not other sources.
           * In case of other sources FV Player Alternative Sources will already play the other
           * source based on that error trigger above.
           */
          if( player.conf.playlist.length > 1 && player.conf.clip.sources.length == 0 ) {

            setTimeout( function() {
              player.loading = false; //  we need to reset this to make sure user can pick another video in playlist
              root.removeClass('is-loading'); //  same as above

              player.paused = false;  //  we need to make sure it's not paused which happens in case of autoadvance
              root.removeClass('is-paused');  //  same as above

              player.ready = true;  //  we need to set this otherwise further clicks will make the video load again
              player.bind('load', function() {
                player.ready = false; //  we need to set this otherwise playlist advance won't trigger all the events properly
              });

              setTimeout( function() {
                player.next();
              }, 5000 );

            });
          }

        }


        function onApiChange() {
          player.one('ready progress', function() { //  exp: primary issue here is that the event fires multiple times for each video. And also Flowplayer won't remove the subtitles button/menu when you switch videos

            /**
             * The progress even might trigger for another video in the playlist if we skip quickly.
             * This video might no longer be using YouTube engine.
             */
            if ( 'fvyoutube' !== player.engine.engineName ) {
              return;
            }

            if( youtube.getOptions().indexOf('captions') > -1 ) {

              if( player.video.subtitles ) {
                youtube.unloadModule("captions");
                return;
              }

              var objCurrent = youtube.getOption('captions','track');
              var aSubtitles = youtube.getOption('captions','tracklist');
              if( aSubtitles == 0 ){
                youtube.loadModule("captions");
                return;
              }

              youtube.setOption('captions','fontSize', 1 );

              //  core FP createUIElements()
              var common = flowplayer.common;
              wrap = common.find('.fp-captions', root)[0];
              var wrap = common.find('.fp-subtitle', root)[0];
              wrap = wrap || common.appendTo(common.createElement('div', {'class': 'fp-captions'}), common.find('.fp-player', root)[0]);
              Array.prototype.forEach.call(wrap.children, common.removeNode);

              //  core FP createSubtitleControl()
              var subtitleControl = root.find('.fp-cc')[0] || common.createElement('strong', { className: 'fp-cc' }, 'CC');
              var subtitleMenu = root.find('.fp-subtitle-menu')[0] || common.createElement('div', {className: 'fp-menu fp-subtitle-menu'}, '<strong>Closed Captions</strong>');

              common.find('a', subtitleMenu).forEach(common.removeNode);
              subtitleMenu.appendChild(common.createElement('a', {'data-yt-subtitle-index': -1}, 'No subtitles'));  //  exp: not using data-subtitle-index, but data-yt-subtitle-index to avoid code in core FP lib/ext/subtitle.js

              ( aSubtitles || []).forEach(function(st, i) { //  customized to read from above parsed YouTube subtitles
                var item = common.createElement('a', {'data-yt-subtitle-index': i}, st.displayName);
                if( objCurrent && objCurrent.languageCode && objCurrent.languageCode == st.languageCode) {
                  jQuery(item).addClass('fp-selected');
                }
                subtitleMenu.appendChild(item);
              });
              common.find('.fp-ui', root)[0].appendChild(subtitleMenu);
              common.find('.fp-controls', root)[0].appendChild(subtitleControl);

              root.find('.fp-cc').removeClass('fp-hidden');

              jQuery(document).on('click', '.fp-subtitle-menu a', function(e) {
                e.preventDefault();

                jQuery('a[data-yt-subtitle-index]').removeClass('fp-selected');
                jQuery(this).addClass('fp-selected');

                if( aSubtitles[jQuery(this).data('yt-subtitle-index')] ) {
                  // Was the NL option in use?
                  if( root.data('fv-player-youtube-nl') == undefined ) {
                    root.data('fv-player-youtube-nl', root.hasClass('is-youtube-nl') );
                  }

                  // Do not use the NL mode as it would prevent the subtitles from showing
                  root.removeClass('is-youtube-nl');

                  youtube.setOption('captions','track',{"languageCode": aSubtitles[jQuery(this).data('yt-subtitle-index')].languageCode});
                } else {
                  if( root.data('fv-player-youtube-nl') ) {
                    // Back to NL if it was enabled before
                    root.addClass('is-youtube-nl');
                  }

                  youtube.unloadModule("captions");
                }

              });

            }
          });
        }


        function onReady() {
          // YouTube doesn't tell us if it's a live stream
          // but it seems when you check the duration in this moment
          // it gives 0 on live streams
          var duration = youtube.getDuration();
          if( duration == 0 ) {
            player.live = true;
            jQuery(root).addClass('is-live');

            // TODO: Problem is that when you use this in playlist
            // the next video will also behave like a live stream
            // but it appears to be a problem with Flowplayer in general
          }

          var a = jQuery.extend( loadVideo, getVideoDeatils(youtube) );

          if( !player.ready ) {

            if ( player.autoplayed ) {
                // we init YouTube muted to allow muted autoplay
                // we need to do this before we trigger ready event as there we might need to mute the video for custom start time
                player.mute(true,true); // mute, but don't remember it!

                // look for youtube_unmute_attempted to see what happens next
            }

            youtube.playVideo();

            // TODO: Shouldn't this trigger on YT.PlayerState.PLAYING - if so, do we need this onReady at all?
            //  workaround for iPad "QuotaExceededError: DOM Exception 22: An attempt was made to add something to storage that exceeded the quota." http://stackoverflow.com/questions/14555347/html5-localstorage-error-with-safari-quota-exceeded-err-dom-exception-22-an
            try {
              player.one( 'ready', function() {
                player.trigger( "resume", [player] ); //  not sure why but Flowplayer HTML5 engine triggers resume event once the video starts to play
              });
              player.trigger('ready', [player, a] );
            } catch(e) {} //  bug: the seeking doesn't work!
          }

          player.ready = true;

          if( isMobile ) {
            jQuery('.fp-ui',root).hide();
          }

          if( flowplayer.support.iOS.version < 11 || flowplayer.support.android.version < 5 ) { // tested on Android 6
            root.find('.fp-speed').hide();

            player.YTErrorTimeout = setTimeout( function() {
              if( !player.error && youtube.getPlayerState() == -1 ) {  //  exp: the onError event sometimes won't fire :( (Safari 11 most of the time)
                player.trigger('error', [ player, { code: 4, video: player.video, custom_message: 'Error: YouTube video not started'  } ] );
              }
            }, 1000 );
          }
        }


        function onStateChange(e) {//console.log('onStateChange '+e.data+' '+ ( e.target ? jQuery('.flowplayer').index(jQuery(e.target.getIframe()).parents('.flowplayer')) : false ) );
          if( root.find('.fv-fp-no-picture.is-active').length == 0 ) jQuery('.fvyoutube-engine',root).show();

          switch (e.data) {
            case -1:  //  exp: means "unstarted", runs for playlist item change
              jQuery('.fp-splash',root).css('pointer-events',''); //  exp: for random playlist autoplay
              //player.ready = false;  //  todo: causes ready event on playlist advance - should it be there?

              // we need to set the status properly, what if the VAST ad loads before YouTube engine does, it must be able to resume the video
              player.playing = false;
              player.paused = true;

              // The video might not be playable, it might be set to start in XY hours
              // Unfortunately this information is not part of any of the get* calls on youtube
              // So we just check again if the video is still in the -1 status
              // If it is, then we show the UI to make sure the "Live in XY hours" message is visible
              // Then the video plays properly once live stream gets live.
              setTimeout( function() {
                var fresh_status = youtube.getPlayerState();
                if( fresh_status == -1 ) {
                  fv_player_log('This video did not start yet!');

                  root.removeClass('is-youtube-nl');

                  /**
                   * If we did preload YouTube iframe, the ready event does not run, so the video
                   * never stops loading. So since we detected the video is not playable, we need
                   * to make sure the splash is removed so that user can see the original YouTube UI
                   */
                  if ( player.fv_yt_did_preload ) {
                    root.find( '.fp-splash' ).remove();
                    root.removeClass( 'is-loading' ).addClass( 'is-ready' );
                  }
                }
              }, 1000 );
              break;

            case FV_YT.PlayerState.BUFFERING:    //  3, seems to me we don't need this at all
              if( typeof(youtube.getCurrentTime) == "function") {
                player.trigger('seek', [player, youtube.getCurrentTime()] );
              }
              break;

            case FV_YT.PlayerState.CUED:         //  5
              root.removeClass('is-loading');
              root.addClass('is-paused');
              player.loading = false;  //  exp: without this the core Flowplayer will think the player is still loading and wont' allow iphone users to click the playlist thumbs more than twice

              if( !flowplayer.support.firstframe  ) { // todo: this whole part doesn't make sense anymore, as .fv-pf-yt-temp is no more, but it should be
                var playlist_item = jQuery('[rel='+root.attr('id')+'] span').eq(player.video.index);
                jQuery('.fv-pf-yt-temp',root).css('background-image', playlist_item.css('background-image') );
                if( !flowplayer.support.dataload ) jQuery('.fp-ui',root).hide(); //  exp: hide the UI so that the iframe can be clicked into on iPad
                jQuery('.fv-pf-yt-temp',root).show();
                jQuery('.fv-pf-yt-temp-play',root).show();
              }

              break;

            case FV_YT.PlayerState.ENDED:  //  0
              player.playing = false;

              // TODO: Sometimes the end time is missing 1 second to match the duration
              // However the same issue appears on https://www.youtube.com/watch?v=QRS8MkLhQmM
              // where the video loads as having duration of 1:37 which then changes to 1:36 in a second
              clearInterval(intUIUpdate);
              intUIUpdate = false;

              player.trigger( "pause", [player] );  //  not sure why but Flowplayer HTML5 engine triggers pause event before the video finishes
              player.trigger( "finish", [player] );

              jQuery('.fvyoutube-engine',root).hide();

              jQuery('.fv-pf-yt-temp2',root).show();
              jQuery('.fp-ui',root).show();
              break;

            case FV_YT.PlayerState.PAUSED:   //  2

              // Was it paused because of unmuting? This happens on Safari even on desktop.
              if( player.autoplayed && player.youtube_unmute_attempted === 1 ) {
                player.youtube_unmute_attempted = 2;
                fv_player_log('FV FP YouTube: Volume restore failed.');

                player.mute(true,true); // mute, but don't remember it!
                youtube.playVideo();

                jQuery('body').one('click', function() {
                  if( player && player.ready ) {
                    fv_player_log('FV FP YouTube: Volume restore on click.');

                    player.volume(player.volumeLevel); // unmute
                  }
                });
                return;
              }

              if( player.seeking ) {
                youtube.playVideo();
                return;
              }

              clearInterval(intUIUpdate);
              intUIUpdate = false;
              player.trigger( "pause", [player] );
              break;

            case FV_YT.PlayerState.PLAYING:    //  1
              triggerVideoInfoUpdate();
              onReady();
              triggerUIUpdate();
              if( isMobile ) {
                var ui = jQuery('.fp-ui',root);
                ui.show();
                jQuery('.fp-splash',root).css('pointer-events',''); //  iPad iOS 7 couldn't pause video after it started
                if( !jQuery(root).hasClass('no-controlbar') && fv_player_pro_youtube_is_old_android() || flowplayer.support.iOS && flowplayer.support.iOS.version < 10 ) {
                  ui.hide();
                }
              }
              if( player.seeking ) {
                player.seeking = false;

                //  todo: stop progress event perhaps
                if( typeof(youtube.getCurrentTime) == "function") {
                  player.trigger('seek', [player, youtube.getCurrentTime()] );
                }
              }

              if( player.paused ) {
                player.trigger( "resume", [player] );
              }

              // Without this delay we cannot be sure the youtube.isMuted() reports properly in playlists
              // We could make the lag shorter in triggerUIUpdate()
              player.one('progress', function() {
                if( player.autoplayed && !player.youtube_unmute_attempted && youtube.isMuted() ) {
                  fv_player_log('FV FP YouTube: Trying to restore volume to '+player.volumeLevel);

                  player.volume(player.volumeLevel); // unmute

                  // used to try to unmute the video once paused due to "unmuting failed and the element was paused instead because the user didn't interact with the document before."
                  player.youtube_unmute_attempted = 1;
                  // But it has to pause quickly, what if user paused the video?
                  setTimeout( function() {
                    player.youtube_unmute_attempted = false;
                  }, 500 );
                }
              } );

              // Hide UI again if it was shown previously
              // To show the "Live in XY hours" message
              if( window.fv_player_pro && fv_player_pro.youtube_nl ) {
                root.addClass('is-youtube-nl');
              }

              break;

          }

        }


        function triggerUIUpdate() {
          var P_previous = false;
          if( intUIUpdate ) return;

          // Initial quick update to lower the youtube_unmute_attempted lag
          intUIUpdate = setTimeout( triggerUIUpdate_cb, 100 );

          // Periodic update
          intUIUpdate = setInterval( triggerUIUpdate_cb, 250 );

          function triggerUIUpdate_cb() {
            if( typeof(youtube) == "undefined" || typeof(youtube.getCurrentTime) == "undefined" ){
                return;
              }

            var P = youtube.getCurrentTime();

            if( isMobile ) {  //  YouTube sometimes doesn't fire the event to signal that the seeking was finished on iPad
              if( typeof(player.seeking) != "undefined" && player.seeking && P_previous && P_previous < P ) {
                //player.seeking = false;
                player.trigger('seek', [player] );
              }
              P_previous = P;
            }

            var time = player.video.time = (P > 0) ? P : 0;

            // for some YouTube Live streams we might get the current time of even
            // 500 days! If we pass that to progress event below, it would result
            // in checking the cuepoints for too long and stalling the browser:
            // https://github.com/flowplayer/flowplayer/blob/d5b70e7a40518582287d9b73aa76ea568c948816/lib/ext/cuepoint.js#L24-L31
            // So we start from 0 here!
            //
            // TODO: What about FV Player Pro custom start time?
            if( player.live ) {
              if( live_stream_start_time == 0 ) {
                live_stream_start_time = time;
              }
              time = time - live_stream_start_time;
            }

            player.trigger("progress", [player, time] );
            var buffer = youtube.getVideoLoadedFraction() * player.video.duration + 0.5;
            if( buffer < player.video.duration && !player.video.buffered) {
                player.video.buffer = buffer;
                player.trigger("buffer", [player, player.video.buffer ] );
            } else if (!player.video.buffered) {
                player.video.buffered = true;

                if ( player.video.buffer ) {
                  player.trigger("buffer", [player, player.video.buffer ] )
                }

                player.trigger("buffered", [player]);
            }
          }
        }


        function triggerVideoInfoUpdate() {
          //if( engine.playing ) return;
          //engine.playing = true;

          jQuery.extend(player.video, getVideoDeatils(youtube) );
        }


        var aResolutions = {
              'small': { width: 320, height: 240, bitrate: 64 },
              'medium': { width: 640, height: 360, bitrate: 512 },
              'large': { width: 854, height: 480, bitrate: 640 },
              'hd720': { width: 1280, height: 720, bitrate: 2000 },
              'hd1080': { width: 1920, height: 1080, bitrate: 4000 }
            },
            aQuality = {
               bitrates: false,
               defaultQuality: "default",
               activeClass: "active",
               qualityLabels: {
                   medium: 'medium',
                   large: 'large',
                   'hd720': 'hd'
               }
            },
            common = flowplayer.common,
            intUIUpdate = false,
            isMobile = fv_player_pro_youtube_is_mobile(),
            loadVideo,
            root = jQuery(root),
            youtube,
            live_stream_start_time = 0;

        var engine = {
            engineName: engineImpl.engineName,

            load: function (video) {
                loadVideo = video;
                live_stream_start_time = 0;

                var video_id = fv_player_pro_youtube_get_video_id(video.src);
                if( !video_id ){
                  root.find('.fp-ui').append('<div class="fp-message"><h2>' + fv_flowplayer_translations.invalid_youtube + '</h2></div>');
                  root.addClass('is-error').removeClass('is-loading');
                  //  todo: trigger error event in a normal way?
                  return;
                }

                if( youtube ) {//console.log('YT already loaded');
                  if( !flowplayer.support.dataload && !flowplayer.support.inlineVideo  ) {  //  exp: for old iOS
                    youtube.cueVideoById( video_id, 0, 'default' );
                  } else {//console.log('y 2');
                    youtube.loadVideoById( video_id, 0, 'default' );
                  }

                } else if( player.youtube && player.youtube.getIframe() ) { // youtube and its iframe exists - was not destroyed
                  //console.log('YT preloaded',player.youtube.getIframe());
                  youtube = player.youtube;

                  //  this removes the start-up event listeners
                  player.fv_yt_onReady();
                  player.fv_yt_onStateChange();
                  player.fv_yt_onError();

                  youtube.addEventListener('onReady',onReady);
                  youtube.addEventListener('onStateChange',onStateChange);
                  youtube.addEventListener('onError',onError);
                  youtube.addEventListener('onApiChange',onApiChange);
                  if( !flowplayer.support.dataload && !flowplayer.support.inlineVideo  ) { //  exp: for old iOS
                    youtube.cueVideoById( video_id, 0, 'default' );

                    //  exp: we just changed the video to something else, so we need to let it process it
                    setTimeout( function() {
                      onReady();
                    },100); // todo: find some better way!
                  } else {
                    youtube.loadVideoById( video_id, 0, 'default' );
                  }

                } else {//console.log('YT not yet loaded');
                  common.removeNode(common.findDirect("video", root)[0] || common.find(".fp-player > video", root)[0]);
                  var wrapperTag = common.createElement("div");
                  wrapperTag.className = 'fp-engine fvyoutube-engine';
                  common.prepend(common.find(".fp-player", root)[0], wrapperTag);

                  var intLoad = setInterval( function() {
                    if( typeof(FV_YT) == "undefined" || typeof(FV_YT.Player) == "undefined" ) {
                      //console.log('YT not awaken yet!');
                      return;
                    }

                    clearInterval(intLoad);

                    /*var had_youtube_before =
                      jQuery('presto-player[src*=\\.youtube\\.com], presto-player[src*=\\.youtu\\.be], presto-player[src*=\\.youtube-nocookie\\.com]').length ||
                      jQuery('iframe[src*=\\.youtube\\.com], iframe[src*=\\.youtu\\.be], iframe[src*=\\.youtube-nocookie\\.com]').length;*/

                    youtube = new FV_YT.Player(
                      wrapperTag,
                      fv_player_pro_youtube_player_vars(video_id, root, {
                        onReady: onReady,
                        onStateChange: onStateChange,
                        onError: onError,
                        onApiChange: onApiChange,
                      })
                    );

                    /*if( had_youtube_before ) {
                      //youtube.loadVideoById( video_id, 0, 'default' );

                      setTimeout( function() {
                        onReady();
                      },1000);
                    }

                    console.log(youtube);*/

                    var iframe = jQuery('.fp-engine.fvyoutube-engine',root);
                    iframe[0].allowFullscreen = false;
                    /* in Chrome it's possible to double click the video entery YouTube fullscreen that way. Cancelling the event won't help, so here is a pseudo-fix */
                    iframe.on("webkitfullscreenchange", function() {
                      if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                      }
                      return false;
                    });
                  }, 5 );
                }

                //  exp: only needed if we decide not to use standard player for iPad etc.
                //  copy of original Flowplayer variable declarations
                var FS_ENTER = "fullscreen",
                  FS_EXIT = "fullscreen-exit",
                  FS_SUPPORT = flowplayer.support.fullscreen,
                  win = window,
                  scrollX,
                  scrollY;

                //  copy of original Flowplayer function
                player.fullscreen = function(flag) {
                  var wrapper = jQuery(root).find('.fp-player')[0];

                  if (player.disabled) return;

                  if (flag === undefined) flag = !player.isFullscreen;

                  if (flag) {
                    scrollY = win.scrollY;
                    scrollX = win.scrollX;
                  }

                  if (FS_SUPPORT) {

                     if (flag) {
                        ['requestFullScreen', 'webkitRequestFullScreen', 'mozRequestFullScreen', 'msRequestFullscreen'].forEach(function(fName) {
                           if (typeof wrapper[fName] === 'function') {
                              wrapper[fName](Element.ALLOW_KEYBOARD_INPUT);
                              if (fName === 'webkitRequestFullScreen' && !document.webkitFullscreenElement)  { // Element.ALLOW_KEYBOARD_INPUT not allowed
                                 wrapper[fName]();
                              }
                              return false;
                           }
                        });

                     } else {
                        ['exitFullscreen', 'webkitCancelFullScreen', 'mozCancelFullScreen', 'msExitFullscreen'].forEach(function(fName) {
                          if (typeof document[fName] === 'function') {
                            document[fName]();
                          }
                        });
                     }

                  } else {
                     player.trigger(flag ? FS_ENTER : FS_EXIT, [player]);
                  }

                  return player;
                };

                player.on('fullscreen-exit', function() {
                  win.scrollTo(scrollX, scrollY);
                });
            },

            mute: function(flag) {
              if( typeof(youtube) == "undefined" ) return;
              player.muted = !!flag;
              if( flag ) youtube.mute(); else youtube.unMute();
              player.trigger('mute', [player, flag]);
            },

            pause: function () {
              clearInterval(player.YTErrorTimeout);
              youtube.pauseVideo();
            },

            pick: function (sources) {
              var i, source;
              for (i = 0; i < sources.length; i = i + 1) {
                source = sources[i];
                if( source.src.match(/(youtube\.com|youtube-nocookie\.com|youtu\.be)/) ) {
                  if(source.src.match(/\/shorts\//)) {
                    source.src = source.src.replace('/shorts/', '/watch?v=') // replace shorts with /watch?v=
                  }

                  return source;
                }
              }
            },

            resume: function () {
              if( player.finished ) {
                //videoTag.currentTime = 0;
              }
              if( typeof(youtube.playVideo) != "undefined" ) {
                youtube.playVideo();
              }
            },

            seek: function (time) {
              youtube.seekTo(time, true);
              player.seeking = true;
              loadVideo.currentTime = time;
              triggerUIUpdate();
            },

            speed: function (val) {
              youtube.setPlaybackRate( parseFloat(val) );
              player.trigger('speed', [player, val]);
            },

            stop: function() {
              youtube.stopVideo();
            },

            unload: function () { //  todo: using youtube.stopVideo breaks things, no good experience with youtube.destroy either
              //engine.playing = false;

              clearInterval(intUIUpdate);

              if( !fv_player_pro_youtube_is_mobile() ) {
                youtube.destroy();
                jQuery('.fvyoutube-engine',root).remove();
                clearInterval(intUIUpdate);

              } else {//console.log('YT mobile unload');
                youtube.stopVideo(); //  exp. engine.youtube is somehow undefined here?

                player.one( 'load', function(e,api) {
                  if ( api.engine.engineName == 'fvyoutube' ) return;

                  clearInterval(intUIUpdate);
                  youtube.destroy();
                  player.youtube = false;

                  jQuery('.fvyoutube-engine',root).remove();
                  jQuery('.fv-pf-yt-temp2',root).remove();
                  jQuery(root).removeClass('is-ytios11');

                  // TODO: Remove dead code
                  //  exp: if the next video is not YouTube, iPad will have issues loading it as there was no video element on the page previously
                  //e.preventDefault();
                  /*jQuery('.fp-ui',root).css('background-image','');
                  jQuery(root).removeClass('is-loading');
                  jQuery(root).removeClass('is-mouseover');
                  jQuery(root).addClass('is-mouseout');
                  jQuery('.fp-ui',root).append('<div class="wpfp_custom_popup fp-notice-load" style="height: 100%"><div class="wpfp_custom_popup_content">' + fv_flowplayer_translations.video_loaded + '</div></div>'); //  we show this so that we can capture the user click

                  api.loading = false;

                  var i = api.video.index;
                  jQuery('.fp-notice-load').one( 'click', function(e) {
                  jQuery('.fp-notice-load',root).remove();

                  var api = jQuery(root).data('flowplayer');
                  api.loading = false;
                  api.error = false;
                  api.play(i);
                  } );*/

                });
              }

              player.youtube_unmute_attempted = false;

              if( !flowplayer.support.firstframe ) {  // prevent playback of the next video on iOS 9 and so on
                player.one( 'ready', function(e,api) {
                  api.stop();
                });
              }
            },

            volume: function (level) {
              if( typeof(youtube.setVolume) == "function" ) {
                if( level > 0 ) player.mute(false);
                player.volumeLevel = level;
                youtube.setVolume( level * 100 );
                player.trigger("volume", [player, level]);
              }
            },

        };

        // When the lightbox is closing or switching frames we need to get rid of YouTube as fancyBox moves the player HTML when closing,
        // which means that the iframe content loads again and YouTube video starts playing.
        jQuery(document).on('afterClose.fb beforeLoad.fb', function() {
          if( youtube && (player.lightbox_visible && !player.lightbox_visible()) && (player.is_in_lightbox && player.is_in_lightbox()) ) {
            // Using player.unload() won't work as the player is not in the splash state
            player.trigger("unload", [player]);

            youtube.destroy();
            youtube = false;

            // Get rid of preloaded YouTube player API reference
            if ( player.youtube ) {
              player.youtube = false;
            }
          }
        });

        return engine;
    };

    engineImpl.engineName = 'fvyoutube';
    engineImpl.canPlay = function (type) {
      return /video\/youtube/i.test(type);
    };
    flowplayer.engines.push(engineImpl);

    flowplayer( function(api,root) {
      if( jQuery(root).hasClass('lightboxed') ) return;

      if( fv_player_pro_youtube_is_mobile() ) {
        // Give Flowplayer a bit of time to finish initializing, like the unload event for splash state players has to finish
        setTimeout( function() {
          fv_player_pro_youtube_preload(root,api);
        });
      }
    });

    jQuery(document).ready( function() {
      if( fv_player_pro_youtube_is_mobile() ) {  //  in Flowplayer 7 Andoird and iOS thinks it can autoplay
        jQuery(document).on( 'afterShow.fb', function() {
          jQuery('.fancybox-slide--current .flowplayer').each( function() {
            fv_player_pro_youtube_preload( this, false, true );
          })
        });

        /**
         * Remove YouTube engine when closing lightbox, this part does it even if you did not play the video.
         * The "afterClose.fb beforeLoad.fb" event handler in engine would not run for such video if closing lightbox.
         *
         * Removing "afterClose.fb beforeLoad.fb" event handler from engine would not properly unload the video -
         * it would no update api.was_played to false. Seems like the issue might be with "youtube" local var.
         * Perhaps the progress event run in the "youtube" local var and then setting it in core Freedom Video Player?
         */
        jQuery(document).on('beforeClose.fb beforeLoad.fb', function( e, instance, slide ) {
          jQuery( '.freedomplayer', slide.$slide ).each( function() {

            var api = jQuery( this ).data('freedomplayer');
            if ( api ) {
              // Using player.unload() won't work as the player is not in the splash state
              api.trigger( "unload", [ api ] );

              // Get rid of preloaded YouTube player API
              if ( api.youtube ) {
                api.youtube.destroy();
                api.youtube = false;
              }
            }
          })
        });
      }
    });

  }());

}




/*
 * YouTube has a limited set of speed settings available and we need to handle special case when a playlist of YouTube, MP4 is started by clicking the 2nd item (MP4)
 */
if (typeof (flowplayer) !== 'undefined'){
  flowplayer(function(api, root) {
    api.on('ready beforeseek', function() {
      if( api.engine.engineName == 'fvyoutube' ) {
        if( typeof(api.youtube) !== 'undefined' && typeof(api.youtube.getAvailablePlaybackRates) == "function" ) {
          api.conf.backupSpeeds = api.conf.speeds;
          api.conf.speeds = api.youtube.getAvailablePlaybackRates();
        }
      } else {
        if( api.youtube ) { // what happens if you play a vdeo which is not YouTube and the YouTube API is still up, needed for mobile
          api.youtube.destroy();
          api.youtube = false;
          jQuery('.fp-ui',root).css('background-image','');
          jQuery('.fvyoutube-engine',root).remove();
          jQuery('.fv-pf-yt-temp2',root).remove();
          jQuery(root).removeClass('is-ytios11');
        }

        if(typeof(api.conf.backupSpeeds) !== 'undefined'){
          api.conf.speeds = api.conf.backupSpeeds;
        }
      }
    })

    // buddyboss-theme - prevent adding div to player root
    if( typeof(jQuery.fn.fitVids) != 'undefined' ) {
      jQuery(root).addClass('fitvidsignore');
    }

  })
}
