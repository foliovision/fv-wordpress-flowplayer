function FVFPCheckVideo(id, nonce) {
	source = jQuery( '#src_'+id).val(); 
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",         
			timeout: 3000,
			data: { _ajax_nonce: nonce, id: id, source: source, action: 'fvp_ajax_action_checkvideo'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
					//jQuery( '#'+id ).removeClass("lnkrob-processing"); 
				}
				else {
					// TODO replace DOM element
					jQuery( '#video_'+id ).before( data ).remove(); 
				}
			}
		} );
}
/*
var video;
function setValue() {
	video = document.getElementById('video');
	playVid();
}

function checkStatus() {
	video.addEventListener('canplay',playVid,true);
}

function playVid() {
// Create variables for all relevant elements
//	var video = document.getElementById('video');
	var pause =  document.getElementById('pause');
	var play =  document.getElementById('play');
	var timer = document.getElementById('timer');
	var duration = document.getElementById('duration');
	var volume = document.getElementById('volume');
	var vUp = document.getElementById('v-up');
	var vDn = document.getElementById('v-dn');
	var t; // This is for the timer
// Set some initial values in the page
	volume.firstChild.nodeValue = Math.round(video.volume*10);
	duration.firstChild.nodeValue = Math.round(video.duration);
// Function to begin the timer
	function startCount() {
		t = window.setInterval(function() {
			if (video.ended != true) {
				timer.firstChild.nodeValue = Math.round(video.currentTime + 1);
			} else {
				play.firstChild.nodeValue = 'Play';
				window.clearInterval(t);
			}
		},1000);		
	}
// Function to pause the timer
	function pauseCount() {
		window.clearInterval(t);
	}
// Play & pause when the control is clicked
	play.addEventListener('click',playControl,false);
	video.addEventListener('click',playControl,false);
	function playControl() {
		if (video.paused == false) {
			video.pause();
			this.firstChild.nodeValue = 'Play';
			pauseCount();
		} else {
			video.play();
			this.firstChild.nodeValue = 'Pause';
			duration.firstChild.nodeValue = Math.round(video.duration);
			startCount();
		}
	}
// Increase the volume
	vUp.addEventListener('click',volUp,false);
	function volUp() {
		if (video.volume < 1) {
			video.volume = Math.round((video.volume + 0.1)*10)/10;
			volume.firstChild.nodeValue = Math.round(video.volume*10);
		}
	}
// Decrease the volume
	vDn.addEventListener('click',volDown,false);
	function volDown() {
		if (video.volume > 0) {
			video.volume = Math.round((video.volume - 0.1)*10)/10;
			volume.firstChild.nodeValue = Math.round(video.volume*10);
		}
	}
}

window.onload = setValue;*/
