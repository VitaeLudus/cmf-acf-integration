<?php
/**
 * Hero Block Template
 * 
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 * @param array $context The context provided to the block by the post or it's parent block.
 */

// Get field values
$background_image = get_field('background_image');
$mobile_background = get_field('mobile_background_image');
$title = get_field('title');
$subtitle = get_field('subtitle');
$cta_button = get_field('cta_button');

// Get block anchor and classes
$anchor = !empty($block['anchor']) ? 'id="' . esc_attr($block['anchor']) . '"' : '';
$className = 'hero-section';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Preview mode
if ($is_preview && empty($background_image)) {
    $background_image = array(
        'url' => 'https://via.placeholder.com/1920x800/2271b1/ffffff?text=Hero+Background',
        'alt' => 'Hero placeholder'
    );
}
?>

<section <?php echo $anchor; ?> class="<?php echo esc_attr($className); ?>">
    <?php if ($background_image) : ?>
        <div class="hero-section__background">
            <picture>
                <?php if ($mobile_background) : ?>
                    <source 
                        media="(max-width: 768px)" 
                        srcset="<?php echo esc_url($mobile_background['url']); ?>"
                    >
                <?php endif; ?>
                <img 
                    src="<?php echo esc_url($background_image['url']); ?>" 
                    alt="<?php echo esc_attr($background_image['alt']); ?>"
                    class="hero-section__image"
                >
            </picture>
        </div>
    <?php endif; ?>
    
    <div class="hero-section__content">
        <div class="container">
            <?php if ($title) : ?>
                <h1 class="hero-section__title"><?php echo esc_html($title); ?></h1>
            <?php endif; ?>
            
            <?php if ($subtitle) : ?>
                <div class="hero-section__subtitle"><?php echo wp_kses_post($subtitle); ?></div>
            <?php endif; ?>
            
            <?php if ($cta_button) : ?>
                <a 
                    href="<?php echo esc_url($cta_button['url']); ?>" 
                    class="hero-section__cta btn btn-primary"
                    <?php echo $cta_button['target'] ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>
                >
                    <?php echo esc_html($cta_button['title']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.hero-section {
    position: relative;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.hero-section__background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
}

.hero-section__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-section__content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 60px 20px;
}

.hero-section__title {
    font-size: 48px;
    margin-bottom: 20px;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-section__subtitle {
    font-size: 20px;
    margin-bottom: 30px;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.hero-section__cta {
    display: inline-block;
    padding: 12px 30px;
    background: #2271b1;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
}

.hero-section__cta:hover {
    background: #135e96;
}

/* Alignment variations */
.hero-section.alignfull {
    width: 100vw;
    max-width: 100vw;
    margin-left: calc(50% - 50vw);
}

.hero-section.alignwide {
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

/* Backend preview styles */
.block-editor-block-preview__content .hero-section {
    min-height: 300px;
}

@media (max-width: 768px) {
    .hero-section__title {
        font-size: 32px;
    }
    
    .hero-section__subtitle {
        font-size: 16px;
    }
}
</style>

<?php
// Note about uploaded images:
// When clients upload images through this block, they will automatically be organized:
// - Desktop backgrounds → /uploads/blocks/hero/backgrounds/
// - Mobile backgrounds → /uploads/blocks/hero/mobile/
// This happens transparently based on the field configuration.
?>