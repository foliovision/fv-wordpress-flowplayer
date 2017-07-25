(function ($) {
  ('use strict');
  
  /*
   * Skin live preview
   */
  function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? [
      parseInt(result[1], 16),
      parseInt(result[2], 16),
      parseInt(result[3], 16)
    ] : null;
  }
  
  $(document).ready(function () {
    $('[data-fv-preview]').on('input change', function () {
      
      var style = '';
      
      $('[data-fv-preview]').each(function () {
        
        if ($(this).attr('name') === 'player-position') {
          if ($(this).val() === 'left')
            style += $(this).data('fv-preview');
        
        } else if ($(this).attr('name') === 'subtitleBgColor') {
          var replacement = hexToRgb($(this).val());
          replacement.push($('#subtitleBgAlpha').val());
          style += $(this).data('fv-preview').replace(/%val%/g, replacement.join(', '));
        }else if($(this).attr('name')=== 'hasBorder'){
          if($(this).prop('checked')){
            style += $(this).data('fv-preview').replace(/%val%/g, '1');
          }else{
            style += $(this).data('fv-preview').replace(/%val%/g, '0');
          }
            
        } else {
          style += $(this).data('fv-preview').replace(/%val%/g, $(this).val());
        }
      }, 0);
      $('#fv-style-preview').html(style);
    }).trigger('input');
    
    $('#playlist-design').on('change', function () {
      var match = jQuery('.fp-playlist-external').attr('class').match(/fv-playlist-design-\S+/)
      if( match ) {
        jQuery('.fp-playlist-external').removeClass(match[0]);
      }
      jQuery('.fp-playlist-external').removeClass('visible-captions');
      jQuery('.fp-playlist-external').addClass('fv-playlist-design-'+$(this).val());
    });
  });

  
}(jQuery));