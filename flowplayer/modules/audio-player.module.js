/*
 *  Audio player support
 *
 *  Makes sure the controls are visible at all times as there is no video showing up.
 */
flowplayer(function(api, root) {
  root = jQuery(root);
  var bean = flowplayer.bean;

  if( root.hasClass('is-audio') ) {
    bean.off(root[0], "mouseenter");
    bean.off(root[0], "mouseleave");
    root.removeClass('is-mouseout');
    root.addClass('fixed-controls').addClass('is-mouseover');

    api.on('error', function (e,api, error) {    
      jQuery('.fp-message',root).html( jQuery('.fp-message',root).html().replace(/video/,'audio') );
    });

    root.on('click', function(e) {
      if( !api.ready) {
        e.preventDefault();
        e.stopPropagation();
        api.load();
      }
    })
  }
})