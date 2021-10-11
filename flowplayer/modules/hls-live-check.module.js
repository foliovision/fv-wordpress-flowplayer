/*
If there is an error in live stream, it shows a special message and reload the streamp after a while
*/

flowplayer( function(api,root) {
  var
    initialDelay = 30,
    continueDelay = 10,
    useDelay = initialDelay,
    retryLabel = fv_flowplayer_translations.live_stream_retry,
    timer;
  
  // clear interval, error + unload allowing the player to be clicked to play again
  api.clearLiveStreamCountdown = function() {
    if( timer ) {
      clearInterval(timer);
      api.error = api.loading = false;
      jQuery(root).removeClass('is-error');
      jQuery(root).find('.fp-message.fp-shown').remove();
      api.unload();
    }
  }
  
  api.conf.flashls = {
    // limit amount of retries to load hls manifests in Flash
    manifestloadmaxretry: 2
  }

  api.on('ready', function() {
    useDelay = initialDelay;
    retryLabel = fv_flowplayer_translations.live_stream_retry;
  }).on('progress', function() {
    useDelay = continueDelay;
    retryLabel = fv_flowplayer_translations.live_stream_continue;

    // Without this the error handler might do the count down in "timer" and reload the video for no reason
    clearInterval(timer);
  });
  
  api.on("error", function (e, api, err) {
    setTimeout( function() {
      // exit if it's not live stream and the video is not Vimeo Event URL (used by FV Player Vimeo Live Streaming)
      if( !api.conf.clip.live && !api.conf.live && !( err.video && err.video.src.match(/\/\/vimeo.com\/event\//) ) ) return;
      
      var delay = useDelay;
      if( api.conf.clip.streaming_time ) {
        delay = api.conf.clip.streaming_time - Math.floor( Date.now()/1000 );
      } else if( api.conf.clip.live_starts_in ) {
        delay = api.conf.clip.live_starts_in;
      }

      var startLabel = fv_flowplayer_translations.live_stream_starting.replace( /%d/, secondsToDhms(delay) );
      retryLabel = retryLabel.replace( /%d/, secondsToDhms(delay) );

      var message = api.conf.clip.live_starts_in ? startLabel : retryLabel;

      clearInterval(timer);

      // 1 occurs in case of FV Player Vimeo Live Streaming
      if (err.code === 1 || err.code === 2 || err.code === 4) {
        root.className += " is-offline";

        if (flowplayer.support.flashVideo) {
          api.one("flashdisabled", function () {
            root.querySelector(".fp-flash-disabled").style.display = "none";
          });
        }
        
        var messageElement = root.querySelector(".fp-ui .fp-message");
        messageElement.innerHTML = message;

        // do not use too big waiting time, what if the stream is re-scheduled
        var reload_delay = delay > 300 ? 300 : delay;

        timer = setInterval(function () {
          reload_delay -= 1;
          delay -= 1;

          if (reload_delay > 0 && messageElement) {
            messageElement.querySelector("span").innerHTML = secondsToDhms(delay);
          } else {

            clearInterval(timer);

            // Does the video need help at all?
            if( !api.error ) {
              return;
            }

            api.error = api.loading = false;
            
            messageElement = root.querySelector(".fp-ui .fp-message");
            if (messageElement) {
              root.querySelector(".fp-ui").removeChild(messageElement);
            }
            root.className = root.className.replace(/\bis-(error|offline)\b/g, "");
            api.load();
          }

        }, 1000);
      }
    },1);

  });

  function secondsToDhms(seconds) {
    seconds = Number(seconds);
    var d = Math.floor(seconds / (3600*24));
    var h = Math.floor(seconds % (3600*24) / 3600);
    var m = Math.floor(seconds % 3600 / 60);
    var s = Math.floor(seconds % 60);
    var t = fv_flowplayer_translations;

    var output = d > 0 ? (d == 1 ? t.duration_1_day.replace(/%s/,d) : t.duration_n_days.replace(/%s/,d) ) : "";
    if( output && h > 0 ) output += ", ";
    output += h > 0 ? (h == 1 ? t.duration_1_hour.replace(/%s/,h) : t.duration_n_hours.replace(/%s/,h) ) : "";
    if( output && m > 0 ) output += ", ";
    output += m > 0 ? (m == 1 ? t.duration_1_minute.replace(/%s/,m) : t.duration_n_minutes.replace(/%s/,m) )  : "";
    if( output && s > 0 ) output += t.and;
    output += s > 0 ? (s == 1 ? t.duration_1_second.replace(/%s/,s) : t.duration_n_seconds.replace(/%s/,s) )  : "";

    return output;
  }
  
});