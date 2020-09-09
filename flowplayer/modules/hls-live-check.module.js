/*
If there is an error in live stream, it shows a special message and reload the streamp after a while
*/

flowplayer( function(api,root) {
  
  var initialDelay = 30,
    timer;
  
  api.conf.flashls = {
    // limit amount of retries to load hls manifests in Flash
    manifestloadmaxretry: 2
  }
  
  api.on("error", function (e, api, err) {
    setTimeout( function() {
      // whitelisting Vimeo Event URLs used by FV Player Vimeo Live Streaming
      if( !api.conf.clip.live && !api.conf.live && !err.video.src.match(/\/\/vimeo.com\/event\//) ) return;
      
      var delay = api.conf.clip.live_starts_in || initialDelay,
        message = api.conf.clip.live_starts_in ? "<h2>Live stream scheduled</h2><p>Starting in <span>" + secondsToDhms(delay) + "</span>.</p>" : "<h2>We are sorry, currently no live stream available.</h2><p>Retrying in <span>" + secondsToDhms(delay) + "</span> seconds ...</p>";

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

        timer = setInterval(function () {
          delay -= 1;

          if (delay > 0 && messageElement) {
            messageElement.querySelector("span").innerHTML = secondsToDhms(delay);
          } else {
            clearInterval(timer);
            api.error = api.loading = false;
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
    
    var output = d > 0 ? d + (d == 1 ? " day" : " days") : "";
    if( output && h > 0 ) output += ", ";
    output += h > 0 ? h + (h == 1 ? " hour" : " hours") : "";
    if( output && m > 0 ) output += ", ";
    output += m > 0 ? m + (m == 1 ? " minute" : " minutes") : "";
    if( output && s > 0 ) output += " and ";
    output += s > 0 ? s + (s == 1 ? " second" : " seconds") : "";
    return output;
  }
  
});