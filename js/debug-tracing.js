var
  fv_player_trace_enabled = true, // if false, no tracing will occur
  fv_player_trace_timeout_check_timer = -1, // timer task that would be checking for a trace timeout, which will in turn
                                            // finalize the whole trace
  fv_player_max_full_trace_time_seconds = 10; // once this many seconds passed and no trace is sent out in the meanwhile,
                                              // a final closing trace will be sent to the tracer automatically

/**
 * Adds a new log element from an event, so it can be viewed in the Zipkin tracer.
 *
 * @param spanName Name of the span - event - to send to the tracer.
 * @param tags Any additional tags with extra information that could help investigating current application state.
 */
function fv_player_trace(spanName, tags) {
  if (!fv_player_trace_enabled) {
    return;
  }

  // stop the trace timeout task, if found
  if (fv_player_trace_timeout_check_timer > -1) {
    clearTimeout(fv_player_trace_timeout_check_timer);
  }

  var data = { span_name: spanName };

  if (tags && typeof(tags) != 'undefined') {
    data.tags = tags;
  }

  // send out a new trace to the server
  jQuery.post( flowplayer.conf.tracing_ajax , {
    action: 'fv_player_debug_trace',
    data: JSON.stringify( data ),
  }, function() {
    // all ok, no action needed
  }, 'json' ).error(function(xhr, type, msg) {
    console.log('Error saving trace:', msg);
  });

  // start a new trace timeout task
  fv_player_trace_timeout_check_timer = setTimeout(fv_player_trace_send_final_span, fv_player_max_full_trace_time_seconds * 1000);
}

// sends out a final closing trace to the server, effectively closing the whole trace
// and allowing the fv_player_trace() function to open a new one
function fv_player_trace_send_final_span() {
  jQuery.post( flowplayer.conf.tracing_ajax , {
    data: JSON.stringify( {
      span_name: 'final',
      finalize: 1
    } ),
  }, function() {
    // all ok, no action needed - cancel timed task in case this function was called manually
    if (fv_player_trace_timeout_check_timer > -1) {
      clearTimeout(fv_player_trace_timeout_check_timer);
    }

    fv_player_trace_timeout_check_timer = -1;
  }, 'json' ).error(function(xhr, type, msg) {
    console.log('Error finalizing trace! Retrying in ' + fv_player_max_full_trace_time_seconds + ' seconds. Error msg:', msg);

    // retry policy is to keep trying until we can close the trace
    fv_player_trace_timeout_check_timer = setTimeout(fv_player_trace_send_final_span, fv_player_max_full_trace_time_seconds * 1000);
  });
}