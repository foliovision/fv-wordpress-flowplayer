<?php
/**
 * FV Player S3 Uploader Standalone Function
 * 
 * This function provides a standalone S3 uploader that can be used anywhere on the website.
 * It outputs a button to pick a file and includes JavaScript to handle the upload process.
 * 
 * @param array $options Configuration options for the uploader
 * @return string HTML output for the uploader
 */
function fv_player_s3_uploader_standalone($options = array()) {
    // Default options
    $defaults = array(
        'button_text' => 'Choose Video File',
        'button_class' => 'fv-player-s3-upload-btn',
        'container_class' => 'fv-player-s3-upload-container',
        'progress_class' => 'fv-player-s3-upload-progress',
        'progress_bar_class' => 'fv-player-s3-upload-progress-bar',
        'progress_text_class' => 'fv-player-s3-upload-progress-text',
        'file_input_name' => 'fv_player_s3_upload_file',
        'file_input_class' => 'fv-player-s3-upload-file-input',
        'form_id' => 'fv-player-s3-upload-form',
        'on_upload_complete' => 'function(data) { console.log("Upload completed:", data); }',
        'on_upload_error' => 'function() { console.log("Upload failed"); }',
        'on_upload_start' => 'function() { console.log("Upload started"); }',
        'min_file_size' => 5242880, // 5MB in bytes
        'accepted_file_types' => '.mp4,.mov,.web,.flv,.avi,.vmw,.avchd,.swf,.mkv,.webm,.mpeg,.mpg',
        'success_message' => 'Upload completed successfully!',
        'error_message' => 'Upload failed. Please try again.',
        'file_too_small_message' => 'Only files larger than 5MB can be uploaded using this uploader.',
        'browser_not_supported_message' => 'You are using an unsupported browser. Please update your browser.'
    );
    
    $options = wp_parse_args($options, $defaults);
    
    // Ensure required scripts and styles are loaded
    fv_player_s3_uploader_standalone_enqueue_assets();
    
    // Generate unique IDs to avoid conflicts
    $unique_id = 'fv_player_s3_' . uniqid();
    $options['container_id'] = $unique_id . '_container';
    
    // Start output buffering
    ob_start();
    ?>
    <div id="<?php echo esc_attr($options['container_id']); ?>" class="<?php echo esc_attr($options['container_class']); ?>">
        <!-- Upload interface will be created by JavaScript -->
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Use the existing FV Player S3 uploader initialization with standalone mode
        fv_flowplayer_init_s3_uploader({
            standalone_mode: true,
            container_id: '<?php echo esc_js($options['container_id']); ?>',
            upload_button_class: '<?php echo esc_js($options['button_class']); ?>',
            upload_button_text: '<?php echo esc_js($options['button_text']); ?>',
            cancel_button_class: '<?php echo esc_js($options['button_class']); ?>-cancel',
            upload_progress_class: '<?php echo esc_js($options['progress_class']); ?>',
            upload_progress_bar_enclosure_class: '<?php echo esc_js($options['progress_bar_class']); ?>-container',
            upload_progress_bar_class: '<?php echo esc_js($options['progress_bar_class']); ?>',
            upload_progress_bar_number_class: '<?php echo esc_js($options['progress_text_class']); ?>',
            file_select_input_name: '<?php echo esc_js($options['file_input_name']); ?>',
            file_select_input_class: '<?php echo esc_js($options['file_input_class']); ?>',
            tab_id: 'standalone-uploader',
            upload_success_message: '<?php echo esc_js($options['success_message']); ?>',
            upload_start_callback: function() {
                // Call upload start callback
                if (typeof window['<?php echo esc_js($options['on_upload_start']); ?>'] === 'function') {
                    window['<?php echo esc_js($options['on_upload_start']); ?>']();
                }
            },
            upload_success_callback: function(data) {
                // Call success callback
                if (typeof window['<?php echo esc_js($options['on_upload_complete']); ?>'] === 'function') {
                    window['<?php echo esc_js($options['on_upload_complete']); ?>'](data);
                }
            },
            upload_error_callback: function() {
                // Call error callback
                if (typeof window['<?php echo esc_js($options['on_upload_error']); ?>'] === 'function') {
                    window['<?php echo esc_js($options['on_upload_error']); ?>']();
                }
            }
        });
        
        // Prevent form submission during upload
        $('#' + '<?php echo esc_js($options['container_id']); ?>').closest('form').on('submit', function(e) {
            // Check if there's an active upload
            if (window.s3upload && window.s3upload.isUploading && window.s3upload.isUploading()) {
                e.preventDefault();
                alert('Please wait for the video upload to complete before submitting the form.');
                return false;
            }
        });
    });
    </script>
    
    <style>
    .<?php echo esc_attr($options['container_class']); ?> {
        margin: 20px 0;
    }
    
    .<?php echo esc_attr($options['button_class']); ?> {
        background: #0073aa;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .<?php echo esc_attr($options['button_class']); ?>:hover {
        background: #005a87;
    }
    
    .<?php echo esc_attr($options['progress_class']); ?> {
        margin-top: 10px;
    }
    
    .<?php echo esc_attr($options['progress_bar_class']); ?>-container {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .<?php echo esc_attr($options['progress_bar_class']); ?> {
        height: 100%;
        background-color: #0073aa;
        transition: width 0.3s ease;
    }
    
    .<?php echo esc_attr($options['progress_text_class']); ?> {
        font-size: 12px;
        color: #666;
    }
    </style>
    <?php
    
    return ob_get_clean();
}

/**
 * Enqueue required assets for the standalone S3 uploader
 */
function fv_player_s3_uploader_standalone_enqueue_assets() {
    static $assets_loaded = false;
    
    if ($assets_loaded) {
        return;
    }
    
    global $fv_wp_flowplayer_ver;
    
    // Enqueue jQuery if not already loaded
    wp_enqueue_script('jquery');
    
    // Enqueue S3 uploader scripts (this includes the existing fv_flowplayer_init_s3_uploader function)
    wp_enqueue_script('fv-player-s3-uploader', flowplayer::get_plugin_url().'/js/s3upload.js', array('jquery'), $fv_wp_flowplayer_ver);
    wp_enqueue_script('fv-player-s3-uploader-base', flowplayer::get_plugin_url().'/js/s3-upload-base.js', array('jquery', 'fv-player-s3-uploader'), $fv_wp_flowplayer_ver);
    
    // Localize script with nonce
    wp_localize_script('fv-player-s3-uploader', 'fv_player_s3_uploader', array(
        'validate_file_nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
        'create_multiupload_nonce' => wp_create_nonce('fv_flowplayer_create_multiupload'),
        'ajaxurl' => flowplayer::get_plugin_url().'/controller/s3-ajax.php'
    ));
    
    // Enqueue S3 uploader styles
    wp_enqueue_style('fv-player-s3-uploader-css', flowplayer::get_plugin_url() . '/css/s3-uploader.css', array(), filemtime(dirname(__FILE__).'/../css/s3-uploader.css'));
    
    $assets_loaded = true;
}

/**
 * Shortcode for the S3 uploader
 * 
 * Usage: [fv_player_s3_uploader button_text="Upload Video" on_upload_complete="myCallback"]
 */
function fv_player_s3_uploader_shortcode($atts) {
    $atts = shortcode_atts(array(
        'button_text' => 'Choose Video File',
        'button_class' => 'fv-player-s3-upload-btn',
        'container_class' => 'fv-player-s3-upload-container',
        'progress_class' => 'fv-player-s3-upload-progress',
        'progress_bar_class' => 'fv-player-s3-upload-progress-bar',
        'progress_text_class' => 'fv-player-s3-upload-progress-text',
        'file_input_name' => 'fv_player_s3_upload_file',
        'file_input_class' => 'fv-player-s3-upload-file-input',
        'form_id' => 'fv-player-s3-upload-form',
        'on_upload_complete' => '',
        'on_upload_error' => '',
        'on_upload_start' => '',
        'min_file_size' => 5242880,
        'accepted_file_types' => '.mp4,.mov,.web,.flv,.avi,.vmw,.avchd,.swf,.mkv,.webm,.mpeg,.mpg',
        'success_message' => 'Upload completed successfully!',
        'error_message' => 'Upload failed. Please try again.',
        'file_too_small_message' => 'Only files larger than 5MB can be uploaded using this uploader.',
        'browser_not_supported_message' => 'You are using an unsupported browser. Please update your browser.'
    ), $atts);
    
    return fv_player_s3_uploader_standalone($atts);
}

// Register shortcode
add_shortcode('fv_player_s3_uploader', 'fv_player_s3_uploader_shortcode'); 