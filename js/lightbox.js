/*!
	Colorbox v1.5.0 - 2014-02-27
	jQuery lightbox and modal window plugin
	(c) 2014 Jack Moore - http://www.jacklmoore.com/colorbox
	license: http://www.opensource.org/licenses/mit-license.php
*/
(function(t,e,i){function o(i,o,n){var r=e.createElement(i);return o&&(r.id=Z+o),n&&(r.style.cssText=n),t(r)}function n(){return i.innerHeight?i.innerHeight:t(i).height()}function r(e,i){i!==Object(i)&&(i={}),this.cache={},this.el=e,this.get=function(e){var o,n;return void 0!==this.cache[e]?n=this.cache[e]:(o=t(this.el).attr("data-cbox-"+e),void 0!==o?n=o:void 0!==i[e]?n=i[e]:void 0!==X[e]&&(n=X[e]),this.cache[e]=n),t.isFunction(n)?n.call(this.el):n}}function h(t){var e=E.length,i=(z+t)%e;return 0>i?e+i:i}function s(t,e){return Math.round((/%/.test(t)?("x"===e?W.width():n())/100:1)*parseInt(t,10))}function a(t,e){return t.get("photo")||t.get("photoRegex").test(e)}function l(t,e){return t.get("retinaUrl")&&i.devicePixelRatio>1?e.replace(t.get("photoRegex"),t.get("retinaSuffix")):e}function d(t){"contains"in x[0]&&!x[0].contains(t.target)&&(t.stopPropagation(),x.focus())}function c(t){c.str!==t&&(x.add(v).removeClass(c.str).addClass(t),c.str=t)}function g(){z=0,rel&&"nofollow"!==rel?(E=t("."+te).filter(function(){var e=t.data(this,Y),i=new r(this,e);return i.get("rel")===rel}),z=E.index(_.el),-1===z&&(E=E.add(_.el),z=E.length-1)):E=t(_.el)}function u(i){t(e).trigger(i),se.triggerHandler(i)}function p(i){var n;G||(n=t(i).data("fv_player_pro_colorbox"),_=new r(i,n),rel=_.get("rel"),g(),$||($=q=!0,c(_.get("className")),x.css({visibility:"hidden",display:"block"}),L=o(ae,"LoadedContent","width:0; height:0; overflow:hidden; visibility:hidden"),b.css({width:"",height:""}).append(L),D=T.height()+k.height()+b.outerHeight(!0)-b.height(),j=C.width()+H.width()+b.outerWidth(!0)-b.width(),A=L.outerHeight(!0),N=L.outerWidth(!0),_.w=s(_.get("initialWidth"),"x"),_.h=s(_.get("initialHeight"),"y"),L.css({width:"",height:_.h}),J.position(),u(ee),_.get("onOpen"),O.add(R).hide(),x.focus(),_.get("trapFocus")&&e.addEventListener&&(e.addEventListener("focus",d,!0),se.one(re,function(){e.removeEventListener("focus",d,!0)})),_.get("returnFocus")&&se.one(re,function(){t(_.el).focus()})),v.css({opacity:parseFloat(_.get("opacity")),cursor:_.get("overlayClose")?"pointer":"auto",visibility:"visible"}).show(),_.get("closeButton")?B.html(_.get("close")).appendTo(b):B.appendTo("<div/>"),w())}function f(){!x&&e.body&&(V=!1,W=t(i),x=o(ae).attr({id:Y,"class":t.support.opacity===!1?Z+"IE":"",role:"dialog",tabindex:"-1"}).hide(),v=o(ae,"Overlay").hide(),M=t([o(ae,"LoadingOverlay")[0],o(ae,"LoadingGraphic")[0]]),y=o(ae,"Wrapper"),b=o(ae,"Content").append(R=o(ae,"Title"),F=o(ae,"Current"),P=t('<button type="button"/>').attr({id:Z+"Previous"}),K=t('<button type="button"/>').attr({id:Z+"Next"}),I=o("button","Slideshow"),M),B=t('<button type="button"/>').attr({id:Z+"Close"}),y.append(o(ae).append(o(ae,"TopLeft"),T=o(ae,"TopCenter"),o(ae,"TopRight")),o(ae,!1,"clear:left").append(C=o(ae,"MiddleLeft"),b,H=o(ae,"MiddleRight")),o(ae,!1,"clear:left").append(o(ae,"BottomLeft"),k=o(ae,"BottomCenter"),o(ae,"BottomRight"))).find("div div").css({"float":"left"}),S=o(ae,!1,"position:absolute; width:9999px; visibility:hidden; display:none; max-width:none;"),O=K.add(P).add(F).add(I),t(e.body).append(v,x.append(y,S)))}function m(){function i(t){t.which>1||t.shiftKey||t.altKey||t.metaKey||t.ctrlKey||(t.preventDefault(),p(this))}return x?(V||(V=!0,K.click(function(){J.next()}),P.click(function(){J.prev()}),B.click(function(){J.close()}),v.click(function(){_.get("overlayClose")&&J.close()}),t(e).bind("keydown."+Z,function(t){var e=t.keyCode;$&&_.get("escKey")&&27===e&&(t.preventDefault(),J.close()),$&&_.get("arrowKey")&&E[1]&&!t.altKey&&(37===e?(t.preventDefault(),P.click()):39===e&&(t.preventDefault(),K.click()))}),t.isFunction(t.fn.on)?t(e).on("click."+Z,"."+te,i):t("."+te).live("click."+Z,i)),!0):!1}function w(){var n,r,h,d=J.prep,c=++le;q=!0,U=!1,u(he),u(ie),_.get("onLoad"),_.h=_.get("height")?s(_.get("height"),"y")-A-D:_.get("innerHeight")&&s(_.get("innerHeight"),"y"),_.w=_.get("width")?s(_.get("width"),"x")-N-j:_.get("innerWidth")&&s(_.get("innerWidth"),"x"),_.mw=_.w,_.mh=_.h,_.get("maxWidth")&&(_.mw=s(_.get("maxWidth"),"x")-N-j,_.mw=_.w&&_.w<_.mw?_.w:_.mw),_.get("maxHeight")&&(_.mh=s(_.get("maxHeight"),"y")-A-D,_.mh=_.h&&_.h<_.mh?_.h:_.mh),n=_.get("href"),Q=setTimeout(function(){M.show()},100),_.get("inline")?(h=o(ae).hide().insertBefore(t(n)[0]),se.one(he,function(){h.replaceWith(L.children())}),d(t(n))):_.get("iframe")?d(" "):_.get("html")?d(_.get("html")):a(_,n)?(n=l(_,n),U=e.createElement("img"),t(U).addClass(Z+"Photo").bind("error",function(){d(o(ae,"Error").html(_.get("imgError")))}).one("load",function(){var e;c===le&&(t.each(["alt","longdesc","aria-describedby"],function(e,i){var o=t(_.el).attr(i)||t(_.el).attr("data-"+i);o&&U.setAttribute(i,o)}),_.get("retinaImage")&&i.devicePixelRatio>1&&(U.height=U.height/i.devicePixelRatio,U.width=U.width/i.devicePixelRatio),_.get("scalePhotos")&&(r=function(){U.height-=U.height*e,U.width-=U.width*e},_.mw&&U.width>_.mw&&(e=(U.width-_.mw)/U.width,r()),_.mh&&U.height>_.mh&&(e=(U.height-_.mh)/U.height,r())),_.h&&(U.style.marginTop=Math.max(_.mh-U.height,0)/2+"px"),E[1]&&(_.get("loop")||E[z+1])&&(U.style.cursor="pointer",U.onclick=function(){J.next()}),U.style.width=U.width+"px",U.style.height=U.height+"px",setTimeout(function(){d(U)},1))}),setTimeout(function(){U.src=n},1)):n&&S.load(n,_.get("data"),function(e,i){c===le&&d("error"===i?o(ae,"Error").html(_.get("xhrError")):t(this).contents())})}var v,x,y,b,T,C,H,k,E,W,L,S,M,R,F,I,K,P,B,O,_,D,j,A,N,z,U,$,q,G,Q,J,V,X={html:!1,photo:!1,iframe:!1,inline:!1,transition:"elastic",speed:300,fadeOut:300,width:!1,initialWidth:"600",innerWidth:!1,maxWidth:!1,height:!1,initialHeight:"450",innerHeight:!1,maxHeight:!1,scalePhotos:!0,scrolling:!0,opacity:.9,preloading:!0,className:!1,overlayClose:!0,escKey:!0,arrowKey:!0,top:!1,bottom:!1,left:!1,right:!1,fixed:!1,data:void 0,closeButton:!0,fastIframe:!0,open:!1,reposition:!0,loop:!0,slideshow:!1,slideshowAuto:!0,slideshowSpeed:2500,slideshowStart:"start slideshow",slideshowStop:"stop slideshow",photoRegex:/\.(gif|png|jp(e|g|eg)|bmp|ico|webp|jxr)((#|\?).*)?$/i,retinaImage:!1,retinaUrl:!1,retinaSuffix:"@2x.$1",current:"image {current} of {total}",previous:"previous",next:"next",close:"close",xhrError:"This content failed to load.",imgError:"This image failed to load.",returnFocus:!0,trapFocus:!0,onOpen:!1,onLoad:!1,onComplete:!1,onCleanup:!1,onClosed:!1,rel:function(){return this.rel},href:function(){return t(this).attr("href")},title:function(){return this.title}},Y="fv_player_pro_colorbox",Z="fv_player_pro_box",te=Z+"Element",ee=Z+"_open",ie=Z+"_load",oe=Z+"_complete",ne=Z+"_cleanup",re=Z+"_closed",he=Z+"_purge",se=t("<a/>"),ae="div",le=0,de={},ce=function(){function t(){clearTimeout(h)}function e(){(_.get("loop")||E[z+1])&&(t(),h=setTimeout(J.next,_.get("slideshowSpeed")))}function i(){I.html(_.get("slideshowStop")).unbind(a).one(a,o),se.bind(oe,e).bind(ie,t),x.removeClass(s+"off").addClass(s+"on")}function o(){t(),se.unbind(oe,e).unbind(ie,t),I.html(_.get("slideshowStart")).unbind(a).one(a,function(){J.next(),i()}),x.removeClass(s+"on").addClass(s+"off")}function n(){r=!1,I.hide(),t(),se.unbind(oe,e).unbind(ie,t),x.removeClass(s+"off "+s+"on")}var r,h,s=Z+"Slideshow_",a="click."+Z;return function(){r?_.get("slideshow")||(se.unbind(ne,n),n()):_.get("slideshow")&&E[1]&&(r=!0,se.one(ne,n),_.get("slideshowAuto")?i():o(),I.show())}}();t.fv_player_pro_colorbox||(t(f),J=t.fn[Y]=t[Y]=function(e,i){var o,n=this;return n[0]?(f(),m()&&(e=e||{},i&&(e.onComplete=i),t.isFunction(n)&&(e.open=!0),n.each(function(){var i=t.data(this,Y)||{};t.data(this,Y,t.extend(i,e))}).addClass(te),o=new r(n[0],e),o.get("open")&&p(n[0])),n):n},J.position=function(e,i){function o(){T[0].style.width=k[0].style.width=b[0].style.width=parseInt(x[0].style.width,10)-j+"px",b[0].style.height=C[0].style.height=H[0].style.height=parseInt(x[0].style.height,10)-D+"px"}var r,h,a,l=0,d=0,c=x.offset();if(W.unbind("resize."+Z),x.css({top:-9e4,left:-9e4}),h=W.scrollTop(),a=W.scrollLeft(),_.get("fixed")?(c.top-=h,c.left-=a,x.css({position:"fixed"})):(l=h,d=a,x.css({position:"absolute"})),d+=_.get("right")!==!1?Math.max(W.width()-_.w-N-j-s(_.get("right"),"x"),0):_.get("left")!==!1?s(_.get("left"),"x"):Math.round(Math.max(W.width()-_.w-N-j,0)/2),l+=_.get("bottom")!==!1?Math.max(n()-_.h-A-D-s(_.get("bottom"),"y"),0):_.get("top")!==!1?s(_.get("top"),"y"):Math.round(Math.max(n()-_.h-A-D,0)/2),x.css({top:c.top,left:c.left,visibility:"visible"}),y[0].style.width=y[0].style.height="9999px",r={width:_.w+N+j,height:_.h+A+D,top:l,left:d},e){var g=0;t.each(r,function(t){return r[t]!==de[t]?(g=e,void 0):void 0}),e=g}de=r,e||x.css(r),x.dequeue().animate(r,{duration:e||0,complete:function(){o(),q=!1,y[0].style.width=_.w+N+j+"px",y[0].style.height=_.h+A+D+"px",_.get("reposition")&&setTimeout(function(){W.bind("resize."+Z,J.position)},1),i&&i()},step:o})},J.resize=function(t){var e;$&&(t=t||{},t.width&&(_.w=s(t.width,"x")-N-j),t.innerWidth&&(_.w=s(t.innerWidth,"x")),L.css({width:_.w}),t.height&&(_.h=s(t.height,"y")-A-D),t.innerHeight&&(_.h=s(t.innerHeight,"y")),t.innerHeight||t.height||(e=L.scrollTop(),L.css({height:"auto"}),_.h=L.height()),L.css({height:_.h}),e&&L.scrollTop(e),J.position("none"===_.get("transition")?0:_.get("speed")))},J.prep=function(i){function n(){return _.w=_.w||L.width(),_.w=_.mw&&_.mw<_.w?_.mw:_.w,_.w}function s(){return _.h=_.h||L.height(),_.h=_.mh&&_.mh<_.h?_.mh:_.h,_.h}if($){var d,g="none"===_.get("transition")?0:_.get("speed");L.remove(),L=o(ae,"LoadedContent").append(i),L.hide().appendTo(S.show()).css({width:n(),overflow:_.get("scrolling")?"auto":"hidden"}).css({height:s()}).prependTo(b),S.hide(),t(U).css({"float":"none"}),c(_.get("className")),d=function(){function i(){t.support.opacity===!1&&x[0].style.removeAttribute("filter")}var o,n,s=E.length;$&&(n=function(){clearTimeout(Q),M.hide(),u(oe),_.get("onComplete")},R.html(_.get("title")).show(),L.show(),s>1?("string"==typeof _.get("current")&&F.html(_.get("current").replace("{current}",z+1).replace("{total}",s)).show(),K[_.get("loop")||s-1>z?"show":"hide"]().html(_.get("next")),P[_.get("loop")||z?"show":"hide"]().html(_.get("previous")),ce(),_.get("preloading")&&t.each([h(-1),h(1)],function(){var i,o=E[this],n=new r(o,t.data(o,Y)),h=n.get("href");h&&a(n,h)&&(h=l(n,h),i=e.createElement("img"),i.src=h)})):O.hide(),_.get("iframe")?(o=e.createElement("iframe"),"frameBorder"in o&&(o.frameBorder=0),"allowTransparency"in o&&(o.allowTransparency="true"),_.get("scrolling")||(o.scrolling="no"),t(o).attr({src:_.get("href"),name:(new Date).getTime(),"class":Z+"Iframe",allowFullScreen:!0}).one("load",n).appendTo(L),se.one(he,function(){o.src="//about:blank"}),_.get("fastIframe")&&t(o).trigger("load")):n(),"fade"===_.get("transition")?x.fadeTo(g,1,i):i())},"fade"===_.get("transition")?x.fadeTo(g,0,function(){J.position(0,d)}):J.position(g,d)}},J.next=function(){!q&&E[1]&&(_.get("loop")||E[z+1])&&(z=h(1),p(E[z]))},J.prev=function(){!q&&E[1]&&(_.get("loop")||z)&&(z=h(-1),p(E[z]))},J.close=function(){$&&!G&&(G=!0,$=!1,u(ne),_.get("onCleanup"),W.unbind("."+Z),v.fadeTo(_.get("fadeOut")||0,0),x.stop().fadeTo(_.get("fadeOut")||0,0,function(){x.add(v).css({opacity:1,cursor:"auto"}).hide(),u(he),L.remove(),setTimeout(function(){G=!1,u(re),_.get("onClosed")},1)}))},J.remove=function(){x&&(x.stop(),t.fv_player_pro_colorbox.close(),x.stop().remove(),v.remove(),G=!1,x=null,t("."+te).removeData(Y).removeClass(te),t(e).unbind("click."+Z))},J.element=function(){return t(_.el)},J.settings=X)})(jQuery,document,window);



/*
 *  Lightbox
 */
jQuery(document).ready(fv_player_lightbox_bind);
jQuery(document).ajaxComplete(fv_player_lightbox_bind);

function fv_player_lightbox_bind(){
 
  if( typeof(jQuery().fv_player_pro_colorbox) == "undefined" ) return;
  
  function fv_player_colorbox_class( that ) {
    that = jQuery(that);
    if( that.attr('data-class') && that.attr('data-class').length > 0 ) return that.attr('data-class');
    return false;
  } 
  
  function fv_player_colorbox_rel( that ) {
    that = jQuery(that);
    if( that.attr('rel') && that.attr('rel').length > 0 ) return that.attr('rel');
    return 'group1';
  }  
  
  function fv_player_colorbox_title( that ) {
    that = jQuery(that);
    if( typeof(that.attr('title')) == "undefined" && typeof(that.find('img').attr('alt')) == "undefined" ) {
      return false;
    }
    if( that.attr('title') && that.attr('title').length > 0 ) return that.attr('title');
    if( that.find('img') && that.find('img').attr('alt') && that.find('img').attr('alt').length > 0 ) {
      return that.find('img').attr('alt');
    } else {
      return false;
    }    
  }
  
  function fv_player_colorbox_keyboard( that ) {
    var api = jQuery('#fv_player_pro_boxLoadedContent').find('.flowplayer').data("flowplayer");
    if( api && api.ready ) return false;
    return true;
  }
  
  var defaults = {
    rel: function() { return fv_player_colorbox_rel(this) },
    current: "{current} of {total}",      
    onLoad: fv_lightbox_flowplayer_shutdown,
    onCleanup: fv_lightbox_flowplayer_shutdown,
    title: function() { return fv_player_colorbox_title(this) },
    href: function() { return fv_player_colorbox_scrset(this) },
    className: function() { return fv_player_colorbox_class(this) },    
    arrowKey: function() { return fv_player_colorbox_keyboard(this) }
  };

  /*
   * Lightbox
   */
  if( fv_player_lightbox.lightbox_images ) {
    
    jQuery("a[data-colorbox]").each( function() {
      jQuery(this).attr('href', jQuery(this).attr('data-colorbox') );
    });
    
    //Lightbox for images href="*.jpg"
    var args = jQuery.extend( {}, defaults );
    args.maxHeight = '100%';
    args.maxWidth = '100%';
    args.initialHeight = 48;
    args.initialWidth = 96;
    args.scrolling = false;
    jQuery(".colorbox, .lightbox").filter(function() {
        return this.href.match(/\.(png|jpg|jpeg|gif|webp)/i);
    }).fv_player_pro_colorbox( args );
    
    //Lightbox for non image divs href="#loginForm"
    args.inline = true;
    jQuery(".colorbox[href^='#'], .lightbox[href^='#']").fv_player_pro_colorbox( args );    
    
    //Lightbox external sites href="example.com"    
    var args2 = jQuery.extend( {}, defaults );
    args2.height = '80%';
    args2.width = '80%';
    args2.iframe = true;
    
    jQuery(".colorbox, .lightbox").filter(function() {
      return !this.href.match(/\.(png|jpg|jpeg|gif|webp)/i)
    }).not('[href^="#"]').fv_player_pro_colorbox( args2 );
    
  }
  
  /*
   * Lightbox videos
   */
  jQuery(".flowplayer.lightbox-starter").each( function() {
    var player = jQuery(this);
    if( parseInt(player.css('width')) < 10 || parseInt(player.css('height')) < 10 ) {
      //if (!parseInt(origRatio, 10))
      var ratio = jQuery('.fp-ratio', player);
      if( ratio.length < 1){
        player.append('<div class="fp-ratio"></div>');
        ratio = jQuery('.fp-ratio', player);
      }
      ratio.css("paddingTop", player.data('ratio') * 100 + "%");
    }
    //if (!support.inlineBlock) $("object", root).height(root.height());  
  } );
  
  var args3 = jQuery.extend( {}, defaults );
  args3.href = function(){ return this.getAttribute('data-fv-lightbox')||this.getAttribute('href'); }
  args3.inline = true;
  args3.maxHeight = '100%';
  args3.maxWidth = '100%';
  args3.initialHeight = 48;
  args3.initialWidth = 96;
  args3.scrolling = false;
  args3.innerWidth = fv_player_lightbox_width;
  args3.innerHeight = 'auto';
  
  jQuery("a[id ^=fv_flowplayer_][id $=_lightbox_starter], .flowplayer.lightbox-starter").fv_player_pro_colorbox(args3).addClass('et_smooth_scroll_disabled');

  
  var fv_player_lightbox_fresh = true;
  jQuery(document).bind('fv_player_pro_box_complete', function(e){
    
    if( typeof(flowplayer) == "undefined" ) return;
    
    if( fv_player_lightbox_fresh && jQuery('#fv_player_pro_boxLoadedContent').find('.flowplayer').data("flowplayer") && !flowplayer.support.touch ) {
      var api = jQuery('#fv_player_pro_boxLoadedContent').find('.flowplayer').data("flowplayer");
      api.load();
    } 
    fv_player_lightbox_fresh = false;
  });
  jQuery(document).bind('fv_player_pro_box_closed', function(e){
    fv_player_lightbox_fresh = true;
  });  
}


//  todo: move to new code style
if( typeof(flowplayer) != "undefined" ) { //  stop lightbox from playing if it's closed
  flowplayer(function (api,root) {
    root = jQuery(root);    
    if( !root.hasClass('lightboxed') ) return; //  only work for lightboxed video!
 
    api.bind("ready", function (e, api, video) {      
      if( root.parent().attr('id') != 'fv_player_pro_boxLoadedContent') {
        api.one('progress', function(e,api) {
          api.pause();
          //FV_Flowplayer_Pro.log('FV FP: Lightbox: stoping closed video!'); 
        });
      }
    });       
  }); 
}




/* Colorbox resize function */


var fv_lightbox_resizeTimer;

function fv_lightbox_resizeWidth() {
  jQuery.fv_player_pro_colorbox.resize({width:jQuery(window).width(), height:'auto'});
  var newHeight = ( jQuery('#fv_player_pro_boxLoadedContent').children().attr('data-ratio') ) ? jQuery('#fv_player_pro_boxLoadedContent').innerWidth()*jQuery('#fv_player_pro_boxLoadedContent').children().attr('data-ratio') : 'auto';
  jQuery('#fv_player_pro_boxLoadedContent').children().css({width:jQuery('#fv_player_pro_boxLoadedContent').innerWidth(), height:newHeight});
  jQuery.fv_player_pro_colorbox.resize({width:jQuery(window).width(), innerHeight:jQuery('#fv_player_pro_boxLoadedContent').children().height()});
}

function fv_lightbox_resizeHeight() {
  //FV_Flowplayer_Pro.log('Too small vertically');
  jQuery.fv_player_pro_colorbox.resize({width:'auto', height:jQuery(window).height()});
  var newWidth = ( jQuery('#fv_player_pro_boxLoadedContent').children().attr('data-ratio') ) ? jQuery('#fv_player_pro_boxLoadedContent').innerHeight()/jQuery('#fv_player_pro_boxLoadedContent').children().attr('data-ratio') : 'auto';
  jQuery('#fv_player_pro_boxLoadedContent').children().css({width:newWidth, height:jQuery('#fv_player_pro_boxLoadedContent').innerHeight()});
  jQuery.fv_player_pro_colorbox.resize({innerWidth:jQuery('#fv_player_pro_boxLoadedContent').children().width(), height:jQuery(window).height()});
}
      
function fv_lightbox_resizeColorBox() {

    if (fv_lightbox_resizeTimer) clearTimeout(fv_lightbox_resizeTimer);
    fv_lightbox_resizeTimer = setTimeout(function() {
      if (jQuery('#fv_player_pro_boxOverlay').is(':visible')) {

        var windowWidth = jQuery(window).width() - 28;
        var windowHeight = jQuery(window).height() - 62;
        if(jQuery('#fv_player_pro_boxLoadedContent').children('img').length > 0 ) {
          var img = new Image()
          img.src = jQuery('#fv_player_pro_boxLoadedContent').children('img').attr('src'); 
          var max_width = img.width, max_height = img.height;
        }else if(jQuery('#fv_player_pro_boxLoadedContent').children('iframe').length > 0 ) {
           var max_width = jQuery(window).width()*0.8;
           var max_height = jQuery(window).height()*0.8;
        }else if( jQuery('#fv_player_pro_boxLoadedContent').find('.flowplayer').length > 0 ) {
          var objFlowplayer = jQuery('#fv_player_pro_boxLoadedContent').find('.flowplayer');          
          var max_width = parseInt(objFlowplayer.css('max-width'));
          var max_height = parseInt(objFlowplayer.css('max-height'));
        }
        var ratioWindow = windowHeight/windowWidth;
        var ratioContent = max_height/max_width;
        
        //FV_Flowplayer_Pro.log('resizeColorBox '+max_width+'x'+max_height+' into '+jQuery(window).width()+'x'+jQuery(window).height()+' effective '+windowWidth+'x'+windowHeight+' that is '+ratioWindow+' vs '+ratioContent);
        
        if( windowWidth > max_width && windowHeight > max_height ) {
          jQuery.fv_player_pro_colorbox.resize({innerWidth:max_width, innerHeight:max_height});
          jQuery('#fv_player_pro_boxLoadedContent').children().css({width:max_width, height:max_height});
          return;
        }
        
        if( ratioWindow < ratioContent ) {
          if( windowHeight <= max_height ) {
            fv_lightbox_resizeHeight();
          } else if( windowWidth <= max_width ) {
            fv_lightbox_resizeWidth();
          } 
        } else {
          if( windowWidth <= max_width ) {
            fv_lightbox_resizeWidth();
          } else if( windowHeight <= max_height ) {
            fv_lightbox_resizeHeight();
          }  
        }
      }
    }, 300)
}

jQuery(window).resize(fv_lightbox_resizeColorBox);
if( document.addEventListener ) {      
  window.addEventListener("orientationchange", fv_lightbox_resizeColorBox, false);
}

function fv_lightbox_flowplayer_shutdown() {
  setTimeout( 'fv_lightbox_resizeColorBox', 100 );  
  
  if( typeof('flowplayer') == "undefined" ) {
    return;
  }

  jQuery('.flowplayer').each( function() {
    var api = jQuery(this).data("flowplayer");
    if( typeof(api) == "undefined") {
      return;
    }
    if( api.ready ) {
      api.unload();
    }
    if( api.loading  ) {
      api.one('ready',function(){
        if(api.engine.engineName === 'fvyoutube')
          api.unload();
      })
    }
  } );
}

function fv_player_lightbox_width() {
  var elFlowplayer = jQuery( jQuery(this).data('fv-lightbox') || jQuery(this).attr('href') );
  return parseInt(elFlowplayer.css('max-width'));  
}

/*
 *  https://github.com/albell/parse-srcset, 1.0.2
 */

/**
 * Srcset Parser
 *
 * By Alex Bell |  MIT License
 *
 * JS Parser for the string value that appears in markup <img srcset="here">
 *
 * @returns Array [{url: _, d: _, w: _, h:_}, ...]
 *
 * Based super duper closely on the reference algorithm at:
 * https://html.spec.whatwg.org/multipage/embedded-content.html#parse-a-srcset-attribute
 *
 * Most comments are copied in directly from the spec
 * (except for comments in parens).
 */
if ( typeof(parseSrcset) == "undefined") {
  
!function(a,b){"function"==typeof define&&define.amd?define([],b):"object"==typeof module&&module.exports?module.exports=b():a.parseSrcset=b()}(this,function(){return function(a){function b(a){return" "===a||"\t"===a||"\n"===a||"\f"===a||"\r"===a}function c(b){var c,d=b.exec(a.substring(p));if(d)return c=d[0],p+=c.length,c}function r(){for(c(e),m="",n="in descriptor";;){if(o=a.charAt(p),"in descriptor"===n)if(b(o))m&&(l.push(m),m="",n="after descriptor");else{if(","===o)return p+=1,m&&l.push(m),void s();if("("===o)m+=o,n="in parens";else{if(""===o)return m&&l.push(m),void s();m+=o}}else if("in parens"===n)if(")"===o)m+=o,n="in descriptor";else{if(""===o)return l.push(m),void s();m+=o}else if("after descriptor"===n)if(b(o));else{if(""===o)return void s();n="in descriptor",p-=1}p+=1}}function s(){var c,d,e,f,h,m,n,o,p,b=!1,g={};for(f=0;f<l.length;f++)h=l[f],m=h[h.length-1],n=h.substring(0,h.length-1),o=parseInt(n,10),p=parseFloat(n),i.test(n)&&"w"===m?((c||d)&&(b=!0),0===o?b=!0:c=o):j.test(n)&&"x"===m?((c||d||e)&&(b=!0),p<0?b=!0:d=p):i.test(n)&&"h"===m?((e||d)&&(b=!0),0===o?b=!0:e=o):b=!0;b?console&&console.log&&console.log("Invalid srcset descriptor found in '"+a+"' at '"+h+"'."):(g.url=k,c&&(g.w=c),d&&(g.d=d),e&&(g.h=e),q.push(g))}for(var k,l,m,n,o,d=a.length,e=/^[ \t\n\r\u000c]+/,f=/^[, \t\n\r\u000c]+/,g=/^[^ \t\n\r\u000c]+/,h=/[,]+$/,i=/^\d+$/,j=/^-?(?:[0-9]+|[0-9]*\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/,p=0,q=[];;){if(c(f),p>=d)return q;k=c(g),l=[],","===k.slice(-1)?(k=k.replace(h,""),s()):r()}}});

}

/*
 *  Check if any of the retina images is not big enough for full-screen lightbox view.
 *  However, if the found image is not at least 2/3 of the screen size, it won't be used.
 *  Then it simply uses href image
 */
function fv_player_colorbox_scrset(args) {
  var src = jQuery(args).attr('href');
  if( src.match(/\.(png|jpg|jpeg|gif|webp)/i) ){
    var aSources = false;
    var srcset = jQuery(args).find('img[srcset]');
    if ( srcset.length > 0 ) {
      aSources = parseSrcset(srcset.attr('srcset'));
    } else {
      srcset = jQuery(args).find('img[data-lazy-srcset]');
      if ( srcset.length > 0 ) {
        aSources = parseSrcset(srcset.attr('data-lazy-srcset'));
      }
    }
    
    if( jQuery(args).attr('data-colorbox-srcset') ) {
      var aHrefSources = parseSrcset(jQuery(args).attr('data-colorbox-srcset'));
      aSources = aSources.concat(aHrefSources);
    }
    
    if( aSources ) {
      var original_width = 0;
      if( jQuery('img',args).length > 0 ) {
        aSources.push( { url: jQuery('img',args)[0].src, d: 1, w: jQuery('img',args)[0].naturalWidth, h: jQuery('img',args)[0].naturalHeight } );
        original_width = jQuery('img',args)[0].naturalWidth;
      }      
      
      var find = jQuery(window).width() > jQuery(window).height() ? jQuery(window).width() : jQuery(window).height();
      var ratio = typeof(window.devicePixelRatio) != "undefined" ? window.devicePixelRatio : 1;
      find = find * ratio;
      var win = -1;      
      
      jQuery(aSources).each( function(k,v) {
        //  todo: ignore crop sizes!
        
        if( !v.w && original_width > 0 && v.d > 0 ) aSources[k].w = original_width * v.d;
        if( win == -1 || Math.abs(aSources[k].w - find) < Math.abs(aSources[win].w - find) ){
          win = k;
        }
      });
      
      if( aSources[win].w*1.5 > find ) {
        src = aSources[win].url;
      }
    }
    
  }
  return src;
}