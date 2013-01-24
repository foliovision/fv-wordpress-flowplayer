<?php
/**
 * Displays metatags for frontend.
 */
?>
<?php if ( !is_null($html5) && $html5 || is_admin() ) { ?>
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
  .flowplayer .fp-controls { background-color: <?php echo trim($this->conf['backgroundColor']); ?> }
  .flowplayer { background-color: <?php echo trim($this->conf['canvas']); ?> }
  .flowplayer .fp-duration { color: <?php echo trim($this->conf['durationColor']); ?> }
  .flowplayer .fp-elapsed { color: <?php echo trim($this->conf['timeColor']); ?> }
  .flowplayer .fp-volumelevel { background-color: <?php echo trim($this->conf['progressColor']); ?> }  
  .flowplayer .fp-volumeslider { background-color: <?php echo trim($this->conf['bufferColor']); ?> }
  .flowplayer .fp-timeline { background-color: <?php echo trim($this->conf['timelineColor']); ?> }
  .flowplayer .fp-progress { background-color: <?php echo trim($this->conf['progressColor']); ?> }
  .flowplayer .fp-buffer { background-color: <?php echo trim($this->conf['bufferColor']); ?> }
</style>
<?php
}
else
if ( !is_null($html5) ) {
?>
<link rel="stylesheet" href="<?php echo RELATIVE_PATH; ?>/css/flowplayer.css" type="text/css" media="screen" />
<?php } ?>