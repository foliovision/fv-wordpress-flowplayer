<?php

class FV_Player_Cues {
  
  public function __construct() {
    add_filter('fv_player_item', array($this, 'add_cues'), 10, 3 );
    add_filter('fv_player_db_video_meta_save', array($this, 'parse_post_metadata'), 10, 3);
  }

  /**
   * Method used in WP filter. Receives video meta data array
   * as well as post data to extract cues from and returns
   * updated video meta array with cues formatted in a way
   * that can be stored in the database.
   *
   * @param array $video_meta     Existing video meta data to merge
   *                              new cues meta data into.
   * @param array $meta_post_data Relevant data from $_POST which include
   *                              all cues metadata.
   * @param int   $video_index    Index of the video currently being processed,
   *                              so we can retrieve the correct cues meta
   *                              data for it.
   *
   * @return array Returns an augmented array of the video meta data,
   *               adding cues meta data into it.
   */
  function parse_post_metadata($video_meta, $meta_post_data, $video_index) {
    if (empty($meta_post_data['cues'])) {
      // if we have no cues or video meta, just return what we received
      return $video_meta;
    }

    // prepare all options for this video
    foreach ( $meta_post_data['cues'][$video_index] as $cue_values ) {
      if ($cue_values['value']) {
        $m = array(
          'meta_key' => 'cues_'.$cue_values['type'],
          'meta_value' => $cue_values['value']
        );

        // add ID, if present
        if (!empty($cue_values['id'])) {
          $m['id'] = $cue_values['id'];
        }

        $video_meta[] = $m;
      }
    }

    return $video_meta;
  }

  function add_cues( $aItem, $index ) {
    global $fv_fp;

    $aCues = $fv_fp->get_cues($index);

    foreach( $aCues AS $key => $cue ) {
      $objCue = new stdClass;
      $objCue->type = $key;
      $objCue->time = (int) $cue['time'];
      $objCue->duration = (int) $cue['duration'];
      $objCue->data = array(
        'link' => $cue['link']
      );

      switch ($key) {
        case 'ann' : $objCue->data['text'] = $cue['value'];
                     break;

        case 'img' : $objCue->data['image'] = $cue['value'];
                     break;

        case 'html' : $objCue->data['html'] = $cue['value'];
                      break;
      }

      $aOutput[] = $objCue;
    }

    if( count($aCues) ) {
      $aItem['cuepoints'] = $aOutput;
    }

    return $aItem;
  }

}

$FV_Player_Cues = new FV_Player_Cues();
