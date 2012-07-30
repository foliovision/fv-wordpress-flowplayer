<?php
/**
 * Displays metatags for frontend.
 */
?>
<?php    
   $aUserAgents = array('iphone', 'ipod', 'iPad', 'aspen', 'incognito', 'webmate', 'android', 'Android', 'dream', 'cupcake', 'froyo','blackberry', 'blackberry9500', 'blackberry9520', 'blackberry9530', 'blackberry9550', 'blackberry9800', 'Palm', 'webos', 's8000', 'bada', 'Opera Mini', 'Opera Mobi', 'htc_touch_pro');
   $mobileUserAgent = false;
   foreach($aUserAgents as $userAgent){
      if(stripos($_SERVER['HTTP_USER_AGENT'],$userAgent))
         $mobileUserAgent = true;
   }
   function ffilesize($file){
    $ch = curl_init($file);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data === false)
      return false;
    if (preg_match('/Content-Length: (\d+)/', $data, $matches))
      return (float)$matches[1];
}

?>
<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.css" type="text/css" media="screen" />
<?php if( $mobileUserAgent == false ){   
   $conf = get_option( 'fvwpflowplayer' );
	if (!isset($conf['optimizejs']) || (!$conf['optimizejs']) || ($conf['optimizejs']=='false')){
		echo "<script type=\"text/javascript\" src=\"" . RELATIVE_PATH . "/flowplayer/flowplayer.min.js\"></script>\n";
		echo "<script type=\"text/javascript\" src=\"" . RELATIVE_PATH . "/js/checkvideo.js\"></script>\n";
		echo "<!--[if lt IE 7.]>
      <script defer type=\"text/javascript\" src=\"" . RELATIVE_PATH . "/js/pngfix.js\"></script>
      <![endif]-->
      <script type=\"text/javascript\">	
      	/*<![CDATA[*/
      		function fp_replay(hash) {
      			var fp = document.getElementById('wpfp_'+hash);
      			var popup = document.getElementById('wpfp_'+hash+'_popup');
      			fp.removeChild(popup);
      			flowplayer('wpfp_'+hash).play();
      		}
      		function fp_share(hash) {
      			var cp = document.getElementById('wpfp_'+hash+'_custom_popup');
      			cp.innerHTML = '<div style=\"margin-top: 10px; text-align: center;\"><label for=\"permalink\" style=\"color: white;\">Permalink to this page:</label><input onclick=\"this.select();\" id=\"permalink\" name=\"permalink\" type=\"text\" value=\"http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\" /></div>';
      		}
      	/*]]>*/
      </script>";
      }

} ?>