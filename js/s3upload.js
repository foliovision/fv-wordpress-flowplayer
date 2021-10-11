function S3MultiUpload(file) {
    this.PART_SIZE = 100 * 1024 * 1024; // 100 MB per chunk
//    this.PART_SIZE = 5 * 1024 * 1024 * 1024; // Minimum part size defined by aws s3 is 5 MB, maximum 5 GB
    this.SERVER_LOC = ajaxurl + '?'; // Location of our server where we'll send all AWS commands and multipart instructions
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
    this.completeErrors = 0;
}

/**
 * Creates the multipart upload
 */
S3MultiUpload.prototype.createMultipartUpload = function() {
    var self = this;
    jQuery.post(self.SERVER_LOC, {
        action: 'create_multiupload',
        fileInfo: self.fileInfo,
    }).done(function(data) {
        self.sendBackData = data;
        self.uploadParts();
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
        end = Math.min(start + this.PART_SIZE, this.file.size);
		    filePart = this.file.slice(start, end);
        // this is to prevent push blob with 0Kb
        if (filePart.size > 0)
            blobs.push(filePart);
        start = this.PART_SIZE * ++partNum;
    }

    for (var i = 0; i < blobs.length; i++) {
        blob = blobs[i];
        promises.push(this.uploadXHR[i]=jQuery.post(this.SERVER_LOC, {
            action: 'multiupload_send_part',
            sendBackData: this.sendBackData,
            partNumber: i+1,
            contentLength: blob.size
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
        sendBackData: self.sendBackData
    }).done(function(data) {

    });
};

/**
 * Complete multipart upload
 */
S3MultiUpload.prototype.completeMultipartUpload = function() {
    var self = this;

    if (this.completed) return;

    jQuery.post(self.SERVER_LOC, {
        action: 'multiupload_complete',
        sendBackData: self.sendBackData
    }).done(function(data) {
        self.onUploadCompleted(data);
        self.completeErrors = 0;
        self.completed = true;
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // if we had an error, retry and only show error if at least 3 completion requests fail
        if ( this.completeErrors++ > 2 ) {
            self.onServerError('complete', jqXHR, textStatus, errorThrown);
            self.completeErrors = 0;
            self.completed = true;
        } else {
            setTimeout( this.completeMultipartUpload, this.completeErrors * this.retryBackoffTimeout );
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
