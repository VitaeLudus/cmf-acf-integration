# Custom Media Folders - ACF Pro Integration

Automatically organize ACF image uploads into custom folders based on field configuration. Perfect for Roots/Sage/Radicle workflows.

## Features

- **Automatic folder routing** - Images uploaded through ACF fields go directly to specified folders
- **Developer-controlled** - Set folder paths in field configuration, transparent to clients
- **Flexible paths** - Use existing folders or specify custom paths
- **Auto-create folders** - Optionally create folders automatically if they don't exist
- **Block-ready** - Works with ACF Blocks v2 (block.json)
- **Roots/Bedrock compatible** - Composer-ready for modern WordPress development

## Installation

### Standard WordPress
1. Install and activate Custom Media Folders plugin
2. Install and activate Advanced Custom Fields Pro
3. Install this integration plugin

### Bedrock/Composer
```bash
composer require custom-media-folders/acf-integration
```

Or add to your `composer.json`:
```json
{
    "require": {
        "custom-media-folders/acf-integration": "^1.0"
    }
}
```

## Usage

### Basic Usage - PHP Registration

When registering ACF fields in PHP, add the custom folder settings:

```php
acf_add_local_field_group(array(
    'fields' => array(
        array(
            'key' => 'field_hero_image',
            'label' => 'Hero Image',
            'name' => 'hero_image',
            'type' => 'image',
            
            // Custom Media Folders settings
            'cmf_custom_path' => 'blocks/hero/backgrounds',  // Where to store uploads
            'cmf_auto_create' => 1,                          // Auto-create if doesn't exist
        ),
    ),
    // ... rest of field group config
));
```

### Usage with ACF Composer (Sage)

```php
use StoutLogic\AcfBuilder\FieldsBuilder;

$hero = new FieldsBuilder('hero_block');
$hero
    ->addImage('background_image', [
        'label' => 'Background Image',
        'cmf_custom_path' => 'blocks/hero/backgrounds',
        'cmf_auto_create' => 1,
    ]);
```

### Available Settings

Each image/gallery/file field can have these settings:

| Setting | Type | Description |
|---------|------|-------------|
| `cmf_folder` | int | ID of existing folder from Media > Media Folders |
| `cmf_custom_path` | string | Custom path like 'blocks/hero' or 'team/photos' |
| `cmf_auto_create` | bool | Auto-create folder if it doesn't exist (default: true) |

### Folder Path Examples

```php
// Simple paths
'cmf_custom_path' => 'team/photos'           // → /uploads/team/photos/
'cmf_custom_path' => 'blog/featured'         // → /uploads/blog/featured/

// Nested paths
'cmf_custom_path' => 'blocks/hero/desktop'   // → /uploads/blocks/hero/desktop/
'cmf_custom_path' => 'products/gallery/2024' // → /uploads/products/gallery/2024/

// Using existing folder by ID
'cmf_folder' => 5  // Use folder with ID 5 from CMF admin
```

## Complete Example - Hero Block

### 1. Block Registration (block.json)
```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 2,
    "name": "acf/hero-section",
    "title": "Hero Section",
    "acf": {
        "mode": "preview",
        "renderTemplate": "blocks/hero/hero.php"
    }
}
```

### 2. ACF Fields with CMF Integration
```php
acf_add_local_field_group(array(
    'key' => 'group_hero',
    'fields' => array(
        array(
            'key' => 'field_desktop_bg',
            'name' => 'desktop_background',
            'type' => 'image',
            'cmf_custom_path' => 'heroes/desktop',
            'cmf_auto_create' => 1,
        ),
        array(
            'key' => 'field_mobile_bg',
            'name' => 'mobile_background', 
            'type' => 'image',
            'cmf_custom_path' => 'heroes/mobile',
            'cmf_auto_create' => 1,
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
));
```

### 3. Result
- Desktop images → `/uploads/heroes/desktop/`
- Mobile images → `/uploads/heroes/mobile/`
- Folders created automatically
- Client just uploads, doesn't see folder structure

## Recommended Folder Structure

For a typical Sage/ACF Blocks project:

```
uploads/
├── blocks/
│   ├── hero/
│   │   ├── backgrounds/
│   │   └── mobile/
│   ├── testimonials/
│   └── cta/
├── team/
│   ├── photos/
│   └── signatures/
├── portfolio/
│   ├── gallery/
│   └── thumbnails/
└── blog/
    ├── featured/
    └── content/
```

## Pre-create Folder Structure

Add to your theme's functions.php or a plugin:

```php
add_action('after_switch_theme', function() {
    if (!class_exists('CMF_Folder_Manager')) {
        return;
    }

    $manager = new CMF_Folder_Manager();
    
    // Create folder structure
    $folders = [
        'blocks/hero/backgrounds',
        'blocks/hero/mobile',
        'blocks/testimonials',
        'team/photos',
        'portfolio/gallery',
    ];
    
    foreach ($folders as $path) {
        $manager->get_or_create_from_path($path);
    }
});
```

## Working with Existing Folders

1. Go to **Media > Media Folders**
2. Create your folder structure
3. Note the folder IDs
4. Use in your field configuration:

```php
'cmf_folder' => 12,  // Use folder with ID 12
```

## Tips for Roots/Radicle Users

### Sage 10 Block Example

```php
// app/Blocks/HeroBlock.php
namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class HeroBlock extends Block
{
    public function fields()
    {
        $hero = new FieldsBuilder('hero_block');
        
        $hero->addImage('background_image', [
            'cmf_custom_path' => 'blocks/hero/backgrounds',
            'cmf_auto_create' => 1,
        ]);
        
        return $hero->build();
    }
}
```

### Bedrock Folder Structure

The plugin respects Bedrock's folder structure:
- Uploads go to: `web/app/uploads/[your-custom-folders]/`
- Works with Bedrock's modified `wp-content` structure

## Troubleshooting

### Images not going to specified folder
- Check that both Custom Media Folders and this integration are active
- Verify the folder path is valid (no leading/trailing slashes)
- Check PHP error logs for any issues

### Folders not being created
- Ensure `cmf_auto_create` is set to `1` or `true`
- Check write permissions on uploads directory
- Verify Custom Media Folders database tables exist

### Works in admin but not in frontend uploads
- This integration hooks into ACF's upload process
- For frontend forms, ensure ACF form is properly initialized

## Advanced Usage

### Conditional Folder Selection

```php
add_filter('acf/prepare_field/key=field_hero_image', function($field) {
    // Different folders based on site/context
    if (is_multisite()) {
        $blog_id = get_current_blog_id();
        $field['cmf_custom_path'] = "site-{$blog_id}/heroes";
    }
    return $field;
});
```

### Dynamic Folder Paths

```php
// Use current year in path
$current_year = date('Y');
$field['cmf_custom_path'] = "portfolio/{$current_year}";
```

## Support

- Check Custom Media Folders is working first
- Ensure ACF Pro is activated
- Enable WordPress debug mode for detailed errors
- Folder paths should not start or end with `/`

## License

GPL v2 or later