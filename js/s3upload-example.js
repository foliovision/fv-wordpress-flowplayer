/**
 * Example usage of the improved S3MultiUpload with file type validation
 */

// Example 1: Basic usage with default validation
function uploadFileWithValidation(file) {
    var uploader = new S3MultiUpload(file);
    
    // Override validation error handler
    uploader.onValidationError = function(error) {
        console.error('File validation failed:', error);
        alert('File validation failed: ' + error);
    };
    
    // Override progress handler
    uploader.onProgressChanged = function(uploadedSize, totalSize, bitrate) {
        var percent = Math.round((uploadedSize / totalSize) * 100);
        console.log('Upload progress: ' + percent + '% (' + formatFileSize(uploadedSize) + ' / ' + formatFileSize(totalSize) + ')');
    };
    
    // Override completion handler
    uploader.onUploadCompleted = function(serverData) {
        console.log('Upload completed successfully:', serverData);
        alert('File uploaded successfully!');
    };
    
    // Start the upload (validation happens automatically)
    uploader.start();
}

// Example 2: Custom validation settings
function uploadFileWithCustomValidation(file) {
    var uploader = new S3MultiUpload(file);
    
    // Set custom allowed MIME types (only video files)
    uploader.setAllowedMimeTypes([
        'video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv'
    ]);
    
    // Set custom file size limit (1 GB)
    uploader.setMaxFileSize(1024 * 1024 * 1024);
    
    uploader.onValidationError = function(error) {
        console.error('Custom validation failed:', error);
        alert('File validation failed: ' + error);
    };
    
    uploader.onUploadCompleted = function(serverData) {
        console.log('Video upload completed:', serverData);
    };
    
    uploader.start();
}

// Example 3: Manual validation before upload
function validateAndUploadFile(file) {
    var uploader = new S3MultiUpload(file);
    
    // Manual validation
    uploader.validateFileType(function(isValid, error) {
        if (isValid) {
            console.log('File validation passed. Detected type:', uploader.validatedFileType);
            
            // Set up event handlers
            uploader.onProgressChanged = function(uploadedSize, totalSize, bitrate) {
                updateProgressBar(uploadedSize, totalSize);
            };
            
            uploader.onUploadCompleted = function(serverData) {
                console.log('Upload completed:', serverData);
                showSuccessMessage();
            };
            
            uploader.onServerError = function(command, jqXHR, textStatus, errorThrown) {
                console.error('Server error:', command, errorThrown);
                showErrorMessage('Server error: ' + errorThrown);
            };
            
            // Start upload
            uploader.createMultipartUpload();
        } else {
            console.error('File validation failed:', error);
            showErrorMessage('File validation failed: ' + error);
        }
    });
}

// Example 4: File input with validation
function setupFileUpload() {
    var fileInput = document.getElementById('fileInput');
    var uploadButton = document.getElementById('uploadButton');
    var statusDiv = document.getElementById('status');
    
    fileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        
        var uploader = new S3MultiUpload(file);
        
        // Show file info
        statusDiv.innerHTML = 'File: ' + file.name + ' (' + formatFileSize(file.size) + ')';
        
        // Validate file
        uploader.validateFileType(function(isValid, error) {
            if (isValid) {
                statusDiv.innerHTML += '<br>✓ File type validated: ' + uploader.validatedFileType;
                uploadButton.disabled = false;
                
                // Store uploader for later use
                uploadButton.uploader = uploader;
            } else {
                statusDiv.innerHTML += '<br>✗ Validation failed: ' + error;
                uploadButton.disabled = true;
            }
        });
    });
    
    uploadButton.addEventListener('click', function() {
        if (this.uploader) {
            this.uploader.onProgressChanged = function(uploadedSize, totalSize, bitrate) {
                var percent = Math.round((uploadedSize / totalSize) * 100);
                statusDiv.innerHTML = 'Uploading: ' + percent + '%';
            };
            
            this.uploader.onUploadCompleted = function(serverData) {
                statusDiv.innerHTML = '✓ Upload completed successfully!';
            };
            
            this.uploader.onValidationError = function(error) {
                statusDiv.innerHTML = '✗ Upload failed: ' + error;
            };
            
            this.uploader.start();
            this.disabled = true;
        }
    });
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Example UI helper functions
function updateProgressBar(uploadedSize, totalSize) {
    var progressBar = document.getElementById('progressBar');
    if (progressBar) {
        var percent = Math.round((uploadedSize / totalSize) * 100);
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
    }
}

function showSuccessMessage() {
    alert('File uploaded successfully!');
}

function showErrorMessage(message) {
    alert('Error: ' + message);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setupFileUpload();
}); 