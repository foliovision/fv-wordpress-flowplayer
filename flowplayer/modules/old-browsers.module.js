if( typeof(fv_flowplayer_browser_ff_m4v_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ff_m4v_array ) {
    fv_flowplayer_browser_ff_m4v( i );
  }
}
if( typeof(fv_flowplayer_browser_chrome_fail_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_chrome_fail_array ) {
    fv_flowplayer_browser_chrome_fail( i, fv_flowplayer_browser_chrome_fail_array[i]['attrs'], fv_flowplayer_browser_chrome_fail_array[i]['mp4'], fv_flowplayer_browser_chrome_fail_array[i]['auto_buffer'] );
  }
}

if( typeof(fv_flowplayer_browser_ie_array) != "undefined" ) {
  for( var i in fv_flowplayer_browser_ie_array ) {
    fv_flowplayer_browser_ie( i );
  }
}