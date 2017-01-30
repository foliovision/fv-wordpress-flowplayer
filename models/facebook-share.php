<?php

class FV_Facebook_Share {

  public function __construct() {

    add_action('wp_head', array($this, 'fb_share_tags'), 0);
    add_action('fv_player_admin_integrations', array($this, 'admin_integrations'), 0);
  }

  function fb_share_tags() {
    global $fv_fp, $post, $FV_Player_Pro;
    if (!isset($fv_fp->conf['integrations']['facebook_sharing']) || $fv_fp->conf['integrations']['facebook_sharing'] !== 'true' || !is_singular())
      return;

    $content = $post->post_content;

    $matches = array();
    if (!preg_match("/\[fvplayer[^]]*/", $content, $matches))
      return;

    $aAtts = shortcode_parse_atts($matches[0] . ' ]');
    $sUrl = $aAtts['src'];
    if (empty($sUrl) || strpos($sUrl, '.mp4') === null || flowplayer::is_s3($sUrl) || isset($FV_Player_Pro) && method_exists($FV_Player_Pro, 'is_dynamic_item') && $FV_Player_Pro->is_dynamic_item($sUrl))
      return;

    $sUrl = preg_replace('/https?:\/\/?/', '', $sUrl);
    $httpUrl = 'http://' . $sUrl;
    $httpsUrl = 'https://' . $sUrl;

    $sName = get_bloginfo('name');
    $sTitle = $post->post_title;
    $sSplash = isset($aAtts['splash']) ? $aAtts['splash'] : '';
    $sDescription = $post->post_excerpt;
    ?>
    <meta property="og:site_name" content="<?php echo $sName; ?>">
    <meta property="og:title" content="<?php echo $sTitle; ?>">
    <meta property="og:image" content="<?php echo $sSplash; ?>">
    <meta property="og:description" content="<?php echo $sDescription; ?>">
    <meta property="og:type" content="video">

    <meta property="og:video:url" content="<?php echo $httpUrl; ?>">
    <meta property="og:video:secure_url" content="<?php echo $httpsUrl; ?>">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="480">
    <meta property="og:video:height" content="360">
    <?php
  }

  function admin_integrations() {
    ?>
    <tr>
      <td><label for="facebook_sharing">Facebook Video Sharing:</label></td>
      <td>
        <p class="description">
          <input type="hidden" name="integrations[facebook_sharing]" value="false" />
          <input type="checkbox" name="integrations[facebook_sharing]" id="facebook_sharing" value="true" <?php if (isset($fv_fp->conf['integrations']['facebook_sharing']) && $fv_fp->conf['integrations']['facebook_sharing'] == 'true') echo 'checked="checked"'; ?> />
          <?php _e('New kind of sharing for Facebook', 'fv_flowplayer'); ?>
        </p>
      </td>
    </tr>
    <?php
  }

}

$FV_Facebook_Share = new FV_Facebook_Share();
