<?php

class FV_Player_UUP {
  
  public function __construct() {
    add_filter( 'the_content', array( $this, 'account' ), 999 );

    add_filter( 'the_content', array( $this, 'profile' ), 999 );
  }
  
  
  function account( $content ) {
    global $post;
    
    if( !isset($post->post_content) || stripos($post->post_content,'[usersultra_my_account') === false || !isset($_GET['module']) || $_GET['module'] != 'videos' ) return $content;

    $objHTML = new DOMDocument();
    libxml_use_internal_errors(true);
    $objHTML->loadHTML($content);
    libxml_use_internal_errors(false);
    
    $objFinder = new DomXPath($objHTML);
    
    $objUploader = new FV_Player_Custom_Videos( array( 'id' => 1 ) );
    
    $aNodes = $objFinder->query("//*[contains(@class, 'add-new-video')]");
    if( $aNodes ) {
      foreach ($aNodes as $objNode) {
          $objParent = $objNode->parentNode;
          while ($objParent->hasChildNodes()){
            $objParent->removeChild($objParent->childNodes->item(0));
          }
          
          $fragment = $objHTML->createDocumentFragment();
          $fragment->appendXML( $objUploader->get_form() );
          $objParent->appendChild( $fragment);          
      }
      
      $content = $objHTML->saveHTML();
    }
    
    return $content;
  }
  

  function profile( $content ) {
    global $post;
    
    if( !isset($post->post_content) || stripos($post->post_content,'[usersultra_profile') === false || !isset($_GET['my_videos']) ) return $content;
    
    $objVideos = new FV_Player_Custom_Videos( array( 'id' => 1 ) );
    if( !$objVideos->have_videos() ) return $content;
    
    //var_dump($objVideos->get_html());die();

    $objHTML = new DOMDocument();
    libxml_use_internal_errors(true);
    $objHTML->loadHTML($content);
    libxml_use_internal_errors(false);
    
    $objFinder = new DomXPath($objHTML);
    
    $aNodes = $objFinder->query("//*[contains(@class, 'videolist')]/ul");
    if( $aNodes ) {
      foreach ($aNodes as $objNode) {
          $objParent = $objNode->parentNode;
          
          $objChild = $objHTML->createElement('ul');
          $fragment = $objHTML->createDocumentFragment();
          $fragment->appendXML( $objVideos->get_html() );
          $objChild->appendChild( $fragment);
      
          $objParent->insertBefore($objChild, $objNode);
          $objParent->removeChild($objNode);
      }
      
      $content = $objHTML->saveHTML();
    }
    
    return $content;
  }
  

}


$FV_Player_UUP = new FV_Player_UUP();
