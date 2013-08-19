<?php
  global $post;
  $post_id = $post->ID;
  
  $conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if( isset($conf["allowuploads"]) && $conf["allowuploads"] == 'true' ) {
	  $allow_uploads = $conf["allowuploads"];
	  $upload_field_width = '70%';
	} else {
	  $upload_field_width = '100%';
	}
	
	$helper_tag = ( is_plugin_active('jetpack/jetpack.php') ) ? 'b' : 'span';
?>
<style>
.fv-wp-flowplayer-notice { background-color: #FFFFE0; border-color: #E6DB55; margin: 5px 0 15px; padding: 0 0.6em; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; } 
.fv-wp-flowplayer-notice.fv-wp-flowplayer-note { background-color: #F8F8F8; border-color: #E0E0E0; } 
.fv-wp-flowplayer-notice p { font-family: sans-serif; font-size: 12px; margin: 0.5em 0; padding: 2px; } 
</style>
  
<script type='text/javascript'>
jQuery(document).ready(function(){ 
  if( jQuery(".fv-wordpress-flowplayer-button").length > 0 && jQuery().colorbox ) {     
    jQuery(".fv-wordpress-flowplayer-button").colorbox( 
      { width:"600px", height:"620px", href: "#fv-wordpress-flowplayer-popup", inline: true, onComplete : fv_wp_flowplayer_edit, onClosed : fv_wp_flowplayer_on_close }
    );
    
    jQuery(".fv-wordpress-flowplayer-button").click( function() {
      if( jQuery('#wp-content-wrap').hasClass('html-active') ) {
        jQuery(".fv-wordpress-flowplayer-button").after( ' <strong class="fv-wordpress-flowplayer-error">Please use the Visual editor</strong>' );
		    jQuery(".fv-wordpress-flowplayer-error").delay(2000).fadeOut( 500,function() { jQuery(this).remove(); } );
        return false;
      }
    } );
  }
});




var fv_wp_flowplayer_content;
var fv_wp_flowplayer_re_edit = /\[[^\]]*?<<?php echo $helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $helper_tag; ?>>[^\]]*?\]/mi;
var fv_wp_flowplayer_re_insert = /<<?php echo $helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $helper_tag; ?>>/gi;
var fv_wp_flowplayer_hTinyMCE;
var fv_wp_flowplayer_oEditor;
var fv_wp_fp_shortcode_remains;




function fv_wp_flowplayer_init() {
  if( typeof tinyMCE !== 'undefined' ) {
    fv_wp_flowplayer_hTinyMCE = tinyMCE.getInstanceById('content');
  }
  else {
    fv_wp_flowplayer_oEditor = FCKeditorAPI.GetInstance('content');    
  }
  jQuery('#fv_wp_flowplayer_file_info').hide();
  jQuery("#fv_wp_flowplayer_field_src_2_wrapper").hide();
  jQuery("#fv_wp_flowplayer_field_src_2_uploader").hide();
  jQuery("#fv_wp_flowplayer_field_src_1_wrapper").hide();
  jQuery("#fv_wp_flowplayer_field_src_1_uploader").hide();
  jQuery("#add_format_wrapper").show();
  jQuery("#add_rtmp_wrapper").show(); 
  jQuery("#fv_wp_flowplayer_field_rtmp_wrapper").hide();
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
      fv_wp_flowplayer_oEditor.InsertHtml('<<?php echo $helper_tag; ?> rel="FCKFVWPFlowplayerPlaceholder">&shy;</<?php echo $helper_tag; ?>>');
      fv_wp_flowplayer_content = fv_wp_flowplayer_oEditor.GetHTML();    
    }           
	}
	else {
    fv_wp_flowplayer_content = fv_wp_flowplayer_hTinyMCE.getContent();
    fv_wp_flowplayer_hTinyMCE.settings.validate = false;
    if (fv_wp_flowplayer_content.match( fv_wp_flowplayer_re_insert ) == null) {      
      //fv_wp_flowplayer_hTinyMCE.selection.setContent('<span data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></span>');
      fv_wp_flowplayer_hTinyMCE.execCommand('mceInsertContent', false,'<<?php echo $helper_tag; ?> data-mce-bogus="1" rel="FCKFVWPFlowplayerPlaceholder"></<?php echo $helper_tag; ?>>');
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
    
    
  	if( srcrtmp != null && srcrtmp[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_rtmp").value = srcrtmp[1];
  		document.getElementById("fv_wp_flowplayer_field_rtmp_wrapper").style.display = 'table-row';
  		document.getElementById("add_rtmp_wrapper").style.display = 'none';   
  	}
    if( srcrtmp_path != null && srcrtmp_path[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_rtmp_path").value = srcrtmp_path[1];
      document.getElementById("fv_wp_flowplayer_field_rtmp_wrapper").style.display = 'table-row';
      document.getElementById("add_rtmp_wrapper").style.display = 'none';           
    }    
    
    if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
    if( srcurl1 != null && srcurl1[1] != null ) {
  		document.getElementById("fv_wp_flowplayer_field_src_1").value = srcurl1[1];
      document.getElementById("fv_wp_flowplayer_field_src_1_wrapper").style.display = 'table-row';
      //document.getElementById("fv_wp_flowplayer_field_src_1_uploader").style.display = 'table-row';
      if( srcurl2 != null && srcurl2[1] != null ) {
    		document.getElementById("fv_wp_flowplayer_field_src_2").value = srcurl2[1];
        document.getElementById("fv_wp_flowplayer_field_src_2_wrapper").style.display = 'table-row';
        //document.getElementById("fv_wp_flowplayer_field_src_2_uploader").style.display = 'table-row';
        document.getElementById("add_format_wrapper").style.display = 'none';        
      }            
    }     
    
  	if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];
  	if( srcurl != null && srcurl[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_src").value = srcurl[1];  		
    
  	if( iheight != null && iheight[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_height").value = iheight[1];
  	if( iwidth != null && iwidth[1] != null )
  		document.getElementById("fv_wp_flowplayer_field_width").value = iwidth[1];
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
  	
  	jQuery("#fv_wp_flowplayer_field_insert-button").attr( 'value', 'Update' );    
	} else {
    fv_wp_fp_shortcode_remains = '';
  }
  
  jQuery('#cboxContent').css('background','white');
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
	var shortcode = '';
  var shorttag = 'fvplayer';
	
	if(
		jQuery("#fv_wp_flowplayer_field_rtmp_wrapper").is(":visible") &&
		(
			( jQuery("#fv_wp_flowplayer_field_rtmp").val() != '' && jQuery("#fv_wp_flowplayer_field_rtmp_path").val() == '' ) ||
			( jQuery("#fv_wp_flowplayer_field_rtmp").val() == '' && jQuery("#fv_wp_flowplayer_field_rtmp_path").val() != '' )
		)
	) {
		alert('Please enter both server and path for your RTMP video.');
		return false;
	} else if( document.getElementById("fv_wp_flowplayer_field_src").value == '' && jQuery("#fv_wp_flowplayer_field_rtmp").val() == '' && jQuery("#fv_wp_flowplayer_field_rtmp_path").val() == '') {
		alert('Please enter the file name of your video file.');
		return false;
	} else 
	
	shortcode = '[' + shorttag;	
   
  if ( document.getElementById("fv_wp_flowplayer_field_src").value != '' ) {
    shortcode += ' src=\'' + document.getElementById("fv_wp_flowplayer_field_src").value + '\''; 
  } 
   
  if ( document.getElementById("fv_wp_flowplayer_field_src_1").value != '' ) {
    shortcode += ' src1=\'' + document.getElementById("fv_wp_flowplayer_field_src_1").value + '\''; 
  }
  if ( document.getElementById("fv_wp_flowplayer_field_src_2").value != '' ) {
    shortcode += ' src2=\'' + document.getElementById("fv_wp_flowplayer_field_src_2").value + '\''; 
  }
  
  if ( document.getElementById("fv_wp_flowplayer_field_rtmp").value != '' ) {
    shortcode += ' rtmp="' + document.getElementById("fv_wp_flowplayer_field_rtmp").value + '"'; 
  }
  if ( document.getElementById("fv_wp_flowplayer_field_rtmp_path").value != '' ) {
    shortcode += ' rtmp_path="' + document.getElementById("fv_wp_flowplayer_field_rtmp_path").value + '"'; 
  }  
		
	if( document.getElementById("fv_wp_flowplayer_field_width").value != '' && document.getElementById("fv_wp_flowplayer_field_width").value % 1 != 0 ) {
		alert('Please enter a valid width.');
		return false;
	}
	if( document.getElementById("fv_wp_flowplayer_field_width").value != '' )
		shortcode += ' width=' + document.getElementById("fv_wp_flowplayer_field_width").value;
		
	if( document.getElementById("fv_wp_flowplayer_field_height").value != '' && document.getElementById("fv_wp_flowplayer_field_height").value % 1 != 0 ) {
		alert('Please enter a valid height.');
		return false;
	}
	if( document.getElementById("fv_wp_flowplayer_field_height").value != '' )
		shortcode += ' height=' + document.getElementById("fv_wp_flowplayer_field_height").value;
	
  if( document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex == 1 )
	  shortcode += ' autoplay=true';
	if( document.getElementById("fv_wp_flowplayer_field_autoplay").selectedIndex == 2 )
	  shortcode += ' autoplay=false';
    
  if( document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex == 1 )
	  shortcode += ' embed=true';
	if( document.getElementById("fv_wp_flowplayer_field_embed").selectedIndex == 2 )
	  shortcode += ' embed=false';    
    
  if( document.getElementById("fv_wp_flowplayer_field_align").selectedIndex == 1 )
	  shortcode += ' align="left"';
	if( document.getElementById("fv_wp_flowplayer_field_align").selectedIndex == 2 )
	  shortcode += ' align="right"';    
        
    
  if( document.getElementById("fv_wp_flowplayer_field_loop").checked )
		shortcode += ' loop=true';    
		
	if( document.getElementById("fv_wp_flowplayer_field_mobile").value != '' )
		shortcode += ' mobile=\'' + document.getElementById("fv_wp_flowplayer_field_mobile").value + '\'';    		
		
	if( document.getElementById("fv_wp_flowplayer_field_splash").value != '' )
		shortcode += ' splash=\'' + document.getElementById("fv_wp_flowplayer_field_splash").value + '\'';
    
	if( document.getElementById("fv_wp_flowplayer_field_subtitles").value != '' )
		shortcode += ' subtitles=\'' + document.getElementById("fv_wp_flowplayer_field_subtitles").value + '\'';    
    
  if( document.getElementById("fv_wp_flowplayer_field_splashend").checked )
		shortcode += ' splashend=show';
    
  if( document.getElementById("fv_wp_flowplayer_field_redirect").value != '' )
		shortcode += ' redirect=\'' + document.getElementById("fv_wp_flowplayer_field_redirect").value + '\'';        
    
  if( document.getElementById("fv_wp_flowplayer_field_popup").value != '' ) {
		var popup = document.getElementById("fv_wp_flowplayer_field_popup").value;
		popup = popup.replace(/&/g,'&amp;');
		popup = popup.replace(/'/g,'\\\'');
		popup = popup.replace(/"/g,'&quot;');
		popup = popup.replace(/</g,'&lt;');
		popup = popup.replace(/>/g,'&gt;');
		shortcode += ' popup=\'' + popup +'\''
	}        
	
  if( document.getElementById("fv_wp_flowplayer_field_ad").value != '' ) {
		var ad = document.getElementById("fv_wp_flowplayer_field_ad").value;
		ad = ad.replace(/&/g,'&amp;');
		ad = ad.replace(/'/g,'\\\'');
		ad = ad.replace(/"/g,'&quot;');
		ad = ad.replace(/</g,'&lt;');
		ad = ad.replace(/>/g,'&gt;');
		shortcode += ' ad=\'' + ad +'\''
	}     	
	
	if( document.getElementById("fv_wp_flowplayer_field_ad_width").value != '' )
		shortcode += ' ad_width=' + document.getElementById("fv_wp_flowplayer_field_ad_width").value;
	if( document.getElementById("fv_wp_flowplayer_field_ad_height").value != '' )
		shortcode += ' ad_height=' + document.getElementById("fv_wp_flowplayer_field_ad_height").value;		
	if( document.getElementById("fv_wp_flowplayer_field_ad_skip").checked != '' )
		shortcode += ' ad_skip=yes';				
	
	if( fv_wp_fp_shortcode_remains.length > 0 ) {
  	shortcode += ' ' + fv_wp_fp_shortcode_remains.trim();
  }
  
	shortcode += ']';
	
	jQuery(".fv-wordpress-flowplayer-button").colorbox.close();
  
	fv_wp_flowplayer_insert( shortcode );  
}

function add_format() {
  if ( jQuery("#fv_wp_flowplayer_field_src").val() != '' ) {
    if ( jQuery("#fv_wp_flowplayer_field_src_1_wrapper").is(":visible") ) {      
      if ( jQuery("#fv_wp_flowplayer_field_src_1").val() != '' ) {
        jQuery("#fv_wp_flowplayer_field_src_2_wrapper").show();
        jQuery("#fv_wp_flowplayer_field_src_2_uploader").show();
        jQuery("#add_format_wrapper").hide();
      }
      else {
        alert('Please enter the file name of your second video file.');
      }
    }
    else {
      jQuery("#fv_wp_flowplayer_field_src_1_wrapper").show();
      jQuery("#fv_wp_flowplayer_field_src_1_uploader").show();
    }
  }
  else {
    alert('Please enter the file name of your video file.');
  }
}

function add_rtmp() {
	jQuery("#fv_wp_flowplayer_field_rtmp_wrapper").show();
	jQuery("#add_rtmp_wrapper").hide();
}

</script>
<div style="display: none">
  <div id="fv-wordpress-flowplayer-popup">
  	<table class="slidetoggle describe" width="100%">
  		<tbody>
  			<tr>
  				<th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_src" class="alignright">Video</label></th>
  				<td colspan="2" class="field"><input type="text" class="text" id="fv_wp_flowplayer_field_src" name="fv_wp_flowplayer_field_src" style="width: <?php echo $upload_field_width; ?>" value="" />
  				<?php if ($allow_uploads=="true") { ?>      
            <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_video&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
    			<?php }; //allow uplads video ?></td>
  			</tr>
  
        <tr style="display: none" id="fv_wp_flowplayer_file_info">
          <th></th>
          <td>
            Video Duration: <span id="fv_wp_flowplayer_file_duration"></span><br />
            File size: <span id="fv_wp_flowplayer_file_size"></span>MB
          </td>
        </tr>
  			<tr><th></th>
  				<td class="field"><label for="fv_wp_flowplayer_field_width">Width <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_width" name="fv_wp_flowplayer_field_width" style="width: 18%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_height">Height <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_height" name="fv_wp_flowplayer_field_height" style="width: 18%" value=""/></td>
  			</tr>
        
        <tr style="display: none;" id="fv_wp_flowplayer_field_src_1_wrapper">
  				<th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_src_1" class="alignright">Video</label></th>
  				<td colspan="2" class="field"><input type="text" class="text" id="fv_wp_flowplayer_field_src_1" name="fv_wp_flowplayer_field_src_1" style="width: <?php echo $upload_field_width; ?>" value=""/>
					<?php if ($allow_uploads=="true") { ?> 
            <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_video_1&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
  				<?php }; //allow uplads video ?>
	        </td>
  			</tr>
        
        <tr style="display: none;" id="fv_wp_flowplayer_field_src_2_wrapper">
  				<th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_src_2" class="alignright">Video</label></th>
  				<td colspan="2" class="field"><input type="text" class="text" id="fv_wp_flowplayer_field_src_2" name="fv_wp_flowplayer_field_src_2" style="width: <?php echo $upload_field_width; ?>" value=""/>
					<?php if ($allow_uploads=="true") {	?>  
            <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_video_2&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
    			<?php }; //allow uplads video ?>
    			</td>    			
  			</tr>
  			
        <tr style="display: none;" id="fv_wp_flowplayer_field_rtmp_wrapper">
  				<th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_rtmp" class="alignright">RTMP Server</label></th>
  				<td colspan="2" class="field">
  					<input type="text" class="text" id="fv_wp_flowplayer_field_rtmp" name="fv_wp_flowplayer_field_rtmp" value="" style="width: 40%" />
    				&nbsp;<label for="fv_wp_flowplayer_field_rtmp_path"><strong>RTMP Path</strong></label>
    				<input type="text" class="text" id="fv_wp_flowplayer_field_rtmp_path" name="fv_wp_flowplayer_field_rtmp_path" value="" style="width: 37%" />
    			</td> 
  			</tr>  			
        
        <tr id="fv_wp_flowplayer_add_format_wrapper">
    			<th scope="row" class="label" style="width: 18%"></th>
  				<td class="field" style="width: 50%"><div id="add_format_wrapper"><a href="#" class="partial-underline" onclick="add_format()" style="outline: 0"><span id="add-format">+</span>&nbsp;Add another format</a> (i.e. WebM, OGV)</div></td>
  				<td class="field"><div id="add_rtmp_wrapper"><a href="#" class="partial-underline" onclick="add_rtmp()" style="outline: 0"><span id="add-rtmp">+</span>&nbsp;Add RTMP</a></div></td>  				
  			</tr>      
  			
        <tr<?php if( $conf["interface"]["mobile"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_mobile" class="alignright">Mobile Video</label></th>
  				<td class="field" colspan="2"><input type="text" id="fv_wp_flowplayer_field_mobile" name="fv_wp_flowplayer_field_mobile" style="width: <?php echo $upload_field_width; ?>" value=""/>
  					<?php if ($allow_uploads=='true') { ?>
              <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_mobile&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
          	<?php }; //allow uploads splash image ?></td>
  			</tr>
  			
        <tr>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_splash" class="alignright">Splash Image</label></th>
  				<td class="field" colspan="2"><input type="text" id="fv_wp_flowplayer_field_splash" name="fv_wp_flowplayer_field_splash" style="width: <?php echo $upload_field_width; ?>" value=""/>
  					<?php if ($allow_uploads=='true') { ?>
              <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_splash&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Image</a>
          	<?php }; //allow uploads splash image ?></td>
  			</tr>
      
        <tr<?php if( $conf["interface"]["subtitles"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_subtitles" class="alignright">Subtitles</label></th>
  				<td class="field" colspan="2"><input type="text" id="fv_wp_flowplayer_field_subtitles" name="fv_wp_flowplayer_field_subtitles" style="width: <?php echo $upload_field_width; ?>" value=""/>
  					<?php if ($allow_uploads=='true') { ?>
              <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_subtitles&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Subtitles</a>
          	<?php }; //allow uploads splash image ?></td>
  			</tr>

      </tbody>
    </table>
    <table width="100%">
      <tbody> 
        <?php
        foreach( $conf["interface"] AS $option ) {
          if( $option == 'true' ) {
            $show_additonal_features = true;
          } else {
            $show_more_features = true;
          }
        }
        ?>     
        <tr<?php if( !$show_additonal_features ) echo ' style="display: none"';?>>
          <th scope="row" width="18%"></th>
          <td style="text-align: left; padding: 10px 0; text-transform: uppercase;">Additional features</td>
        </tr>
        <tr<?php if( $conf["interface"]["popup"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_popup" class="alignright">HTML Popup</label></th>
  				<td><textarea type="text" id="fv_wp_flowplayer_field_popup" name="fv_wp_flowplayer_field_popup" style="width: 93%"></textarea></td>
  			</tr>
        <tr<?php if( $conf["interface"]["redirect"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_redirect" class="alignright">Redirect to</label></th>
  				<td class="field"><input type="text" id="fv_wp_flowplayer_field_redirect" name="fv_wp_flowplayer_field_redirect" style="width: 93%" /></td>
  			</tr>
        <tr<?php if( $conf["interface"]["autoplay"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_autoplay" class="alignright">Autoplay</label></th>
  				<td class="field">
            <select id="fv_wp_flowplayer_field_autoplay" name="fv_wp_flowplayer_field_autoplay">
              <option>Default</option>
              <option>On</option>
              <option>Off</option>
            </select>
          </td>
  			</tr>
        <tr<?php if( $conf["interface"]["loop"] !== 'true' ) { echo ' style="display: none"'; } ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_loop" class="alignright">Loop*</label></th>
  				<td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_loop" name="fv_wp_flowplayer_field_loop" /></td>
  			</tr>   
        <tr<?php if( $conf["interface"]["splashend"] !== 'true' ) { echo ' style="display: none"'; } ?>>
          <th scope="row" class="label">
            <label for="fv_wp_flowplayer_field_splashend">Splash end*</label>
          </th>
          <td>
            <input type="checkbox" id="fv_wp_flowplayer_field_splashend" name="fv_wp_flowplayer_field_splashend" /> (show splash image at the end)
          </td> 
        </tr>    
        <tr<?php if( $conf["interface"]["embed"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_embed" class="alignright">Embeding</label></th>
  				<td class="field">
            <select id="fv_wp_flowplayer_field_embed" name="fv_wp_flowplayer_field_embed">
              <option>Default</option>
              <option>On</option>
              <option>Off</option>
            </select>
          </td>
  			</tr>           
        <tr<?php if( $conf["interface"]["ads"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_ad" class="alignright">Ad code</label></th>
  				<td>
  					<textarea type="text" id="fv_wp_flowplayer_field_ad" name="fv_wp_flowplayer_field_ad" style="width: 93%"></textarea>
  				</td>
  			</tr> 
  			<tr<?php if( $conf["interface"]["ads"] !== 'true' ) echo ' style="display: none"'; ?>><th></th>
  				<td class="field">
  					<label for="fv_wp_flowplayer_field_ad_width">Width <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_width" name="fv_wp_flowplayer_field_ad_width" style="width: 18%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_ad_height">Height <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_height" name="fv_wp_flowplayer_field_ad_height" style="width: 18%" value=""/><br />
  					<input type="checkbox" id="fv_wp_flowplayer_field_ad_skip" name="fv_wp_flowplayer_field_ad_skip" /> Skip global ad in this video  					
  				</td>
  			</tr>			
        <tr<?php if( $conf["interface"]["align"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_align" class="alignright">Align</label></th>
  				<td>
            <select id="fv_wp_flowplayer_field_align" name="fv_wp_flowplayer_field_align">
              <option>Default</option>
              <option>Left</option>
              <option>Right</option>
            </select>
  				</td>
  			</tr>   			
  			<tr>
  				<th scope="row" class="label"></th>					
            	<td  style="padding-top: 20px;"><input type="button" value="Insert" name="insert" id="fv_wp_flowplayer_field_insert-button" class="button-primary alignleft" onclick="fv_wp_flowplayer_submit();" />
  				</td>
  			</tr>
            <?php if( !$allow_uploads && current_user_can('manage_options') ) { ?> 
            <tr>
              <td colspan="2">
              	<div class="fv-wp-flowplayer-notice">Admin note: Video uploads are currenty disabled, set 'Allow User Uploads' to true in <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer">Settings</a></div>
              </td>
            </tr>            
            <?php } ?>
            <?php if( current_user_can('manage_options') ) { ?> 
            <tr>
              <td colspan="2">
              	<div class="fv-wp-flowplayer-notice fv-wp-flowplayer-note">Admin note: Enable more per video features in Interface options in <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer#interface">Settings</a></div>
              </td>
            </tr>            
            <?php } ?>            
  		</tbody>
  	</table>
  </div>
</div>