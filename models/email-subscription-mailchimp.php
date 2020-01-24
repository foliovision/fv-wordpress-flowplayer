<?php

global $fv_fp, $fv_player_MailChimp;

$fv_player_MailChimp = false;
try {
  $fv_player_MailChimp = new \DrewM\MailChimp\MailChimp($fv_fp->conf['mailchimp_api']);
  $fv_player_MailChimp->verify_ssl = false;
} catch( Exception $e ) {}

function fv_player_mailchimp_result() {
  global $fv_player_MailChimp;
  if( $fv_player_MailChimp ) {
    return $fv_player_MailChimp->get('lists');
  } else {
    return false;
  }
}

function fv_player_mailchimp_last_error() {
  global $fv_player_MailChimp;
  if( $fv_player_MailChimp ) {
    return $fv_player_MailChimp->getLastError();
  } else {
    return false;
  }
}

function fv_player_mailchimp_post($list_id, $email, $merge_fields ) {
  global $fv_player_MailChimp;
  return $fv_player_MailChimp->post("lists/$list_id/members", array(
      'email_address' => $email,
      'status' => 'subscribed',
      'merge_fields' => (object)$merge_fields));
}

function fv_player_mailchimp_get($list_id ) {
  global $fv_player_MailChimp;
  return $fv_player_MailChimp->get("lists/{$list_id}/merge-fields");
}