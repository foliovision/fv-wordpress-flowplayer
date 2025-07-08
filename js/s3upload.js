function S3MultiUpload(file) {
    this.PART_SIZE = 100 * 1024 * 1024; // 100 MB per chunk
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
    
    // File type validation
    this.allowedMimeTypes = [
        'video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv',
        'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska',
        'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a', 'audio/aac', 'audio/flac',
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff',
        'application/pdf', 'application/zip', 'application/x-zip-compressed'
    ];
    
    this.fileSignatures = {
        // Video formats
        'video/mp4': ['ftyp', 'mp4', 'M4V', 'isom', 'iso2', 'avc1', 'mp41', 'mp42'],
        'video/webm': ['webm'],
        'video/ogg': ['OggS'],
        'video/avi': ['RIFF'],
        'video/mov': ['ftyp', 'qt  ', 'M4V ', 'mp41', 'mp42'],
        'video/wmv': ['ASF_'],
        'video/flv': ['FLV'],
        'video/mkv': ['matroska'],
        
        // Audio formats
        'audio/mp3': ['ID3', '\xFF\xFB', '\xFF\xF3', '\xFF\xF2'],
        'audio/wav': ['RIFF'],
        'audio/ogg': ['OggS'],
        'audio/m4a': ['ftyp', 'M4A ', 'mp4a'],
        'audio/aac': ['ADIF', 'ADTS'],
        'audio/flac': ['fLaC'],
        
        // Image formats
        'image/jpeg': ['\xFF\xD8\xFF'],
        'image/png': ['\x89PNG\r\n\x1A\n'],
        'image/gif': ['GIF87a', 'GIF89a'],
        'image/webp': ['RIFF'],
        'image/bmp': ['BM'],
        'image/tiff': ['II*\x00', 'MM\x00*'],
        
        // Document formats
        'application/pdf': ['%PDF'],
        'application/zip': ['PK\x03\x04', 'PK\x05\x06', 'PK\x07\x08']
    };
    
    this.maxFileSize = 10 * 1024 * 1024 * 1024; // 10 GB default max
    this.validatedFileType = null;
    this.validationError = null;
}

/**
 * Validates file type using both MIME type and file signatures
 * @param {Function} callback - Callback function with (isValid, error) parameters
 */
S3MultiUpload.prototype.validateFileType = function(callback) {
    var self = this;
    
    // Check file size first
    if (this.file.size > this.maxFileSize) {
        this.validationError = 'File size exceeds maximum allowed size of ' + this.formatFileSize(this.maxFileSize);
        callback(false, this.validationError);
        return;
    }
    
    // Check if MIME type is in allowed list
    var mimeTypeAllowed = this.allowedMimeTypes.indexOf(this.file.type) !== -1;
    
    // Read file header to check file signature
    var reader = new FileReader();
    reader.onload = function(e) {
        var arrayBuffer = e.target.result;
        var uint8Array = new Uint8Array(arrayBuffer);
        var header = self.arrayBufferToString(uint8Array.slice(0, 64)); // Read first 64 bytes
        
        var signatureMatch = self.checkFileSignature(header, uint8Array);
        
        if (signatureMatch) {
            self.validatedFileType = signatureMatch;
            // If MIME type doesn't match signature, use the detected type
            if (!mimeTypeAllowed) {
                self.fileInfo.type = signatureMatch;
            }
            callback(true, null);
        } else if (mimeTypeAllowed) {
            // If signature check fails but MIME type is allowed, proceed with caution
            self.validatedFileType = self.file.type;
            callback(true, 'Warning: File signature could not be verified, but MIME type is allowed');
        } else {
            self.validationError = 'File type not allowed. Detected: ' + self.file.type + 
                                 '. Allowed types: ' + self.allowedMimeTypes.join(', ');
            callback(false, self.validationError);
        }
    };
    
    reader.onerror = function() {
        self.validationError = 'Failed to read file for validation';
        callback(false, self.validationError);
    };
    
    reader.readAsArrayBuffer(this.file.slice(0, 64)); // Read first 64 bytes
};

/**
 * Checks file signature against known patterns
 * @param {string} header - File header as string
 * @param {Uint8Array} uint8Array - File header as byte array
 * @returns {string|null} - Detected MIME type or null
 */
S3MultiUpload.prototype.checkFileSignature = function(header, uint8Array) {
    for (var mimeType in this.fileSignatures) {
        var signatures = this.fileSignatures[mimeType];
        for (var i = 0; i < signatures.length; i++) {
            var signature = signatures[i];
            
            // Check for text-based signatures
            if (header.indexOf(signature) === 0) {
                return mimeType;
            }
            
            // Check for binary signatures (hex patterns)
            if (signature.startsWith('\\x')) {
                var hexPattern = signature.replace(/\\x/g, '');
                var patternBytes = [];
                for (var j = 0; j < hexPattern.length; j += 2) {
                    patternBytes.push(parseInt(hexPattern.substr(j, 2), 16));
                }
                
                var match = true;
                for (var k = 0; k < patternBytes.length; k++) {
                    if (uint8Array[k] !== patternBytes[k]) {
                        match = false;
                        break;
                    }
                }
                
                if (match) {
                    return mimeType;
                }
            }
        }
    }
    return null;
};

/**
 * Converts ArrayBuffer to string for text-based signature checking
 * @param {Uint8Array} uint8Array - Byte array
 * @returns {string} - String representation
 */
S3MultiUpload.prototype.arrayBufferToString = function(uint8Array) {
    var string = '';
    for (var i = 0; i < uint8Array.length; i++) {
        string += String.fromCharCode(uint8Array[i]);
    }
    return string;
};

/**
 * Formats file size in human readable format
 * @param {number} bytes - Size in bytes
 * @returns {string} - Formatted size string
 */
S3MultiUpload.prototype.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

/**
 * Sets allowed MIME types for validation
 * @param {Array} mimeTypes - Array of allowed MIME types
 */
S3MultiUpload.prototype.setAllowedMimeTypes = function(mimeTypes) {
    this.allowedMimeTypes = mimeTypes;
};

/**
 * Sets maximum file size limit
 * @param {number} maxSize - Maximum size in bytes
 */
S3MultiUpload.prototype.setMaxFileSize = function(maxSize) {
    this.maxFileSize = maxSize;
};

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
 * Call this function to start uploading to server with validation
 */
S3MultiUpload.prototype.start = function() {
    var self = this;
    this.validateFileType(function(isValid, error) {
        if (isValid) {
            self.createMultipartUpload();
        } else {
            self.onValidationError(error);
        }
    });
};

/** private */
S3MultiUpload.prototype.uploadParts = function() {
    var blobs = this.blobs = [], promises = [];
    var start = 0;
    var end, blob;
    var partNum = 0;

    while(start < this.file.size) {
        end = Math.min(start + this.PART_SIZE, this.file.size);
        var filePart = this.file.slice(start, end);
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
 * Override this method to handle file validation errors
 * @param {string} error - Validation error message
 */
S3MultiUpload.prototype.onValidationError = function(error) {};
