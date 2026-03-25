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
   * Copy of https://www.youtube.com/s/player/c9168c90/www-widgetapi.vflset/www-widgetapi.js with YT changed to FV_YT.
   *
   * Commented out parts where it runs onYTReady(), onYouTubeIframeAPIReady(), onYouTubePlayerAPIReady()
   */
  (function() {
    'use strict';
    var m, ca = typeof Object.create == "function" ? Object.create : function(a) {
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
        var u;
        a: {
            var ea = {
                a: !0
            }
              , fa = {};
            try {
                fa.__proto__ = ea;
                u = fa.a;
                break a
            } catch (a) {}
            u = !1
        }
        t = u ? function(a, b) {
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
    function v(a) {
        var b = typeof Symbol != "undefined" && Symbol.iterator && a[Symbol.iterator];
        if (b)
            return b.call(a);
        if (typeof a.length == "number")
            return {
                next: ia(a)
            };
        throw Error(String(a) + " is not an iterable or ArrayLike");
    }
    function ja(a) {
        if (!(a instanceof Object))
            throw new TypeError("Iterator result " + a + " is not an object");
    }
    function y() {
        this.o = !1;
        this.j = null;
        this.m = void 0;
        this.g = 1;
        this.i = this.l = 0;
        this.D = this.h = null
    }
    function z(a) {
        if (a.o)
            throw new TypeError("Generator is already running");
        a.o = !0
    }
    y.prototype.B = function(a) {
        this.m = a
    }
    ;
    function A(a, b) {
        a.h = {
            N: b,
            O: !0
        };
        a.g = a.l || a.i
    }
    y.prototype.T = function() {
        return this.g
    }
    ;
    y.prototype.getNextAddress = y.prototype.T;
    y.prototype.U = function() {
        return this.m
    }
    ;
    y.prototype.getYieldResult = y.prototype.U;
    y.prototype.return = function(a) {
        this.h = {
            return: a
        };
        this.g = this.i
    }
    ;
    y.prototype["return"] = y.prototype.return;
    y.prototype.V = function(a) {
        this.h = {
            C: a
        };
        this.g = this.i
    }
    ;
    y.prototype.jumpThroughFinallyBlocks = y.prototype.V;
    y.prototype.u = function(a, b) {
        this.g = b;
        return {
            value: a
        }
    }
    ;
    y.prototype.yield = y.prototype.u;
    y.prototype.Y = function(a, b) {
        a = v(a);
        var c = a.next();
        ja(c);
        if (c.done)
            this.m = c.value,
            this.g = b;
        else
            return this.j = a,
            this.u(c.value, b)
    }
    ;
    y.prototype.yieldAll = y.prototype.Y;
    y.prototype.C = function(a) {
        this.g = a
    }
    ;
    y.prototype.jumpTo = y.prototype.C;
    y.prototype.G = function() {
        this.g = 0
    }
    ;
    y.prototype.jumpToEnd = y.prototype.G;
    y.prototype.I = function(a, b) {
        this.l = a;
        b != void 0 && (this.i = b)
    }
    ;
    y.prototype.setCatchFinallyBlocks = y.prototype.I;
    y.prototype.X = function(a) {
        this.l = 0;
        this.i = a || 0
    }
    ;
    y.prototype.setFinallyBlock = y.prototype.X;
    y.prototype.H = function(a, b) {
        this.g = a;
        this.l = b || 0
    }
    ;
    y.prototype.leaveTryBlock = y.prototype.H;
    y.prototype.F = function(a) {
        this.l = a || 0;
        a = this.h.N;
        this.h = null;
        return a
    }
    ;
    y.prototype.enterCatchBlock = y.prototype.F;
    y.prototype.K = function(a, b, c) {
        c ? this.D[c] = this.h : this.D = [this.h];
        this.l = a || 0;
        this.i = b || 0
    }
    ;
    y.prototype.enterFinallyBlock = y.prototype.K;
    y.prototype.W = function(a, b) {
        b = this.D.splice(b || 0)[0];
        (b = this.h = this.h || b) ? b.O ? this.g = this.l || this.i : b.C != void 0 && this.i < b.C ? (this.g = b.C,
        this.h = null) : this.g = this.i : this.g = a
    }
    ;
    y.prototype.leaveFinallyBlock = y.prototype.W;
    y.prototype.S = function(a) {
        return new C(a)
    }
    ;
    y.prototype.forIn = y.prototype.S;
    function C(a) {
        this.i = a;
        this.g = [];
        for (var b in a)
            this.g.push(b);
        this.g.reverse()
    }
    C.prototype.h = function() {
        for (; this.g.length > 0; ) {
            var a = this.g.pop();
            if (a in this.i)
                return a
        }
        return null
    }
    ;
    C.prototype.getNext = C.prototype.h;
    function ka(a) {
        this.g = new y;
        this.h = a
    }
    function la(a, b) {
        z(a.g);
        var c = a.g.j;
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
            var k = b.call(a.g.j, c);
            ja(k);
            if (!k.done)
                return a.g.o = !1,
                k;
            var h = k.value
        } catch (f) {
            return a.g.j = null,
            A(a.g, f),
            E(a)
        }
        a.g.j = null;
        d.call(a.g, h);
        return E(a)
    }
    function E(a) {
        for (; a.g.g; )
            try {
                var b = a.h(a.g);
                if (b)
                    return a.g.o = !1,
                    {
                        value: b.value,
                        done: !1
                    }
            } catch (c) {
                a.g.m = void 0,
                A(a.g, c)
            }
        a.g.o = !1;
        if (a.g.h) {
            b = a.g.h;
            a.g.h = null;
            if (b.O)
                throw b.N;
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
            a.g.j ? b = D(a, a.g.j.next, b, a.g.B) : (a.g.B(b),
            b = E(a));
            return b
        }
        ;
        this.throw = function(b) {
            z(a.g);
            a.g.j ? b = D(a, a.g.j["throw"], b, a.g.B) : (A(a.g, b),
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
        return new Promise(function(d, k) {
            function h(f) {
                f.done ? d(f.value) : Promise.resolve(f.value).then(b, c).then(h, k)
            }
            h(a.next())
        }
        )
    }
    function F(a) {
        return na(new ma(new ka(a)))
    }
    r("Symbol", function(a) {
        function b(h) {
            if (this instanceof b)
                throw new TypeError("Symbol is not a constructor");
            return new c(d + (h || "") + "_" + k++,h)
        }
        function c(h, f) {
            this.g = h;
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
                return oa(ia(this))
            }
        });
        return a
    });
    function oa(a) {
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
            } catch (g) {
                e.reject(g)
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
                    var g = f[e];
                    f[e] = null;
                    try {
                        g()
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
                return function(n) {
                    g || (g = !0,
                    l.call(e, n))
                }
            }
            var e = this
              , g = !1;
            return {
                resolve: f(this.G),
                reject: f(this.l)
            }
        }
        ;
        b.prototype.G = function(f) {
            if (f === this)
                this.l(new TypeError("A Promise cannot resolve to itself"));
            else if (f instanceof b)
                this.I(f);
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
                e ? this.F(f) : this.m(f)
            }
        }
        ;
        b.prototype.F = function(f) {
            var e = void 0;
            try {
                e = f.then
            } catch (g) {
                this.l(g);
                return
            }
            typeof e == "function" ? this.K(e, f) : this.m(f)
        }
        ;
        b.prototype.l = function(f) {
            this.u(2, f)
        }
        ;
        b.prototype.m = function(f) {
            this.u(1, f)
        }
        ;
        b.prototype.u = function(f, e) {
            if (this.h != 0)
                throw Error("Cannot settle(" + f + ", " + e + "): Promise already settled in state" + this.h);
            this.h = f;
            this.i = e;
            this.h === 2 && this.H();
            this.B()
        }
        ;
        b.prototype.H = function() {
            var f = this;
            k(function() {
                if (f.D()) {
                    var e = q.console;
                    typeof e !== "undefined" && e.error(f.i)
                }
            }, 1)
        }
        ;
        b.prototype.D = function() {
            if (this.o)
                return !1;
            var f = q.CustomEvent
              , e = q.Event
              , g = q.dispatchEvent;
            if (typeof g === "undefined")
                return !0;
            typeof f === "function" ? f = new f("unhandledrejection",{
                cancelable: !0
            }) : typeof e === "function" ? f = new e("unhandledrejection",{
                cancelable: !0
            }) : (f = q.document.createEvent("CustomEvent"),
            f.initCustomEvent("unhandledrejection", !1, !0, f));
            f.promise = this;
            f.reason = this.i;
            return g(f)
        }
        ;
        b.prototype.B = function() {
            if (this.g != null) {
                for (var f = 0; f < this.g.length; ++f)
                    h.h(this.g[f]);
                this.g = null
            }
        }
        ;
        var h = new c;
        b.prototype.I = function(f) {
            var e = this.j();
            f.J(e.resolve, e.reject)
        }
        ;
        b.prototype.K = function(f, e) {
            var g = this.j();
            try {
                f.call(e, g.resolve, g.reject)
            } catch (l) {
                g.reject(l)
            }
        }
        ;
        b.prototype.then = function(f, e) {
            function g(x, B) {
                return typeof x == "function" ? function(aa) {
                    try {
                        l(x(aa))
                    } catch (ba) {
                        n(ba)
                    }
                }
                : B
            }
            var l, n, w = new b(function(x, B) {
                l = x;
                n = B
            }
            );
            this.J(g(f, l), g(e, n));
            return w
        }
        ;
        b.prototype.catch = function(f) {
            return this.then(void 0, f)
        }
        ;
        b.prototype.J = function(f, e) {
            function g() {
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
            this.g == null ? h.h(g) : this.g.push(g);
            this.o = !0
        }
        ;
        b.resolve = d;
        b.reject = function(f) {
            return new b(function(e, g) {
                g(f)
            }
            )
        }
        ;
        b.race = function(f) {
            return new b(function(e, g) {
                for (var l = v(f), n = l.next(); !n.done; n = l.next())
                    d(n.value).J(e, g)
            }
            )
        }
        ;
        b.all = function(f) {
            var e = v(f)
              , g = e.next();
            return g.done ? d([]) : new b(function(l, n) {
                function w(aa) {
                    return function(ba) {
                        x[aa] = ba;
                        B--;
                        B == 0 && l(x)
                    }
                }
                var x = []
                  , B = 0;
                do
                    x.push(void 0),
                    B++,
                    d(g.value).J(w(x.length - 1), n),
                    g = e.next();
                while (!g.done)
            }
            )
        }
        ;
        return b
    });
    function G(a, b) {
        return Object.prototype.hasOwnProperty.call(a, b)
    }
    var pa = typeof Object.assign == "function" ? Object.assign : function(a, b) {
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
        return a || pa
    });
    r("Symbol.dispose", function(a) {
        return a ? a : Symbol("Symbol.dispose")
    });
    r("WeakMap", function(a) {
        function b(g) {
            this.g = (e += Math.random() + 1).toString();
            if (g) {
                g = v(g);
                for (var l; !(l = g.next()).done; )
                    l = l.value,
                    this.set(l[0], l[1])
            }
        }
        function c() {}
        function d(g) {
            var l = typeof g;
            return l === "object" && g !== null || l === "function"
        }
        function k(g) {
            if (!G(g, f)) {
                var l = new c;
                p(g, f, {
                    value: l
                })
            }
        }
        function h(g) {
            var l = Object[g];
            l && (Object[g] = function(n) {
                if (n instanceof c)
                    return n;
                Object.isExtensible(n) && k(n);
                return l(n)
            }
            )
        }
        if (function() {
            if (!a || !Object.seal)
                return !1;
            try {
                var g = Object.seal({})
                  , l = Object.seal({})
                  , n = new a([[g, 2], [l, 3]]);
                if (n.get(g) != 2 || n.get(l) != 3)
                    return !1;
                n.delete(g);
                n.set(l, 4);
                return !n.has(g) && n.get(l) == 4
            } catch (w) {
                return !1
            }
        }())
            return a;
        var f = "$jscomp_hidden_" + Math.random();
        h("freeze");
        h("preventExtensions");
        h("seal");
        var e = 0;
        b.prototype.set = function(g, l) {
            if (!d(g))
                throw Error("Invalid WeakMap key");
            k(g);
            if (!G(g, f))
                throw Error("WeakMap key fail: " + g);
            g[f][this.g] = l;
            return this
        }
        ;
        b.prototype.get = function(g) {
            return d(g) && G(g, f) ? g[f][this.g] : void 0
        }
        ;
        b.prototype.has = function(g) {
            return d(g) && G(g, f) && G(g[f], this.g)
        }
        ;
        b.prototype.delete = function(g) {
            return d(g) && G(g, f) && G(g[f], this.g) ? delete g[f][this.g] : !1
        }
        ;
        return b
    });
    r("Map", function(a) {
        function b() {
            var e = {};
            return e.previous = e.next = e.head = e
        }
        function c(e, g) {
            var l = e[1];
            return oa(function() {
                if (l) {
                    for (; l.head != e[1]; )
                        l = l.previous;
                    for (; l.next != l.head; )
                        return l = l.next,
                        {
                            done: !1,
                            value: g(l)
                        };
                    l = null
                }
                return {
                    done: !0,
                    value: void 0
                }
            })
        }
        function d(e, g) {
            var l = g && typeof g;
            l == "object" || l == "function" ? h.has(g) ? l = h.get(g) : (l = "" + ++f,
            h.set(g, l)) : l = "p_" + g;
            var n = e[0][l];
            if (n && G(e[0], l))
                for (e = 0; e < n.length; e++) {
                    var w = n[e];
                    if (g !== g && w.key !== w.key || g === w.key)
                        return {
                            id: l,
                            list: n,
                            index: e,
                            entry: w
                        }
                }
            return {
                id: l,
                list: n,
                index: -1,
                entry: void 0
            }
        }
        function k(e) {
            this[0] = {};
            this[1] = b();
            this.size = 0;
            if (e) {
                e = v(e);
                for (var g; !(g = e.next()).done; )
                    g = g.value,
                    this.set(g[0], g[1])
            }
        }
        if (function() {
            if (!a || typeof a != "function" || !a.prototype.entries || typeof Object.seal != "function")
                return !1;
            try {
                var e = Object.seal({
                    x: 4
                })
                  , g = new a(v([[e, "s"]]));
                if (g.get(e) != "s" || g.size != 1 || g.get({
                    x: 4
                }) || g.set({
                    x: 4
                }, "t") != g || g.size != 2)
                    return !1;
                var l = g.entries()
                  , n = l.next();
                if (n.done || n.value[0] != e || n.value[1] != "s")
                    return !1;
                n = l.next();
                return n.done || n.value[0].x != 4 || n.value[1] != "t" || !l.next().done ? !1 : !0
            } catch (w) {
                return !1
            }
        }())
            return a;
        var h = new WeakMap;
        k.prototype.set = function(e, g) {
            e = e === 0 ? 0 : e;
            var l = d(this, e);
            l.list || (l.list = this[0][l.id] = []);
            l.entry ? l.entry.value = g : (l.entry = {
                next: this[1],
                previous: this[1].previous,
                head: this[1],
                key: e,
                value: g
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
        k.prototype.forEach = function(e, g) {
            for (var l = this.entries(), n; !(n = l.next()).done; )
                n = n.value,
                e.call(g, n[1], n[0], this)
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
                var k = d.entries()
                  , h = k.next();
                if (h.done || h.value[0] != c || h.value[1] != c)
                    return !1;
                h = k.next();
                return h.done || h.value[0] == c || h.value[0].x != 4 || h.value[1] != h.value[0] ? !1 : k.next().done
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
            this.g.forEach(function(h) {
                return c.call(d, h, h, k)
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
                for (var k = d.length, h = 0; h < k; h++) {
                    var f = d[h];
                    if (b.call(c, f, h, d)) {
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
              , h = typeof Symbol != "undefined" && Symbol.iterator && b[Symbol.iterator];
            if (typeof h == "function") {
                b = h.call(b);
                for (var f = 0; !(h = b.next()).done; )
                    k.push(c.call(d, h.value, f++))
            } else
                for (h = b.length,
                f = 0; f < h; f++)
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
    function qa(a) {
        return Object.prototype.hasOwnProperty.call(a, ra) && a[ra] || (a[ra] = ++sa)
    }
    var ra = "closure_uid_" + (Math.random() * 1E9 >>> 0)
      , sa = 0;
    function J(a, b) {
        a = a.split(".");
        for (var c = H, d; a.length && (d = a.shift()); )
            a.length || b === void 0 ? c[d] && c[d] !== Object.prototype[d] ? c = c[d] : c = c[d] = {} : c[d] = b
    }
    function ta(a, b) {
        function c() {}
        c.prototype = b.prototype;
        a.R = b.prototype;
        a.prototype = new c;
        a.prototype.constructor = a;
        a.ga = function(d, k, h) {
            for (var f = Array(arguments.length - 2), e = 2; e < arguments.length; e++)
                f[e - 2] = arguments[e];
            return b.prototype[k].apply(d, f)
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
        for (var d = a.length, k = typeof a === "string" ? a.split("") : a, h = 0; h < d; h++)
            h in k && b.call(c, k[h], h, a)
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
        H.setTimeout(function() {
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
    ;function K() {
        this.i = this.i;
        this.j = this.j
    }
    K.prototype.i = !1;
    K.prototype.dispose = function() {
        this.i || (this.i = !0,
        this.L())
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
    K.prototype.L = function() {
        if (this.j)
            for (; this.j.length; )
                this.j.shift()()
    }
    ;
    function Ka(a) {
        var b = {}, c;
        for (c in a)
            b[c] = a[c];
        return b
    }
    ;var La = /&/g
      , Ma = /</g
      , Na = />/g
      , Oa = /"/g
      , Pa = /'/g
      , Qa = /\x00/g
      , Ra = /[\x00&<>"']/;
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
    var Sa = new L("about:invalid#zClosurez");
    function Ta(a) {
        this.aa = a
    }
    function M(a) {
        return new Ta(function(b) {
            return b.substr(0, a.length + 1).toLowerCase() === a + ":"
        }
        )
    }
    var Ua = [M("data"), M("http"), M("https"), M("mailto"), M("ftp"), new Ta(function(a) {
        return /^[^:]*([/?#]|$)/.test(a)
    }
    )]
      , Va = /^\s*(?!javascript:)(?:[\w+.-]+:|[^:/?#]*(?:[/?#]|$))/i;
    var Wa = {
        fa: 0,
        da: 1,
        ea: 2,
        0: "FORMATTED_HTML_CONTENT",
        1: "EMBEDDED_INTERNAL_CONTENT",
        2: "EMBEDDED_TRUSTED_EXTERNAL_CONTENT"
    };
    function N(a, b) {
        b = Error.call(this, a + " cannot be used with intent " + Wa[b]);
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
                    var Xa = Object.getOwnPropertyDescriptor(O, P);
                    Xa && Object.defineProperty(N, P, Xa)
                } else
                    N[P] = O[P];
    N.R = O.prototype;
    function Ya(a) {
        Ra.test(a) && (a.indexOf("&") != -1 && (a = a.replace(La, "&amp;")),
        a.indexOf("<") != -1 && (a = a.replace(Ma, "&lt;")),
        a.indexOf(">") != -1 && (a = a.replace(Na, "&gt;")),
        a.indexOf('"') != -1 && (a = a.replace(Oa, "&quot;")),
        a.indexOf("'") != -1 && (a = a.replace(Pa, "&#39;")),
        a.indexOf("\x00") != -1 && (a = a.replace(Qa, "&#0;")));
        return a
    }
    ;var Za, Q;
    a: {
        for (var $a = ["CLOSURE_FLAGS"], R = H, ab = 0; ab < $a.length; ab++)
            if (R = R[$a[ab]],
            R == null) {
                Q = null;
                break a
            }
        Q = R
    }
    var bb = Q && Q[610401301];
    Za = bb != null ? bb : !1;
    function S() {
        var a = H.navigator;
        return a && (a = a.userAgent) ? a : ""
    }
    var T, cb = H.navigator;
    T = cb ? cb.userAgentData || null : null;
    function db() {
        return Za ? !!T && T.brands.length > 0 : !1
    }
    function eb(a) {
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
    function fb() {
        for (var a = S(), b = RegExp("([A-Z][\\w ]+)/([^\\s]+)\\s*(?:\\((.*?)\\))?", "g"), c = [], d; d = b.exec(a); )
            c.push([d[1], d[2], d[3] || void 0]);
        a = eb(c);
        if (db())
            a: {
                if (Za && T)
                    for (b = 0; b < T.brands.length; b++)
                        if ((c = T.brands[b].brand) && c.indexOf("Chromium") != -1) {
                            b = !0;
                            break a
                        }
                b = !1
            }
        else
            b = (S().indexOf("Chrome") != -1 || S().indexOf("CriOS") != -1) && (db() || S().indexOf("Edge") == -1) || S().indexOf("Silk") != -1;
        return b ? a(["Chrome", "CriOS", "HeadlessChrome"]) : ""
    }
    function gb() {
        if (db()) {
            var a = T.brands.find(function(b) {
                return b.brand === "Chromium"
            });
            if (!a || !a.version)
                return NaN;
            a = a.version.split(".")
        } else {
            a = fb();
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
        this.u = !!a
    }
    ta(U, K);
    m = U.prototype;
    m.subscribe = function(a, b, c) {
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
    function hb(a, b, c) {
        var d = V;
        if (a = d.h[a]) {
            var k = d.g;
            (a = a.find(function(h) {
                return k[h + 1] == b && k[h + 2] == c
            })) && d.M(a)
        }
    }
    m.M = function(a) {
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
    m.P = function(a, b) {
        var c = this.h[a];
        if (c) {
            var d = Array(arguments.length - 1), k = arguments.length, h;
            for (h = 1; h < k; h++)
                d[h - 1] = arguments[h];
            if (this.u)
                for (h = 0; h < c.length; h++)
                    k = c[h],
                    ib(this.g[k + 1], this.g[k + 2], d);
            else {
                this.m++;
                try {
                    for (h = 0,
                    k = c.length; h < k && !this.i; h++) {
                        var f = c[h];
                        this.g[f + 1].apply(this.g[f + 2], d)
                    }
                } finally {
                    if (this.m--,
                    this.l.length > 0 && this.m == 0)
                        for (; c = this.l.pop(); )
                            this.M(c)
                }
            }
            return h != 0
        }
        return !1
    }
    ;
    function ib(a, b, c) {
        Ha(function() {
            a.apply(b, c)
        })
    }
    m.clear = function(a) {
        if (a) {
            var b = this.h[a];
            b && (b.forEach(this.M, this),
            delete this.h[a])
        } else
            this.g.length = 0,
            this.h = {}
    }
    ;
    m.L = function() {
        U.R.L.call(this);
        this.clear();
        this.l.length = 0
    }
    ;
    var jb = RegExp("^(?:([^:/?#.]+):)?(?://(?:([^\\\\/?#]*)@)?([^\\\\/?#]*?)(?::([0-9]+))?(?=[\\\\/?#]|$))?([^?#]+)?(?:\\?([^#]*))?(?:#([\\s\\S]*))?$");
    function kb(a) {
        var b = a.match(jb);
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
    function lb(a, b, c) {
        if (Array.isArray(b))
            for (var d = 0; d < b.length; d++)
                lb(a, String(b[d]), c);
        else
            b != null && c.push(a + (b === "" ? "" : "=" + encodeURIComponent(String(b))))
    }
    var mb = /#|$/;
    var nb = ["https://www.google.com"];
    function ob() {
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
    function pb() {
        return F(function(a) {
            var b = a.return;
            var c = gb() >= 119;
            return b.call(a, c && !!navigator.permissions && !!navigator.permissions.query && "requestStorageAccessFor"in document)
        })
    }
    function qb() {
        var a = new ob
          , b = ["https://www.youtube.com"];
        b = b === void 0 ? nb : b;
        F(function(c) {
            switch (c.g) {
            case 1:
                return c.u(pb(), 2);
            case 2:
                if (!c.m) {
                    c.C(3);
                    break
                }
                return c.u(Promise.all(b.map(function(d) {
                    var k;
                    return F(function(h) {
                        if (h.g == 1)
                            return h.I(2),
                            h.u(navigator.permissions.query({
                                name: "top-level-storage-access",
                                requestedOrigin: d
                            }), 4);
                        if (h.g != 2)
                            return k = h.m,
                        k.state === "prompt" && a.g.push(d),
                            h.H(0);
                        h.F();
                        h.G()
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
      , rb = []
      , V = new U
      , sb = {};
    function tb() {
        for (var a = v(rb), b = a.next(); !b.done; b = a.next())
            b = b.value,
            b()
    }
    function ub(a, b) {
        return a.tagName.toLowerCase().substring(0, 3) === "yt:" ? a.getAttribute(b) : a.dataset ? a.dataset[b] : a.getAttribute("data-" + b)
    }
    function vb(a) {
        V.P.apply(V, arguments)
    }
    ;function wb(a) {
        return (a.search("cue") === 0 || a.search("load") === 0) && a !== "loadModule"
    }
    function xb(a) {
        return a.search("get") === 0 || a.search("is") === 0
    }
    ;var yb = window;
    function X(a, b) {
        this.A = {};
        this.playerInfo = {};
        this.videoTitle = "";
        this.j = this.g = null;
        this.h = 0;
        this.m = !1;
        this.l = [];
        this.i = null;
        this.B = {};
        this.options = null;
        this.u = this.ba.bind(this);
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
            yb.yt_embedsEnableRsaforFromIframeApi && qb();
            c = a.tagName.toLowerCase() === "iframe";
            b.host || (b.host = c ? kb(a.src) : "https://www.youtube.com");
            this.options = b || {};
            b = [this.options, window.YTConfig || {}];
            for (var d = 0; d < b.length; d++)
                b[d].host && (b[d].host = b[d].host.toString().replace("http://", "https://"));
            if (!c) {
                b = document.createElement("iframe");
                c = a.attributes;
                d = 0;
                for (var k = c.length; d < k; d++) {
                    var h = c[d].value;
                    h != null && h !== "" && h !== "null" && b.setAttribute(c[d].name, h)
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
                a = zb(this, b);
                c = "" + Y(this, "host") + Ab(this) + "?";
                d = [];
                for (var f in a)
                    lb(f, a[f], d);
                f = c + d.join("&");
                if (yb.yt_embedsEnableIframeSrcWithIntent) {
                    var e = e === void 0 ? Ua : e;
                    a: if (e = e === void 0 ? Ua : e,
                    f instanceof L)
                        e = f;
                    else {
                        for (a = 0; a < e.length; ++a)
                            if (c = e[a],
                            c instanceof Ta && c.aa(f)) {
                                e = new L(f);
                                break a
                            }
                        e = void 0
                    }
                    e = e || Sa;
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
                        e = Va.test(e) ? e : void 0;
                    e !== void 0 && (b.src = e);
                    b.sandbox.add("allow-presentation", "allow-top-navigation")
                } else
                    b.src = f;
                a = b
            }
            this.g = a;
            this.g.id || (this.g.id = "widget" + qa(this.g));
            W[this.g.id] = this;
            if (window.postMessage) {
                this.i = new U;
                Bb(this);
                b = Y(this, "events");
                for (var g in b)
                    b.hasOwnProperty(g) && this.addEventListener(g, b[g]);
                for (var l in sb)
                    sb.hasOwnProperty(l) && Cb(this, l)
            }
        }
    }
    m = X.prototype;
    m.setSize = function(a, b) {
        this.g.width = a.toString();
        this.g.height = b.toString();
        return this
    }
    ;
    m.getIframe = function() {
        return this.g
    }
    ;
    m.addEventListener = function(a, b) {
        var c = b;
        typeof b === "string" && (c = function() {
            window[b].apply(window, arguments)
        }
        );
        if (!c)
            return this;
        this.i.subscribe(a, c);
        Db(this, a);
        return this
    }
    ;
    function Cb(a, b) {
        b = b.split(".");
        if (b.length === 2) {
            var c = b[1];
            "player" === b[0] && Db(a, c)
        }
    }
    m.destroy = function() {
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
    function Eb(a, b, c) {
        c = c || [];
        c = Array.prototype.slice.call(c);
        b = {
            event: "command",
            func: b,
            args: c
        };
        a.m ? a.sendMessage(b) : a.l.push(b)
    }
    m.ba = function() {
        Fb(this) || clearInterval(this.h)
    }
    ;
    function Fb(a) {
        if (!a.g || !a.g.contentWindow)
            return !1;
        a.sendMessage({
            event: "listening"
        });
        return !0
    }
    function Bb(a) {
        Gb(a, a.id, String(Y(a, "host")));
        var b = Number(yb.yt_embedsWidgetPollIntervalMs) || 250;
        a.h = setInterval(a.u, b);
        a.g && (a.o = function() {
            clearInterval(a.h);
            a.h = setInterval(a.u, b)
        }
        ,
        a.g.addEventListener("load", a.o))
    }
    function Hb(a) {
        var b = a.getBoundingClientRect();
        a = Math.max(0, Math.min(b.bottom, window.innerHeight || document.documentElement.clientHeight) - Math.max(b.top, 0)) * Math.max(0, Math.min(b.right, window.innerWidth || document.documentElement.clientWidth) - Math.max(b.left, 0));
        a = (b = b.height * b.width) ? a / b : 0;
        return document.visibilityState === "hidden" || a < .5 ? 1 : a < .75 ? 2 : a < .85 ? 3 : a < .95 ? 4 : a < 1 ? 5 : 6
    }
    function Db(a, b) {
        a.B[b] || (a.B[b] = !0,
        Eb(a, "addEventListener", [b]))
    }
    m.sendMessage = function(a) {
        a.id = this.id;
        a.channel = "widget";
        a = JSON.stringify(a);
        var b = kb(this.g.src || "").replace("http:", "https:");
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
    function Ab(a) {
        if ((a = String(Y(a, "videoId"))) && (a.length !== 11 || !a.match(/^[a-zA-Z0-9\-_]+$/)))
            throw Error("Invalid video id");
        return "/embed/" + a
    }
    function zb(a, b) {
        var c = Y(a, "playerVars");
        c ? c = Ka(c) : c = {};
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
        window.location.href && va(["debugjs", "debugcss"], function(k) {
            var h = window.location.href;
            var f = h.search(mb);
            b: {
                var e = 0;
                for (var g = k.length; (e = h.indexOf(k, e)) >= 0 && e < f; ) {
                    var l = h.charCodeAt(e - 1);
                    if (l == 38 || l == 63)
                        if (l = h.charCodeAt(e + g),
                        !l || l == 61 || l == 38 || l == 35)
                            break b;
                    e += g + 1
                }
                e = -1
            }
            if (e < 0)
                h = null;
            else {
                g = h.indexOf("&", e);
                if (g < 0 || g > f)
                    g = f;
                e += k.length + 1;
                h = decodeURIComponent(h.slice(e, g !== -1 ? g : 0).replace(/\+/g, " "))
            }
            h !== null && (c[k] = h)
        });
        window.location.href && (c.forigin = window.location.href);
        a = window.location.ancestorOrigins;
        c.aoriginsup = a === void 0 ? 0 : 1;
        a && a.length > 0 && (c.aorigins = Array.from(a).join(","));
        window.document.referrer && (c.gporigin = window.document.referrer);
        b && (c.vf = Hb(b));
        return c
    }
    function Ib(a, b) {
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
    function Jb(a, b) {
        b = v(b);
        for (var c = b.next(), d = {}; !c.done; d = {
            v: void 0
        },
        c = b.next())
            d.v = c.value,
            a[d.v] || (d.v === "getCurrentTime" ? a[d.v] = function() {
                var k = this.playerInfo.currentTime;
                if (this.playerInfo.playerState === 1) {
                    var h = (Date.now() / 1E3 - this.playerInfo.currentTimeLastUpdated_) * this.playerInfo.playbackRate;
                    h > 0 && (k += Math.min(h, 1))
                }
                return k
            }
            : wb(d.v) ? a[d.v] = function(k) {
                return function() {
                    this.playerInfo = {};
                    this.A = {};
                    Eb(this, k.v, arguments);
                    return this
                }
            }(d) : xb(d.v) ? a[d.v] = function(k) {
                return function() {
                    var h = k.v
                      , f = 0;
                    h.search("get") === 0 ? f = 3 : h.search("is") === 0 && (f = 2);
                    return this.playerInfo[h.charAt(f).toLowerCase() + h.substring(f + 1)]
                }
            }(d) : a[d.v] = function(k) {
                return function() {
                    Eb(this, k.v, arguments);
                    return this
                }
            }(d))
    }
    m.getVideoEmbedCode = function() {
        var a = "" + Y(this, "host") + Ab(this)
          , b = Number(Y(this, "width"))
          , c = Number(Y(this, "height"));
        if (isNaN(b) || isNaN(c))
            throw Error("Invalid width or height property");
        b = Math.floor(b);
        c = Math.floor(c);
        var d = this.videoTitle;
        a = Ya(a);
        d = Ya(d != null ? d : "YouTube video player");
        return '<iframe width="' + b + '" height="' + c + '" src="' + a + '" title="' + (d + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>')
    }
    ;
    m.getOptions = function(a) {
        return this.A.namespaces ? a ? this.A[a] ? this.A[a].options || [] : [] : this.A.namespaces || [] : []
    }
    ;
    m.getOption = function(a, b) {
        if (this.A.namespaces && a && b && this.A[a])
            return this.A[a][b]
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
      , Kb = null;
    function Lb(a) {
        if (a.tagName.toLowerCase() !== "iframe") {
            var b = ub(a, "videoid");
            b && (b = {
                videoId: b,
                width: ub(a, "width"),
                height: ub(a, "height")
            },
            new X(a,b))
        }
    }
    function Gb(a, b, c) {
        Z || (Z = {},
        Kb = new Set,
        Mb.addEventListener("message", function(d) {
            a: if (Kb.has(d.origin)) {
                try {
                    var k = JSON.parse(d.data)
                } catch (e) {
                    break a
                }
                var h = Z[k.id];
                if (h && d.origin === h.Z)
                    switch (d = h.ca,
                    d.m = !0,
                    d.m && (va(d.l, d.sendMessage, d),
                    d.l.length = 0),
                    h = k.event,
                    k = k.info,
                    h) {
                    case "apiInfoDelivery":
                        if (I(k))
                            for (var f in k)
                                k.hasOwnProperty(f) && (d.A[f] = k[f]);
                        break;
                    case "infoDelivery":
                        Ib(d, k);
                        break;
                    case "initialDelivery":
                        I(k) && (clearInterval(d.h),
                        d.playerInfo = {},
                        d.A = {},
                        Jb(d, k.apiInterface),
                        Ib(d, k));
                        break;
                    case "alreadyInitialized":
                        clearInterval(d.h);
                        break;
                    case "readyToListen":
                        Fb(d);
                        break;
                    default:
                        d.i.i || (f = {
                            target: d,
                            data: k
                        },
                        d.i.P(h, f),
                        vb("player." + h, f))
                    }
            }
        }));
        Z[b] = {
            ca: a,
            Z: c
        };
        Kb.add(c)
    }
    var Mb = window;
    J("FV_YT.PlayerState.UNSTARTED", -1);
    J("FV_YT.PlayerState.ENDED", 0);
    J("FV_YT.PlayerState.PLAYING", 1);
    J("FV_YT.PlayerState.PAUSED", 2);
    J("FV_YT.PlayerState.BUFFERING", 3);
    J("FV_YT.PlayerState.CUED", 5);
    J("FV_YT.get", function(a) {
        return W[a]
    });
    J("FV_YT.scan", tb);
    J("FV_YT.subscribe", function(a, b, c) {
        V.subscribe(a, b, c);
        sb[a] = !0;
        for (var d in W)
            W.hasOwnProperty(d) && Cb(W[d], a)
    });
    J("FV_YT.unsubscribe", function(a, b, c) {
        hb(a, b, c)
    });
    J("FV_YT.Player", X);
    X.prototype.destroy = X.prototype.destroy;
    X.prototype.setSize = X.prototype.setSize;
    X.prototype.getIframe = X.prototype.getIframe;
    X.prototype.addEventListener = X.prototype.addEventListener;
    X.prototype.getVideoEmbedCode = X.prototype.getVideoEmbedCode;
    X.prototype.getOptions = X.prototype.getOptions;
    X.prototype.getOption = X.prototype.getOption;
    rb.push(function(a) {
        var b = a;
        b || (b = document);
        a = ya(b.getElementsByTagName("yt:player"));
        b = ya((b || document).querySelectorAll(".yt-player"));
        va(xa(a, b), Lb)
    });
    typeof YTConfig !== "undefined" && YTConfig.parsetags && YTConfig.parsetags !== "onload" || tb();
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

  /**
   * Avoid issue with Google Tag Manager trying to inspect the YouTube instance using new YT.Player()
   * on top of existing FV Player YouTube engine.
   */
  function fv_player_yotube_avoid_google_tag_manager_inspect( wrapperTag ) {
    if ( window.google_tag_manager && window.google_tag_manager.sequence ) {
      for ( var i = 0; i < window.google_tag_manager.sequence; i++ ) {
        wrapperTag.setAttribute( 'data-gtm-yt-inspected-' + i, true );
      }
    }
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

      fv_player_yotube_avoid_google_tag_manager_inspect( wrapperTag );

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

                  fv_player_yotube_avoid_google_tag_manager_inspect( wrapperTag );

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
