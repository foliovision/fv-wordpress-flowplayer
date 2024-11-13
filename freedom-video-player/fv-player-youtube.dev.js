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
   * Copy of https://www.youtube.com/s/player/baafab19/www-widgetapi.vflset/www-widgetapi.js with YT changed to FV_YT.
   *
   * Commented out parts where it runs onYTReady(), onYouTubeIframeAPIReady(), onYouTubePlayerAPIReady()
   */
  (function() {
    'use strict';
    var q;
    function aa(a) {
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
    var r = typeof Object.defineProperties == "function" ? Object.defineProperty : function(a, b, c) {
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
    var t = da(this);
    function u(a, b) {
        if (b)
            a: {
                var c = t;
                a = a.split(".");
                for (var d = 0; d < a.length - 1; d++) {
                    var g = a[d];
                    if (!(g in c))
                        break a;
                    c = c[g]
                }
                a = a[a.length - 1];
                d = c[a];
                b = b(d);
                b != d && b != null && r(c, a, {
                    configurable: !0,
                    writable: !0,
                    value: b
                })
            }
    }
    u("Symbol", function(a) {
        function b(k) {
            if (this instanceof b)
                throw new TypeError("Symbol is not a constructor");
            return new c(d + (k || "") + "_" + g++,k)
        }
        function c(k, e) {
            this.g = k;
            r(this, "description", {
                configurable: !0,
                writable: !0,
                value: e
            })
        }
        if (a)
            return a;
        c.prototype.toString = function() {
            return this.g
        }
        ;
        var d = "jscomp_symbol_" + (Math.random() * 1E9 >>> 0) + "_"
          , g = 0;
        return b
    });
    u("Symbol.iterator", function(a) {
        if (a)
            return a;
        a = Symbol("Symbol.iterator");
        for (var b = "Array Int8Array Uint8Array Uint8ClampedArray Int16Array Uint16Array Int32Array Uint32Array Float32Array Float64Array".split(" "), c = 0; c < b.length; c++) {
            var d = t[b[c]];
            typeof d === "function" && typeof d.prototype[a] != "function" && r(d.prototype, a, {
                configurable: !0,
                writable: !0,
                value: function() {
                    return ea(aa(this))
                }
            })
        }
        return a
    });
    function ea(a) {
        a = {
            next: a
        };
        a[Symbol.iterator] = function() {
            return this
        }
        ;
        return a
    }
    function v(a) {
        var b = typeof Symbol != "undefined" && Symbol.iterator && a[Symbol.iterator];
        if (b)
            return b.call(a);
        if (typeof a.length == "number")
            return {
                next: aa(a)
            };
        throw Error(String(a) + " is not an iterable or ArrayLike");
    }
    var fa = typeof Object.create == "function" ? Object.create : function(a) {
        function b() {}
        b.prototype = a;
        return new b
    }
    , w;
    if (typeof Object.setPrototypeOf == "function")
        w = Object.setPrototypeOf;
    else {
        var x;
        a: {
            var ha = {
                a: !0
            }
              , ia = {};
            try {
                ia.__proto__ = ha;
                x = ia.a;
                break a
            } catch (a) {}
            x = !1
        }
        w = x ? function(a, b) {
            a.__proto__ = b;
            if (a.__proto__ !== b)
                throw new TypeError(a + " is not extensible");
            return a
        }
        : null
    }
    var ja = w;
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
    y.prototype.s = function(a) {
        this.m = a
    }
    ;
    function B(a, b) {
        a.i = {
            O: b,
            P: !0
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
    function ka(a) {
        this.g = new y;
        this.h = a
    }
    function la(a, b) {
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
            var g = b.call(a.g.h, c);
            if (!(g instanceof Object))
                throw new TypeError("Iterator result " + g + " is not an object");
            if (!g.done)
                return a.g.j = !1,
                g;
            var k = g.value
        } catch (e) {
            return a.g.h = null,
            B(a.g, e),
            E(a)
        }
        a.g.h = null;
        d.call(a.g, k);
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
            if (b.P)
                throw b.O;
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
    function ma(a) {
        this.next = function(b) {
            z(a.g);
            a.g.h ? b = D(a, a.g.h.next, b, a.g.s) : (a.g.s(b),
            b = E(a));
            return b
        }
        ;
        this.throw = function(b) {
            z(a.g);
            a.g.h ? b = D(a, a.g.h["throw"], b, a.g.s) : (B(a.g, b),
            b = E(a));
            return b
        }
        ;
        this.return = function(b) {
            return la(a, b)
        }
        ;
        this[Symbol.iterator] = function() {
            return this
        }
    }
    function na(a) {
        function b(d) {
            return a.next(d)
        }
        function c(d) {
            return a.throw(d)
        }
        return new Promise(function(d, g) {
            function k(e) {
                e.done ? d(e.value) : Promise.resolve(e.value).then(b, c).then(k, g)
            }
            k(a.next())
        }
        )
    }
    function oa(a) {
        return na(new ma(new ka(a)))
    }
    u("Promise", function(a) {
        function b(e) {
            this.h = 0;
            this.i = void 0;
            this.g = [];
            this.s = !1;
            var f = this.j();
            try {
                e(f.resolve, f.reject)
            } catch (h) {
                f.reject(h)
            }
        }
        function c() {
            this.g = null
        }
        function d(e) {
            return e instanceof b ? e : new b(function(f) {
                f(e)
            }
            )
        }
        if (a)
            return a;
        c.prototype.h = function(e) {
            if (this.g == null) {
                this.g = [];
                var f = this;
                this.i(function() {
                    f.l()
                })
            }
            this.g.push(e)
        }
        ;
        var g = t.setTimeout;
        c.prototype.i = function(e) {
            g(e, 0)
        }
        ;
        c.prototype.l = function() {
            for (; this.g && this.g.length; ) {
                var e = this.g;
                this.g = [];
                for (var f = 0; f < e.length; ++f) {
                    var h = e[f];
                    e[f] = null;
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
        c.prototype.j = function(e) {
            this.i(function() {
                throw e;
            })
        }
        ;
        b.prototype.j = function() {
            function e(l) {
                return function(m) {
                    h || (h = !0,
                    l.call(f, m))
                }
            }
            var f = this
              , h = !1;
            return {
                resolve: e(this.J),
                reject: e(this.l)
            }
        }
        ;
        b.prototype.J = function(e) {
            if (e === this)
                this.l(new TypeError("A Promise cannot resolve to itself"));
            else if (e instanceof b)
                this.L(e);
            else {
                a: switch (typeof e) {
                case "object":
                    var f = e != null;
                    break a;
                case "function":
                    f = !0;
                    break a;
                default:
                    f = !1
                }
                f ? this.I(e) : this.m(e)
            }
        }
        ;
        b.prototype.I = function(e) {
            var f = void 0;
            try {
                f = e.then
            } catch (h) {
                this.l(h);
                return
            }
            typeof f == "function" ? this.M(f, e) : this.m(e)
        }
        ;
        b.prototype.l = function(e) {
            this.A(2, e)
        }
        ;
        b.prototype.m = function(e) {
            this.A(1, e)
        }
        ;
        b.prototype.A = function(e, f) {
            if (this.h != 0)
                throw Error("Cannot settle(" + e + ", " + f + "): Promise already settled in state" + this.h);
            this.h = e;
            this.i = f;
            this.h === 2 && this.K();
            this.S()
        }
        ;
        b.prototype.K = function() {
            var e = this;
            g(function() {
                if (e.T()) {
                    var f = t.console;
                    typeof f !== "undefined" && f.error(e.i)
                }
            }, 1)
        }
        ;
        b.prototype.T = function() {
            if (this.s)
                return !1;
            var e = t.CustomEvent
              , f = t.Event
              , h = t.dispatchEvent;
            if (typeof h === "undefined")
                return !0;
            typeof e === "function" ? e = new e("unhandledrejection",{
                cancelable: !0
            }) : typeof f === "function" ? e = new f("unhandledrejection",{
                cancelable: !0
            }) : (e = t.document.createEvent("CustomEvent"),
            e.initCustomEvent("unhandledrejection", !1, !0, e));
            e.promise = this;
            e.reason = this.i;
            return h(e)
        }
        ;
        b.prototype.S = function() {
            if (this.g != null) {
                for (var e = 0; e < this.g.length; ++e)
                    k.h(this.g[e]);
                this.g = null
            }
        }
        ;
        var k = new c;
        b.prototype.L = function(e) {
            var f = this.j();
            e.B(f.resolve, f.reject)
        }
        ;
        b.prototype.M = function(e, f) {
            var h = this.j();
            try {
                e.call(f, h.resolve, h.reject)
            } catch (l) {
                h.reject(l)
            }
        }
        ;
        b.prototype.then = function(e, f) {
            function h(p, A) {
                return typeof p == "function" ? function(ba) {
                    try {
                        l(p(ba))
                    } catch (ca) {
                        m(ca)
                    }
                }
                : A
            }
            var l, m, n = new b(function(p, A) {
                l = p;
                m = A
            }
            );
            this.B(h(e, l), h(f, m));
            return n
        }
        ;
        b.prototype.catch = function(e) {
            return this.then(void 0, e)
        }
        ;
        b.prototype.B = function(e, f) {
            function h() {
                switch (l.h) {
                case 1:
                    e(l.i);
                    break;
                case 2:
                    f(l.i);
                    break;
                default:
                    throw Error("Unexpected state: " + l.h);
                }
            }
            var l = this;
            this.g == null ? k.h(h) : this.g.push(h);
            this.s = !0
        }
        ;
        b.resolve = d;
        b.reject = function(e) {
            return new b(function(f, h) {
                h(e)
            }
            )
        }
        ;
        b.race = function(e) {
            return new b(function(f, h) {
                for (var l = v(e), m = l.next(); !m.done; m = l.next())
                    d(m.value).B(f, h)
            }
            )
        }
        ;
        b.all = function(e) {
            var f = v(e)
              , h = f.next();
            return h.done ? d([]) : new b(function(l, m) {
                function n(ba) {
                    return function(ca) {
                        p[ba] = ca;
                        A--;
                        A == 0 && l(p)
                    }
                }
                var p = []
                  , A = 0;
                do
                    p.push(void 0),
                    A++,
                    d(h.value).B(n(p.length - 1), m),
                    h = f.next();
                while (!h.done)
            }
            )
        }
        ;
        return b
    });
    function F(a, b) {
        return Object.prototype.hasOwnProperty.call(a, b)
    }
    var pa = typeof Object.assign == "function" ? Object.assign : function(a, b) {
        for (var c = 1; c < arguments.length; c++) {
            var d = arguments[c];
            if (d)
                for (var g in d)
                    F(d, g) && (a[g] = d[g])
        }
        return a
    }
    ;
    u("Object.assign", function(a) {
        return a || pa
    });
    u("Symbol.dispose", function(a) {
        return a ? a : Symbol("Symbol.dispose")
    });
    u("WeakMap", function(a) {
        function b(h) {
            this.g = (f += Math.random() + 1).toString();
            if (h) {
                h = v(h);
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
        function g(h) {
            if (!F(h, e)) {
                var l = new c;
                r(h, e, {
                    value: l
                })
            }
        }
        function k(h) {
            var l = Object[h];
            l && (Object[h] = function(m) {
                if (m instanceof c)
                    return m;
                Object.isExtensible(m) && g(m);
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
            } catch (n) {
                return !1
            }
        }())
            return a;
        var e = "$jscomp_hidden_" + Math.random();
        k("freeze");
        k("preventExtensions");
        k("seal");
        var f = 0;
        b.prototype.set = function(h, l) {
            if (!d(h))
                throw Error("Invalid WeakMap key");
            g(h);
            if (!F(h, e))
                throw Error("WeakMap key fail: " + h);
            h[e][this.g] = l;
            return this
        }
        ;
        b.prototype.get = function(h) {
            return d(h) && F(h, e) ? h[e][this.g] : void 0
        }
        ;
        b.prototype.has = function(h) {
            return d(h) && F(h, e) && F(h[e], this.g)
        }
        ;
        b.prototype.delete = function(h) {
            return d(h) && F(h, e) && F(h[e], this.g) ? delete h[e][this.g] : !1
        }
        ;
        return b
    });
    u("Map", function(a) {
        function b() {
            var f = {};
            return f.previous = f.next = f.head = f
        }
        function c(f, h) {
            var l = f[1];
            return ea(function() {
                if (l) {
                    for (; l.head != f[1]; )
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
        function d(f, h) {
            var l = h && typeof h;
            l == "object" || l == "function" ? k.has(h) ? l = k.get(h) : (l = "" + ++e,
            k.set(h, l)) : l = "p_" + h;
            var m = f[0][l];
            if (m && F(f[0], l))
                for (f = 0; f < m.length; f++) {
                    var n = m[f];
                    if (h !== h && n.key !== n.key || h === n.key)
                        return {
                            id: l,
                            list: m,
                            index: f,
                            o: n
                        }
                }
            return {
                id: l,
                list: m,
                index: -1,
                o: void 0
            }
        }
        function g(f) {
            this[0] = {};
            this[1] = b();
            this.size = 0;
            if (f) {
                f = v(f);
                for (var h; !(h = f.next()).done; )
                    h = h.value,
                    this.set(h[0], h[1])
            }
        }
        if (function() {
            if (!a || typeof a != "function" || !a.prototype.entries || typeof Object.seal != "function")
                return !1;
            try {
                var f = Object.seal({
                    x: 4
                })
                  , h = new a(v([[f, "s"]]));
                if (h.get(f) != "s" || h.size != 1 || h.get({
                    x: 4
                }) || h.set({
                    x: 4
                }, "t") != h || h.size != 2)
                    return !1;
                var l = h.entries()
                  , m = l.next();
                if (m.done || m.value[0] != f || m.value[1] != "s")
                    return !1;
                m = l.next();
                return m.done || m.value[0].x != 4 || m.value[1] != "t" || !l.next().done ? !1 : !0
            } catch (n) {
                return !1
            }
        }())
            return a;
        var k = new WeakMap;
        g.prototype.set = function(f, h) {
            f = f === 0 ? 0 : f;
            var l = d(this, f);
            l.list || (l.list = this[0][l.id] = []);
            l.o ? l.o.value = h : (l.o = {
                next: this[1],
                previous: this[1].previous,
                head: this[1],
                key: f,
                value: h
            },
            l.list.push(l.o),
            this[1].previous.next = l.o,
            this[1].previous = l.o,
            this.size++);
            return this
        }
        ;
        g.prototype.delete = function(f) {
            f = d(this, f);
            return f.o && f.list ? (f.list.splice(f.index, 1),
            f.list.length || delete this[0][f.id],
            f.o.previous.next = f.o.next,
            f.o.next.previous = f.o.previous,
            f.o.head = null,
            this.size--,
            !0) : !1
        }
        ;
        g.prototype.clear = function() {
            this[0] = {};
            this[1] = this[1].previous = b();
            this.size = 0
        }
        ;
        g.prototype.has = function(f) {
            return !!d(this, f).o
        }
        ;
        g.prototype.get = function(f) {
            return (f = d(this, f).o) && f.value
        }
        ;
        g.prototype.entries = function() {
            return c(this, function(f) {
                return [f.key, f.value]
            })
        }
        ;
        g.prototype.keys = function() {
            return c(this, function(f) {
                return f.key
            })
        }
        ;
        g.prototype.values = function() {
            return c(this, function(f) {
                return f.value
            })
        }
        ;
        g.prototype.forEach = function(f, h) {
            for (var l = this.entries(), m; !(m = l.next()).done; )
                m = m.value,
                f.call(h, m[1], m[0], this)
        }
        ;
        g.prototype[Symbol.iterator] = g.prototype.entries;
        var e = 0;
        return g
    });
    u("Set", function(a) {
        function b(c) {
            this.g = new Map;
            if (c) {
                c = v(c);
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
                  , d = new a(v([c]));
                if (!d.has(c) || d.size != 1 || d.add(c) != d || d.size != 1 || d.add({
                    x: 4
                }) != d || d.size != 2)
                    return !1;
                var g = d.entries()
                  , k = g.next();
                if (k.done || k.value[0] != c || k.value[1] != c)
                    return !1;
                k = g.next();
                return k.done || k.value[0] == c || k.value[0].x != 4 || k.value[1] != k.value[0] ? !1 : g.next().done
            } catch (e) {
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
            var g = this;
            this.g.forEach(function(k) {
                return c.call(d, k, k, g)
            })
        }
        ;
        return b
    });
    u("Array.prototype.find", function(a) {
        return a ? a : function(b, c) {
            a: {
                var d = this;
                d instanceof String && (d = String(d));
                for (var g = d.length, k = 0; k < g; k++) {
                    var e = d[k];
                    if (b.call(c, e, k, d)) {
                        b = e;
                        break a
                    }
                }
                b = void 0
            }
            return b
        }
    });
    u("Array.from", function(a) {
        return a ? a : function(b, c, d) {
            c = c != null ? c : function(f) {
                return f
            }
            ;
            var g = []
              , k = typeof Symbol != "undefined" && Symbol.iterator && b[Symbol.iterator];
            if (typeof k == "function") {
                b = k.call(b);
                for (var e = 0; !(k = b.next()).done; )
                    g.push(c.call(d, k.value, e++))
            } else
                for (k = b.length,
                e = 0; e < k; e++)
                    g.push(c.call(d, b[e], e));
            return g
        }
    });
    u("Object.is", function(a) {
        return a ? a : function(b, c) {
            return b === c ? b !== 0 || 1 / b === 1 / c : b !== b && c !== c
        }
    });
    u("Array.prototype.includes", function(a) {
        return a ? a : function(b, c) {
            var d = this;
            d instanceof String && (d = String(d));
            var g = d.length;
            c = c || 0;
            for (c < 0 && (c = Math.max(c + g, 0)); c < g; c++) {
                var k = d[c];
                if (k === b || Object.is(k, b))
                    return !0
            }
            return !1
        }
    });
    u("String.prototype.includes", function(a) {
        return a ? a : function(b, c) {
            if (this == null)
                throw new TypeError("The 'this' value for String.prototype.includes must not be null or undefined");
            if (b instanceof RegExp)
                throw new TypeError("First argument to String.prototype.includes must not be a regular expression");
            return (this + "").indexOf(b, c || 0) !== -1
        }
    });
    /*

 Copyright The Closure Library Authors.
 SPDX-License-Identifier: Apache-2.0
*/
    var G = this || self;
    function H(a) {
        var b = typeof a;
        return b == "object" && a != null || b == "function"
    }
    function qa(a) {
        return Object.prototype.hasOwnProperty.call(a, ra) && a[ra] || (a[ra] = ++sa)
    }
    var ra = "closure_uid_" + (Math.random() * 1E9 >>> 0)
      , sa = 0;
    function I(a, b) {
        a = a.split(".");
        var c = G;
        a[0]in c || typeof c.execScript == "undefined" || c.execScript("var " + a[0]);
        for (var d; a.length && (d = a.shift()); )
            a.length || b === void 0 ? c[d] && c[d] !== Object.prototype[d] ? c = c[d] : c = c[d] = {} : c[d] = b
    }
    function ta(a, b) {
        function c() {}
        c.prototype = b.prototype;
        a.H = b.prototype;
        a.prototype = new c;
        a.prototype.constructor = a;
        a.Y = function(d, g, k) {
            for (var e = Array(arguments.length - 2), f = 2; f < arguments.length; f++)
                e[f - 2] = arguments[f];
            return b.prototype[g].apply(d, e)
        }
    }
    ;var ua = Array.prototype.indexOf ? function(a, b) {
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
      , va = Array.prototype.forEach ? function(a, b, c) {
        Array.prototype.forEach.call(a, b, c)
    }
    : function(a, b, c) {
        for (var d = a.length, g = typeof a === "string" ? a.split("") : a, k = 0; k < d; k++)
            k in g && b.call(c, g[k], k, a)
    }
    ;
    function wa(a, b) {
        b = ua(a, b);
        b >= 0 && Array.prototype.splice.call(a, b, 1)
    }
    function xa(a) {
        return Array.prototype.concat.apply([], arguments)
    }
    function ya(a) {
        var b = a.length;
        if (b > 0) {
            for (var c = Array(b), d = 0; d < b; d++)
                c[d] = a[d];
            return c
        }
        return []
    }
    ;function za(a, b) {
        this.i = a;
        this.j = b;
        this.h = 0;
        this.g = null
    }
    za.prototype.get = function() {
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
    function Aa(a) {
        G.setTimeout(function() {
            throw a;
        }, 0)
    }
    ;function Ba() {
        this.h = this.g = null
    }
    Ba.prototype.add = function(a, b) {
        var c = Ca.get();
        c.set(a, b);
        this.h ? this.h.next = c : this.g = c;
        this.h = c
    }
    ;
    Ba.prototype.remove = function() {
        var a = null;
        this.g && (a = this.g,
        this.g = this.g.next,
        this.g || (this.h = null),
        a.next = null);
        return a
    }
    ;
    var Ca = new za(function() {
        return new Da
    }
    ,function(a) {
        return a.reset()
    }
    );
    function Da() {
        this.next = this.scope = this.g = null
    }
    Da.prototype.set = function(a, b) {
        this.g = a;
        this.scope = b;
        this.next = null
    }
    ;
    Da.prototype.reset = function() {
        this.next = this.scope = this.g = null
    }
    ;
    var Ea, Fa = !1, Ga = new Ba;
    function Ha(a) {
        Ea || Ia();
        Fa || (Ea(),
        Fa = !0);
        Ga.add(a, void 0)
    }
    function Ia() {
        var a = Promise.resolve(void 0);
        Ea = function() {
            a.then(Ja)
        }
    }
    function Ja() {
        for (var a; a = Ga.remove(); ) {
            try {
                a.g.call(a.scope)
            } catch (c) {
                Aa(c)
            }
            var b = Ca;
            b.j(a);
            b.h < 100 && (b.h++,
            a.next = b.g,
            b.g = a)
        }
        Fa = !1
    }
    ;function J() {
        this.i = this.i;
        this.j = this.j
    }
    J.prototype.i = !1;
    J.prototype.dispose = function() {
        this.i || (this.i = !0,
        this.C())
    }
    ;
    J.prototype[Symbol.dispose] = function() {
        this.dispose()
    }
    ;
    J.prototype.addOnDisposeCallback = function(a, b) {
        this.i ? b !== void 0 ? a.call(b) : a() : (this.j || (this.j = []),
        b && (a = a.bind(b)),
        this.j.push(a))
    }
    ;
    J.prototype.C = function() {
        if (this.j)
            for (; this.j.length; )
                this.j.shift()()
    }
    ;
    var Ka = /&/g
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
    function K(a) {
        this.g = a
    }
    K.prototype.toString = function() {
        return this.g
    }
    ;
    var Ra = new K("about:invalid#zClosurez");
    function Sa(a) {
        this.R = a
    }
    function L(a) {
        return new Sa(function(b) {
            return b.substr(0, a.length + 1).toLowerCase() === a + ":"
        }
        )
    }
    var Ta = [L("data"), L("http"), L("https"), L("mailto"), L("ftp"), new Sa(function(a) {
        return /^[^:]*([/?#]|$)/.test(a)
    }
    )];
    function Ua(a) {
        var b = b === void 0 ? Ta : b;
        a: if (b = b === void 0 ? Ta : b,
        !(a instanceof K)) {
            for (var c = 0; c < b.length; ++c) {
                var d = b[c];
                if (d instanceof Sa && d.R(a)) {
                    a = new K(a);
                    break a
                }
            }
            a = void 0
        }
        return a || Ra
    }
    var Va = /^\s*(?!javascript:)(?:[\w+.-]+:|[^:/?#]*(?:[/?#]|$))/i;
    var Wa = {
        X: 0,
        V: 1,
        W: 2,
        0: "FORMATTED_HTML_CONTENT",
        1: "EMBEDDED_INTERNAL_CONTENT",
        2: "EMBEDDED_TRUSTED_EXTERNAL_CONTENT"
    };
    function M(a, b) {
        b = Error.call(this, a + " cannot be used with intent " + Wa[b]);
        this.message = b.message;
        "stack"in b && (this.stack = b.stack);
        this.type = a;
        this.name = "TypeCannotBeUsedWithIntentError"
    }
    var N = Error;
    M.prototype = fa(N.prototype);
    M.prototype.constructor = M;
    if (ja)
        ja(M, N);
    else
        for (var O in N)
            if (O != "prototype")
                if (Object.defineProperties) {
                    var Xa = Object.getOwnPropertyDescriptor(N, O);
                    Xa && Object.defineProperty(M, O, Xa)
                } else
                    M[O] = N[O];
    M.H = N.prototype;
    function Ya(a, b) {
        a.removeAttribute("srcdoc");
        var c = "allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox allow-storage-access-by-user-activation".split(" ");
        a.setAttribute("sandbox", "");
        for (var d = 0; d < c.length; d++)
            a.sandbox.supports && !a.sandbox.supports(c[d]) || a.sandbox.add(c[d]);
        if (b instanceof K)
            if (b instanceof K)
                b = b.g;
            else
                throw Error("");
        else
            b = Va.test(b) ? b : void 0;
        b !== void 0 && (a.src = b)
    }
    ;function Za(a) {
        Qa.test(a) && (a.indexOf("&") != -1 && (a = a.replace(Ka, "&amp;")),
        a.indexOf("<") != -1 && (a = a.replace(La, "&lt;")),
        a.indexOf(">") != -1 && (a = a.replace(Ma, "&gt;")),
        a.indexOf('"') != -1 && (a = a.replace(Na, "&quot;")),
        a.indexOf("'") != -1 && (a = a.replace(Oa, "&#39;")),
        a.indexOf("\x00") != -1 && (a = a.replace(Pa, "&#0;")));
        return a
    }
    ;var $a, P;
    a: {
        for (var ab = ["CLOSURE_FLAGS"], Q = G, bb = 0; bb < ab.length; bb++)
            if (Q = Q[ab[bb]],
            Q == null) {
                P = null;
                break a
            }
        P = Q
    }
    var cb = P && P[610401301];
    $a = cb != null ? cb : !1;
    function R() {
        var a = G.navigator;
        return a && (a = a.userAgent) ? a : ""
    }
    var S, db = G.navigator;
    S = db ? db.userAgentData || null : null;
    function eb() {
        return $a ? S ? S.brands.some(function(a) {
            return (a = a.brand) && a.indexOf("Chromium") != -1
        }) : !1 : !1
    }
    ;function fb() {
        return $a ? !!S && S.brands.length > 0 : !1
    }
    function gb(a) {
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
    function hb() {
        for (var a = R(), b = RegExp("([A-Z][\\w ]+)/([^\\s]+)\\s*(?:\\((.*?)\\))?", "g"), c = [], d; d = b.exec(a); )
            c.push([d[1], d[2], d[3] || void 0]);
        a = gb(c);
        return (fb() ? eb() : (R().indexOf("Chrome") != -1 || R().indexOf("CriOS") != -1) && (fb() || R().indexOf("Edge") == -1) || R().indexOf("Silk") != -1) ? a(["Chrome", "CriOS", "HeadlessChrome"]) : ""
    }
    function ib() {
        if (fb()) {
            var a = S.brands.find(function(b) {
                return b.brand === "Chromium"
            });
            if (!a || !a.version)
                return NaN;
            a = a.version.split(".")
        } else {
            a = hb();
            if (a === "")
                return NaN;
            a = a.split(".")
        }
        return a.length === 0 ? NaN : Number(a[0])
    }
    ;function T(a) {
        J.call(this);
        this.s = 1;
        this.l = [];
        this.m = 0;
        this.g = [];
        this.h = {};
        this.A = !!a
    }
    ta(T, J);
    q = T.prototype;
    q.subscribe = function(a, b, c) {
        var d = this.h[a];
        d || (d = this.h[a] = []);
        var g = this.s;
        this.g[g] = a;
        this.g[g + 1] = b;
        this.g[g + 2] = c;
        this.s = g + 3;
        d.push(g);
        return g
    }
    ;
    function jb(a, b, c) {
        var d = U;
        if (a = d.h[a]) {
            var g = d.g;
            (a = a.find(function(k) {
                return g[k + 1] == b && g[k + 2] == c
            })) && d.D(a)
        }
    }
    q.D = function(a) {
        var b = this.g[a];
        if (b) {
            var c = this.h[b];
            this.m != 0 ? (this.l.push(a),
            this.g[a + 1] = function() {}
            ) : (c && wa(c, a),
            delete this.g[a],
            delete this.g[a + 1],
            delete this.g[a + 2])
        }
        return !!b
    }
    ;
    q.G = function(a, b) {
        var c = this.h[a];
        if (c) {
            var d = Array(arguments.length - 1), g = arguments.length, k;
            for (k = 1; k < g; k++)
                d[k - 1] = arguments[k];
            if (this.A)
                for (k = 0; k < c.length; k++)
                    g = c[k],
                    kb(this.g[g + 1], this.g[g + 2], d);
            else {
                this.m++;
                try {
                    for (k = 0,
                    g = c.length; k < g && !this.i; k++) {
                        var e = c[k];
                        this.g[e + 1].apply(this.g[e + 2], d)
                    }
                } finally {
                    if (this.m--,
                    this.l.length > 0 && this.m == 0)
                        for (; c = this.l.pop(); )
                            this.D(c)
                }
            }
            return k != 0
        }
        return !1
    }
    ;
    function kb(a, b, c) {
        Ha(function() {
            a.apply(b, c)
        })
    }
    q.clear = function(a) {
        if (a) {
            var b = this.h[a];
            b && (b.forEach(this.D, this),
            delete this.h[a])
        } else
            this.g.length = 0,
            this.h = {}
    }
    ;
    q.C = function() {
        T.H.C.call(this);
        this.clear();
        this.l.length = 0
    }
    ;
    var lb = RegExp("^(?:([^:/?#.]+):)?(?://(?:([^\\\\/?#]*)@)?([^\\\\/?#]*?)(?::([0-9]+))?(?=[\\\\/?#]|$))?([^?#]+)?(?:\\?([^#]*))?(?:#([\\s\\S]*))?$");
    function mb(a) {
        var b = a.match(lb);
        a = b[1];
        var c = b[2]
          , d = b[3];
        b = b[4];
        var g = "";
        a && (g += a + ":");
        d && (g += "//",
        c && (g += c + "@"),
        g += d,
        b && (g += ":" + b));
        return g
    }
    function nb(a, b, c) {
        if (Array.isArray(b))
            for (var d = 0; d < b.length; d++)
                nb(a, String(b[d]), c);
        else
            b != null && c.push(a + (b === "" ? "" : "=" + encodeURIComponent(String(b))))
    }
    function ob(a) {
        var b = [], c;
        for (c in a)
            nb(c, a[c], b);
        return b.join("&")
    }
    var pb = /#|$/;
    var qb = ["https://www.google.com"];
    function rb() {
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
    function sb() {
        return oa(function(a) {
            var b = a.return;
            var c = ib() >= 119;
            return b.call(a, c && !!navigator.permissions && !!navigator.permissions.query && "requestStorageAccessFor"in document)
        })
    }
    function tb() {
        var a = new rb
          , b = ["https://www.youtube.com"];
        b = b === void 0 ? qb : b;
        oa(function(c) {
            switch (c.g) {
            case 1:
                return C(c, sb(), 2);
            case 2:
                if (!c.m) {
                    c.g = 3;
                    break
                }
                return C(c, Promise.all(b.map(function(d) {
                    var g;
                    return oa(function(k) {
                        if (k.g == 1)
                            return k.l = 2,
                            C(k, navigator.permissions.query({
                                name: "top-level-storage-access",
                                requestedOrigin: d
                            }), 4);
                        k.g != 2 ? (g = k.m,
                        g.state === "prompt" && a.g.push(d),
                        k.g = 0,
                        k.l = 0) : (k.l = 0,
                        k.i = null,
                        k.g = 0)
                    })
                })), 4);
            case 4:
                a.g.length > 0 && window.addEventListener("click", a.h);
            case 3:
                return c.return()
            }
        })
    }
    ;var V = {}
      , ub = []
      , U = new T
      , vb = {};
    function wb() {
        for (var a = v(ub), b = a.next(); !b.done; b = a.next())
            b = b.value,
            b()
    }
    function xb(a, b) {
        return a.tagName.toLowerCase().substring(0, 3) === "yt:" ? a.getAttribute(b) : a.dataset ? a.dataset[b] : a.getAttribute("data-" + b)
    }
    function yb(a) {
        U.G.apply(U, arguments)
    }
    ;function zb(a) {
        return (a.search("cue") === 0 || a.search("load") === 0) && a !== "loadModule"
    }
    function Ab(a) {
        return a.search("get") === 0 || a.search("is") === 0
    }
    ;var W = window;
    function X(a, b) {
        this.v = {};
        this.playerInfo = {};
        this.videoTitle = "";
        this.j = this.g = null;
        this.h = 0;
        this.m = !1;
        this.l = [];
        this.i = null;
        this.A = {};
        this.options = null;
        if (!a)
            throw Error("YouTube player element ID required.");
        this.id = qa(this);
        b = Object.assign({
            title: "video player",
            videoId: "",
            width: 640,
            height: 360
        }, b || {});
        var c = document;
        if (a = typeof a === "string" ? c.getElementById(a) : a) {
            W.yt_embedsEnableRsaforFromIframeApi && tb();
            c = a.tagName.toLowerCase() === "iframe";
            b.host || (b.host = c ? mb(a.src) : "https://www.youtube.com");
            this.options = b || {};
            b = [this.options, window.YTConfig || {}];
            for (var d = 0; d < b.length; d++)
                b[d].host && (b[d].host = b[d].host.toString().replace("http://", "https://"));
            c || (b = Bb(this, a),
            this.j = a,
            (c = a.parentNode) && c.replaceChild(b, a),
            a = b);
            this.g = a;
            this.g.id || (this.g.id = "widget" + qa(this.g));
            V[this.g.id] = this;
            if (window.postMessage) {
                this.i = new T;
                Cb(this);
                a = Y(this, "events");
                for (var g in a)
                    a.hasOwnProperty(g) && this.addEventListener(g, a[g]);
                for (var k in vb)
                    vb.hasOwnProperty(k) && Db(this, k)
            }
        }
    }
    q = X.prototype;
    q.setSize = function(a, b) {
        this.g.width = a.toString();
        this.g.height = b.toString();
        return this
    }
    ;
    q.getIframe = function() {
        return this.g
    }
    ;
    q.addEventListener = function(a, b) {
        var c = b;
        typeof b === "string" && (c = function() {
            window[b].apply(window, arguments)
        }
        );
        if (!c)
            return this;
        this.i.subscribe(a, c);
        Eb(this, a);
        return this
    }
    ;
    function Db(a, b) {
        b = b.split(".");
        if (b.length === 2) {
            var c = b[1];
            "player" === b[0] && Eb(a, c)
        }
    }
    q.destroy = function() {
        this.g && this.g.id && (V[this.g.id] = null);
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
        this.g && this.s && this.g.removeEventListener("load", this.s);
        this.j = this.g = null
    }
    ;
    function Fb(a, b, c) {
        c = c || [];
        c = Array.prototype.slice.call(c);
        b = {
            event: "command",
            func: b,
            args: c
        };
        a.m ? a.sendMessage(b) : a.l.push(b)
    }
    function Bb(a, b) {
        var c = document.createElement("iframe");
        b = b.attributes;
        for (var d = 0, g = b.length; d < g; d++) {
            var k = b[d].value;
            k != null && k !== "" && k !== "null" && c.setAttribute(b[d].name, k)
        }
        c.setAttribute("frameBorder", "0");
        c.setAttribute("allowfullscreen", "");
        c.setAttribute("allow", "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share");
        c.setAttribute("referrerPolicy", "strict-origin-when-cross-origin");
        c.setAttribute("title", "YouTube " + Y(a, "title"));
        (b = Y(a, "width")) && c.setAttribute("width", b.toString());
        (b = Y(a, "height")) && c.setAttribute("height", b.toString());
        var e = Gb(a);
        e.enablejsapi = window.postMessage ? 1 : 0;
        window.location.host && (e.origin = window.location.protocol + "//" + window.location.host);
        e.widgetid = a.id;
        window.location.href && va(["debugjs", "debugcss"], function(f) {
            var h = window.location.href;
            var l = h.search(pb);
            b: {
                var m = 0;
                for (var n = f.length; (m = h.indexOf(f, m)) >= 0 && m < l; ) {
                    var p = h.charCodeAt(m - 1);
                    if (p == 38 || p == 63)
                        if (p = h.charCodeAt(m + n),
                        !p || p == 61 || p == 38 || p == 35)
                            break b;
                    m += n + 1
                }
                m = -1
            }
            if (m < 0)
                h = null;
            else {
                n = h.indexOf("&", m);
                if (n < 0 || n > l)
                    n = l;
                m += f.length + 1;
                h = decodeURIComponent(h.slice(m, n !== -1 ? n : 0).replace(/\+/g, " "))
            }
            h !== null && (e[f] = h)
        });
        W.yt_embedsEnableIframeApiSendFullEmbedUrl && (window.location.href && (e.forigin = window.location.href),
        b = window.location.ancestorOrigins,
        e.aoriginsup = b === void 0 ? 0 : 1,
        b && b.length > 0 && (e.aorigins = Array.from(b).join(",")),
        window.document.referrer && (e.gporigin = window.document.referrer));
        a = "" + Y(a, "host") + Hb(a) + "?" + ob(e);
        W.yt_embedsEnableIframeSrcWithIntent ? (Ya(c, Ua(a)),
        c.sandbox.add("allow-presentation", "allow-top-navigation")) : c.src = a;
        return c
    }
    q.F = function() {
        this.g && this.g.contentWindow ? this.sendMessage({
            event: "listening"
        }) : clearInterval(this.h)
    }
    ;
    function Cb(a) {
        Ib(a, a.id, String(Y(a, "host")));
        a.h = setInterval(a.F.bind(a), 250);
        a.g && (a.s = function() {
            clearInterval(a.h);
            a.h = setInterval(a.F.bind(a), 250)
        }
        ,
        a.g.addEventListener("load", a.s))
    }
    function Eb(a, b) {
        a.A[b] || (a.A[b] = !0,
        Fb(a, "addEventListener", [b]))
    }
    q.sendMessage = function(a) {
        a.id = this.id;
        a.channel = "widget";
        a = JSON.stringify(a);
        var b = mb(this.g.src || "").replace("http:", "https:");
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
    function Hb(a) {
        if (W.yt_embedsEnableIframeApiVideoIdValidation) {
            a = String(Y(a, "videoId"));
            if (a.includes("../"))
                throw Error("Invalid video id");
            return "/embed/" + a
        }
        return "/embed/" + String(Y(a, "videoId"))
    }
    function Gb(a) {
        var b = Y(a, "playerVars");
        if (b) {
            var c = {}, d;
            for (d in b)
                c[d] = b[d];
            b = c
        } else
            b = {};
        window !== window.top && document.referrer && (b.widget_referrer = document.referrer.substring(0, 256));
        if (a = Y(a, "embedConfig")) {
            if (H(a))
                try {
                    a = JSON.stringify(a)
                } catch (g) {
                    console.error("Invalid embed config JSON", g)
                }
            b.embed_config = a
        }
        return b
    }
    function Jb(a, b) {
        if (H(b)) {
            for (var c in b)
                b.hasOwnProperty(c) && (a.playerInfo[c] = b[c]);
            a.playerInfo.hasOwnProperty("videoData") && (b = a.playerInfo.videoData,
            b.hasOwnProperty("title") && b.title ? (b = b.title,
            b !== a.videoTitle && (a.videoTitle = b,
            a.g.setAttribute("title", b))) : (a.videoTitle = "",
            a.g.setAttribute("title", "YouTube " + Y(a, "title"))))
        }
    }
    function Kb(a, b) {
        b = v(b);
        for (var c = b.next(), d = {}; !c.done; d = {
            u: void 0
        },
        c = b.next())
            d.u = c.value,
            a[d.u] || (d.u === "getCurrentTime" ? a[d.u] = function() {
                var g = this.playerInfo.currentTime;
                if (this.playerInfo.playerState === 1) {
                    var k = (Date.now() / 1E3 - this.playerInfo.currentTimeLastUpdated_) * this.playerInfo.playbackRate;
                    k > 0 && (g += Math.min(k, 1))
                }
                return g
            }
            : zb(d.u) ? a[d.u] = function(g) {
                return function() {
                    this.playerInfo = {};
                    this.v = {};
                    Fb(this, g.u, arguments);
                    return this
                }
            }(d) : Ab(d.u) ? a[d.u] = function(g) {
                return function() {
                    var k = g.u
                      , e = 0;
                    k.search("get") === 0 ? e = 3 : k.search("is") === 0 && (e = 2);
                    return this.playerInfo[k.charAt(e).toLowerCase() + k.substring(e + 1)]
                }
            }(d) : a[d.u] = function(g) {
                return function() {
                    Fb(this, g.u, arguments);
                    return this
                }
            }(d))
    }
    q.getVideoEmbedCode = function() {
        var a = "" + Y(this, "host") + Hb(this)
          , b = Number(Y(this, "width"))
          , c = Number(Y(this, "height"));
        if (isNaN(b) || isNaN(c))
            throw Error("Invalid width or height property");
        b = Math.floor(b);
        c = Math.floor(c);
        var d = this.videoTitle;
        a = Za(a);
        d = Za(d != null ? d : "YouTube video player");
        return '<iframe width="' + b + '" height="' + c + '" src="' + a + '" title="' + (d + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>')
    }
    ;
    q.getOptions = function(a) {
        return this.v.namespaces ? a ? this.v[a] ? this.v[a].options || [] : [] : this.v.namespaces || [] : []
    }
    ;
    q.getOption = function(a, b) {
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
      , Lb = null;
    function Mb(a) {
        if (a.tagName.toLowerCase() !== "iframe") {
            var b = xb(a, "videoid");
            b && (b = {
                videoId: b,
                width: xb(a, "width"),
                height: xb(a, "height")
            },
            new X(a,b))
        }
    }
    function Ib(a, b, c) {
        Z || (Z = {},
        Lb = new Set,
        Nb.addEventListener("message", function(d) {
            a: if (Lb.has(d.origin)) {
                try {
                    var g = JSON.parse(d.data)
                } catch (f) {
                    break a
                }
                var k = Z[g.id];
                if (k && d.origin === k.N)
                    switch (d = k.U,
                    d.m = !0,
                    d.m && (va(d.l, d.sendMessage, d),
                    d.l.length = 0),
                    k = g.event,
                    g = g.info,
                    k) {
                    case "apiInfoDelivery":
                        if (H(g))
                            for (var e in g)
                                g.hasOwnProperty(e) && (d.v[e] = g[e]);
                        break;
                    case "infoDelivery":
                        Jb(d, g);
                        break;
                    case "initialDelivery":
                        H(g) && (clearInterval(d.h),
                        d.playerInfo = {},
                        d.v = {},
                        Kb(d, g.apiInterface),
                        Jb(d, g));
                        break;
                    default:
                        d.i.i || (e = {
                            target: d,
                            data: g
                        },
                        d.i.G(k, e),
                        yb("player." + k, e))
                    }
            }
        }));
        Z[b] = {
            U: a,
            N: c
        };
        Lb.add(c)
    }
    var Nb = window;
    I("FV_YT.PlayerState.UNSTARTED", -1);
    I("FV_YT.PlayerState.ENDED", 0);
    I("FV_YT.PlayerState.PLAYING", 1);
    I("FV_YT.PlayerState.PAUSED", 2);
    I("FV_YT.PlayerState.BUFFERING", 3);
    I("FV_YT.PlayerState.CUED", 5);
    I("FV_YT.get", function(a) {
        return V[a]
    });
    I("FV_YT.scan", wb);
    I("FV_YT.subscribe", function(a, b, c) {
        U.subscribe(a, b, c);
        vb[a] = !0;
        for (var d in V)
            V.hasOwnProperty(d) && Db(V[d], a)
    });
    I("FV_YT.unsubscribe", function(a, b, c) {
        jb(a, b, c)
    });
    I("FV_YT.Player", X);
    X.prototype.destroy = X.prototype.destroy;
    X.prototype.setSize = X.prototype.setSize;
    X.prototype.getIframe = X.prototype.getIframe;
    X.prototype.addEventListener = X.prototype.addEventListener;
    X.prototype.getVideoEmbedCode = X.prototype.getVideoEmbedCode;
    X.prototype.getOptions = X.prototype.getOptions;
    X.prototype.getOption = X.prototype.getOption;
    ub.push(function(a) {
        var b = a;
        b || (b = document);
        a = ya(b.getElementsByTagName("yt:player"));
        b = ya((b || document).querySelectorAll(".yt-player"));
        va(xa(a, b), Mb)
    });
    typeof YTConfig !== "undefined" && YTConfig.parsetags && YTConfig.parsetags !== "onload" || wb();
    /*var Ob = G.onYTReady;
    Ob && Ob();
    var Pb = G.onYouTubeIframeAPIReady;
    Pb && Pb();
    var Qb = G.onYouTubePlayerAPIReady;
    Qb && Qb();*/
}
).call(this);

}



if( typeof(flowplayer) != "undefined" ) {

  function fv_player_pro_youtube_get_video_id( src ) {
    var aMatch;
    if( aMatch = src.match(/(?:\?|&)v=([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    if( aMatch = src.match(/youtu.be\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    if( aMatch = src.match(/embed\/([a-zA-Z0-9_-]+)(?:\?|$|&)/) ){
      return aMatch[1];
    }
    if( aMatch = src.match(/shorts\/([a-zA-Z0-9_-]+)/) ){
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

    //  signal to the other players that 1MB YouTube API base.js has loaded
    jQuery(document).trigger('fv-player-yt-api-loaded');
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

        jQuery('.fp-notice-load').one( 'click', function()  {
          jQuery('.fp-notice-load',root).remove();

          //var api = jQuery(root).data('flowplayer');
          player.trigger('error', [ player, { code: 4, video: player.video } ] );
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
    // Include Safari (which means iPad too)
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


  function fv_player_pro_youtube_preload(that, api) {
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
      common.prepend(common.find(".fp-player", root)[0], wrapperTag);

      //console.log('new YT preload');  //  probably shouldn't happen when used in lightbox

      // this is the event which lets the player load YouTube
      jQuery(document).one('fv-player-yt-api-loaded', function() {

        // only one player can enter the loading phase
        if ( ! window.fv_player_pro_yt_load && window.fv_player_pro_yt_loading ) {
          return;
        }

        window.fv_player_pro_yt_loading = true;

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
      });

      if( !window.fv_player_pro_yt_load ) {
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

          player.trigger('error', [ player, { code: 4, video: player.video } ] );

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

            // we init YouTube muted to allow muted autoplay
            // we need to do this before we trigger ready event as there we might need to mute the video for custom start time
            player.mute(true,true); // mute, but don't remember it!
            youtube.playVideo();
            // look for youtube_unmute_attempted to see what happens next

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
                player.trigger('error', [ player, { code: 4, video: player.video } ] );
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
              setTimeout( function() {
                var fresh_status = youtube.getPlayerState();
                if( fresh_status == -1 ) {
                  fv_player_log('This video did not start yet!');

                  root.removeClass('is-youtube-nl');
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

              // Was it paused because of unmuting?
              if( player.youtube_unmute_attempted === 1 ) {
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
              player.one('progress', function() {
                if( !player.youtube_unmute_attempted && youtube.isMuted() ) {
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
          intUIUpdate = setInterval(function () {
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

          }, 250);

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

                  youtube = new FV_YT.Player(
                    wrapperTag,
                    fv_player_pro_youtube_player_vars(video_id, root, {
                      onReady: onReady,
                      onStateChange: onStateChange,
                      onError: onError,
                      onApiChange: onApiChange,
                    })
                  );

                  var iframe = jQuery('.fp-engine.fvyoutube-engine',root);
                  iframe[0].allowFullscreen = false;
                  /* in Chrome it's possible to double click the video entery YouTube fullscreen that way. Cancelling the event won't help, so here is a pseudo-fix */
                  iframe.on("webkitfullscreenchange", function() {
                    if (document.webkitCancelFullScreen) {
                      document.webkitCancelFullScreen();
                    }
                    return false;
                  });
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
            fv_player_pro_youtube_preload(this);  //  todo: fix if you are opening the lightbox the second time
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
