<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Conversion {

  public function __construct() {
    add_action( 'admin_notices', array( $this, 'convert__start') );
    add_action( 'fv_player_conversion_buttons', array( $this, 'conversion_button') );
  }

  public function conversion_button() {
    ?>
      <tr>
        <td><label>Convert JW Player shortcodes to <code>[fvplayer]</code>:</label></td>
        <td>
          <p class="description">
            <input type="button" class="button" value="<?php esc_attr_e('Convert JW Player shortcodes', 'fv-player-pro'); ?>" style="margin-top: 2ex;" onclick="if( confirm('<?php esc_attr_e('This converts the [jwplayer] shortcodes into [fvplayer] shortcodes.\n\n Please make sure you backup your database before continuing. You can use post revisions to get back to previous version of your posts as well.', 'fv-player-pro'); ?>') ) location.href='<?php echo wp_nonce_url( site_url('wp-admin/admin.php?page=fvplayer'), 'convert_jwplayer', 'convert_jwplayer'); ?>'; "/>
          </p>
        </td>
      </tr>
    <?php
  }

  function convert__start() {
    if( current_user_can('manage_options') && isset($_GET['convert_jwplayer']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['convert_jwplayer'] ) ),'convert_jwplayer') ) {
      $this->convert__process('jwplayer');
    }
  }

  function convert__process( $type = false ) {
    echo '<p>Running the conversion process for ' . esc_html( $type ) . '. If anything fails, remember to restore your backup or revert the change of the post to the previous revision.</p>';
    echo '<p>Scroll down to the end of the list to see the status.</p>';

    $sType = sanitize_title($type);

    global $wpdb;
    $aPosts = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_status != 'inherit' AND post_content LIKE %s ORDER BY post_date DESC",
        '%' . $wpdb->esc_like( $sType ) . '%'
      )
    );

    $tMax = ini_get('max_execution_time') ? ini_get('max_execution_time') : 30;
    $tStart = microtime(true);
    $iCount = 0;
    $iFound = 0;

    echo "<ul>\n";
    foreach( $aPosts AS $objPost ) {
      if( microtime(true) - $tStart > ($tMax - 2) ) {
        break;
      }

      echo "<li><strong>" .  esc_html( $objPost->post_title ) . '</strong> (' . intval( $objPost->ID ) . ') ';

      $method = 'convert__'.$sType.'_callback';
      $new_content = $this->$method($objPost->post_content);

      if( strlen($new_content) != strlen($objPost->post_content) || $new_content != $objPost->post_content ) {
        $iFound++;

        $post_id = wp_update_post( array( 'ID' => $objPost->ID, 'post_content' => $new_content ) );
        if( is_wp_error($post_id) ) {
          $errors = $post_id->get_error_messages();
          echo "Error: ";
          foreach ($errors as $error) {
            echo esc_html( $error );
          }
          echo "</li>";
        } else {
          $iCount++;
          echo "<a target='_blank' href='".get_permalink($objPost->ID)."'>".$objPost->post_title."</a> updated ok</li>\n";
        }
      }

    }
    echo "</ul>\n";

    if( $iFound == 0 ) {
      echo "<p>No more posts with " . esc_html( $type )." embeds found!</p>\n";
    } else {
      echo "<p>Updated " . intval( $iCount ) . " posts out of " . intval( $iFound ) . " posts with " . esc_html( $type ) . " embeds.</p>\n";
    }

    if( microtime(true) - $tStart > ($tMax - 5) ) {
      echo "<p><strong>Execution terminated</strong>: PHP max_execution_time reached, run this process again to process the remaining posts!</p>\n";
    } else {
      echo "<p>All done!</p>\n";
    }

    die();
  }

  function convert__jwplayer_callback( $content ) {
    $content = preg_replace_callback( '~\[jwplayer.*?\]~', array( $this, 'convert__jwplayer_callback_parse' ), $content );
    return $content;
  }

  function convert__jwplayer_callback_parse( $aMatch ) {

    echo '<p>Replacing <code>' . esc_html( $aMatch[0] ) . '</code><p>';

    $bGotSomething = false;

    $aFVPlayer = array();

    $shortcode = rtrim($aMatch[0],']');

    $aJW = shortcode_parse_atts($shortcode);

    if( !empty($aJW['playlist']) ) {
      echo "<p><code>playlist</code> argument not supported!</p>";
      return $aMatch[0];
    }

    if( !empty($aJW['file']) ) {
      if( stripos($aJW['file'],'.xml') !== false ) {
        echo "<p><code>XML MRSS</code> not supported!</p>";
        return $aMatch[0];
      }

      $bGotSomething = true;
      $aFVPlayer['src'] = $aJW['file'];
    }
    if( !empty($aJW['image']) ) $aFVPlayer['splash'] = $aJW['image'];

    if( !empty($aJW['playlistid']) ) {
      $sPlaylistItems = get_post_meta( $aJW['playlistid'], 'jwplayermodule_playlist_items', true );
      if( !$sPlaylistItems ) $sPlaylistItems = get_post_meta( $aJW['playlistid'], 'jwplayermodule_playlist_items', true );

      $aAttachments = get_posts( array( 'include' => $sPlaylistItems, 'orderby' => 'post__in', 'post_type' => 'attachment' ) );
      if( count($aAttachments) > 0 ) {
        $iCount = 0;
        $aFVPlaylist = array();
        $aFVCaptions = array();
        foreach( $aAttachments AS $objAttachment ) {
          $src = get_post_meta($objAttachment->ID,'_wp_attached_file',true);
          $src = $this->get_full_url($src);

          $splash = $this->parse_jwplayer_image($objAttachment->ID);

          if( $iCount == 0 ) {
            $aFVPlayer['src'] = $src;
            if( $splash ) $aFVPlayer['splash'] = $splash;
          }
          else {
            $item = $src;
            if( $splash ) {
              $src .= ','.$splash;
            }
            $aFVPlaylist[] = $src;
          }

          $aFVCaptions[] = str_replace( array('"',';'), '', flowplayer::esc_caption($objAttachment->post_title) );
          $iCount++;
        }
        $aFVPlayer['playlist'] = implode(';',$aFVPlaylist);
        $aFVPlayer['caption'] = implode(';',$aFVCaptions);
        $bGotSomething = true;
      }

    } else if( !empty($aJW['mediaid']) ) {
      $objAttachment = get_post($aJW['mediaid']);
      if( $objAttachment ) {
        $src = get_post_meta($objAttachment->ID,'_wp_attached_file',true);
        $src = $this->get_full_url($src);

        $splash = $this->parse_jwplayer_image($objAttachment->ID);

        $aFVPlayer['src'] = $src;
        if( $splash ) $aFVPlayer['splash'] = $splash;
        $aFVPlayer['caption'] = flowplayer::esc_caption($objAttachment->post_title);
        $bGotSomething = true;
      }
    }

    if( !$bGotSomething ) {
      echo "<p>No arguments recognized!</p>";
      return $aMatch[0];
    }

    $aShortcode = array();
    foreach( $aFVPlayer AS $k => $v ) {
      $aShortcode[] = $k.'="'.$v.'"';
    }

    $out = '[fvplayer '.implode(' ',$aShortcode).']';
    echo '<p>With <code>' . esc_html( $out ) . '</code><p>';
    return $out;
  }


  function get_full_url( $url ) {
    if( stripos($url,'://') === false && stripos($url,'/') !== 0 ) {
      $aUploads = wp_upload_dir();
      $url = trailingslashit($aUploads['baseurl']).$url;
    }
    return $url;
  }


  function parse_jwplayer_image( $attachment_id ) {
    $splash_id = get_post_meta($attachment_id,'jwplayermodule_thumbnail',true);
    $splash_url = false;

    if( is_numeric($splash_id) ) {
      if( $featured_image = wp_get_attachment_image_src( $splash_id, 'large' ) ) {
        $splash_url = $featured_image[0];
      }
    } else if( !$splash_id ) {
      if( $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id($attachment_id), 'large' ) ) {
        $splash_url = $featured_image[0];
      }
    } else {
      $splash_url = $this->get_full_url($splash_id);
    }
    return $splash_url;
  }

}

$FV_Player_Conversion = new FV_Player_Conversion();
