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
    if( !api.conf.clip.live ) return;
    
    var delay = initialDelay;

    clearInterval(timer);

    if (err.code === 2 || err.code === 4) {
      root.className += " is-offline";

      if (flowplayer.support.flashVideo) {
        api.one("flashdisabled", function () {
          root.querySelector(".fp-flash-disabled").style.display = "none";
        });
      }
      
      var messageElement = root.querySelector(".fp-ui .fp-message");
      messageElement.innerHTML = "<h2>We are sorry, currently no live stream available.</h2><p>Retrying in <span>" + initialDelay + "</span> seconds ...</p>";

      timer = setInterval(function () {
        delay -= 1;

        if (delay && messageElement) {
          messageElement.querySelector("span").innerHTML = delay;
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

  });
  
});