<?php
/**
 * Unit tests for S3 Upload Validation
 */

class Test_S3_Upload_Validation extends WP_UnitTestCase {

    private $s3_upload;

    public function setUp(): void {
        parent::setUp();
        
        // Include the S3 upload class
        require_once(dirname(__FILE__) . '/../../../controller/s3-upload.php');
        
        global $FV_Player_S3_Upload;
        $this->s3_upload = $FV_Player_S3_Upload;
    }

    public function test_validate_file_upload_missing_nonce() {
        // Test that validation fails without nonce
        $_POST = array();
        $_FILES = array();
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('error', $output);
    }

    public function test_validate_file_upload_invalid_nonce() {
        // Test that validation fails with invalid nonce
        $_POST = array(
            'nonce' => 'invalid_nonce'
        );
        $_FILES = array();
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('error', $output);
    }

    public function test_validate_file_upload_no_file() {
        // Test that validation fails when no file is uploaded
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file')
        );
        $_FILES = array();
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('error', $output);
    }

    public function test_validate_file_upload_success() {
        // Create a temporary test file
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, 'test content');
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.mp4',
                'type' => 'video/mp4',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.mp4',
                'type' => 'video/mp4',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('success', $output);
    }

    public function test_validate_file_upload_dangerous_extension() {
        // Create a temporary test file
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, 'test content');
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.php',
                'type' => 'application/x-httpd-php',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.php',
                'type' => 'application/x-httpd-php',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('error', $output);
    }

    public function test_validate_file_upload_executable_content() {
        // Create a temporary test file with PHP code
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, '<?php echo "hello"; ?>');
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.txt',
                'type' => 'text/plain',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('error', $output);
    }

    public function test_validate_file_upload_linux_executable() {
        // Create a temporary test file with ELF header (Linux executable)
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, "\x7fELF\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00");
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.so',
                'type' => 'application/octet-stream',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.so',
                'type' => 'application/octet-stream',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('error', $output);
        $this->assertStringContainsString('Linux executable', $output);
    }

    public function test_validate_file_upload_windows_executable() {
        // Create a temporary test file with MZ header (Windows executable)
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00");
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.exe',
                'type' => 'application/octet-stream',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.exe',
                'type' => 'application/octet-stream',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('error', $output);
        $this->assertStringContainsString('Windows executable', $output);
    }

    public function test_validate_file_upload_valid_mp4() {
        // Create a temporary test file with MP4 header
        $temp_file = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($temp_file, "\x00\x00\x00\x20ftypmp4\x00\x00\x00\x00mp4\x00\x00\x00\x00");
        
        $_POST = array(
            'nonce' => wp_create_nonce('fv_flowplayer_validate_file'),
            'file_info' => json_encode(array(
                'name' => 'test.mp4',
                'type' => 'video/mp4',
                'size' => 1024
            ))
        );
        
        $_FILES = array(
            'file_chunk' => array(
                'name' => 'test.mp4',
                'type' => 'video/mp4',
                'tmp_name' => $temp_file,
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            )
        );
        
        ob_start();
        $this->s3_upload->validate_file_upload();
        $output = ob_get_clean();
        
        // Clean up
        unlink($temp_file);
        
        $this->assertStringContainsString('success', $output);
    }
} 