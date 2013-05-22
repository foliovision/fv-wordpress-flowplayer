<?php
/**
 * Displays metatags for frontend.
 */
 
global $fv_wp_flowplayer_core_ver;
?>
<script type="text/javascript" src="<?php echo RELATIVE_PATH ?>/flowplayer/flowplayer.min.js?ver=<?php echo $fv_wp_flowplayer_core_ver; ?>"></script>
<?php if ($this->conf['disableembedding'] == 'true') { ?>
<script type="text/javascript">                                                                     
  flowplayer.conf.embed = false;
</script>
<?php } ?>
<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.css?ver=<?php echo $fv_wp_flowplayer_core_ver; ?>" type="text/css" media="screen" />
<?php
  if ( isset($this->conf['key']) && $this->conf['key'] != 'false' && strlen($this->conf['key']) > 0 && isset($this->conf['logo']) && $this->conf['logo'] != 'false' && strlen($this->conf['logo']) > 0 ) { ?>
<style type="text/css">
  .flowplayer .fp-logo { display: block; opacity: 1; }    
</style>                                              
<?php
  }
?>
<style type="text/css">
	.flowplayer, flowplayer * { margin: 0 auto; display: block; }
	.flowplayer .fp-controls { background-color: <?php echo trim($this->conf['backgroundColor']); ?> !important; }
	.flowplayer { background-color: <?php echo trim($this->conf['canvas']); ?> !important; }
	.flowplayer .fp-duration { color: <?php echo trim($this->conf['durationColor']); ?> !important; }
	.flowplayer .fp-elapsed { color: <?php echo trim($this->conf['timeColor']); ?> !important; }
	.flowplayer .fp-volume { text-align: left; }
	.flowplayer .fp-volumelevel { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }  
	.flowplayer .fp-volumeslider { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
	.flowplayer .fp-timeline { background-color: <?php echo trim($this->conf['timelineColor']); ?> !important; }
	.flowplayer .fp-progress { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }
	.flowplayer .fp-buffer { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
	#content .fv-wp-flowplayer-notice { background-color: #FFFFE0; border-color: #E6DB55; margin: 5px 0 15px; padding: 0 0.6em; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; } 
	#content .fv-wp-flowplayer-notice p { font-family: sans-serif; font-size: 12px; margin: 0.5em 0; padding: 2px; } 
	#content .flowplayer a, .flowplayer a:hover { text-decoration: none; border-bottom: none; }
	#content .flowplayer { font-family: <?php echo trim($this->conf['font-face']); ?>; }
	#content .flowplayer .fp-embed-code { padding: 3px 7px; }
	#content .flowplayer .fp-embed-code textarea { line-height: 1.4; white-space: pre-wrap; color: <?php echo trim($this->conf['durationColor']); ?> !important; height: 160px; font-size: 10px; }
</style>
