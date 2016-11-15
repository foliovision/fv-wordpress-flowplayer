var FVFP_iStoreWidth = 0;
var FVFP_iStoreHeight = 0;  
var FVFP_sStoreRTMP = 0;   
var FVFP_sWidgetId;

var FVFP_oPreviewDebounce;
jQuery(document).ready(function($){ 
  if( jQuery().fv_player_box ) {     
    $(document).on( 'click', '.fv-wordpress-flowplayer-button', function(e) {
      e.preventDefault();
      $.fv_player_box( {
        top: "100px",
        initialWidth: 1100,
        initialHeight: 50,
        width:"1100px",
        height:"100px",
        href: "#fv-player-shortcode-editor",
        inline: true,
        title: 'Add FV Player',
        onComplete : fv_wp_flowplayer_edit,
        onClosed : fv_wp_flowplayer_on_close,
        onOpen: function(){
          jQuery("#fv_player_box").addClass("fv-flowplayer-shortcode-editor");
          jQuery("#cboxOverlay").addClass("fv-flowplayer-shortcode-editor");
        }
      } );
      FVFP_sWidgetId = $(this).data().number;
    });
    
  }
  
  /* 
   * NAV TABS 
   */
  $('.fv-player-tabs-header a').click( function() {
    $('.fv-player-tabs-header a').removeClass('nav-tab-active');
    $(this).addClass('nav-tab-active').show();
    $( '.fv-player-tabs > .fv-player-tab' ).hide();
    $( '.' + $(this).data('tab') ).show();
    
    fv_wp_flowplayer_dialog_resize();
  });
  
  /* 
   * Select playlist item 
   */
  $(document).on('click','.fv-player-tab-playlist tr td', function(e) {
    var new_index = $(this).parents('tr').index();
    jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);    
   
    $('a[data-tab=fv-player-tab-video-files]').click();    
    
    $('.fv-player-tab-video-files table').hide();
    $('.fv-player-tab-video-files table').eq(new_index).show();
    
    $('.fv-player-tab-subtitles table').hide();
    $('.fv-player-tab-subtitles table').eq(new_index).show();
    
    
    if($('.fv-player-tab-playlist [data-index]').length > 1){
      $('.fv-player-playlist-item-title').html('Playlist item no. ' + ++new_index);
      $('.playlist_edit').html($('.playlist_edit').data('edit'));
      
      jQuery('#fv-player-shortcode-editor-editor').attr('class','is-playlist');
    }else{
      $('.playlist_edit').html($('.playlist_edit').data('create'));
      
      jQuery('#fv-player-shortcode-editor-editor').attr('class','is-singular');
    }

    fv_wp_flowplayer_submit(true);
  });
  $(document).on('input','.fv_wp_flowplayer_field_width', function(e) {
    $('.fv_wp_flowplayer_field_width').val(e.target.value);
  })

  $(document).on('input','.fv_wp_flowplayer_field_height', function(e) {
    $('.fv_wp_flowplayer_field_height').val(e.target.value);
  })
  
  /* 
   * Remove playlist item 
   */
  $(document).on('click','.fv-player-tab-playlist tr .fvp_item_remove', function(e) {
    e.stopPropagation();
    var index = $(e.target).parents('[data-index]').attr('data-index');
    $(e.target).parents('[data-index]').remove();
    jQuery('.fv-player-tab-video-files table[data-index=' + index + ']').remove();
    jQuery('.fv-player-tab-subtitles table[data-index=' + index + ']').remove();
    fv_wp_flowplayer_submit(true);
  });
  
  $('.fv-player-tab-playlist table tbody').sortable({
    start: function( event, ui ) {
      FVFP_iStoreWidth = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val();	  
      FVFP_iStoreHeight = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val();	  
      FVFP_sStoreRTMP = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val();
    },
    update: function( event, ui ) {    
      var items = []; 
      $('.fv-player-tab-playlist table tbody tr').each(function(){
        var index = $(this).data('index');
        items.push({
          items : jQuery('.fv-player-tab-video-files table[data-index=' + index + ']').clone(),
          subs : jQuery('.fv-player-tab-subtitles table[data-index=' + index + ']').clone(),
        })
        jQuery('.fv-player-tab-video-files table[data-index=' + index + ']').remove();
        jQuery('.fv-player-tab-subtitles table[data-index=' + index + ']').remove();
      })
      
      for(var  i in items){
        if(!items.hasOwnProperty(i))continue;
        jQuery('.fv-player-tab-video-files').append(items[i].items);
        jQuery('.fv-player-tab-subtitles').append(items[i].subs);
      }
     
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val( FVFP_iStoreWidth );
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val( FVFP_iStoreHeight );
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val( FVFP_sStoreRTMP );
      fv_wp_flowplayer_submit(true);
    },
    axis: 'y',
    //handle: '.fvp_item_sort',
    containment: ".fv-player-tab-playlist"
  });
  
  /*
   * Uploader 
   */
  var fv_flowplayer_uploader;
  var fv_flowplayer_uploader_button;

  $(document).on( 'click', '#fv-player-shortcode-editor .button.add_media', function(e) {
      e.preventDefault();
      
      fv_flowplayer_uploader_button = jQuery(this);
      jQuery('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
      fv_flowplayer_uploader_button.siblings('input[type=text]').addClass('fv_flowplayer_target' );
                       
      //If the uploader object has already been created, reopen the dialog
      if (fv_flowplayer_uploader) {
          fv_flowplayer_uploader.open();
          return;
      }

      //Extend the wp.media object
      fv_flowplayer_uploader = wp.media.frames.file_frame = wp.media({
          title: 'Add Video',
          button: {
              text: 'Choose'
          },
          multiple: false
      });
      
      fv_flowplayer_uploader.on('open', function() {
        jQuery('.media-frame-title h1').text(fv_flowplayer_uploader_button.text());
      });      

      //When a file is selected, grab the URL and set it as the text field's value
      fv_flowplayer_uploader.on('select', function() {
          attachment = fv_flowplayer_uploader.state().get('selection').first().toJSON();

          $('.fv_flowplayer_target').val(attachment.url);
          $('.fv_flowplayer_target').removeClass('fv_flowplayer_target' );
        
          if( attachment.type == 'video' ) {
            if( typeof(attachment.width) != "undefined" && attachment.width > 0 ) {
              $('#fv_wp_flowplayer_field_width').val(attachment.width);
            }
            if( typeof(attachment.height) != "undefined" && attachment.height > 0 ) {
              $('#fv_wp_flowplayer_field_height').val(attachment.height);
            }
            if( typeof(attachment.fileLength) != "undefined" ) {
              $('#fv_wp_flowplayer_file_info').show();
              $('#fv_wp_flowplayer_file_duration').html(attachment.fileLength);
            }
            if( typeof(attachment.filesizeHumanReadable) != "undefined" ) {
              $('#fv_wp_flowplayer_file_info').show();
              $('#fv_wp_flowplayer_file_size').html(attachment.filesizeHumanReadable);
            }
            
          } else if( attachment.type == 'image' && typeof(fv_flowplayer_set_post_thumbnail_id) != "undefined" ) {
            if( jQuery('#remove-post-thumbnail').length > 0 ){
              return;
            }
            jQuery.post(ajaxurl, {
                action:"set-post-thumbnail",
                post_id: fv_flowplayer_set_post_thumbnail_id,
                thumbnail_id: attachment.id,
                 _ajax_nonce: fv_flowplayer_set_post_thumbnail_nonce,
                cookie: encodeURIComponent(document.cookie)
              }, function(str){
                var win = window.dialogArguments || opener || parent || top;
                if ( str == '0' ) {
                  alert( setPostThumbnailL10n.error );
                } else {
                  jQuery('#postimagediv .inside').html(str);
                  jQuery('#postimagediv .inside #plupload-upload-ui').hide();
                }
              } );
            
          }
          fv_wp_flowplayer_submit(true);
      });

      //Open the uploader dialog
      fv_flowplayer_uploader.open();

  });
  
  fv_player_playlist_item_template = jQuery('.fv-player-tab-playlist table tbody tr').parent().html();
  fv_player_playlist_video_template = jQuery('.fv-player-tab-video-files table.fv-player-playlist-item').parent().html();
  fv_player_playlist_subtitles_template = jQuery('.fv-fp-subtitle').parent().html();
  fv_player_playlist_subtitles_box_template = jQuery('.fv-player-tab-subtitles').html();


  /*
   * Preview
   */
  jQuery(document).on('input', '.fv-player-tabs [name]' ,function(){
    if(FVFP_oPreviewDebounce){
      clearTimeout(FVFP_oPreviewDebounce);
      FVFP_oPreviewDebounce = setTimeout(function(){
        fv_wp_flowplayer_submit(true);
      },700);
    }else{
      fv_wp_flowplayer_submit(true);
      FVFP_oPreviewDebounce = setTimeout(function(){
      },700);
    }
  });
  
  /*
   * End of playlist Actions   
   */
 
  jQuery('#fv_wp_flowplayer_field_actions_end').change(function(){
    var value = jQuery(this).val();
    jQuery('.fv_player_actions_end-toggle').hide().find('[name]').val('');
    
    switch(value){
      case 'redirect': 
        jQuery('#fv_wp_flowplayer_field_' + value).parents('tr').show(); 
        break; 
      case 'popup':
        jQuery('#fv_wp_flowplayer_field_' + value).parents('tr').show();
        jQuery('#fv_wp_flowplayer_field_' + value + '_id').parents('tr').show();
        break;
    }
  });
  
  fv_wp_flowplayer_dialog_resize();
});






var fv_wp_flowplayer_content;
var fv_wp_flowplayer_hTinyMCE;
var fv_wp_flowplayer_oEditor;
var fv_wp_fp_shortcode_remains;
var fv_player_playlist_item_template;
var fv_player_playlist_video_template;
var fv_player_playlist_subtitles_template;
var fv_player_playlist_subtitles_box_template;
var fv_wp_fp_shortcode;


/*
 * Initializes shortcode, removes playlist items, hides elements
 */
function fv_wp_flowplayer_init() {
  
  if( jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').length ){
    fv_wp_flowplayer_content = jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').val();
  } else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length){
    fv_wp_flowplayer_content = jQuery('#content:not([aria-hidden=true])').val();
  } else if( typeof tinymce !== 'undefined' && typeof tinymce.majorVersion !== 'undefined' && typeof tinymce.activeEditor !== 'undefined' && tinymce.majorVersion >= 4 ){
    fv_wp_flowplayer_hTinyMCE = tinymce.activeEditor;
  } else if( typeof tinyMCE !== 'undefined' ) {
    fv_wp_flowplayer_hTinyMCE = tinyMCE.getInstanceById('content');
  } else if(typeof(FCKeditorAPI) !== 'undefined' ){
    fv_wp_flowplayer_oEditor = FCKeditorAPI.GetInstance('content');    
  }
  
  jQuery('#fv_wp_flowplayer_file_info').hide();
  jQuery(".fv_wp_flowplayer_field_src_2_wrapper").hide();
  jQuery("#fv_wp_flowplayer_field_src_2_uploader").hide();
  jQuery(".fv_wp_flowplayer_field_src_1_wrapper").hide();
  jQuery("#fv_wp_flowplayer_field_src_1_uploader").hide();
  jQuery("#add_format_wrapper").show();
  jQuery("#add_rtmp_wrapper").show(); 
  jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").hide();
  jQuery('#fv-player-shortcode-editor-preview').attr('class','preview-no');
  
  jQuery('.fv-player-tab-video-files table').each( function(i,e) {
    if( i == 0 ) return;
    jQuery(e).remove();
  } );
  
  jQuery('.fv-player-tab-playlist table tbody tr').each( function(i,e) {
    if( i == 0 ) return;
    jQuery(e).remove();
  } );
  
  jQuery('.fv-player-tab-subtitles').html(fv_player_playlist_subtitles_box_template);
  
  jQuery('.fv_wp_flowplayer_field_subtitles_lang').val(0);

  /**
   * TABS 
   */ 
  jQuery('#fv-player-shortcode-editor a[data-tab=fv-player-tab-playlist]').hide();
  jQuery('#fv-player-shortcode-editor a[data-tab=fv-player-tab-video-files]').trigger('click');
  jQuery('.nav-tab').show;
  jQuery('#fv-player-shortcode-editor-editor .fv-player-tabs>.fv-player-tab').each(function(){
    if(! jQuery(this).find('tr:not(.fv_player_interface_hide):not(.fv_player_actions_end-toggle)').length ){
      jQuery('.nav-tab').eq(jQuery(this).index()).addClass('fv_player_interface_hide');
    }
  })
  if(jQuery('.nav-tab:not(.fv_player_interface_hide)').length <=1 ){
    jQuery('.nav-tab:not(.fv_player_interface_hide)').addClass('fv_player_interface_hide');
  }
  
  jQuery('#fv-player-shortcode-editor-editor').attr('class','is-singular');
  jQuery('.fv-player-tab-playlist').hide();
  jQuery('.fv-player-playlist-item-title').html('');
  jQuery('.fv-player-tab-video-files table').show();
  
  jQuery('.playlist_edit').html(jQuery('.playlist_edit').data('create'));  
  
  
}

/*
 * Sends new shortcode to editor
 */
function fv_wp_flowplayer_insert( shortcode ) {
  if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length ) {
    fv_wp_flowplayer_content = fv_wp_flowplayer_content .replace(/#fvp_placeholder#/,shortcode);
    fv_wp_flowplayer_set_html( fv_wp_flowplayer_content );
  }else if( fv_wp_flowplayer_content.match( fv_wp_flowplayer_re_edit ) ) {
    fv_wp_flowplayer_content = fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_edit, shortcode )
    fv_wp_flowplayer_set_html( fv_wp_flowplayer_content );
  }
  else {
    if ( fv_wp_flowplayer_content != '' ) {      
      fv_wp_flowplayer_content = fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_insert, shortcode )      
      fv_wp_flowplayer_set_html( fv_wp_flowplayer_content );            
    } else {
      fv_wp_flowplayer_content = shortcode;
      send_to_editor( shortcode );  
    }                                                
  }  
} 

/*
 * Removes playlist item 
 * keywords: remove palylist item
 */
function fv_wp_flowplayer_playlist_remove(link) {
  FVFP_iStoreWidth = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val();	  
  FVFP_iStoreHeight = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val();	  
  FVFP_sStoreRTMP = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val();
	jQuery(link).parents('table').remove();	
  jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val( FVFP_iStoreWidth );
  jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val( FVFP_iStoreHeight );
  jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val( FVFP_sStoreRTMP );	
	return false;
}

/*
 * Adds playlist item
 * keywords: add playlist item
 */
function fv_flowplayer_playlist_add( sInput, sCaption ) {
  jQuery('.fv-player-tab-playlist table tbody').append(fv_player_playlist_item_template);
  var ids = jQuery('.fv-player-tab-playlist [data-index]').map(function() {
    return parseInt(jQuery(this).attr('data-index'), 10);
  }).get();  
  var newIndex = Math.max.apply(Math, ids) + 1;
  var current = jQuery('.fv-player-tab-playlist table tbody tr').last();
  current.attr('data-index', newIndex);
  current.find('.fvp_item_video').html( 'Video ' + (newIndex + 1) );
  
  jQuery('.fv-player-tab-video-files').append(fv_player_playlist_video_template);
  var new_item = jQuery('.fv-player-tab-video-files table:last');
  new_item.hide();
  //jQuery('.fv-player-tab-video-files table').hover( function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').show(); }, function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').hide(); } );
  
  if( sInput ) {
    aInput = sInput.split(',');
    var count = 0;
    for( var i in aInput ) {
      if( aInput[i].match(/^rtmp:/) ) new_item.find('.fv_wp_flowplayer_field_rtmp_path').val(aInput[i].replace(/^rtmp:/,''));
      else if( aInput[i].match(/\.(jpg|png|gif|jpe|jpeg)$/) ) new_item.find('.fv_wp_flowplayer_field_splash').val(aInput[i]);
      else {
        if( count == 0 ) {
          new_item.find('#fv_wp_flowplayer_field_src').val(aInput[i]);
        } else {
          new_item.find('#fv_wp_flowplayer_field_src_'+count).val(aInput[i]);
        }
        count++;
      }
    }
    if( sCaption ) {
      jQuery('[name=fv_wp_flowplayer_field_caption]',new_item).val(sCaption);
    }
  }
  
  jQuery('.fv-player-tab-subtitles').append(fv_player_playlist_subtitles_box_template);
  jQuery('.fv-player-tab-subtitles table:last').hide();
  jQuery('.fv-player-tab-subtitles table:last').attr('data-index', newIndex);
  
  jQuery('.fv-player-tab-video-files table:last').attr('data-index', newIndex);
  
  fv_wp_flowplayer_dialog_resize(); 
  return false;
}

/*
 * Displays playlist editor
 * keywords: show playlist 
 */
function fv_flowplayer_playlist_show() {
  
  jQuery('#fv-player-shortcode-editor-editor').attr('class','is-playlist-active');
  jQuery('.fv-player-tabs-header .nav-tab').attr('style',false);
  jQuery('a[data-tab=fv-player-tab-playlist]').click()
  
  //fills playlist edistor table from individual video tables
  var video_files = jQuery('.fv-player-tab-video-files table');
  video_files.each( function() {    
    var current = jQuery(this);
    var currentUrl = current.find('#fv_wp_flowplayer_field_src').val();
    if(!currentUrl.length){
      currentUrl = 'Video ' + (jQuery(this).index() + 1);
    }
    var playlist_row = jQuery('.fv-player-tab-playlist table tbody tr').eq( video_files.index(current) );
    
    current.attr('data-index', current.index() );
    playlist_row.attr('data-index', current.index() );
    
    var video_preview = current.find('#fv_wp_flowplayer_field_splash').val()
    playlist_row.find('.fvp_item_video').html( video_preview.length ? '<img src="' + video_preview + '" />' : currentUrl.split("/").pop()  );
    playlist_row.find('.fvp_item_caption').html( current.find('#fv_wp_flowplayer_field_caption').val() );
  });
  //initial indexing
  jQuery('.fv-player-tab.fv-player-tab-subtitles table').each(function(){
    jQuery(this).attr('data-index', jQuery(this).index() );
  })
  
  jQuery('.fv-player-tab-playlist').show(); 
  
  fv_wp_flowplayer_dialog_resize();
}

/*
 * Adds another language to subtitle menu
 */
function fv_flowplayer_language_add( sInput, sLang ,iTabIndex ) {
  if(!iTabIndex){
    iTabIndex = 0;
  }
  var oTab = jQuery('.fv-fp-subtitles').eq(iTabIndex);
  oTab.append( fv_player_playlist_subtitles_template ); 
  jQuery('.fv-fp-subtitle:last' , oTab).hover( function() { jQuery(this).find('.fv-fp-subtitle-remove').show(); }, function() { jQuery(this).find('.fv-fp-subtitle-remove').hide(); } );
  
  if( sInput ) {
    jQuery('.fv-fp-subtitle:last input.fv_wp_flowplayer_field_subtitles' , oTab ).val(sInput);
  }
  
  if ( sLang ) {
    jQuery('.fv-fp-subtitle:last select.fv_wp_flowplayer_field_subtitles_lang' , oTab).val(sLang);
  }
  
  jQuery('.fv-fp-subtitle:last .fv-fp-subtitle-remove' , oTab).click(function(){
    
    if(jQuery(this).parents('.fv-fp-subtitles').find('.fv-fp-subtitle').length > 1){
      jQuery(this).parents('.fv-fp-subtitle').remove();
    }else{
      jQuery(this).parents('.fv-fp-subtitle').find('[name]').val('');
    }
    fv_wp_flowplayer_dialog_resize();
    
  })
  
  fv_wp_flowplayer_dialog_resize();
  return false;
}

/*
 * removes previous values from editor
 * fills new values from shortcode
 */
function fv_wp_flowplayer_edit() {	
  
  var dialog = jQuery('#fv_player_box.fv-flowplayer-shortcode-editor');
  dialog.removeAttr('tabindex');
  
  fv_wp_flowplayer_init();
  
  jQuery("#fv-player-shortcode-editor input").each( function() { jQuery(this).val( '' ); jQuery(this).attr( 'checked', false ) } );
  jQuery("#fv-player-shortcode-editor textarea").each( function() { jQuery(this).val( '' ) } );
  jQuery('#fv-player-shortcode-editor select').prop('selectedIndex',0);
  jQuery("[name=fv_wp_flowplayer_field_caption]").each( function() { jQuery(this).val( '' ) } );
  jQuery(".fv_player_field_insert-button").attr( 'value', 'Insert' );
  
  if(jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').length){
    if(fv_wp_flowplayer_content.match(/\[/) ) {
      fv_wp_flowplayer_content = '[<'+fvwpflowplayer_helper_tag+' rel="FCKFVWPFlowplayerPlaceholder">&shy;</'+fvwpflowplayer_helper_tag+'>'+fv_wp_flowplayer_content.replace('[','')+'';
    } else {
      fv_wp_flowplayer_content =   '<'+fvwpflowplayer_helper_tag+' rel="FCKFVWPFlowplayerPlaceholder">&shy;</'+fvwpflowplayer_helper_tag+'>'+fv_wp_flowplayer_content+'';
    }
    
  }else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length ){    
    var bFound = false;
    var position = jQuery('#content:not([aria-hidden=true])').prop('selectionStart');
    for(var start = position; start--; start >= 0){
      if( fv_wp_flowplayer_content[start] == '['){
        bFound = true; break;
      }else if(fv_wp_flowplayer_content[start] == ']'){
        break
      }
    }
    var shortcode = [];
   
    if(bFound){    
      var temp = fv_wp_flowplayer_content.slice(start);
      temp = temp.match(/^\[fvplayer[^\[\]]*]?/);
      if(temp){
        shortcode = temp;
        fv_wp_flowplayer_content = fv_wp_flowplayer_content.slice(0, start) + '#fvp_placeholder#' + fv_wp_flowplayer_content.slice(start).replace(/^\[[^\[\]]*]?/, '');
      }else{
        fv_wp_flowplayer_content = fv_wp_flowplayer_content.slice(0, position) + '#fvp_placeholder#' + fv_wp_flowplayer_content.slice(position);
      }
    }else{
      fv_wp_flowplayer_content = fv_wp_flowplayer_content.slice(0, position) + '#fvp_placeholder#' + fv_wp_flowplayer_content.slice(position);
    }   
  }else	if( fv_wp_flowplayer_hTinyMCE == undefined || tinyMCE.activeEditor.isHidden() ) {  
    fv_wp_flowplayer_content = fv_wp_flowplayer_oEditor.GetHTML();    
    if (fv_wp_flowplayer_content.match( fv_wp_flowplayer_re_insert ) == null) {
      fv_wp_flowplayer_oEditor.InsertHtml('<'+fvwpflowplayer_helper_tag+' rel="FCKFVWPFlowplayerPlaceholder">&shy;</'+fvwpflowplayer_helper_tag+'>');
      fv_wp_flowplayer_content = fv_wp_flowplayer_oEditor.GetHTML();    
    }           
	}
	else {
    fv_wp_flowplayer_content = fv_wp_flowplayer_hTinyMCE.getContent();
    fv_wp_flowplayer_hTinyMCE.settings.validate = false;
    if (fv_wp_flowplayer_content.match( fv_wp_flowplayer_re_insert ) == null) {   
      var tags = ['b','span','div'];
      for( var i in tags ){
        fv_wp_flowplayer_hTinyMCE.execCommand('mceInsertContent', false,'<'+tags[i]+' data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></'+tags[i]+'>');
        fv_wp_flowplayer_content = fv_wp_flowplayer_hTinyMCE.getContent();
        
        fv_wp_flowplayer_re_edit = new RegExp( '\\[f[^\\]]*?<'+tags[i]+'[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?</'+tags[i]+'>.*?[^\]\\]', "mi" );
        fv_wp_flowplayer_re_insert = new RegExp( '<'+tags[i]+'[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?</'+tags[i]+'>', "gi" );
        
        if( fv_wp_flowplayer_content.match(fv_wp_flowplayer_re_insert) ){
          break;
        }
        
      }
      
    }
    fv_wp_flowplayer_hTinyMCE.settings.validate = true;		
	}
	
  
  var content = fv_wp_flowplayer_content.replace(/\n/g, '\uffff');          
  if(typeof(shortcode) == 'undefined'){
    var shortcode = content.match( fv_wp_flowplayer_re_edit );  
  }
 
  if( shortcode != null ) { 
    shortcode = shortcode.join('');
    shortcode = shortcode.replace(/^\[|\]+$/gm,'');
  	shortcode = shortcode.replace( fv_wp_flowplayer_re_insert, '' );
  	
  	shortcode = shortcode.replace( /\\'/g,'&#039;' );

	  var shortcode_parse_fix = shortcode.replace(/(popup|ad)='[^']*?'/g, '');
	  shortcode_parse_fix = shortcode_parse_fix.replace(/(popup|ad)="(.*?[^\\\\/])"/g, '');
    fv_wp_fp_shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );  	
  	
    var srcurl = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'src' );
    var srcurl1 = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'src1' );
    var srcurl2 = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'src2' );
    
    var srcrtmp = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'rtmp' );
    var srcrtmp_path = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'rtmp_path' );
  	                                                                          
    var iwidth = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'width' );                                                                              
    var iheight = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'height' );    
    
    var sad_skip = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_skip' );
    var salign = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'align' );
    var scontrolbar = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'controlbar' );
    var sautoplay = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'autoplay' );
    var sliststyle = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'liststyle' );
    var sembed = fv_wp_flowplayer_shortcode_parse_arg( shortcode_parse_fix, 'embed' );
    var sloop = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'loop' );
    var slive = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'live' );
    var sspeed = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'speed' );
    var ssplash = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'splash' );    
    var ssplashend = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'splashend' );
        
    var ssubtitles = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'subtitles' );
    var aSubtitles = shortcode.match(/subtitles_[a-z][a-z]+/g);
    for( var i in aSubtitles ){
      fv_wp_flowplayer_shortcode_parse_arg( shortcode, aSubtitles[i], false, fv_wp_flowplayer_subtitle_parse_arg );
    }
    if(!aSubtitles){
      fv_flowplayer_language_add(false, false );
    }
    
    var smobile = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'mobile' );
    var sredirect = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'redirect' );
    
    var sCaptions = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'caption' );
    var sPlaylist = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'playlist' );

    var sad = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad', true );
    var iadwidth = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_width' );
    var iadheight = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'ad_height' );
    
  	if( srcrtmp != null && srcrtmp[1] != null ) {
  		jQuery(".fv_wp_flowplayer_field_rtmp").val( srcrtmp[1] );
  		jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").css( 'display', 'table-row' );
  		document.getElementById("add_rtmp_wrapper").style.display = 'none';   
  	}
    if( srcrtmp_path != null && srcrtmp_path[1] != null ) {
  		jQuery(".fv_wp_flowplayer_field_rtmp_path").val( srcrtmp_path[1] );
      jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").css( 'display', 'table-row' );
      document.getElementById("add_rtmp_wrapper").style.display = 'none';           
    }
    
    var playlist_row = jQuery('.fv-player-tab-playlist tbody tr:first')    
    
    if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
    if( srcurl1 != null && srcurl1[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_src_1").value = srcurl1[1];
      jQuery(".fv_wp_flowplayer_field_src_1_wrapper").css( 'display', 'table-row' );
      //document.getElementById("fv_wp_flowplayer_field_src_1_uploader").style.display = 'table-row';
      if( srcurl2 != null && srcurl2[1] != null ) {
    		document.getElementById("fv_wp_flowplayer_field_src_2").value = srcurl2[1];
        jQuery(".fv_wp_flowplayer_field_src_2_wrapper").css( 'display', 'table-row' );
        //document.getElementById("fv_wp_flowplayer_field_src_2_uploader").style.display = 'table-row';
        document.getElementById("add_format_wrapper").style.display = 'none';        
      }            
    }     
    
  	if( srcurl != null && srcurl[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
      playlist_row.find('.fvp_item_video').html( srcurl[1] );
    }
      

    
    
  	if( iheight != null && iheight[1] != null ) jQuery(".fv_wp_flowplayer_field_height").val(iheight[1]);
  	if( iwidth != null && iwidth[1] != null ) jQuery(".fv_wp_flowplayer_field_width").val(iwidth[1]);
  	if( sautoplay != null && sautoplay[1] != null ) {
  		if (sautoplay[1] == 'true') 
        document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex = 1;
      if (sautoplay[1] == 'false') 
        document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex = 2;
    }
  	if( sliststyle != null && sliststyle[1] != null ) {
  		if (sliststyle[1] == 'tabs') 
        document.getElementById("fv_wp_flowplayer_field_liststyle").selectedIndex = 1;
        if (sliststyle[1] == 'prevnext') 
        document.getElementById("fv_wp_flowplayer_field_liststyle").selectedIndex = 2;
        if (sliststyle[1] == 'vertical') 
        document.getElementById("fv_wp_flowplayer_field_liststyle").selectedIndex = 3;
        if (sliststyle[1] == 'horizontal') 
        document.getElementById("fv_wp_flowplayer_field_liststyle").selectedIndex = 4;
    }    
  	if( sembed != null && sembed[1] != null ) {
  		if (sembed[1] == 'true') 
        document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex = 1;
      if (sembed[1] == 'false') 
        document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex = 2;
    }    
  	if( smobile != null && smobile[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_mobile").value = smobile[1];
      
  	if( ssplash != null && ssplash[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_splash").value = ssplash[1];
      playlist_row.find('.fvp_item_splash').html( '<img width="120" src="'+ssplash[1]+'" />' );
  	}
    
  	if( ssubtitles != null && ssubtitles[1] != null )
  		jQuery(".fv_wp_flowplayer_field_subtitles").eq(0).val( ssubtitles[1] );
    
  	if( sad != null && sad[1] != null ) {
  		sad = sad[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
  		sad = sad.replace(/&amp;/g,'&');
  		document.getElementById("fv_wp_flowplayer_field_ad").value = sad;
  	}  		
  	if( iadheight != null && iadheight[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_ad_height").value = iheight[1];
  	if( iadwidth != null && iadwidth[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_ad_width").value = iwidth[1];
    if( sad_skip != null && sad_skip[1] != null && sad_skip[1] == 'yes' )
  		document.getElementById("fv_wp_flowplayer_field_ad_skip").checked = 1;   		
    if( slive != null && slive[1] != null && slive[1] == 'true' )
  		document.getElementById("fv_wp_flowplayer_field_live").checked = 1;
  	if( sspeed != null && sspeed[1] != null ) {
  		if (sspeed[1] == 'buttons') 
        document.getElementById("fv_wp_flowplayer_field_speed").selectedIndex = 1;
      if (sspeed[1] == 'no') 
        document.getElementById("fv_wp_flowplayer_field_speed").selectedIndex = 2;
    } 
    /*
    if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' )
  		document.getElementById("fv_wp_flowplayer_field_splashend").checked = 1;  
    if( sloop != null && sloop[1] != null && sloop[1] == 'true' )
  		document.getElementById("fv_wp_flowplayer_field_loop").checked = 1;
    if( sredirect != null && sredirect[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_redirect").value = sredirect[1];
    */
   
   
    /*
     * Video end dropdown
     */
    document.getElementById("fv_wp_flowplayer_field_popup").parentNode.style.display = 'none'
    var spopup = fv_wp_flowplayer_shortcode_parse_arg( shortcode, 'popup', true );
   
    if( sredirect != null && sredirect[1] != null ){
      document.getElementById("fv_wp_flowplayer_field_actions_end").selectedIndex = 1;  
      document.getElementById("fv_wp_flowplayer_field_redirect").value = sredirect[1];
      jQuery('#fv_wp_flowplayer_field_redirect').parents('tr').show();  
    }else if( sloop != null && sloop[1] != null && sloop[1] == 'true' ){
  		document.getElementById("fv_wp_flowplayer_field_actions_end").selectedIndex = 2;  
    }else if( spopup != null && spopup[1] != null ) { 
      document.getElementById("fv_wp_flowplayer_field_actions_end").selectedIndex = 3;  
      
      spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
      spopup = spopup.replace(/&amp;/g,'&');
      
      jQuery("#fv_wp_flowplayer_field_popup_id").parents('tr').show();
      if (spopup === null || !isNaN(parseInt(spopup)) || spopup === 'no' || spopup === 'random') {
        jQuery("#fv_wp_flowplayer_field_popup_id").val(spopup)
      } else {
        jQuery("#fv_wp_flowplayer_field_popup").val(spopup).parent().show();
      }
      
    }else if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' ){
      document.getElementById('fv_wp_flowplayer_field_actions_end').selectedIndex = 4
    }
      
      
  		
      
   
  	if( salign != null && salign[1] != null ) {
  		if (salign[1] == 'left') 
        document.getElementById("fv_wp_flowplayer_field_align").selectedIndex = 1;
      if (salign[1] == 'right') 
        document.getElementById("fv_wp_flowplayer_field_align").selectedIndex = 2;
    }
    
  	if( scontrolbar != null && scontrolbar[1] != null ) {
  		if (scontrolbar[1] == 'yes' || scontrolbar[1] == 'show' ) 
        document.getElementById("fv_wp_flowplayer_field_controlbar").selectedIndex = 1;
      if (scontrolbar[1] == 'no' || scontrolbar[1] == 'hide' ) 
        document.getElementById("fv_wp_flowplayer_field_controlbar").selectedIndex = 2;
    }       
    
    var aCaptions = false;
    if( sCaptions ) {
      sCaptions[1] = sCaptions[1].replace(/\\;/gi, '<!--FV Flowplayer Caption Separator-->').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
      aCaptions = sCaptions[1].split(';');
      for( var i in aCaptions ){
        aCaptions[i] = aCaptions[i].replace(/\\"/gi, '"');
        aCaptions[i] = aCaptions[i].replace(/\\<!--FV Flowplayer Caption Separator-->/gi, ';');
        aCaptions[i] = aCaptions[i].replace(/<!--FV Flowplayer Caption Separator-->/gi, ';');
      }
      
      var caption = aCaptions.shift();
      jQuery('[name=fv_wp_flowplayer_field_caption]',jQuery('.fv-player-playlist-item').eq(0)).val( caption );
      playlist_row.find('.fvp_item_caption').html( caption );
    }
    
    if( sPlaylist ) {    	
			aPlaylist = sPlaylist[1].split(';');
			for( var i in aPlaylist ) {
        if( typeof(aCaptions) != "undefined" && typeof(aCaptions[i]) != "undefined" ) {
          fv_flowplayer_playlist_add( aPlaylist[i], aCaptions[i] );
        } else {
        	fv_flowplayer_playlist_add( aPlaylist[i] );
        }
			}

    }
    
    if( jQuery('.fv-fp-subtitles .fv-fp-subtitle:first input.fv_wp_flowplayer_field_subtitles').val() == '' ) {
      jQuery('.fv-fp-subtitles .fv-fp-subtitle:first').remove();
    }    
    
    jQuery(document).trigger('fv_flowplayer_shortcode_parse', [ shortcode_parse_fix, fv_wp_fp_shortcode_remains ] );
  	
  	jQuery(".fv_player_field_insert-button").attr( 'value', 'Update' );    
	} else {
    fv_wp_fp_shortcode_remains = '';
  }
  
  jQuery('.fv_wp_flowplayer_playlist_head').hover(
  	function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').show(); }, function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').hide(); } );  
  
  //???
  jQuery('#cboxContent').css('background','white');
  
  
  if(sPlaylist){    
    fv_flowplayer_playlist_show();
  }
  //initial preview
  fv_wp_flowplayer_submit(true);
}


function fv_wp_flowplayer_dialog_resize() {
  var iContentHeight = jQuery('#fv-player-shortcode-editor').height();
  if( iContentHeight < 50 ) iContentHeight = 50;
  if( iContentHeight > jQuery(window).height() - 160 ) iContentHeight = jQuery(window).height() - 160;
  jQuery('#fv-player-shortcode-editor').fv_player_box.resize({width:1100, height:(iContentHeight+50)})
}


function fv_wp_flowplayer_on_close() {
  fv_wp_flowplayer_init();
  fv_wp_flowplayer_set_html( fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_insert, '' ) );
}   


function fv_wp_flowplayer_set_html( html ) {
  if( jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').length ){
    jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').val(html);      
    jQuery('#widget-widget_fvplayer-'+FVFP_sWidgetId+'-text').trigger('fv_flowplayer_shortcode_insert', [ html ] );
  }else if( typeof(FCKeditorAPI) == 'undefined' && jQuery('#content:not([aria-hidden=true])').length ){
    jQuery('#content:not([aria-hidden=true])').val(html); 
  }else if( fv_wp_flowplayer_hTinyMCE == undefined || tinyMCE.activeEditor.isHidden() ) {
    fv_wp_flowplayer_oEditor.SetHTML( html );      
  }
  else {		
    fv_wp_flowplayer_hTinyMCE.setContent( html );
  }
}


function fv_wp_flowplayer_submit( preview ) {
  fv_wp_fp_shortcode = '';
  var shorttag = 'fvplayer';
	
	if(
    !preview &&
    jQuery(".fv_wp_flowplayer_field_rtmp").attr('placeholder') == '' &&
		jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").is(":visible") &&
		(
			( jQuery(".fv_wp_flowplayer_field_rtmp").val() != '' && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() == '' ) ||
			( jQuery(".fv_wp_flowplayer_field_rtmp").val() == '' && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() != '' )
		)
	) {
		alert('Please enter both server and path for your RTMP video.');
		return false;
	} else if( 
          !preview &&
          document.getElementById("fv_wp_flowplayer_field_src").value == '' 
          && jQuery(".fv_wp_flowplayer_field_rtmp").val() == '' 
          && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() == '') {
		alert('Please enter the file name of your video file.');
		return false;
	} else {
    fv_wp_fp_shortcode = '[' + shorttag;
  }
	
	
   
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_src','src');
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_src_1','src1');
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_src_2','src2');
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_rtmp','rtmp');
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_rtmp_path','rtmp_path');
  
  if(!preview){
    fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_width','width','int');
    fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_height','height','int');
  }else{
    var width = jQuery('#fv_wp_flowplayer_field_width').val();
    width = width ? parseInt(width) : 460;
    fv_wp_fp_shortcode += ' width="' + width + '" '
    var height = jQuery('#fv_wp_flowplayer_field_height').val();
    height = height ? parseInt(height) : 300;
    fv_wp_fp_shortcode += ' height="' + height + '" '
  }
  
  
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_align', 'align', false, false, ['left', 'right'] );
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_autoplay', 'autoplay', false, false, ['true', 'false'] );
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_liststyle', 'liststyle', false, false, ['tabs', 'prevnext', 'vertical','horizontal'] );
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_controlbar', 'controlbar', false, false, ['yes', 'no'] );
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_embed', 'embed', false, false, ['true', 'false'] );
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_speed', 'speed', false, false, ['buttons', 'no'] );
  
  fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_live', 'live', false, true );
			        
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_mobile','mobile');  
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_splash','splash');
  
  /*
   * End of playlist dropdown
   * legacy:
   * fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_loop', 'loop', false, true );
   * fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_redirect','redirect');
   * fv_wp_flowplayer_shortcode_write_arg( 'fv_wp_flowplayer_field_splashend', 'splashend', false, true, ['show'] );
   */
  switch(jQuery('#fv_wp_flowplayer_field_actions_end').val()){
    case 'loop': fv_wp_fp_shortcode += ' loop="true"'; break;
    case 'splashend': fv_wp_fp_shortcode += ' splashend="show"'; break;
    case 'redirect': fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_redirect','redirect'); break;
    case 'popup': 
      if( jQuery('[name=fv_wp_flowplayer_field_popup]').val() !== ''){
        fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_popup','popup','html');
      }else{
        fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_popup_id', 'popup', false, false, ['no','random','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16'] );
      }
    break;
  }
  
  
  jQuery('.fv_wp_flowplayer_field_subtitles').each( function() {
    var lang = jQuery(this).siblings('.fv_wp_flowplayer_field_subtitles_lang').val();
    var field = lang ? 'subtitles_' + lang : 'subtitles'
    fv_wp_flowplayer_shortcode_write_arg( jQuery(this)[0], field );
  });   
  
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_ad','ad','html');
  //  
  
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_ad_height','ad_height','int');
  fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_ad_skip','ad_skip', false, true, ['yes']);
	fv_wp_flowplayer_shortcode_write_arg('fv_wp_flowplayer_field_ad_width','ad_width','int');  
		
	if( jQuery('.fv-player-tab-video-files table').length > 0 ) {
		var aPlaylistItems = new Array();
    var aPlaylistCaptions = new Array();
		jQuery('.fv-player-tab-video-files table').each(function(i,e) {      
      aPlaylistCaptions.push(jQuery('[name=fv_wp_flowplayer_field_caption]',this).attr('value').trim().replace(/\;/gi,'\\;').replace(/"/gi,'&amp;quot;') );
      
		  if( i == 0 ) return;  
      var aPlaylistItem = new Array();      
      jQuery(this).find('input').each( function() {
        if( jQuery(this).attr('name').match(/fv_wp_flowplayer_field_caption/) ) return;     
        if( jQuery(this).hasClass('fv_wp_flowplayer_field_rtmp') ) return;
        if( jQuery(this).hasClass('extra-field') ) return;
        if( jQuery(this).attr('value').trim().length > 0 ) { 
          var value = jQuery(this).attr('value').trim()
          if( jQuery(this).hasClass('fv_wp_flowplayer_field_rtmp_path') ) value = "rtmp:"+value;
          aPlaylistItem.push(value);
        }
      } );			
      if( aPlaylistItem.length > 0 ) {
        aPlaylistItems.push(aPlaylistItem.join(','));
      }
    }
		);
		var sPlaylistItems = aPlaylistItems.join(';');
    var sPlaylistCaptions = aPlaylistCaptions.join(';');
		if( sPlaylistItems.length > 0 ) {
			fv_wp_fp_shortcode += ' playlist="'+sPlaylistItems+'"';
		}
    
    var bPlaylistCaptionExists = false;
    for( var i in aPlaylistCaptions ){
      if( typeof(aPlaylistCaptions[i]) == "string" && aPlaylistCaptions[i].trim().length > 0 ) {
        bPlaylistCaptionExists = true;
      }
    }
		if( bPlaylistCaptionExists && sPlaylistCaptions.length > 0 ) {
			fv_wp_fp_shortcode += ' caption="'+sPlaylistCaptions+'"';
		}    
	}
  
  jQuery(document).trigger('fv_flowplayer_shortcode_create');
	
	if( fv_wp_fp_shortcode_remains.trim().length > 0 ) {
  	fv_wp_fp_shortcode += ' ' + fv_wp_fp_shortcode_remains.trim();
  }
  
	fv_wp_fp_shortcode += ']';
	
  //Preview
  if(preview){  
    //jQuery('#fv-player-tabs-debug').html(fv_wp_fp_shortcode);
    if( ! fv_wp_fp_shortcode.match(/src=/) ){
      jQuery('#fv-player-shortcode-editor-preview').attr('class','preview-no'); 
      fv_wp_flowplayer_dialog_resize();
      return;
    }
    jQuery('#fv-player-shortcode-editor-preview').attr('class','preview-loading');

    var url = fv_Player_site_base + '?fv_player_embed=1&fv_player_preview=' + encodeURIComponent(fv_wp_fp_shortcode);
    var iFrame = jQuery('#fv-player-shortcode-editor-preview-iframe');
    if(url !== iFrame.attr('src') ){
      //iFrame load event was not firing reliably
      jQuery(document).on('fvp-preview-complete',function(){
        jQuery('#fv-player-shortcode-editor-preview').attr('class','preview-show');
        setTimeout(function(){
          jQuery(iFrame).height( jQuery(iFrame).contents().find('#wrapper').height() );
          setTimeout(function(){
            jQuery(iFrame).height( jQuery(iFrame).contents().find('#wrapper').height() );
            fv_wp_flowplayer_dialog_resize();
          },100);
        },0);
      });
      iFrame.attr('src', url);
    }
    return;
  }
  
	jQuery(".fv-wordpress-flowplayer-button").fv_player_box.close();
  
	fv_wp_flowplayer_insert( fv_wp_fp_shortcode );  
}

function fv_wp_flowplayer_add_format() {
  if ( jQuery("#fv_wp_flowplayer_field_src").val() != '' ) {
    if ( jQuery(".fv_wp_flowplayer_field_src_1_wrapper").is(":visible") ) {      
      if ( jQuery("#fv_wp_flowplayer_field_src_1").val() != '' ) {
        jQuery(".fv_wp_flowplayer_field_src_2_wrapper").show();
        jQuery("#fv_wp_flowplayer_field_src_2_uploader").show();
        jQuery("#add_format_wrapper").hide();
      }
      else {
        alert('Please enter the file name of your second video file.');
      }
    }
    else {
      jQuery(".fv_wp_flowplayer_field_src_1_wrapper").show();
      jQuery("#fv_wp_flowplayer_field_src_1_uploader").show();
    }
    fv_wp_flowplayer_dialog_resize();
  }
  else {
    alert('Please enter the file name of your video file.');
  }
}

function fv_wp_flowplayer_add_rtmp() {
	jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").show();
	jQuery("#add_rtmp_wrapper").hide();
	fv_wp_flowplayer_dialog_resize();
}

function fv_wp_flowplayer_shortcode_parse_arg( sShortcode, sArg, bHTML, sCallback ) {

  var rDoubleQ = new RegExp(sArg+"=\"","g");
  var rSingleQ = new RegExp(sArg+"='","g");
  var rNoQ = new RegExp(sArg+"=[^\"']","g");
  
  var rMatch = false;
  if( sShortcode.match(rDoubleQ) ) {
    //rMatch = new RegExp(sArg+'="(.*?[^\\\\/])"',"g");
    rMatch = new RegExp('[ "\']' + sArg + '="(.*?[^\\\\])"', "g");
  } else if (sShortcode.match(rSingleQ)) {
    rMatch = new RegExp('[ "\']' + sArg + "='([^']*?)'", "g");
  } else if (sShortcode.match(rNoQ)) {
    rMatch = new RegExp('[ "\']' + sArg + "=([^\\]\\s,]+)", "g");
  }

  if( !rMatch ){
    return false;
  }
  
  var aOutput = rMatch.exec(sShortcode);
  fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( rMatch, '' );
 
  if( bHTML ) {
    aOutput[1] = aOutput[1].replace(/\\"/g, '"').replace(/\\(\[|\])/g, '$1');
  }
  
  if( sCallback ) {
    sCallback(aOutput);
  } else {
   return aOutput;
  }
}


function fv_wp_flowplayer_subtitle_parse_arg( args ) {
  var input = ('fv_wp_flowplayer_subtitle_parse_arg',args);
  var aLang = input[0].match(/subtitles_([a-z][a-z])/);
  fv_flowplayer_language_add( input[1], aLang[1] );
}


function fv_wp_flowplayer_shortcode_write_args( sField, sArg, sKind, bCheckbox, aValues ) {
  jQuery('[id='+sField+']').each( function(k,v) {
    k = (k==0) ? '' : k;
    fv_wp_flowplayer_shortcode_write_arg(jQuery(this)[0],sArg+k, sKind, bCheckbox, aValues);
  });    
}

function fv_wp_flowplayer_shortcode_write_arg( sField, sArg, sKind, bCheckbox, aValues ) {
  var element;
  if ( typeof(sField) == "string" ) {
    element = document.getElementById(sField);
  } else {
    element = sField;
  }
  if( typeof(element) == "undefined") {
    return false;
  }
  
  var sValue = false;
  if( bCheckbox ) {
    if( element.checked ){
      if( aValues ) {
        sValue = aValues[0];
      } else {
        sValue = 'true';
      }
    }
  } else if( aValues ){
    if( typeof(aValues[element.selectedIndex -1 ]) == "undefined" ) {
      return false;
    }
    sValue = aValues[element.selectedIndex -1 ];
  } else if( element.value != '' ) {
    sValue = element.value.trim();
    var sOutput = false;
    
    if( sKind == "int" ) {
      if( sValue % 1 !=0 ){
        return false;
      }
    } else if( sKind == 'html' ){
      sValue = sValue.replace(/&/g,'&amp;');
      //sValue = sValue.replace(/'/g,'\\\'');
      //sValue = sValue.replace(/"/g,'&quot;');
      sValue = sValue.replace(/</g,'&lt;');
      sValue = sValue.replace(/>/g,'&gt;');
    }
  } else {
    return false;
  }
    
  if( !sValue ){
    return false;
  }

  if( sValue.match(/"/) || sKind == 'html' ){
    sOutput = '"'+sValue.replace(/"/g, '\\"').replace(/(\[|\])/g, '\\$1')+'"';
  } else {
    sOutput = '"'+sValue+'"';
  }
  
  if( sOutput ){
    fv_wp_fp_shortcode += ' '+sArg+'='+sOutput; 
  }
  return sValue;
}

jQuery(document).on('fv_flowplayer_shortcode_insert', function(e) {
  jQuery(e.target).siblings('.button.fv-wordpress-flowplayer-button').val('Edit');
})