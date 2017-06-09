<?php

class FV_Player_Timeline_Actions {
  
  public function __construct() {
    add_filter( 'fv_flowplayer_shortcode', array( $this, 'shortcode' ), 10, 3 );
    add_filter('fv_player_item', array($this, 'add_timeline_actions'), 10, 2 );

  }

  function add_timeline_actions( $aItem, $index ) {
    global $fv_fp;
    
    if( empty($fv_fp->aCurArgs['actions']) ) return $aItem;
    
    $aActions = explode(',', $fv_fp->aCurArgs['actions'] );
    if( count($aActions) == 0 ) return $aItem;
    
    $aOutput = array();
    foreach( $aActions AS $k => $v ) {
      $v = explode('-',$v);
      $objAction = new stdClass;
      $objAction->time = $v[0];
      $objAction->popup = $v[1];
      $aOutput[] = $objAction;
    }
    
    if( count($aOutput) ) {
      $aItem['cuepoints'] = $aOutput;
    }
    
    return $aItem;
  }
  
  function shortcode( $attrs ) {
    $aArgs = func_get_args();
    
    if( isset($aArgs[2]) && isset($aArgs[2]['actions']) ) {
      $attrs['actions'] = $aArgs[2]['actions'];
    }
    
    return $attrs;
  }  

}

$FV_Player_Timeline_Actions = new FV_Player_Timeline_Actions();
