<?php
/**
 * Plugin Name: Custom Media Folders - ACF Pro Integration
 * Plugin URI: https://yourwebsite.com/
 * Description: ACF Pro integration for Custom Media Folders - automatically organize ACF uploads into specified folders
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: cmf-acf
 * Requires Plugins: custom-media-folders, advanced-custom-fields-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check dependencies
add_action('plugins_loaded', 'cmf_acf_check_dependencies', 5);
function cmf_acf_check_dependencies() {
    if (!class_exists('Custom_Media_Folders') || !class_exists('ACF')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Custom Media Folders - ACF Integration requires both Custom Media Folders and Advanced Custom Fields Pro to be active.', 'cmf-acf'); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Initialize the integration
    CMF_ACF_Integration::get_instance();
}

/**
 * Main ACF Integration Class
 */
class CMF_ACF_Integration {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Current upload field folder
     */
    private $current_field_folder = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add custom folder setting to ACF image and gallery fields
        add_action('acf/render_field_settings/type=image', array($this, 'add_folder_setting'));
        add_action('acf/render_field_settings/type=gallery', array($this, 'add_folder_setting'));
        add_action('acf/render_field_settings/type=file', array($this, 'add_folder_setting'));
        
        // Hook into ACF upload process
        add_filter('acf/upload_prefilter/type=image', array($this, 'set_upload_folder'), 10, 3);
        add_filter('acf/upload_prefilter/type=gallery', array($this, 'set_upload_folder'), 10, 3);
        add_filter('acf/upload_prefilter/type=file', array($this, 'set_upload_folder'), 10, 3);
        
        // Modify upload directory when ACF is uploading
        add_filter('upload_dir', array($this, 'modify_upload_dir'), 20);
        
        // Add folder selector UI for field settings
        add_action('admin_footer', array($this, 'add_folder_selector_script'));
        
        // AJAX handler for getting folder list
        add_action('wp_ajax_cmf_acf_get_folders', array($this, 'ajax_get_folders'));
        
        // Store the folder ID when attachment is added via ACF
        add_action('add_attachment', array($this, 'save_acf_attachment_folder'), 15);
    }
    
    /**
     * Add folder setting to ACF field configuration
     */
    public function add_folder_setting($field) {
        // Get folders for dropdown
        $folders = $this->get_folder_choices();
        
        // Add folder path setting
        acf_render_field_setting($field, array(
            'label' => __('Upload Folder', 'cmf-acf'),
            'instructions' => __('Select folder where uploads from this field will be stored', 'cmf-acf'),
            'type' => 'select',
            'name' => 'cmf_folder',
            'choices' => $folders,
            'allow_null' => 1,
            'default_value' => '',
            'ui' => 1,
            'ajax' => 0,
            'placeholder' => __('Default WordPress uploads', 'cmf-acf'),
            'class' => 'cmf-acf-folder-select'
        ));
        
        // Add custom path input option
        acf_render_field_setting($field, array(
            'label' => __('Custom Folder Path', 'cmf-acf'),
            'instructions' => __('Or enter a custom path (e.g., "blocks/hero" or "content/testimonials"). Leave empty to use selection above.', 'cmf-acf'),
            'type' => 'text',
            'name' => 'cmf_custom_path',
            'placeholder' => 'e.g., blocks/hero',
            'class' => 'cmf-acf-custom-path'
        ));
        
        // Add a setting to auto-create folders if they don't exist
        acf_render_field_setting($field, array(
            'label' => __('Auto-create Folders', 'cmf-acf'),
            'instructions' => __('Automatically create the folder if it doesn\'t exist', 'cmf-acf'),
            'type' => 'true_false',
            'name' => 'cmf_auto_create',
            'default_value' => 1,
            'ui' => 1
        ));
    }
    
    /**
     * Get folder choices for dropdown
     */
    private function get_folder_choices() {
        $choices = array();
        
        // Check if CMF is active and get folders
        if (class_exists('CMF_Database')) {
            $db = new CMF_Database();
            $folders = $db->get_all_folders();
            
            if ($folders) {
                foreach ($folders as $folder) {
                    // Create indentation for nested folders
                    $indent = str_repeat('— ', substr_count($folder->path, '/'));
                    $choices[$folder->id] = $indent . $folder->name . ' (/' . $folder->path . ')';
                }
            }
        }
        
        return $choices;
    }
    
    /**
     * Set upload folder based on ACF field settings
     */
    public function set_upload_folder($errors, $file, $field) {
        // Check for custom path first
        if (!empty($field['cmf_custom_path'])) {
            $this->current_field_folder = array(
                'type' => 'path',
                'value' => $field['cmf_custom_path'],
                'auto_create' => !empty($field['cmf_auto_create'])
            );
        }
        // Then check for selected folder
        elseif (!empty($field['cmf_folder'])) {
            $this->current_field_folder = array(
                'type' => 'id',
                'value' => $field['cmf_folder'],
                'auto_create' => !empty($field['cmf_auto_create'])
            );
        } else {
            $this->current_field_folder = null;
        }
        
        return $errors;
    }
    
    /**
     * Modify upload directory for ACF uploads
     */
    public function modify_upload_dir($upload) {
        // Only modify if we have a folder setting from ACF
        if (empty($this->current_field_folder)) {
            return $upload;
        }
        
        // Get the folder manager
        if (!class_exists('CMF_Folder_Manager')) {
            return $upload;
        }
        
        $folder_manager = new CMF_Folder_Manager();
        $db = new CMF_Database();
        $folder = null;
        
        // Handle based on type
        if ($this->current_field_folder['type'] === 'path') {
            // Custom path provided
            $path = trim($this->current_field_folder['value'], '/');
            
            if ($this->current_field_folder['auto_create']) {
                // Auto-create folder structure if needed
                $folder = $folder_manager->get_or_create_from_path($path);
                if (is_wp_error($folder)) {
                    // If creation failed, use default upload
                    return $upload;
                }
            } else {
                // Try to find existing folder by path
                $folder = $db->get_folder_by_path($path);
            }
        } else {
            // Folder ID provided
            $folder = $db->get_folder($this->current_field_folder['value']);
        }
        
        // If we have a valid folder, modify the upload directory
        if ($folder && !is_wp_error($folder)) {
            $folder_path = $folder->path;
            
            // Modify upload paths
            $upload['path'] = trailingslashit($upload['basedir']) . $folder_path;
            $upload['url'] = trailingslashit($upload['baseurl']) . $folder_path;
            $upload['subdir'] = '';
            
            // Store folder ID for attachment metadata
            $GLOBALS['cmf_acf_current_folder_id'] = $folder->id;
            $GLOBALS['cmf_acf_current_folder_path'] = $folder->path;
            
            // Ensure directory exists
            if (!file_exists($upload['path'])) {
                wp_mkdir_p($upload['path']);
            }
        }
        
        return $upload;
    }
    
    /**
     * Save folder information when attachment is added via ACF
     */
    public function save_acf_attachment_folder($attachment_id) {
        // Check if we have folder information from ACF upload
        if (!empty($GLOBALS['cmf_acf_current_folder_id'])) {
            update_post_meta($attachment_id, '_cmf_folder_id', $GLOBALS['cmf_acf_current_folder_id']);
            
            if (!empty($GLOBALS['cmf_acf_current_folder_path'])) {
                update_post_meta($attachment_id, '_cmf_folder_path', $GLOBALS['cmf_acf_current_folder_path']);
            }
            
            // Update folder media count
            if (class_exists('CMF_Database')) {
                $db = new CMF_Database();
                $db->update_media_count($GLOBALS['cmf_acf_current_folder_id']);
            }
            
            // Clear globals
            unset($GLOBALS['cmf_acf_current_folder_id']);
            unset($GLOBALS['cmf_acf_current_folder_path']);
        }
        
        // Reset current field folder
        $this->current_field_folder = null;
    }
    
    /**
     * AJAX handler to get folders
     */
    public function ajax_get_folders() {
        check_ajax_referer('acf_nonce', 'nonce');
        
        $folders = $this->get_folder_choices();
        wp_send_json_success($folders);
    }
    
    /**
     * Add JavaScript for folder selector enhancement
     */
    public function add_folder_selector_script() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, array('acf-field-group'))) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Enhance folder selector UI
            $(document).on('change', '.cmf-acf-folder-select', function() {
                var $customPath = $(this).closest('.acf-field-settings').find('.cmf-acf-custom-path');
                if ($(this).val()) {
                    $customPath.val('').prop('disabled', true).css('opacity', '0.5');
                } else {
                    $customPath.prop('disabled', false).css('opacity', '1');
                }
            });
            
            $(document).on('input', '.cmf-acf-custom-path', function() {
                var $folderSelect = $(this).closest('.acf-field-settings').find('.cmf-acf-folder-select');
                if ($(this).val()) {
                    $folderSelect.val('').trigger('change').prop('disabled', true).css('opacity', '0.5');
                } else {
                    $folderSelect.prop('disabled', false).css('opacity', '1');
                }
            });
            
            // Add helper text
            $('.cmf-acf-folder-select').each(function() {
                if (!$(this).next('.description').length) {
                    $(this).after('<p class="description" style="margin-top: 5px;">Uploads from this field will automatically go to the selected folder.</p>');
                }
            });
        });
        </script>
        <style>
            .cmf-acf-folder-select.select2-container {
                min-width: 300px;
            }
            .cmf-acf-custom-path {
                font-family: monospace;
            }
        </style>
        <?php
    }
}
