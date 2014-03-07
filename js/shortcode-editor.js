var FVFP_iStoreWidth = 0;
var FVFP_iStoreHeight = 0;  
var FVFP_sStoreRTMP = 0;   

jQuery(document).ready(function(){ 
  if( jQuery(".fv-wordpress-flowplayer-button").length > 0 && jQuery().colorbox ) {     
    jQuery(".fv-wordpress-flowplayer-button").colorbox( {
      width:"600px",
      height:"600px",
      href: "#fv-wordpress-flowplayer-popup",
      inline: true,
      onComplete : fv_wp_flowplayer_edit,
      onClosed : fv_wp_flowplayer_on_close,
      onOpen: function(){
        jQuery("#colorbox").addClass("fv-flowplayer-shortcode-editor");
        jQuery("#cboxOverlay").addClass("fv-flowplayer-shortcode-editor");
      }
    } );
    
    jQuery(".fv-wordpress-flowplayer-button").click( function() {
      if( jQuery('#wp-content-wrap').hasClass('html-active') && typeof(FCKeditorAPI) != "object" ) {
        jQuery(".fv-wordpress-flowplayer-button").after( ' <strong class="fv-wordpress-flowplayer-error">Please use the Visual editor</strong>' );
		    jQuery(".fv-wordpress-flowplayer-error").delay(2000).fadeOut( 500,function() { jQuery(this).remove(); } );
        return false;
      }
    } );
  }
  
	jQuery('#fv-flowplayer-playlist').sortable({
	  start: function( event, ui ) {
	    FVFP_iStoreWidth = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val();	  
	    FVFP_iStoreHeight = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val();	  
	    FVFP_sStoreRTMP = jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val();
	  },
    stop: function( event, ui ) {      
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_width').val( FVFP_iStoreWidth );
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_height').val( FVFP_iStoreHeight );
      jQuery('#fv-flowplayer-playlist table:first .fv_wp_flowplayer_field_rtmp').val( FVFP_sStoreRTMP );
    }
  }); 
});




var fv_wp_flowplayer_content;
var fv_wp_flowplayer_hTinyMCE;
var fv_wp_flowplayer_oEditor;
var fv_wp_fp_shortcode_remains;
var fv_wp_fp_playlist_item_template;
var fv_wp_fp_shortcode;



function fv_wp_flowplayer_init() {
  if( typeof tinyMCE !== 'undefined' ) {
    fv_wp_flowplayer_hTinyMCE = tinyMCE.getInstanceById('content');
  }
  else {
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
  jQuery('#fv-flowplayer-playlist table').each( function(i,e) {
    if( i == 0 ) return;
    jQuery(e).remove();
  } );  
  fv_wp_fp_playlist_item_template = jQuery('#fv-flowplayer-playlist table.fv-flowplayer-playlist-item').parent().html();
}


function fv_wp_flowplayer_insert( shortcode ) {
  if( fv_wp_flowplayer_content.match( fv_wp_flowplayer_re_edit ) ) {
    fv_wp_flowplayer_content = fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_edit, shortcode )
    fv_wp_flowplayer_set_html( fv_wp_flowplayer_content );
  }
  else {
    if ( fv_wp_flowplayer_content != '' ) {      
      fv_wp_flowplayer_content = fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_insert, shortcode )      
      fv_wp_flowplayer_set_html( fv_wp_flowplayer_content );            
    } else {
      send_to_editor( shortcode );  //  disappears?
    }                                                
  }  
} 


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


function fv_flowplayer_playlist_add( sInput ) {
  jQuery('#fv-flowplayer-playlist').append(fv_wp_fp_playlist_item_template);
  jQuery('#fv-flowplayer-playlist table:last').html(jQuery('#fv-flowplayer-playlist table:first').html());
  jQuery('#fv-flowplayer-playlist table').hover( function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').show(); }, function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').hide(); } );
  
  if( sInput ) {
    aInput = sInput.split(',');
    var count = 0;
    for( var i in aInput ) {
      if( aInput[i].match(/^rtmp:/) ) jQuery('#fv-flowplayer-playlist table:last').find('.fv_wp_flowplayer_field_rtmp_path').val(aInput[i].replace(/^rtmp:/,''));
      else if( aInput[i].match(/\.(jpg|png|gif|jpe|jpeg)$/) ) jQuery('#fv-flowplayer-playlist table:last').find('.fv_wp_flowplayer_field_splash').val(aInput[i]);
      else { jQuery('#fv-flowplayer-playlist table:last input:visible').eq(count).val(aInput[i]); count++; }
    }
  }
  fv_wp_flowplayer_dialog_resize();
  return false;
}


function fv_wp_flowplayer_edit() {	
  
  fv_wp_flowplayer_init();
  
  jQuery("#fv-wordpress-flowplayer-popup input").each( function() { jQuery(this).val( '' ); jQuery(this).attr( 'checked', false ) } );
  jQuery("#fv-wordpress-flowplayer-popup textarea").each( function() { jQuery(this).val( '' ) } );
  jQuery('#fv_wp_flowplayer_field_autoplay').prop('selectedIndex',0);
  jQuery('#fv_wp_flowplayer_field_embed').prop('selectedIndex',0);
  jQuery('#fv_wp_flowplayer_field_align').prop('selectedIndex',0);  
  jQuery("#fv_wp_flowplayer_field_insert-button").attr( 'value', 'Insert' );
  
	if( fv_wp_flowplayer_hTinyMCE == undefined || tinyMCE.activeEditor.isHidden() ) {  
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
      //fv_wp_flowplayer_hTinyMCE.selection.setContent('<span data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></span>');
      fv_wp_flowplayer_hTinyMCE.execCommand('mceInsertContent', false,'<'+fvwpflowplayer_helper_tag+' data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></'+fvwpflowplayer_helper_tag+'>');
      fv_wp_flowplayer_content = fv_wp_flowplayer_hTinyMCE.getContent();      
    }
    fv_wp_flowplayer_hTinyMCE.settings.validate = true;		
	}
	
  
  var content = fv_wp_flowplayer_content.replace(/\n/g, '\uffff');        

  var shortcode = content.match( fv_wp_flowplayer_re_edit );  

  if( shortcode != null ) {
    shortcode = shortcode.join('');
    shortcode = shortcode.replace('[', '');
    shortcode = shortcode.replace(']', '');
  	shortcode = shortcode.replace( fv_wp_flowplayer_re_insert, '' );
  	
  	shortcode = shortcode.replace( /\\'/g,'&#039;' );
  	  
	  var shortcode_parse_fix = shortcode.replace(/popup='[^']*?'/g, '');
	  shortcode_parse_fix = shortcode_parse_fix.replace(/ad='[^']*?'/g, '');
    fv_wp_fp_shortcode_remains = shortcode_parse_fix.replace( /^\S+\s*?/, '' );  	
  	
  	var srcurl = shortcode_parse_fix.match( /src=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src=['"]([^']*?)['"]/, '' );
  	if( srcurl == null ) {
  		srcurl = shortcode_parse_fix.match( /src=([^,\]\s]*)/ );
      fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src=([^,\]\s]*)/, '' );
    }     
    
  	var srcrtmp = shortcode_parse_fix.match( /rtmp=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /rtmp=['"]([^']*?)['"]/, '' );    
		var srcrtmp_path = shortcode_parse_fix.match( /rtmp_path=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /rtmp_path=['"]([^']*?)['"]/, '' );        
    
    var srcurl1 = shortcode.match( /src1=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src1=['"]([^']*?)['"]/, '' );
  	if( srcurl1 == null ) {
  		srcurl1 = shortcode.match( /src1=([^,\]\s]*)/ );
      fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src1=([^,\]\s]*)/, '' );
    }      
    
    var srcurl2 = shortcode.match( /src2=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src2=['"]([^']*?)['"]/, '' );
  	if( srcurl2 == null ) {
  		srcurl2 = shortcode.match( /src2=([^,\]\s]*)/ );
      fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /src2=([^,\]\s]*)/, '' );
    }
  	                                                                          
    var iheight = shortcode_parse_fix.match( /height="?(\d*)"?/ );			
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /height="?(\d*)"?/, '' );
  	var iwidth = shortcode_parse_fix.match( /width="?(\d*)"?/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /width="?(\d*)"?/, '' );
  	var sautoplay = shortcode.match( /autoplay=([^\s]+)/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /autoplay=([^\s]+)/, '' );
    var sembed = shortcode.match( /embed=([^\s]+)/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /embed=([^\s]+)/, '' );
  	var ssplash = shortcode.match( /splash='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /splash='([^']*)'/, '' );
  	var ssubtitles = shortcode.match( /subtitles='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /subtitles='([^']*)'/, '' );    
  	var smobile = shortcode.match( /mobile='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /mobile='([^']*)'/, '' );        
    var sredirect = shortcode.match( /redirect='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /redirect='([^']*)'/, '' );
  	if( ssplash == null ) {
  		ssplash = shortcode.match( /splash=([^,\]\s]*)/ );
      fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /splash=([^,\]\s]*)/, '' );
    }			
  	var spopup = shortcode.match( /popup='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /popup='([^']*)'/, '' );
    var sad = shortcode.match( /ad='([^']*)'/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /ad='([^']*)'/, '' ); 
    var iadheight = shortcode_parse_fix.match( /ad_height="?(\d*)"?/ );			
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /ad_height="?(\d*)"?/, '' );
  	var iadwidth = shortcode_parse_fix.match( /ad_width="?(\d*)"?/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /ad_width="?(\d*)"?/, '' );      
    var sloop = shortcode.match( /loop=([^\s]+)/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /loop=([^\s]+)/, '' );
    var ssplashend = shortcode.match( /splashend=([^\s]+)/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /splashend=([^\s]+)/, '' );
  	var sad_skip = shortcode.match( /ad_skip=([^\s]+)/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /ad_skip=([^\s]+)/, '' ); 
  	var salign = shortcode.match( /align="([^"]+)"/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /align="([^"]+)"/, '' ); 
    
  	var sPlaylist = shortcode_parse_fix.match( /playlist=['"]([^']*?)['"]/ );
    fv_wp_fp_shortcode_remains = fv_wp_fp_shortcode_remains.replace( /playlist=['"]([^']*?)['"]/, '' );        
    
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
    
  	if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
  	if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];  		
    
  	if( iheight != null && iheight[1] != null ) jQuery(".fv_wp_flowplayer_field_height").val(iheight[1]);
  	if( iwidth != null && iwidth[1] != null ) jQuery(".fv_wp_flowplayer_field_width").val(iwidth[1]);
  	if( sautoplay != null && sautoplay[1] != null ) {
  		if (sautoplay[1] == 'true') 
        document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex = 1;
      if (sautoplay[1] == 'false') 
        document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex = 2;
    }
  	if( sembed != null && sembed[1] != null ) {
  		if (sembed[1] == 'true') 
        document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex = 1;
      if (sembed[1] == 'false') 
        document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex = 2;
    }    
  	if( smobile != null && smobile[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_mobile").value = smobile[1];          
  	if( ssplash != null && ssplash[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_splash").value = ssplash[1];
  	if( ssubtitles != null && ssubtitles[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_subtitles").value = ssubtitles[1];      
  	if( spopup != null && spopup[1] != null ) {
  		spopup = spopup[1].replace(/&#039;/g,'\'').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
  		spopup = spopup.replace(/&amp;/g,'&');
  		document.getElementById("fv_wp_flowplayer_field_popup").value = spopup;
  	}
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
    if( sredirect != null && sredirect[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_redirect").value = sredirect[1];
    if( sloop != null && sloop[1] != null && sloop[1] == 'true' )
  		document.getElementById("fv_wp_flowplayer_field_loop").checked = 1;
    if( ssplashend != null && ssplashend[1] != null && ssplashend[1] == 'show' )
  		document.getElementById("fv_wp_flowplayer_field_splashend").checked = 1;  

  	if( salign != null && salign[1] != null ) {
  		if (salign[1] == 'left') 
        document.getElementById("fv_wp_flowplayer_field_align").selectedIndex = 1;
      if (salign[1] == 'right') 
        document.getElementById("fv_wp_flowplayer_field_align").selectedIndex = 2;
    }    
    
    if( sPlaylist ) {    	
			aPlaylist = sPlaylist[1].split(';');
			for( var i in aPlaylist ) {			
				fv_flowplayer_playlist_add( aPlaylist[i] );
			}
    }
    
    jQuery(document).trigger('fv_flowplayer_shortcode_parse', [ shortcode_parse_fix, fv_wp_fp_shortcode_remains ] );
  	
  	jQuery("#fv_wp_flowplayer_field_insert-button").attr( 'value', 'Update' );    
	} else {
    fv_wp_fp_shortcode_remains = '';
  }
  
  jQuery('.fv_wp_flowplayer_playlist_head').hover(
  	function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').show(); }, function() { jQuery(this).find('.fv_wp_flowplayer_playlist_remove').hide(); } );  
  
  jQuery('#cboxContent').css('background','white');
  
  fv_wp_flowplayer_dialog_resize();
}


function fv_wp_flowplayer_dialog_resize() {
  var iContentHeight = parseInt( jQuery('#fv-wordpress-flowplayer-popup').css('height') );
  if( iContentHeight < 150 ) iContentHeight = 150;
  jQuery('#fv-wordpress-flowplayer-popup').colorbox.resize({width:600, height:(iContentHeight+100)})
}


function fv_wp_flowplayer_on_close() {
  fv_wp_flowplayer_init();
  fv_wp_flowplayer_set_html( fv_wp_flowplayer_content.replace( fv_wp_flowplayer_re_insert, '' ) );
}   


function fv_wp_flowplayer_set_html( html ) {
  if( fv_wp_flowplayer_hTinyMCE == undefined || tinyMCE.activeEditor.isHidden() ) {
    fv_wp_flowplayer_oEditor.SetHTML( html );      
  }
  else {		
    fv_wp_flowplayer_hTinyMCE.setContent( html );
  }
}


function fv_wp_flowplayer_submit() {
  fv_wp_fp_shortcode = '';
  var shorttag = 'fvplayer';
	
	if(
    jQuery(".fv_wp_flowplayer_field_rtmp").attr('placeholder') == '' &&
		jQuery(".fv_wp_flowplayer_field_rtmp_wrapper").is(":visible") &&
		(
			( jQuery(".fv_wp_flowplayer_field_rtmp").val() != '' && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() == '' ) ||
			( jQuery(".fv_wp_flowplayer_field_rtmp").val() == '' && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() != '' )
		)
	) {
		alert('Please enter both server and path for your RTMP video.');
		return false;
	} else if( document.getElementById("fv_wp_flowplayer_field_src").value == '' && jQuery(".fv_wp_flowplayer_field_rtmp").val() == '' && jQuery(".fv_wp_flowplayer_field_rtmp_path").val() == '') {
		alert('Please enter the file name of your video file.');
		return false;
	} else 
	
	fv_wp_fp_shortcode = '[' + shorttag;	
   
  if ( document.getElementById("fv_wp_flowplayer_field_src").value != '' ) {
    fv_wp_fp_shortcode += ' src=\'' + document.getElementById("fv_wp_flowplayer_field_src").value + '\''; 
  } 
   
  if ( document.getElementById("fv_wp_flowplayer_field_src_1").value != '' ) {
    fv_wp_fp_shortcode += ' src1=\'' + document.getElementById("fv_wp_flowplayer_field_src_1").value + '\''; 
  }
  if ( document.getElementById("fv_wp_flowplayer_field_src_2").value != '' ) {
    fv_wp_fp_shortcode += ' src2=\'' + document.getElementById("fv_wp_flowplayer_field_src_2").value + '\''; 
  }
  
  if ( jQuery(".fv_wp_flowplayer_field_rtmp").val() != '' ) {
    fv_wp_fp_shortcode += ' rtmp="' + jQuery(".fv_wp_flowplayer_field_rtmp").val() + '"'; 
  }
  if ( jQuery(".fv_wp_flowplayer_field_rtmp_path").val() != '' ) {
    fv_wp_fp_shortcode += ' rtmp_path="' + jQuery(".fv_wp_flowplayer_field_rtmp_path").val() + '"'; 
  }  
		
	if( jQuery(".fv_wp_flowplayer_field_width").val() != '' && jQuery(".fv_wp_flowplayer_field_width").val() % 1 != 0 ) {
		alert('Please enter a valid width.');
		return false;
	}
	if( jQuery(".fv_wp_flowplayer_field_width").val() != '' )
		fv_wp_fp_shortcode += ' width=' + jQuery(".fv_wp_flowplayer_field_width").val();
		
	if( jQuery(".fv_wp_flowplayer_field_height").val() != '' && jQuery(".fv_wp_flowplayer_field_height").val() % 1 != 0 ) {
		alert('Please enter a valid height.');
		return false;
	}
	if( jQuery(".fv_wp_flowplayer_field_height").val() != '' )
		fv_wp_fp_shortcode += ' height=' + jQuery(".fv_wp_flowplayer_field_height").val();
	
  if( document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex == 1 )
	  fv_wp_fp_shortcode += ' autoplay=true';
	if( document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex == 2 )
	  fv_wp_fp_shortcode += ' autoplay=false';
    
  if( document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex == 1 )
	  fv_wp_fp_shortcode += ' embed=true';
	if( document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex == 2 )
	  fv_wp_fp_shortcode += ' embed=false';    
    
  if( document.getElementById("fv_wp_flowplayer_field_align").selectedIndex == 1 )
	  fv_wp_fp_shortcode += ' align="left"';
	if( document.getElementById("fv_wp_flowplayer_field_align").selectedIndex == 2 )
	  fv_wp_fp_shortcode += ' align="right"';    
        
    
  if( document.getElementById("fv_wp_flowplayer_field_loop").checked )
		fv_wp_fp_shortcode += ' loop=true';    
		
	if( document.getElementById("fv_wp_flowplayer_field_mobile").value != '' )
		fv_wp_fp_shortcode += ' mobile=\'' + document.getElementById("fv_wp_flowplayer_field_mobile").value + '\'';    		
		
	if( document.getElementById("fv_wp_flowplayer_field_splash").value != '' )
		fv_wp_fp_shortcode += ' splash=\'' + document.getElementById("fv_wp_flowplayer_field_splash").value + '\'';
    
	if( document.getElementById("fv_wp_flowplayer_field_subtitles").value != '' )
		fv_wp_fp_shortcode += ' subtitles=\'' + document.getElementById("fv_wp_flowplayer_field_subtitles").value + '\'';    
    
  if( document.getElementById("fv_wp_flowplayer_field_splashend").checked )
		fv_wp_fp_shortcode += ' splashend=show';
    
  if( document.getElementById("fv_wp_flowplayer_field_redirect").value != '' )
		fv_wp_fp_shortcode += ' redirect=\'' + document.getElementById("fv_wp_flowplayer_field_redirect").value + '\'';        
    
  if( document.getElementById("fv_wp_flowplayer_field_popup").value != '' ) {
		var popup = document.getElementById("fv_wp_flowplayer_field_popup").value;
		popup = popup.replace(/&/g,'&amp;');
		popup = popup.replace(/'/g,'\\\'');
		popup = popup.replace(/"/g,'&quot;');
		popup = popup.replace(/</g,'&lt;');
		popup = popup.replace(/>/g,'&gt;');
		fv_wp_fp_shortcode += ' popup=\'' + popup +'\''
	}        
	
  if( document.getElementById("fv_wp_flowplayer_field_ad").value != '' ) {
		var ad = document.getElementById("fv_wp_flowplayer_field_ad").value;
		ad = ad.replace(/&/g,'&amp;');
		ad = ad.replace(/'/g,'\\\'');
		ad = ad.replace(/"/g,'&quot;');
		ad = ad.replace(/</g,'&lt;');
		ad = ad.replace(/>/g,'&gt;');
		fv_wp_fp_shortcode += ' ad=\'' + ad +'\''
	}     	
	
	if( document.getElementById("fv_wp_flowplayer_field_ad_width").value != '' )
		fv_wp_fp_shortcode += ' ad_width=' + document.getElementById("fv_wp_flowplayer_field_ad_width").value;
	if( document.getElementById("fv_wp_flowplayer_field_ad_height").value != '' )
		fv_wp_fp_shortcode += ' ad_height=' + document.getElementById("fv_wp_flowplayer_field_ad_height").value;		
	if( document.getElementById("fv_wp_flowplayer_field_ad_skip").checked != '' )
		fv_wp_fp_shortcode += ' ad_skip=yes';			
		
	if( jQuery('#fv-flowplayer-playlist table').length > 0 ) {
		var aPlaylistItems = new Array();
		jQuery('#fv-flowplayer-playlist table').each(function(i,e) {
		  if( i == 0 ) return;  
      var aPlaylistItem = new Array();
      jQuery(this).find('input:visible').each( function() {
        if( jQuery(this).hasClass('fv_wp_flowplayer_field_rtmp') ) return;      
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
		if( sPlaylistItems.length > 0 ) {
			fv_wp_fp_shortcode += ' playlist="'+sPlaylistItems+'"';
		}
	}
  
  jQuery(document).trigger('fv_flowplayer_shortcode_create');
	
	if( fv_wp_fp_shortcode_remains.trim().length > 0 ) {
  	fv_wp_fp_shortcode += ' ' + fv_wp_fp_shortcode_remains.trim();
  }
  
	fv_wp_fp_shortcode += ']';
		
	jQuery(".fv-wordpress-flowplayer-button").colorbox.close();
  
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
