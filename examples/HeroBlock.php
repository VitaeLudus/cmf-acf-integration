<?php
/**
 * Example ACF Block Registration with Custom Media Folders
 * Place in your Sage theme's app/Blocks/ directory or wherever you register blocks
 */

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class HeroBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Hero Block';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A hero section with background image.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'formatting';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'cover-image';

    /**
     * Data to be passed to the block before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'background_image' => get_field('background_image'),
            'title' => get_field('title'),
            'subtitle' => get_field('subtitle'),
            'cta_button' => get_field('cta_button'),
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $hero = new FieldsBuilder('hero_block');

        $hero
            ->addImage('background_image', [
                'label' => 'Background Image',
                'instructions' => 'Upload the hero background image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                // Custom Media Folders Integration
                'cmf_folder' => '', // Use folder ID from CMF admin
                'cmf_custom_path' => 'blocks/hero/backgrounds', // Or specify custom path
                'cmf_auto_create' => 1, // Auto-create if doesn't exist
            ])
            ->addText('title', [
                'label' => 'Title',
                'instructions' => 'Enter the hero title',
            ])
            ->addTextarea('subtitle', [
                'label' => 'Subtitle',
                'instructions' => 'Enter the hero subtitle',
                'rows' => 3,
            ])
            ->addLink('cta_button', [
                'label' => 'CTA Button',
                'instructions' => 'Add a call-to-action button',
            ]);

        return $hero->build();
    }
}
