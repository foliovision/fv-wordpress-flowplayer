function S3MultiUpload(file) {
    this.FIRST_PART_SIZE = 10 * 1024 * 1024; // 10 MB for first chunk
    this.PART_SIZE = 100 * 1024 * 1024; // 100 MB per chunk for subsequent chunks
//    this.PART_SIZE = 5 * 1024 * 1024 * 1024; // Minimum part size defined by aws s3 is 5 MB, maximum 5 GB
    this.SERVER_LOC = window.fv_flowplayer_browser.ajaxurl + '?'; // Location of our server where we'll send all AWS commands and multipart instructions
    this.completed = false;
    this.file = file;
    this.fileInfo = {
        name: this.file.name,
        type: this.file.type,
        size: this.file.size,
        lastModifiedDate: this.file.lastModifiedDate
    };
    this.sendBackData = null;
    this.uploadXHR = [];
    // Progress monitoring
    this.byterate = []
    this.lastUploadedSize = []
    this.lastUploadedTime = []
    this.loaded = [];
    this.total = [];
    this.chunkRetries = {};
    this.maxRetries = 4;
    this.retryBackoffTimeout = 15000; // ms
    this.completeErrors = 1;
}

/**
 * Creates the multipart upload
 */
S3MultiUpload.prototype.createMultipartUpload = function() {
    var self = this;

    if( fv_player_media_browser.get_current_folder() === 'Home/' ) { // root folder
      self.fileInfo.name =  fv_player_media_browser.get_current_folder() + self.fileInfo.name
    } else { // nested folder
      self.fileInfo.name =  fv_player_media_browser.get_current_folder() + '/' + self.fileInfo.name
    }

    jQuery.post(self.SERVER_LOC, {
        action: 'create_multiupload',
        fileInfo: self.fileInfo,
        nonce: window.fv_player_s3_uploader.create_multiupload_nonce
    }).done(function(data) {
        if( data.error ) {
          self.onServerError('create', null, data.error, null);
        } else {
          self.sendBackData = data;
          self.uploadParts();
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        self.onServerError('create', jqXHR, textStatus, errorThrown);
    });
};

/**
 * Call this function to start uploading to server
 */
S3MultiUpload.prototype.start = function() {
    this.createMultipartUpload();
};

/** private */
S3MultiUpload.prototype.uploadParts = function() {
    var blobs = this.blobs = [], promises = [];
    var start = 0;
    var end, blob;
    var partNum = 0;

    while(start < this.file.size) {
        // Use first chunk size for the first chunk, regular size for subsequent chunks
        var currentPartSize = (partNum === 0) ? this.FIRST_PART_SIZE : this.PART_SIZE;
        end = Math.min(start + currentPartSize, this.file.size);
        var filePart = this.file.slice(start, end);
        // this is to prevent push blob with 0Kb
        if (filePart.size > 0)
            blobs.push(filePart);
        start += currentPartSize;
        partNum++;
    }

    for (var i = 0; i < blobs.length; i++) {
        blob = blobs[i];
        promises.push(this.uploadXHR[i]=jQuery.post(this.SERVER_LOC, {
            action: 'multiupload_send_part',
            sendBackData: this.sendBackData,
            partNumber: i+1,
            contentLength: blob.size,
            nonce: window.fv_player_s3_uploader.multiupload_send_part_nonce
        }));
    }

    jQuery.when.apply(null, promises)
     .then(this.sendAll.bind(this), this.onServerError)
     .done(this.onPrepareCompleted);
};

/**
 * Sends all the created upload parts in a loop
 */
S3MultiUpload.prototype.sendAll = function() {
    var blobs = this.blobs;
    var length = blobs.length;
    if (length==1)
        this.sendToS3(arguments[0], blobs[0], 0);
    else for (var i = 0; i < length; i++) {
        this.sendToS3(arguments[i][0], blobs[i], i);
    }
};
/**
 * Used to send each uploadPart
 * @param  array data  parameters of the part
 * @param  blob blob  data bytes
 * @param  integer index part index (base zero)
 */
S3MultiUpload.prototype.sendToS3 = function(data, blob, index) {
    var self = this;
    var url = data['url'];
    var size = blob.size;
    var request = self.uploadXHR[index] = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (request.readyState === 4) { // 4 is DONE
            // on abort, don't count that as an error - aborted is a manually added field, since status would be 0 whether
            // we aborted manually or an Internet connection interrupt occured, so that's no use to us
            if ( !request.aborted ) {
                // self.uploadXHR[index] = null;
                if (request.status !== 200) {
                    // check if we should retry this transfer of fail
                    if (!self.chunkRetries[url] || self.chunkRetries[url] < self.maxRetries) {
                        if (!self.chunkRetries[url]) {
                            self.chunkRetries[url] = 1;
                        } else {
                            self.chunkRetries[url]++;
                        }

                        //console.log('will retry ' + url + ' due to invalid request status: ' + request.status + ' (' + request.responseText + ')');
                        setTimeout(function () {
                            //console.log('starting retry #' + self.chunkRetries[ url ] + ' for ' + url );
                            self.sendToS3(data, blob, index);
                        }, self.retryBackoffTimeout * self.chunkRetries[url]);
                    } else {
                        self.updateProgress();
                        self.onS3UploadError(request);
                    }
                    return;
                }
                
                // Chunk completed successfully
                self.onChunkCompleted(index, blob, data);
            }
            self.updateProgress();
        }
    };

    request.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            self.total[index] = size;
            self.loaded[index] = e.loaded;
            if (self.lastUploadedTime[index])
            {
                var time_diff=(new Date().getTime() - self.lastUploadedTime[index])/1000;
                if (time_diff > 0.005) // 5 miliseconds has passed
                {
                    var byterate=(self.loaded[index] - self.lastUploadedSize[index])/time_diff;
                    self.byterate[index] = byterate;
                    self.lastUploadedTime[index]=new Date().getTime();
                    self.lastUploadedSize[index]=self.loaded[index];
                }
            }
            else
            {
                self.byterate[index] = 0;
                self.lastUploadedTime[index]=new Date().getTime();
                self.lastUploadedSize[index]=self.loaded[index];
            }
            // Only send update to user once, regardless of how many
            // parallel XHRs we have (unless the first one is over).
            if (index==0 || self.total[0]==self.loaded[0])
                self.updateProgress();
        }
    };
    request.open('PUT', url, true);
    request.send(blob);
};

/**
 * Abort multipart upload
 */
S3MultiUpload.prototype.cancel = function() {
    var self = this;
    for (var i=0; i<this.uploadXHR.length; ++i) {
        this.uploadXHR[i].aborted = true;
        this.uploadXHR[i].abort();
    }
    jQuery.post(self.SERVER_LOC, {
        action: 'multiupload_abort',
        sendBackData: self.sendBackData,
        nonce: window.fv_player_s3_uploader.multiupload_abort_nonce
    }).done(function(data) {

    });
};

/**
 * Complete multipart upload
 */
S3MultiUpload.prototype.completeMultipartUpload = function() {
    var self = this;

    if (this.completed) return;

    self.completed = true; // prevent multiple calls to this function

    jQuery.post(self.SERVER_LOC, {
        action: 'multiupload_complete',
        sendBackData: self.sendBackData,
        nonce: window.fv_player_s3_uploader.multiupload_complete_nonce
    }).done(function(data) {
        self.onUploadCompleted(data);
        self.completeErrors = 1;

    }).fail(function(jqXHR, textStatus, errorThrown) {
        // if we had an error, retry and only show error if at least 3 completion requests fail
        if ( this.completeErrors++ > 3 ) {
            self.onServerError('complete', jqXHR, textStatus, errorThrown);
            self.completeErrors = 1;
            self.completed = true;
        } else {
            setTimeout( function() {
                self.completed = false;
                self.completeMultipartUpload();
            } , this.completeErrors * this.retryBackoffTimeout );
        }
    });
};

/**
 * Track progress, propagate event, and check for completion
 */
S3MultiUpload.prototype.updateProgress = function() {
    var total=0;
    var loaded=0;
    var byterate=0.0;
    var complete=1;
    for (var i=0; i<this.total.length; ++i) {
        loaded += +this.loaded[i] || 0;
        total += this.total[i];
        if (this.loaded[i]!=this.total[i])
        {
            // Only count byterate for active transfers
            byterate += +this.byterate[i] || 0;
            complete=0;
        }
    }
    if (complete)
    this.completeMultipartUpload();
    total=this.fileInfo.size;
    this.onProgressChanged(loaded, total, byterate);
};

// Overridable events:

/**
 * Overrride this function to catch errors occured when communicating to your server
 *
 * @param {type} command Name of the command which failed,one of 'CreateMultipartUpload', 'SignUploadPart','CompleteMultipartUpload'
 * @param {type} jqXHR jQuery XHR
 * @param {type} textStatus resonse text status
 * @param {type} errorThrown the error thrown by the server
 */
S3MultiUpload.prototype.onServerError = function(command, jqXHR, textStatus, errorThrown) {};

/**
 * Overrride this function to catch errors occured when uploading to S3
 *
 * @param XMLHttpRequest xhr the XMLHttpRequest object
 */
S3MultiUpload.prototype.onS3UploadError = function(xhr) {};

/**
 * Override this function to show user update progress
 *
 * @param {type} uploadedSize is the total uploaded bytes
 * @param {type} totalSize the total size of the uploading file
 * @param {type} speed bytes per second
 */
S3MultiUpload.prototype.onProgressChanged = function(uploadedSize, totalSize, bitrate) {};

/**
 * Override this method to execute something when upload finishes
 *
 */
S3MultiUpload.prototype.onUploadCompleted = function(serverData) {};
/**
 * Override this method to execute something when part preparation is completed
 *
 */
S3MultiUpload.prototype.onPrepareCompleted = function() {};

/**
 * Override this method to execute something when a chunk completes uploading
 *
 * @param {number} index The chunk index (0-based)
 * @param {Blob} blob The chunk blob data
 * @param {object} data The response data from the server
 */
S3MultiUpload.prototype.onChunkCompleted = function(index, blob, data) {
    // If this is the first chunk, validate if it's a video
    if (index === 0) {
        this.validateVideo(blob).then((isVideo) => {
            this.onVideoValidationComplete(isVideo, blob);
        });
    }
};

/**
 * Validates if the file is a video by checking file headers
 * @param {Blob} blob The file blob to validate
 * @returns {Promise<boolean>} True if the file is a valid video
 */
S3MultiUpload.prototype.validateVideo = function(blob) {
    return new Promise((resolve) => {
        var reader = new FileReader();
        reader.onload = function(e) {
            var arr = new Uint8Array(e.target.result);
            var header = '';
            
            // Convert first 12 bytes to hex string
            for (var i = 0; i < Math.min(12, arr.length); i++) {
                header += arr[i].toString(16).padStart(2, '0');
            }
            
            // Check for common video file signatures
            var isVideo = false;
            
            // MP4 signature: ftyp
            if (header.indexOf('66747970') !== -1) {
                isVideo = true;
            }
            // AVI signature: RIFF....AVI
            else if (header.indexOf('52494646') !== -1 && header.indexOf('41564920') !== -1) {
                isVideo = true;
            }
            // MOV signature: ftyp
            else if (header.indexOf('66747970') !== -1) {
                isVideo = true;
            }
            // WebM signature: 1a45dfa3
            else if (header.indexOf('1a45dfa3') !== -1) {
                isVideo = true;
            }
            // FLV signature: 464c5601
            else if (header.indexOf('464c5601') !== -1) {
                isVideo = true;
            }
            // WMV signature: 3026b275
            else if (header.indexOf('3026b275') !== -1) {
                isVideo = true;
            }
            // MKV signature: 1a45dfa3
            else if (header.indexOf('1a45dfa3') !== -1) {
                isVideo = true;
            }
            
            resolve(isVideo);
        };
        reader.readAsArrayBuffer(blob);
    });
};

/**
 * Override this method to handle video validation results
 * @param {boolean} isVideo True if the file is a valid video
 * @param {Blob} blob The first chunk blob data
 */
S3MultiUpload.prototype.onVideoValidationComplete = function(isVideo, blob) {};
