<?php
/**
 * Register ACF Fields for Hero Block with Custom Media Folders
 * 
 * This demonstrates how to register ACF fields with CMF folder settings
 * for use with block.json based ACF blocks
 */

add_action('acf/init', 'register_hero_block_fields');
function register_hero_block_fields() {
    
    // Check if function exists
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_hero_section',
        'title' => 'Hero Section Fields',
        'fields' => array(
            array(
                'key' => 'field_hero_bg_image',
                'label' => 'Background Image',
                'name' => 'background_image',
                'type' => 'image',
                'instructions' => 'Upload the hero background image. Images will be automatically organized into the heroes folder.',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'large',
                'library' => 'all',
                'min_width' => 1920,
                'min_height' => 600,
                'mime_types' => 'jpg, jpeg, png, webp',
                
                // Custom Media Folders Integration Settings
                // These settings tell the plugin where to store uploads from this field
                'cmf_custom_path' => 'blocks/hero/backgrounds',  // Custom folder path
                'cmf_auto_create' => 1,                         // Auto-create folder if it doesn't exist
                
                // Alternative: Use existing folder by ID (get from Media > Media Folders admin)
                // 'cmf_folder' => 5,  // Use folder with ID 5
            ),
            array(
                'key' => 'field_hero_mobile_image',
                'label' => 'Mobile Background Image',
                'name' => 'mobile_background_image',
                'type' => 'image',
                'instructions' => 'Optional mobile-specific background image',
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'cmf_custom_path' => 'blocks/hero/mobile',  // Different folder for mobile images
                'cmf_auto_create' => 1,
            ),
            array(
                'key' => 'field_hero_title',
                'label' => 'Title',
                'name' => 'title',
                'type' => 'text',
                'instructions' => 'Main hero title',
                'required' => 1,
                'default_value' => '',
                'placeholder' => 'Enter your hero title',
            ),
            array(
                'key' => 'field_hero_subtitle',
                'label' => 'Subtitle',
                'name' => 'subtitle',
                'type' => 'textarea',
                'instructions' => 'Supporting text below the title',
                'required' => 0,
                'rows' => 3,
                'new_lines' => 'br',
            ),
            array(
                'key' => 'field_hero_cta',
                'label' => 'Call to Action Button',
                'name' => 'cta_button',
                'type' => 'link',
                'instructions' => 'Optional CTA button',
                'required' => 0,
                'return_format' => 'array',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'block',
                    'operator' => '==',
                    'value' => 'acf/hero-section',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ));

    // Register another example: Team Member block
    acf_add_local_field_group(array(
        'key' => 'group_team_member',
        'title' => 'Team Member',
        'fields' => array(
            array(
                'key' => 'field_team_photo',
                'label' => 'Team Member Photo',
                'name' => 'photo',
                'type' => 'image',
                'instructions' => 'Upload team member photo',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                
                // Organize team photos in a dedicated folder
                'cmf_custom_path' => 'team/photos',
                'cmf_auto_create' => 1,
            ),
            array(
                'key' => 'field_team_name',
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
                'required' => 1,
            ),
            array(
                'key' => 'field_team_position',
                'label' => 'Position',
                'name' => 'position',
                'type' => 'text',
            ),
            array(
                'key' => 'field_team_bio',
                'label' => 'Bio',
                'name' => 'bio',
                'type' => 'wysiwyg',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'block',
                    'operator' => '==',
                    'value' => 'acf/team-member',
                ),
            ),
        ),
    ));

    // Example with Gallery field
    acf_add_local_field_group(array(
        'key' => 'group_portfolio_gallery',
        'title' => 'Portfolio Gallery',
        'fields' => array(
            array(
                'key' => 'field_portfolio_images',
                'label' => 'Portfolio Images',
                'name' => 'gallery',
                'type' => 'gallery',
                'instructions' => 'Upload portfolio images',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'min' => 1,
                'max' => 20,
                
                // All portfolio images go to a dedicated folder
                'cmf_custom_path' => 'portfolio/gallery',
                'cmf_auto_create' => 1,
            ),
            array(
                'key' => 'field_portfolio_title',
                'label' => 'Portfolio Title',
                'name' => 'title',
                'type' => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'block',
                    'operator' => '==',
                    'value' => 'acf/portfolio-gallery',
                ),
            ),
        ),
    ));
}

/**
 * Helper function to get folder structure recommendations
 * You can use this to plan your folder organization
 */
function get_recommended_cmf_folder_structure() {
    return array(
        'blocks' => array(
            'hero' => array(
                'backgrounds' => 'Hero section background images',
                'mobile' => 'Mobile-specific hero images',
            ),
            'testimonials' => 'Testimonial related images',
            'cta' => 'Call-to-action section images',
        ),
        'team' => array(
            'photos' => 'Team member photos',
            'signatures' => 'Team member signatures',
        ),
        'portfolio' => array(
            'gallery' => 'Portfolio gallery images',
            'thumbnails' => 'Portfolio thumbnails',
        ),
        'products' => array(
            'featured' => 'Featured product images',
            'gallery' => 'Product gallery images',
        ),
        'blog' => array(
            'featured' => 'Blog post featured images',
            'content' => 'Blog post content images',
        ),
    );
}

/**
 * Optionally pre-create folder structure on theme activation
 */
add_action('after_switch_theme', 'create_cmf_folder_structure');
function create_cmf_folder_structure() {
    if (!class_exists('CMF_Folder_Manager')) {
        return;
    }

    $folder_manager = new CMF_Folder_Manager();
    
    // Create recommended folder structure
    $structure = array(
        'blocks' => array(
            'hero' => array('backgrounds', 'mobile'),
            'testimonials' => array(),
            'cta' => array(),
        ),
        'team' => array('photos', 'signatures'),
        'portfolio' => array('gallery', 'thumbnails'),
        'products' => array('featured', 'gallery'),
        'blog' => array('featured', 'content'),
    );

    foreach ($structure as $parent_name => $children) {
        $parent = $folder_manager->get_or_create_from_path($parent_name);
        
        if (!is_wp_error($parent) && !empty($children)) {
            foreach ($children as $child_name => $grandchildren) {
                if (is_array($grandchildren)) {
                    // Has grandchildren
                    $child_path = $parent_name . '/' . $child_name;
                    $child = $folder_manager->get_or_create_from_path($child_path);
                    
                    if (!is_wp_error($child)) {
                        foreach ($grandchildren as $grandchild_name) {
                            $folder_manager->get_or_create_from_path($child_path . '/' . $grandchild_name);
                        }
                    }
                } else {
                    // No grandchildren (it's actually a child)
                    $folder_manager->get_or_create_from_path($parent_name . '/' . $grandchildren);
                }
            }
        }
    }
}
