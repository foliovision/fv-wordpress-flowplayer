<?php
/**
 * Displays metatags for frontend.
 */

global $fv_wp_flowplayer_ver;
?>

<?php if( is_admin() ) : ?>
	<script type="text/javascript" src="<?php echo RELATIVE_PATH ?>/flowplayer/flowplayer.min.js?ver=<?php echo $fv_wp_flowplayer_ver; ?>"></script>
<?php endif; ?>

<?php if ($this->conf['disableembedding'] == 'true') { ?>
	<script type="text/javascript">                                                                     
		flowplayer.conf.embed = false;
	</script>
<?php } ?>

<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.css?ver=<?php echo $fv_wp_flowplayer_ver; ?>" type="text/css" media="screen" />

<?php if ( isset($this->conf['key']) && $this->conf['key'] != 'false' && strlen($this->conf['key']) > 0 && isset($this->conf['logo']) && $this->conf['logo'] != 'false' && strlen($this->conf['logo']) > 0 ) : ?>
		<style type="text/css">
			.flowplayer .fp-logo { display: block; opacity: 1; }    
		</style>                                              
<?php endif; ?>

<style type="text/css">
	<?php if( isset($this->conf['hasBorder']) && $this->conf['hasBorder'] == "true" ) : ?>
		.flowplayer { border: 1px solid <?php echo trim($this->conf['borderColor']); ?> !important; }
	<?php endif; ?>

	.flowplayer, flowplayer * { margin: 0 auto 28px auto; display: block; }
	.flowplayer .fp-controls { background-color: <?php echo trim($this->conf['backgroundColor']); ?> !important; }
	.flowplayer { background-color: <?php echo trim($this->conf['canvas']); ?> !important; }
	.flowplayer .fp-duration { color: <?php echo trim($this->conf['durationColor']); ?> !important; }
	.flowplayer .fp-elapsed { color: <?php echo trim($this->conf['timeColor']); ?> !important; }
	.flowplayer .fp-volumelevel { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }  
	.flowplayer .fp-volumeslider { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
	.flowplayer .fp-timeline { background-color: <?php echo trim($this->conf['timelineColor']); ?> !important; }
	.flowplayer .fp-progress { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }
	.flowplayer .fp-buffer { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
	#content .flowplayer, .flowplayer { font-family: <?php echo trim($this->conf['font-face']); ?>; }
	#content .flowplayer .fp-embed-code textarea, .flowplayer .fp-embed-code textarea { line-height: 1.4; white-space: pre-wrap; color: <?php echo trim($this->conf['durationColor']); ?> !important; height: 160px; font-size: 10px; }
	
	.fvplayer .mejs-container .mejs-controls { background: <?php echo trim($this->conf['backgroundColor']); ?>!important; } 
	.fvplayer .mejs-controls .mejs-time-rail .mejs-time-current { background: <?php echo trim($this->conf['progressColor']); ?>!important; } 
	.fvplayer .mejs-controls .mejs-time-rail .mejs-time-loaded { background: <?php echo trim($this->conf['bufferColor']); ?>!important; } 
	.fvplayer .mejs-horizontal-volume-current { background: <?php echo trim($this->conf['progressColor']); ?>!important; } 
	.fvplayer .me-cannotplay span { padding: 5px; }
	#content .fvplayer .mejs-container .mejs-controls div { font-family: <?php echo trim($this->conf['font-face']); ?>; }

	.wpfp_custom_popup { display: none; position: absolute; top: 10%; z-index: 2; text-align: center; width: 100%; color: #fff; }
	.wpfp_custom_popup_content {  background: <?php echo trim($this->conf['backgroundColor']) ?>; padding: 1% 5%; width: 65%; margin: 0 auto; }

	.wpfp_custom_ad { position: absolute; bottom: 10%; z-index: 2; width: 100%; color: <?php echo trim($this->conf['adTextColor']); ?>; }
	.wpfp_custom_ad a { color: <?php echo trim($this->conf['adLinksColor']); ?> }
	.wpfp_custom_ad_content { background: <?php echo trim($this->conf['backgroundColor']) ?>; margin: 0 auto; position: relative }
	
	<?php if( current_user_can( 'manage_options' ) ) : ?>
		#content .fv-wp-flowplayer-notice-small, .fv-wp-flowplayer-notice-small { color: <?php echo trim($this->conf['timeColor']); ?>; position: absolute; top: 1%; left: 1%; z-index: 2;}
		#content .fv-wp-flowplayer-notice, .fv-wp-flowplayer-notice { color: black; background-color: #FFFFE0; border-color: #E6DB55; margin: 5px 0 15px; padding: 0 0.6em; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; line-height: 15px; z-index: 100; width: 500px; }
		#content .fv-wp-flowplayer-notice strong, .fv-wp-flowplayer-notice strong { font-weight: bold; }
		#content .fv-wp-flowplayer-notice blockquote, .fv-wp-flowplayer-notice blockquote { font-size: 12px; }
		#content .fv-wp-flowplayer-notice p, .fv-wp-flowplayer-notice p { font-family: sans-serif; font-size: 12px; margin: 0.5em 0; padding: 2px; }
		#content .fv-wp-flowplayer-notice blockquote, #content .fv-wp-flowplayer-notice pre, .fv-wp-flowplayer-notice blockquote, .fv-wp-flowplayer-notice pre { padding: 5px; margin: 0; }
		#content .fv-wp-flowplayer-notice.fv-wp-flowplayer-error, .fv-wp-flowplayer-notice.fv-wp-flowplayer-error { background-color: #FFEBE8; border-color: #CC0000; }
		#content .fv-wp-flowplayer-notice.fv-wp-flowplayer-ok, .fv-wp-flowplayer-notice.fv-wp-flowplayer-ok { background-color: #E0FFE0; border-color: #88AA88; }       
		#content .fv-wp-flowplayer-notice a.techinfo, .fv-wp-flowplayer-notice a.techinfo { float: right; color: gray; }       		
		.fv-wp-fp-hidden { display: none; }
		.fv-wp-flowplayer-notice-parsed .row { text-align: left; border-bottom: 1px solid lightgray; border-right: 1px solid lightgray; border-left: 1px solid lightgray; padding-left: 5px; font-size: 12px; clear: both; }
		.fv-wp-flowplayer-notice-parsed .close { height: 0px; }
		.fv-wp-flowplayer-notice-parsed .value { border-left: 1px solid lightgray; display: inline-block; float: right; padding-left: 5px; width: 270px; /*height: 21px; overflow: hidden;*/ }	
		.fv-wp-flowplayer-notice-parsed.indent { margin-left: 10px; }	
		.fv-wp-flowplayer-notice-parsed.level-1 { background: #f8f8f8; }
		.fv-wp-flowplayer-notice-parsed.level-2 { background: #f0f0f0; }	
		.fv-wp-flowplayer-notice-parsed.level-3 { background: #e8e8e8; }	
		.fv-wp-flowplayer-notice-parsed.level-4 { background: #e0e0e0; }	
		.fv-wp-flowplayer-notice-parsed.level-5 { background: #d8d8d8; }	
		.fv-wp-flowplayer-notice-parsed.level-6 { background: #d0d0d0; }	
		.fv-wp-flowplayer-notice-parsed.level-7 { background: #c8c8c8; }		
		.mail-content-details { height: 200px; overflow: auto; }
	<?php endif; ?>
</style>
