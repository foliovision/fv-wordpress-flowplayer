var
  fv_player_trace_timeout_check_timer = -1, // timer task that would be checking for a trace timeout, which will in turn
                                            // finalize the whole trace
  fv_player_trace_console_enabled = true, // if false, no console logging will occur
  fv_player_max_full_trace_time_seconds = 10; // once this many seconds passed and no trace is sent out in the meanwhile,
                                              // the tracing group will be

// warn user about tracing into console
if (fv_player_trace_console_enabled) {
  console.info('Events tracing enabled. To disable, set fv_player_trace_console_enabled to false in debug-tracing-console.js');
};

// fill-in function to log trace data into console
function fv_player_trace(spanName, tags) {
  if (fv_player_trace_console_enabled) {
    // stop the trace timeout task, if found
    if (fv_player_trace_timeout_check_timer > -1) {
      clearTimeout(fv_player_trace_timeout_check_timer);
    }

    var tx = hrtime();

    // log data to console
    if (tags && typeof(tags) != 'undefined') {
      console.log('trace [' + tx[0] + '.' + tx[1] + '] - ' + spanName + ', tags =', tags);
    } else {
      console.log('trace [' + tx[0] + '.' + tx[1] + '] - ' + spanName);
    }

    // start a new trace timeout task
    fv_player_trace_timeout_check_timer = setTimeout(function() {
      var tx = hrtime();
      console.log('trace [' + tx[0] + '.' + tx[1] + '] - full trace cycle complete');

      // cancel timed task in case this function was called manually
      if (fv_player_trace_timeout_check_timer > -1) {
        clearTimeout(fv_player_trace_timeout_check_timer);
      }

      fv_player_trace_timeout_check_timer = -1;
    }, fv_player_max_full_trace_time_seconds * 1000);
  }
};

function fv_player_trace_send_final_span() {
  fv_player_trace('full trace cycle complete');

  // cancel timed task in case this function was called manually
  if (fv_player_trace_timeout_check_timer > -1) {
    clearTimeout(fv_player_trace_timeout_check_timer);
    fv_player_trace_timeout_check_timer = -1;
  }
};

// polyfil for window.performance.now
var performance = (typeof(global) != 'undefined' ? global.performance : {});
var performanceNow =
  performance.now        ||
  performance.mozNow     ||
  performance.msNow      ||
  performance.oNow       ||
  performance.webkitNow  ||
  function(){ return (new Date()).getTime() };

// generate timestamp or delta
// see http://nodejs.org/api/process.html#process_process_hrtime
function hrtime(previousTimestamp){
  var clocktime = performanceNow.call(performance)*1e-3;
  var seconds = Math.floor(clocktime);
  var nanoseconds = Math.floor((clocktime%1)*1e9);
  if (previousTimestamp) {
    seconds = seconds - previousTimestamp[0];
    nanoseconds = nanoseconds - previousTimestamp[1];
    if (nanoseconds<0) {
      seconds--;
      nanoseconds += 1e9;
    }
  }
  return [seconds,nanoseconds];
};