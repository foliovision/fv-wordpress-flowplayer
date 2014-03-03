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
  $post_id = $post->ID;
  
  $fv_flowplayer_conf = get_option( 'fvwpflowplayer' );
  $allow_uploads = false;

	if( isset($fv_flowplayer_conf["allowuploads"]) && $fv_flowplayer_conf["allowuploads"] == 'true' ) {
	  $allow_uploads = $fv_flowplayer_conf["allowuploads"];
	  $upload_field_class = ' with-button';
	} else {
	  $upload_field_class = '';
	}
	
	$fv_flowplayer_helper_tag = ( is_plugin_active('jetpack/jetpack.php') ) ? 'b' : 'span';
?>
<style>
.fv-wp-flowplayer-notice { background-color: #FFFFE0; border-color: #E6DB55; margin: 5px 0 15px; padding: 0 0.6em; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; } 
.fv-wp-flowplayer-notice.fv-wp-flowplayer-note { background-color: #F8F8F8; border-color: #E0E0E0; } 
.fv-wp-flowplayer-notice p { font-family: sans-serif; font-size: 12px; margin: 0.5em 0; padding: 2px; } 
.fv_wp_flowplayer_playlist_remove { display: none; }
#fv-flowplayer-playlist table { border-bottom: 1px #eee solid; }
#fv-flowplayer-playlist table input, #fv-flowplayer-playlist table input.with-button { width: 93%; }
#fv-flowplayer-playlist table:first-child input.with-button { width: 70%; }
#fv-flowplayer-playlist table tr.video-size { display: none; }
#fv-flowplayer-playlist table tr#fv_wp_flowplayer_add_format_wrapper { display: none; }
#fv-flowplayer-playlist table tr#fv_wp_flowplayer_file_info { display: none; }
#fv-flowplayer-playlist table .fv_wp_flowplayer_field_rtmp { visibility: hidden; }
#fv-flowplayer-playlist table .fv_wp_flowplayer_field_rtmp_wrapper th { visibility: hidden; }
#fv-flowplayer-playlist table .button { display: none; }
#fv-flowplayer-playlist table:first-child tr.video-size { display: table-row; }
#fv-flowplayer-playlist table:first-child tr#fv_wp_flowplayer_add_format_wrapper { display: table-row; }
#fv-flowplayer-playlist table:first-child tr#fv_wp_flowplayer_file_info { display: none; }
#fv-flowplayer-playlist table:first-child .fv_wp_flowplayer_field_rtmp { visibility: visible; }
#fv-flowplayer-playlist table:first-child .fv_wp_flowplayer_field_rtmp_wrapper th { visibility: visible; }
#fv-flowplayer-playlist table:first-child .button { display: inline-block; }
/*#colorbox, #cboxOverlay, #cboxWrapper{ z-index: 100000; }*/
</style>
  
<script>
var fvwpflowplayer_helper_tag = '<?php echo $fv_flowplayer_helper_tag ?>';
var fv_wp_flowplayer_re_edit = /\[[^\]]*?<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>[^\]]*?\]/mi;
var fv_wp_flowplayer_re_insert = /<<?php echo $fv_flowplayer_helper_tag; ?>[^>]*?rel="FCKFVWPFlowplayerPlaceholder"[^>]*?>.*?<\/<?php echo $fv_flowplayer_helper_tag; ?>>/gi;
</script>

<div style="display: none">
  <div id="fv-wordpress-flowplayer-popup">
    <div id="fv-flowplayer-playlist">
  	  <table class="slidetoggle describe fv-flowplayer-playlist-item" width="100%">
        <tbody>
          <?php do_action( 'fv_flowplayer_shortcode_editor_before' ); ?>
          <tr>
            <th scope="row" class="label" style="width: 18%">
              <a class="alignleft fv_wp_flowplayer_playlist_remove" href="#" onclick="return fv_wp_flowplayer_playlist_remove(this)">(remove)</a>
              <label for="fv_wp_flowplayer_field_src" class="alignright">Video</label>
            </th>
            <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src" name="fv_wp_flowplayer_field_src" value="" />
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
          <tr class="video-size"><th></th>
            <td class="field"><label for="fv_wp_flowplayer_field_width">Width <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_width" class="fv_wp_flowplayer_field_width" name="fv_wp_flowplayer_field_width" style="width: 18%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_height">Height <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_height" class="fv_wp_flowplayer_field_height" name="fv_wp_flowplayer_field_height" style="width: 18%" value=""/></td>
          </tr>
          
          <tr style="display: none;" class="fv_wp_flowplayer_field_src_1_wrapper">
            <th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_src_1" class="alignright">Video <small>(another format)</small></label></th>
            <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src_1" name="fv_wp_flowplayer_field_src_1" value=""/>
            <?php if ($allow_uploads=="true") { ?> 
              <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_video_1&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
            <?php }; //allow uplads video ?>
            </td>
          </tr>
          
          <tr style="display: none;" class="fv_wp_flowplayer_field_src_2_wrapper">
            <th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_src_2" class="alignright">Video <small>(another format)</small></label></th>
            <td colspan="2" class="field"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_src_2" name="fv_wp_flowplayer_field_src_2" value=""/>
            <?php if ($allow_uploads=="true") {	?>  
              <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_video_2&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
            <?php }; //allow uplads video ?>
            </td>    			
          </tr>
          
          <tr style="display: none;" class="fv_wp_flowplayer_field_rtmp_wrapper">
            <th scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_rtmp" class="alignright">RTMP Server</label> <?php if( !empty($fv_flowplayer_conf["rtmp"]) ) : ?>(<abbr title="Leave empty to use Flash streaming server from plugin settings">?</abbr>)<?php endif; ?></th>
            <td colspan="2" class="field">
              <input type="text" class="text fv_wp_flowplayer_field_rtmp" id="fv_wp_flowplayer_field_rtmp" name="fv_wp_flowplayer_field_rtmp" value="" style="width: 40%" placeholder="<?php if( !empty($fv_flowplayer_conf["rtmp"]) ) echo $fv_flowplayer_conf["rtmp"]; ?>" />
              &nbsp;<label for="fv_wp_flowplayer_field_rtmp_path"><strong>RTMP Path</strong></label>
              <input type="text" class="text fv_wp_flowplayer_field_rtmp_path" id="fv_wp_flowplayer_field_rtmp_path" name="fv_wp_flowplayer_field_rtmp_path" value="" style="width: 37%" />
            </td> 
          </tr>  			
          
          <tr id="fv_wp_flowplayer_add_format_wrapper">
            <th scope="row" class="label" style="width: 18%"></th>
            <td class="field" style="width: 50%"><div id="add_format_wrapper"><a href="#" class="partial-underline" onclick="fv_wp_flowplayer_add_format()" style="outline: 0"><span id="add-format">+</span>&nbsp;Add another format</a> (i.e. WebM, OGV)</div></td>
            <td class="field"><div id="add_rtmp_wrapper"><a href="#" class="partial-underline" onclick="fv_wp_flowplayer_add_rtmp()" style="outline: 0"><span id="add-rtmp">+</span>&nbsp;Add RTMP</a></div></td>  				
          </tr>      
          
          <tr<?php if( $fv_flowplayer_conf["interface"]["mobile"] !== 'true' ) echo ' style="display: none"'; ?>>
            <th scope="row" class="label"><label for="fv_wp_flowplayer_field_mobile" class="alignright">Mobile Video*</label></th>
            <td class="field" colspan="2"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_mobile" name="fv_wp_flowplayer_field_mobile" value=""/>
              <?php if ($allow_uploads=='true') { ?>
                <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_mobile&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Video</a>
              <?php }; //allow uploads splash image ?></td>
          </tr>
          
          <tr>
            <th scope="row" class="label"><label for="fv_wp_flowplayer_field_splash" class="alignright">Splash Image</label></th>
            <td class="field" colspan="2"><input type="text" class="text fv_wp_flowplayer_field_splash<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_splash" name="fv_wp_flowplayer_field_splash" value=""/>
              <?php if ($allow_uploads=='true') { ?>
                <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_splash&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Image</a>
              <?php }; //allow uploads splash image ?></td>
          </tr>
        		        
          <tr<?php if( $fv_flowplayer_conf["interface"]["subtitles"] !== 'true' ) echo ' style="display: none"'; ?>>
            <th scope="row" class="label"><label for="fv_wp_flowplayer_field_subtitles" class="alignright">Subtitles</label></th>
            <td class="field" colspan="2"><input type="text" class="text<?php echo $upload_field_class; ?>" id="fv_wp_flowplayer_field_subtitles" name="fv_wp_flowplayer_field_subtitles" value=""/>
              <?php if ($allow_uploads=='true') { ?>
                <a class="thickbox button add_media" href="media-upload.php?post_id=<?php echo $post_id; ?>&amp;type=fvplayer_subtitles&amp;TB_iframe=true&amp;width=500&amp;height=300"><span class="wp-media-buttons-icon"></span> Add Subtitles</a>
              <?php }; //allow uploads splash image ?></td>
          </tr>
  
        </tbody>
      </table>
    </div><!-- #fv-flowplayer-playlist-->
    <table<?php if( $fv_flowplayer_conf["interface"]["playlist"] !== 'true' ) echo ' style="display: none"'; ?>>
      <tr id="fv_wp_flowplayer_add_format_wrapper">
        <th scope="row" class="label" style="width: 18%"></th>
        <td class="field" style="width: 50%"></td>
        <td class="field"><div id="add_rtmp_wrapper"><a style="outline: 0" onclick="return fv_flowplayer_playlist_add()" class="partial-underline" href="#"><span id="add-rtmp">+</span>&nbsp;Add Playlist Item</a></div></td>  				
      </tr>
    </table>  					      
    <table width="100%">
      <tbody> 
        <?php
        foreach( $fv_flowplayer_conf["interface"] AS $option ) {
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
        <tr<?php if( $fv_flowplayer_conf["interface"]["popup"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_popup" class="alignright">HTML Popup</label></th>
  				<td><textarea type="text" id="fv_wp_flowplayer_field_popup" name="fv_wp_flowplayer_field_popup" style="width: 93%"></textarea></td>
  			</tr>
        <tr<?php if( $fv_flowplayer_conf["interface"]["redirect"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_redirect" class="alignright">Redirect to</label></th>
  				<td class="field"><input type="text" id="fv_wp_flowplayer_field_redirect" name="fv_wp_flowplayer_field_redirect" style="width: 93%" /></td>
  			</tr>
        <tr<?php if( $fv_flowplayer_conf["interface"]["autoplay"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_autoplay" class="alignright">Autoplay</label></th>
  				<td class="field">
            <select id="fv_wp_flowplayer_field_autoplay" name="fv_wp_flowplayer_field_autoplay">
              <option>Default</option>
              <option>On</option>
              <option>Off</option>
            </select>
          </td>
  			</tr>
        <tr<?php if( $fv_flowplayer_conf["interface"]["loop"] !== 'true' ) { echo ' style="display: none"'; } ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_loop" class="alignright">Loop</label></th>
  				<td class="field"><input type="checkbox" id="fv_wp_flowplayer_field_loop" name="fv_wp_flowplayer_field_loop" /></td>
  			</tr>   
        <tr<?php if( $fv_flowplayer_conf["interface"]["splashend"] !== 'true' ) { echo ' style="display: none"'; } ?>>
          <th scope="row" class="label">
            <label for="fv_wp_flowplayer_field_splashend">Splash end</label>
          </th>
          <td>
            <input type="checkbox" id="fv_wp_flowplayer_field_splashend" name="fv_wp_flowplayer_field_splashend" /> (show splash image at the end)
          </td> 
        </tr>    
        <tr<?php if( $fv_flowplayer_conf["interface"]["embed"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th scope="row" class="label"><label for="fv_wp_flowplayer_field_embed" class="alignright">Embeding</label></th>
  				<td class="field">
            <select id="fv_wp_flowplayer_field_embed" name="fv_wp_flowplayer_field_embed">
              <option>Default</option>
              <option>On</option>
              <option>Off</option>
            </select>
          </td>
  			</tr>           
        <tr<?php if( $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_ad" class="alignright">Ad code</label></th>
  				<td>
  					<textarea type="text" id="fv_wp_flowplayer_field_ad" name="fv_wp_flowplayer_field_ad" style="width: 93%"></textarea>
  				</td>
  			</tr> 
  			<tr<?php if( $fv_flowplayer_conf["interface"]["ads"] !== 'true' ) echo ' style="display: none"'; ?>><th></th>
  				<td class="field">
  					<label for="fv_wp_flowplayer_field_ad_width">Width <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_width" name="fv_wp_flowplayer_field_ad_width" style="width: 18%; margin-right: 25px;"  value=""/> <label for="fv_wp_flowplayer_field_ad_height">Height <small>(px)</small></label> <input type="text" id="fv_wp_flowplayer_field_ad_height" name="fv_wp_flowplayer_field_ad_height" style="width: 18%" value=""/><br />
  					<input type="checkbox" id="fv_wp_flowplayer_field_ad_skip" name="fv_wp_flowplayer_field_ad_skip" /> Skip global ad in this video  					
  				</td>
  			</tr>			
        <tr<?php if( $fv_flowplayer_conf["interface"]["align"] !== 'true' ) echo ' style="display: none"'; ?>>
  				<th valign="top" scope="row" class="label" style="width: 18%"><label for="fv_wp_flowplayer_field_align" class="alignright">Align</label></th>
  				<td>
            <select id="fv_wp_flowplayer_field_align" name="fv_wp_flowplayer_field_align">
              <option>Default</option>
              <option>Left</option>
              <option>Right</option>
            </select>
  				</td>
  			</tr>
        <?php do_action( 'fv_flowplayer_shortcode_editor_after' ); ?>        
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
			<tr<?php if( $fv_flowplayer_conf["interface"]["mobile"] !== 'true' ) echo ' style="display: none"'; ?>>
			  <td colspan="2">* - currently not working with playlist</td>
			</tr>
  		</tbody>
  	</table>
  </div>
</div>