<?php

class FV_Player_Annotations {
  
  public function __construct() {
    add_filter('fv_player_item', array($this, 'add_annotations'), 10, 3 );
    add_filter('fv_player_db_video_meta_save', array($this, 'parse_post_metadata'), 10, 3);
  }

  /**
   * Method used in WP filter. Receives video meta data array
   * as well as post data to extract annotations from and returns
   * updated video meta array with annotations formatted in a way
   * that can be stored in the database.
   *
   * @param array $video_meta     Existing video meta data to merge
   *                              new annotations meta data into.
   * @param array $meta_post_data Relevant data from $_POST which include
   *                              all annotations metadata.
   * @param int   $video_index    Index of the video currently being processed,
   *                              so we can retrieve the correct annotations meta
   *                              data for it.
   *
   * @return array Returns an augmented array of the video meta data,
   *               adding annotations meta data into it.
   */
  function parse_post_metadata($video_meta, $meta_post_data, $video_index) {
    if (empty($meta_post_data['annotations'])) {
      // if we have no annotations or video meta, just return what we received
      return $video_meta;
    }

    // prepare all options for this video
    foreach ( $meta_post_data['annotations'][$video_index] as $annotation_values ) {
      if ($annotation_values['value']) {
        $m = array(
          'meta_key' => 'annotations_'.$annotation_values['type'],
          'meta_value' => $annotation_values['value']
        );

        // add ID, if present
        if (!empty($annotation_values['id'])) {
          $m['id'] = $annotation_values['id'];
        }

        $video_meta[] = $m;
      }
    }

    return $video_meta;
  }

  function add_annotations( $aItem, $index ) {
    global $fv_fp;

    $aSubtitles = $fv_fp->get_annotations($index);

    foreach( $aSubtitles AS $key => $annotation ) {
      $objAnnotation = new stdClass;
      $objAnnotation->type = $key;
      $objAnnotation->time = $annotation['time'];
      $objAnnotation->duration = $annotation['duration'];
      $objAnnotation->data = array(
        'link' => $annotation['link']
      );

      switch ($key) {
        case 'txt' : $objAnnotation->data['text'] = $annotation['value'];
                     break;

        case 'img' : $objAnnotation->data['image'] = $annotation['value'];
                     break;

        case 'html' : $objAnnotation->data['html'] = $annotation['value'];
                      break;
      }

      $aOutput[] = $objAnnotation;
    }

    if( count($aSubtitles) ) {
      $aItem['annotations'] = $aOutput;
    }

    return $aItem;
  }

}

$FV_Player_Annotations = new FV_Player_Annotations();
