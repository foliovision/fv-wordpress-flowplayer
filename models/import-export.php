<?php

class FV_Player_Export_Import {

    public function __construct() {
        add_action('admin_init', array($this, 'init_options'));
        add_action('admin_init', array($this, 'admin__add_meta_boxes'));
        if (!empty($_GET['fv-settings-export']) && !empty($_GET['page']) && $_GET['page'] === 'fvplayer') {
            add_action('admin_init', array($this, 'json_export'));
        }
        if (!empty($_GET['fv-settings-import']) && !empty($_GET['page']) && $_GET['page'] === 'fvplayer') {
            if (isset($_FILES['fileToUpload']) && 0 !== $_FILES['fileToUpload']['size']) {
                add_action('admin_init', array($this, 'json_import'));
            }
        }
    }

    public function init_options() {
        return;
    }

    public function admin__add_meta_boxes() {
        add_meta_box('fv_flowplayer_export_settings', __('Import settings', 'fv-wordpress-flowplayer'), array($this, 'import_box_admin'), 'fv_flowplayer_settings_exip', 'normal');
    }

    public function import_box_admin() {
        ?>
        <table class="form-table2" style="margin: 5px; ">
            <tr>
                <td style="width: 160px"><?php _e('Import your settings', 'fv-wordpress-flowplayer'); ?>:</td>
            </tr>
            <tr>
                <td>
                    <input type="file" id="fileToUpload" name="fileToUpload" />
                    <input type="submit" id="submitUpload" name="Upload File" value="Upload" style="cursor:pointer">
                    <p  id="fileResult"></p>
                </td>
            </tr> 

            <script>
                $('#submitUpload').click(function (event) {
                    event.preventDefault();
                    var file = $('#fileToUpload').get(0).files[0];
                    var formData = new FormData();
                    formData.append('fileToUpload', file);
                    formData.append("fv-settings-import", "complete");
                    $.ajax({
                        url: '<?php echo admin_url('options-general.php?page=fvplayer&fv-settings-import=all#postbox-container-tab_exip'); ?>',
                        beforeSend: function (e) {
                            alert('Are you sure you want to upload document.');
                        },
                        success: function (e) {
                            // console.log(e);   
                            $("#fileResult").text("<?php _e('import completed successfully. I apply new settings ...', 'fv-wordpress-flowplayer'); ?>");
                            window.location.reload(this);
                        },
                        error: function (e) {
                            alert('error ' + e.message);
                        },
                        data: formData,
                        type: 'POST',
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    return false;
                });
            </script>
        </table>
        <?php
    }

    function json_export() {
        $aLists = get_option('fvwpflowplayer');
        $filename = 'export-fv-settings-' . date('Y-m-d') . '.json';
        header('Content-Type: application/json');
        header("Content-Disposition: attachment; filename=$filename");
        header("Cache-Control: no-cache");
        header("Expires: 0");

        echo json_encode($aLists);

        die;
    }

    function json_import() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $extension = end(explode('.', $_FILES['fileToUpload']['name']));
        if ($extension != 'json') {
            wp_die(__('Please upload a valid .json file'));
        }

        $import_file = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
        $settings = (array) json_decode($import_file, true);
        //Update entire array
        update_option('fvwpflowplayer', $settings);
    }

}

$FV_Player_Export_Import = new FV_Player_Export_Import();
