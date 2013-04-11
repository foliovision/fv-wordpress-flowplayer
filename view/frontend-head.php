<?php
/**
 * Displays metatags for frontend.
 */
?>
<script type="text/javascript" src="<?php echo RELATIVE_PATH ?>/flowplayer.html5/flowplayer.min.js"></script>
<?php if ($this->conf['disableembedding'] == 'true') { ?>
<script type="text/javascript">                                                                     
  flowplayer.conf.embed = false;
</script>
<?php } ?>
<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.html5.css" type="text/css" media="screen" />
<?php
  if ( isset($this->conf['key']) && $this->conf['key'] != 'false' && strlen($this->conf['key']) > 0 && isset($this->conf['logo']) && $this->conf['logo'] != 'false' && strlen($this->conf['logo']) > 0 ) { ?>
<style type="text/css">
  .flowplayer .fp-logo { display: block; opacity: 1; }    
</style>                                              
<?php
  }
?>
<style type="text/css">
  .flowplayer .fp-controls { background-color: <?php echo trim($this->conf['backgroundColor']); ?> !important; }
  .flowplayer { background-color: <?php echo trim($this->conf['canvas']); ?> !important; }
  .flowplayer .fp-duration { color: <?php echo trim($this->conf['durationColor']); ?> !important; }
  .flowplayer .fp-elapsed { color: <?php echo trim($this->conf['timeColor']); ?> !important; }
  .flowplayer .fp-volumelevel { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }  
  .flowplayer .fp-volumeslider { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
  .flowplayer .fp-timeline { background-color: <?php echo trim($this->conf['timelineColor']); ?> !important; }
  .flowplayer .fp-progress { background-color: <?php echo trim($this->conf['progressColor']); ?> !important; }
  .flowplayer .fp-buffer { background-color: <?php echo trim($this->conf['bufferColor']); ?> !important; }
</style>
