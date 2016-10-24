<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

  global $post;
  $post_id = isset($post->ID) ? $post->ID : 0;
  
  $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if( isset($fv_flowplayer_conf["allowuploads"]) && $fv_flowplayer_conf["allowuploads"] == 'true' ) {
	  $allow_uploads = $fv_flowplayer_conf["allowuploads"];
	  $upload_field_class = ' with-button';
	} else {
	  $upload_field_class = '';
	}
  
  function fv_flowplayer_admin_select_popups($aArgs){
  global $fv_fp;
  
  $aPopupData = get_option('fv_player_popups');
  

  $sId = (isset($aArgs['id'])?$aArgs['id']:'popups_default');
  $aArgs = wp_parse_args( $aArgs, array( 'id'=>$sId, 'item_id'=>'', 'show_default' => false ) );
  ?>
  <select id="<?php echo $aArgs['id']; ?>" name="<?php echo $aArgs['id']; ?>">
    <?php if( $aArgs['show_default'] ) : ?>
      <option>Use site default</option>
    <?php endif; ?>
    <option <?php if( $aArgs['item_id'] == 'no' ) echo 'selected '; ?>value="no">None</option>
    <option <?php if( $aArgs['item_id'] == 'random' ) echo 'selected '; ?>value="random">Random</option>
    <?php
    if( isset($aPopupData) && is_array($aPopupData) && count($aPopupData) > 0 ) {
      foreach( $aPopupData AS $key => $aPopupAd ) {
        ?><option <?php if( $aArgs['item_id'] == $key ) echo 'selected'; ?> value="<?php echo $key; ?>"><?php
        echo $key;
        if( !empty($aPopupAd['name']) ) echo ' - '.$aPopupAd['name'];
        if( $aPopupAd['disabled'] == 1 ) echo ' (currently disabled)';
        ?></option><?php
      }
    } ?>      
  </select>
  <?php
}
  
	$fv_flowplayer_helper_tag = ( is_plugin_active('jetpack/jetpack.php') ) ? 'b' : 'span';
?>
<style>
#fv-player-shortcode-editor { background-color: white; }
.fv-wp-flowplayer-notice { background-color: #FFFFE0; border-color: #E6DB55; margin: 5px 0 15px; padding: 0 0.6em; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; } 
.fv-wp-flowplayer-notice.fv-wp-flowplayer-note { background-color: #F8F8F8; border-color: #E0E0E0; } 
.fv-wp-flowplayer-notice p { font-family: sans-serif; font-size: 12px; margin: 0.5em 0; padding: 2px; } 
.fv_wp_flowplayer_playlist_remove { display: none; }
#fv-flowplayer-playlist table { border-bottom: 1px #eee solid; }
#fv-player-shortcode-editor table input[type=text], #fv-flowplayer-playlist table input[type=text].with-button { width: 93%; }
#fv-player-shortcode-editor table input[type=text].half-field { width: 46%; }
#fv-player-shortcode-editor table/*:first-child*/ input.with-button { width: 70%; }
#fv-player-shortcode-editor table input.fv_wp_flowplayer_field_subtitles { width: 82%; }
#fv-player-shortcode-editor table input.fv_wp_flowplayer_field_subtitles.with-button { width: 59%; }
#fv-player-shortcode-editor table select.fv_wp_flowplayer_field_subtitles_lang { width: 10%; }
#fv-flowplayer-playlist table tr.video-size { display: none; }
#fv-flowplayer-playlist table tr#fv_wp_flowplayer_add_format_wrapper { display: none; }
#fv-flowplayer-playlist table tr#fv_wp_flowplayer_file_info { display: none; }
#fv-flowplayer-playlist table .fv_wp_flowplayer_field_rtmp { visibility: hidden; }
#fv-flowplayer-playlist table .fv_wp_flowplayer_field_rtmp_wrapper th { visibility: hidden; }
#fv-flowplayer-playlist table .hint { display: none; }
/*#fv-flowplayer-playlist table .button { display: none; }*/
#fv-flowplayer-playlist table:first-child tr.video-size { display: table-row; }
#fv-flowplayer-playlist table:first-child .hint { display: inline; }
#fv-flowplayer-playlist table:first-child tr#fv_wp_flowplayer_add_format_wrapper { display: table-row; }
#fv-flowplayer-playlist table:first-child tr#fv_wp_flowplayer_file_info { display: none; }
#fv-flowplayer-playlist table:first-child .fv_wp_flowplayer_field_rtmp { visibility: visible; }
#fv-flowplayer-playlist table:first-child .fv_wp_flowplayer_field_rtmp_wrapper th { visibility: visible; }
/*#fv-flowplayer-playlist table:first-child .button { display: inline-block; }*/
/*#colorbox, #cboxOverlay, #cboxWrapper{ z-index: 100000; }*/

fv-player-shortcode-editor{
  width:1000px;
}
#fv-player-shortcode-editor-editor{
  top:0px;
  
}
#fv-player-shortcode-editor-preview{
  height: 300px;
  width: 460px;
}
#fv-player-shortcode-editor td{
  vertical-align: top;
}
.fv-player-tabs-header .nav-tab-wrapper{
  margin:0;
}
.fv_player_actions_end-toggle{
  display:none;
}
.fv_player_interface_hide{
  display:none;
}

</style>
  
<script>
var fvwpflowplayer_helper_tag = '<?php echo $fv_flowplayer_helper_tag ?>';
var fv_wp_flowplayer_re_edit = /\[[^\]]*?<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>.*?[^\\]\]/mi;
var fv_wp_flowplayer_re_insert = /<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>/gi;
var fv_Player_site_base = "<?php echo home_url(); ?>";
<?php global $fv_fp; if( isset($fv_fp->conf['postthumbnail']) && $fv_fp->conf['postthumbnail'] == 'true' ) : ?>
var fv_flowplayer_set_post_thumbnail_id = <?php echo $post_id; ?>;
var fv_flowplayer_set_post_thumbnail_nonce = '<?php echo wp_create_nonce( "set_post_thumbnail-$post_id" ); ?>';
<?php endif; ?>
</script>

<div style="display: none">
  <div id="fv-player-shortcode-editor">
    <div id="fv-player-shortcode-editor-editor">
      <table>
        <tr>

          <td>
            <div id="fv-player-shortcode-editor-preview">
              <div id="fv-player-shortcode-editor-preview-spinner">Fancy loading GIF</div>
              <iframe id="fv-player-shortcode-editor-preview-iframe"></iframe>
            </div>
          </td>
          <td>
            <div class="fv-player-tabs-header">
              <h2 class="nav-tab-wrapper hide-if-no-js">
                <a href="#" class="nav-tab nav-tab-active" style="outline: 0px;" data-tab="fv-player-tab-video-files">Video</a>
                <a href="#" class="nav-tab" style="outline: 0px;" data-tab="fv-player-tab-subtitles">Subtitles</a>
                <a href="#" class="nav-tab" style="outline: 0px;" data-tab="fv-player-tab-extras">Extras</a>
                <a href="#" class="nav-tab" style="outline: 0px;" data-tab="fv-player-tab-actions">Actions</a>
              </h2>
            </div>
            <div class="fv-player-tabs">
              <div class="fv-player-tab fv-player-tab-video-files">
                <table class="slidetoggle describe fv-player-playlist-item" width="100%">
                  <tbody>
                    <?php do_action('fv_flowplayer_shortcode_editor_before'); ?>
                    <tr>
                      <th scope="row" class="label" style="width: 19%">
                        <a class="alignleft fv_wp_flowplayer_playlist_remove" href="#" onclick="return fv_wp_flowplayer_playlist_remove(this)"><?php _e('(remove)', 'fv_flowplayer'); ?></a>
                        <label for="fv_wp_flowplayer_field_src" class="alignright"><?php _e('Video', 'fv_flowplayer'); ?></label>
                      </th>
                      <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src" name="fv_wp_flowplayer_field_src" value="" />
                        <?php if ($allow_uploads == "true") { ?>      
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Video', 'fv_flowplayer'); ?></a>
                        <?php }; //allow uplads video ?></td>
                    </tr>

                    <tr style="display: none" id="fv_wp_flowplayer_file_info">
                      <th></th>
                      <td colspan="2">
                        <?php _e('Video Duration', 'fv_flowplayer'); ?>: <span id="fv_wp_flowplayer_file_duration"></span><br />
                        <?php _e('File size', 'fv_flowplayer'); ?>: <span id="fv_wp_flowplayer_file_size"></span>
                      </td>
                    </tr>
                    <tr class="video-size"><th></th>
                      <td class="field" colspan="2"><label for="fv_wp_flowplayer_field_width"><?php _e('Width', 'fv_flowplayer'); ?> <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_width" class="fv_wp_flowplayer_field_width" name="fv_wp_flowplayer_field_width" style="width: 19%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_height"><?php _e('Height', 'fv_flowplayer'); ?> <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_height" class="fv_wp_flowplayer_field_height" name="fv_wp_flowplayer_field_height" style="width: 19%" value=""/></td>
                    </tr>

                    <tr style="display: none;" class="fv_wp_flowplayer_field_src_1_wrapper">
                      <th scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_src_1" class="alignright"><?php _e('Video', 'fv_flowplayer'); ?> <small><?php _e('(another format)', 'fv_flowplayer'); ?></small></label></th>
                      <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src_1" name="fv_wp_flowplayer_field_src_1" value=""/>
                        <?php if ($allow_uploads == "true") { ?> 
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Video', 'fv_flowplayer'); ?></a>
                        <?php }; //allow uplads video ?>
                      </td>
                    </tr>

                    <tr style="display: none;" class="fv_wp_flowplayer_field_src_2_wrapper">
                      <th scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_src_2" class="alignright"><?php _e('Video', 'fv_flowplayer'); ?> <small><?php _e('(another format)', 'fv_flowplayer'); ?></small></label></th>
                      <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src_2" name="fv_wp_flowplayer_field_src_2" value=""/>
                        <?php if ($allow_uploads == "true") { ?>  
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Video', 'fv_flowplayer'); ?></a>
                        <?php }; //allow uplads video ?>
                      </td>    			
                    </tr>

                    <tr class="fv_wp_flowplayer_field_rtmp_wrapper">
                      <th scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_rtmp" class="alignright"><?php _e('RTMP Server', 'fv_flowplayer'); ?></label> <?php if (!empty($fv_flowplayer_conf["rtmp"])) : ?>(<abbr title="<?php _e('Leave empty to use Flash streaming server from plugin settings', 'fv_flowplayer'); ?>">?</abbr>)<?php endif; ?></th>
                      <td colspan="2" class="field">
                        <input type="text" class="text fv_wp_flowplayer_field_rtmp" id="fv_wp_flowplayer_field_rtmp" name="fv_wp_flowplayer_field_rtmp" value="" style="width: 40%" placeholder="<?php if (!empty($fv_flowplayer_conf["rtmp"])) echo $fv_flowplayer_conf["rtmp"]; ?>" />
                        &nbsp;<label for="fv_wp_flowplayer_field_rtmp_path"><strong><?php _e('RTMP Path', 'fv_flowplayer'); ?></strong></label>
                        <input type="text" class="text fv_wp_flowplayer_field_rtmp_path" id="fv_wp_flowplayer_field_rtmp_path" name="fv_wp_flowplayer_field_rtmp_path" value="" style="width: 37%" />
                      </td> 
                    </tr>  			

                    <tr id="fv_wp_flowplayer_add_format_wrapper">
                      <th scope="row" class="label" style="width: 19%"></th>
                      <td class="field" style="width: 50%"><div id="add_format_wrapper"><a href="#" class="partial-underline" onclick="fv_wp_flowplayer_add_format(); return false" style="outline: 0"><span id="add-format">+</span>&nbsp;<?php _e('Add another format', 'fv_flowplayer'); ?></a> <?php _e('(i.e. WebM, OGV)', 'fv_flowplayer'); ?></div></td>
                      <td class="field"><div id="add_rtmp_wrapper"><a href="#" class="partial-underline" onclick="fv_wp_flowplayer_add_rtmp(); return false" style="outline: 0"><span id="add-rtmp">+</span>&nbsp;<?php _e('Add RTMP', 'fv_flowplayer'); ?></a></div></td>  				
                    </tr>      

                    <tr <?php if( !isset($fv_flowplayer_conf["interface"]["mobile"]) || $fv_flowplayer_conf["interface"]["mobile"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                      <th scope="row" class="label"><label for="fv_wp_flowplayer_field_mobile" class="alignright"><?php _e('Mobile video', 'fv_flowplayer'); ?>*</label></th>
                      <td class="field" colspan="2"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_mobile" name="fv_wp_flowplayer_field_mobile" value="" placeholder="<?php _e('Put low-bandwidth video here or leave blank', 'fv_flowplayer'); ?>" />
                        <?php if ($allow_uploads == 'true') { ?>
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Video', 'fv_flowplayer'); ?></a>
                        <?php }; //allow uploads splash image ?></td>
                    </tr>

                    <tr>
                      <th scope="row" class="label"><label for="fv_wp_flowplayer_field_splash" class="alignright"><?php _e('Splash Image', 'fv_flowplayer'); ?></label></th>
                      <td class="field" colspan="2"><input type="text" class="text fv_wp_flowplayer_field_splash<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_splash" name="fv_wp_flowplayer_field_splash" value=""/>
                        <?php if ($allow_uploads == 'true') { ?>
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Image', 'fv_flowplayer'); ?></a>
                        <?php }; //allow uploads splash image ?></td>
                    </tr>


                    
                    <tr class="<?php if (isset($fv_flowplayer_conf["interface"]["playlist_captions"]) && $fv_flowplayer_conf["interface"]["playlist_captions"] == 'true') echo 'playlist_caption'; else echo 'fv_player_interface_hide'; ?>" >
                      <th scope="row" class="label"><label for="fv_wp_flowplayer_field_caption" class="alignright"><?php _e('Caption', 'fv_flowplayer'); ?></label></th>
                      <td class="field" colspan="2"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_caption" name="fv_wp_flowplayer_field_caption" value=""/></td>
                    </tr>

                    <?php do_action('fv_flowplayer_shortcode_editor_item_after'); ?>

                    <tr>
                      <th></th><td>* - <?php _e('currently not working with playlist', 'fv_flowplayer'); ?> </td>
                    </tr>            

                    <?php if (!$allow_uploads && current_user_can('manage_options')) : ?> 
                      <tr>
                        <td colspan="2">
                          <div class="fv-wp-flowplayer-notice"><?php _e('Admin note: Video uploads are currently disabled, set Allow User Uploads to true in', 'fv_flowplayer'); ?> <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer"><?php _e('Settings', 'fv_flowplayer'); ?></a></div>
                        </td>
                      </tr>            
                    <?php endif; ?>

                  </tbody>
                </table>      
              </div>

              <div class="fv-player-tab fv-player-tab-subtitles" style="display: none">
                <table width="100%">
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["subtitles"]) || $fv_flowplayer_conf["interface"]["subtitles"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_subtitles" class="alignright"><?php _e('Subtitles', 'fv_flowplayer'); ?></label></th>
                    <td class="field fv-fp-subtitles" colspan="2">
                      <div class="fv-fp-subtitle">
                        <select class="fv_wp_flowplayer_field_subtitles_lang" name="fv_wp_flowplayer_field_subtitles_lang">
                          <option></option>
                          <?php
                          $aLanguages = flowplayer::get_languages();
                          $aCurrent = explode('-', get_bloginfo('language'));
                          $sCurrent = ''; //aCurrent[0];
                          foreach ($aLanguages AS $sCode => $sLabel) {
                            ?><option value="<?php echo strtolower($sCode); ?>"<?php if (strtolower($sCode) == $sCurrent) echo ' selected'; ?>><?php echo $sCode; ?>&nbsp;&nbsp;(<?php echo $sLabel; ?>)</option>
                            <?php
                          }
                          ?>
                        </select>                
                        <input type="text" class="text<?php echo $upload_field_class; ?> fv_wp_flowplayer_field_subtitles" name="fv_wp_flowplayer_field_subtitles" value=""/>
                        <?php if ($allow_uploads == 'true') { ?>
                          <a class="button add_media" href="#"><span class="wp-media-buttons-icon"></span> <?php _e('Add Subtitles', 'fv_flowplayer'); ?></a>
                          <a class="fv-fp-subtitle-remove" href="#" style="display: none">X</a>
                        <?php }; ?>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                    </td>              
                    <td>
                      <a style="outline: 0" onclick="return fv_flowplayer_language_add(false, <?php echo ( isset($fv_flowplayer_conf["interface"]["playlist_captions"]) && $fv_flowplayer_conf["interface"]["playlist_captions"] == 'true' ) ? 'true' : 'false'; ?>)" class="partial-underline" href="#"><span class="add-subtitle-lang">+</span>&nbsp;<?php _e('Add Another Language', 'fv_flowplayer'); ?></a>
                    </td>
                  </tr>          
                </table>
              </div>

              <div class="fv-player-tab fv-player-tab-extras" style="display: none">
                <table width="100%">
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["autoplay"]) || $fv_flowplayer_conf["interface"]["autoplay"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_autoplay" class="alignright"><?php _e('Autoplay', 'fv_flowplayer'); ?></label></th>
                    <td class="field">
                      <select id="fv_wp_flowplayer_field_autoplay" name="fv_wp_flowplayer_field_autoplay">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('On', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Off', 'fv_flowplayer'); ?></option>
                      </select>
                    </td>
                  </tr>      
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["embed"]) || $fv_flowplayer_conf["interface"]["embed"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_embed" class="alignright"><?php _e('Embedding', 'fv_flowplayer'); ?></label></th>
                    <td class="field">
                      <select id="fv_wp_flowplayer_field_embed" name="fv_wp_flowplayer_field_embed">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('On', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Off', 'fv_flowplayer'); ?></option>
                      </select>
                    </td>
                  </tr>           
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th rowspan="2" valign="top" scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_ad" class="alignright"><?php _e('Ad code', 'fv_flowplayer'); ?></label></th>
                    <td>
                      <textarea type="text" id="fv_wp_flowplayer_field_ad" name="fv_wp_flowplayer_field_ad" style="width: 93%"></textarea>
                    </td>
                  </tr> 
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <td class="field" <?php if( !isset($fv_flowplayer_conf["interface"]["ads"]) || $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                      <label for="fv_wp_flowplayer_field_ad_width"><?php _e('Width', 'fv_flowplayer'); ?> <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_width" name="fv_wp_flowplayer_field_ad_width" style="width: 19%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_ad_height"><?php _e('Height', 'fv_flowplayer'); ?> <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_height" name="fv_wp_flowplayer_field_ad_height" style="width: 19%" value=""/><br />
                      <input type="checkbox" id="fv_wp_flowplayer_field_ad_skip" name="fv_wp_flowplayer_field_ad_skip" /> <?php _e('Skip global ad in this video', 'fv_flowplayer'); ?>  					
                    </td>
                  </tr>			
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["align"]) || $fv_flowplayer_conf["interface"]["align"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th valign="top" scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_align" class="alignright"><?php _e('Align', 'fv_flowplayer'); ?></label></th>
                    <td>
                      <select id="fv_wp_flowplayer_field_align" name="fv_wp_flowplayer_field_align">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Left', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Right', 'fv_flowplayer'); ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["controlbar"]) || $fv_flowplayer_conf["interface"]["controlbar"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>> 
                    <th valign="top" scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_controlbar" class="alignright"><?php _e('Controlbar', 'fv_flowplayer'); ?></label></th>
                    <td>
                      <select id="fv_wp_flowplayer_field_controlbar" name="fv_wp_flowplayer_field_controlbar">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Yes', 'fv_flowplayer'); ?></option>
                        <option><?php _e('No', 'fv_flowplayer'); ?></option>
                      </select>
                    </td>
                  </tr>
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["live"]) || $fv_flowplayer_conf["interface"]["live"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_live" class="alignright"><?php _e('Live stream', 'fv_flowplayer'); ?></label></th>
                    <td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_live" name="fv_wp_flowplayer_field_live" /></td>
                  </tr>
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["speed"]) || $fv_flowplayer_conf["interface"]["speed"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_speed" class="alignright"><?php _e('Speed Buttons', 'fv_flowplayer'); ?></label></th>
                    <td class="field">
                      <select id="fv_wp_flowplayer_field_speed" name="fv_wp_flowplayer_field_speed">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Yes', 'fv_flowplayer'); ?></option>
                        <option><?php _e('No', 'fv_flowplayer'); ?></option>
                      </select>
                    </td>
                  </tr>             
                </table>
              </div>

              <div class="fv-player-tab fv-player-tab-actions" style="display: none">
                <table width="100%">
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["end_actions"]) || $fv_flowplayer_conf["interface"]["end_actions"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                    <th scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_actions_end" class="alignright"><?php _e('End of video', 'fv_flowplayer'); ?></label></th>
                    <td class="field" style="width: 50%">
                      <select id="fv_wp_flowplayer_field_actions_end" name="fv_wp_flowplayer_field_actions_end">
                        <option value=""><?php _e('Nothing', 'fv_flowplayer'); ?></option>
                        <option value="redirect"><?php _e('Redirect', 'fv_flowplayer'); ?></option> 
                        <option value="loop"><?php _e('Loop', 'fv_flowplayer'); ?></option>
                        <option value="popup"><?php _e('Show popup', 'fv_flowplayer'); ?></option>
                        <option value="splashend"><?php _e('Show splash screen', 'fv_flowplayer'); ?></option>
                      </select>          
                    </td>  				
                  </tr>
                                   
                  <tr class="fv_player_actions_end-toggle">
                    <th scope="row" class="label"><label for="fv_wp_flowplayer_field_redirect" class="alignright"><?php _e('Redirect to', 'fv_flowplayer'); ?></label></th>
                    <td class="field"><input type="text" id="fv_wp_flowplayer_field_redirect" name="fv_wp_flowplayer_field_redirect" style="width: 93%" /></td>
                  </tr>
                  
                  <tr class="fv_player_actions_end-toggle">
                    <th valign="top" scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_popup_id" class="alignright"><?php _e('End popup', 'fv_flowplayer'); ?></label></th>
                    <td <?php if ($fv_flowplayer_conf["interface"]["popup"] !== 'true') echo ' class="fv_player_interface_hide"'; ?> >
                      <!-- legacy -->
                      
                      <!-- end legacy -->
                      <?php fv_flowplayer_admin_select_popups(array('id' => 'fv_wp_flowplayer_field_popup_id', 'show_default' => true)) ?>
                      <div>
                        <p><span class="dashicons dashicons-warning"></span> You are using the legacy popup functionality. Move the popup code <a href="<?php echo site_url(); ?>/wp-admin/options-general.php?page=fvplayer#tab_popups" target="_target">here</a>, then use the drop down menu above.</p>
                        <textarea type="text" id="fv_wp_flowplayer_field_popup" name="fv_wp_flowplayer_field_popup" style="width: 93%"></textarea>
                      </div>                      
                    </td>
                  </tr>
                  
                  
                  <tr <?php if( !isset($fv_flowplayer_conf["interface"]["playlist"]) || $fv_flowplayer_conf["interface"]["playlist"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?> id="fv_wp_flowplayer_add_format_wrapper">
                    <th scope="row" class="label" style="width: 19%"><label for="fv_wp_flowplayer_field_liststyle" class="alignright"><?php _e('Playlist Style', 'fv_flowplayer'); ?></label></th>
                    <td class="field" style="width: 50%">
                      <select id="fv_wp_flowplayer_field_liststyle" name="fv_wp_flowplayer_field_liststyle">
                        <option><?php _e('Default', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Tabs', 'fv_flowplayer'); ?></option> 
                        <option><?php _e('Prev/Next', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Vertical', 'fv_flowplayer'); ?></option>
                        <option><?php _e('Horizontal', 'fv_flowplayer'); ?></option>
                      </select>          
                    </td>  				
                  </tr>

                  <?php do_action('fv_flowplayer_shortcode_editor_after'); ?>

                </table>
              </div>
               
              <div <?php if( !isset($fv_flowplayer_conf["interface"]["playlist"]) || $fv_flowplayer_conf["interface"]["playlist"] !== 'true' ) echo ' class="fv_player_interface_hide"'; ?>>
                <span id="playlist_edit" class="alignleft" style="display: none;"><a style="outline: 0" onclick="return fv_flowplayer_playlist_show()" class="partial-underline" href="#"><?php _e('Edit Playlist', 'fv_flowplayer'); ?></a></span>
                <span id="playlist_create" class="alignleft"><a style="outline: 0" onclick="return fv_flowplayer_playlist_show()" class="partial-underline" href="#"><?php _e('Create Playlist', 'fv_flowplayer'); ?></a></span>
              </div>
              
              <input type="button" value="<?php _e('Insert', 'fv_flowplayer'); ?>" name="insert" id="fv_wp_flowplayer_field_insert-button" class="button-primary alignright" onclick="fv_wp_flowplayer_submit();" />    
            </div>
            
            
            
            
            <div class="fv-player-playlist" style="display: none">
              <table class="slidetoggle describe fv-player-playlist-item" width="100%">
                <thead>
                  <tr>
                    <th>Video</th>
                    <th>Splash</th>
                    <th>Caption</th>
                    <th></th>
                  </tr>  
                </thead>
                <tbody>
                  <tr>
                    <td class="fvp_item_video">(new video)</td>
                    <td class="fvp_item_splash">-</td>
                    <td class="fvp_item_caption">-</td>
                    <td class="fvp_item_remove"><a style="display:none;" href="#" >x</a></td>
                  </tr>  
                </tbody>        
              </table>

              <div id="playlist_add_item" class="alignleft"><a style="outline: 0" onclick="return fv_flowplayer_playlist_add()" class="partial-underline" href="#"><span id="add-rtmp">+</span>&nbsp;<?php _e('Add Playlist Item', 'fv_flowplayer'); ?></a></div>
              <input type="button" value="<?php _e('Insert', 'fv_flowplayer'); ?>" name="insert" id="fv_wp_flowplayer_field_insert-button" class="button-primary alignright" onclick="fv_wp_flowplayer_submit();" />

            </div>
            
            
            
          </td>
        </tr>
      </table>
    </div>   
  </div>
</div>