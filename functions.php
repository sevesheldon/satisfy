<?php defined( 'ABSPATH' ) or die;

/*
 * Functions
 */

define( 'SATISFY_THEME_VERSION', '1.1.0' );
define( 'SATISFY_THEME_DIR', get_template_directory() . '/' );
define( 'SATISFY_THEME_URL', get_template_directory_uri() . '/' );
define( 'SATISFY_THEME_ADMIN', 'satisfy-theme-info' );

require_once SATISFY_THEME_DIR . 'inc/satisfy-blog-loop.php';
require_once SATISFY_THEME_DIR . 'inc/satisfy-customize.php';
require_once SATISFY_THEME_DIR . 'inc/satisfy-utils.php';
require_once SATISFY_THEME_DIR . 'inc/satisfy-post-info.php';
require_once SATISFY_THEME_DIR . 'inc/widgets/satisfy-image-widget.php';
require_once SATISFY_THEME_DIR . 'inc/widgets/satisfy-latest-widget.php';


add_action( 'after_setup_theme', 'satisfy_add_support_to_theme' );
add_action( 'widgets_init', 'satisfy_add_widgets_to_theme' );
add_action( 'wp_enqueue_scripts', 'satisfy_load_early_resources', 1 );
add_action( 'wp_enqueue_scripts', 'satisfy_load_theme_resources' );
add_action( 'customize_register', array( 'Satisfy_customize', 'init' ) );
add_action( 'wp_enqueue_scripts', array( 'Satisfy_customize', 'display_styles' ) );
add_action( 'admin_menu', 'satisfy_admin_init' );

add_action( 'satisfy_print_post_info', array( 'Satisfy_post_info', 'print_post_info' ) );
add_action( 'satisfy_print_categories_and_tags', array( 'Satisfy_post_info', 'print_categories_and_tags' ) );

add_filter( 'post_class', 'satisfy_post_class' );
add_filter( 'excerpt_more', 'satisfy_excerpt_more' );

add_action( 'woocommerce_before_main_content', 'satisfy_woo_wrapper_start' );
add_action( 'woocommerce_after_main_content', 'satisfy_woo_wrapper_end' );


// Registers nav menu and most theme support
function satisfy_add_support_to_theme () {
    load_theme_textdomain( 'satisfy', SATISFY_THEME_DIR . 'languages' );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'satisfy' ),
        'footer'  => __( 'Footer Menu', 'satisfy' )
    ) );

    add_theme_support( 'post-thumbnails' );
    add_image_size( 'satisfy-medium', 720, 445, true );

    add_theme_support( 'html5', array(
        'comment-list',
        'gallery',
        'caption'
    ) );

    add_theme_support( 'custom-background', array(
        'default-color' => '#fcfcfc'
    ) );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'woocommerce' );

    add_theme_support( 'infinite-scroll', array(
        'type'      => 'scroll',
        'container' => 'primary-content',
        'wrapper'   => 'infinite-satisfy',
        'render'    => 'satisfy_blog_loop'
    ) );

    add_theme_support( 'custom-logo', array(
        'height'      => 55,
        'width'       => 55,
        'flex-height' => true,
        'flex-width'  => true
    ) );

    add_theme_support( 'custom-header', array(
        'default-text-color' => 'fcfcfc',
        'flex-height'        => true,
        'flex-width'         => true,
        'width'              => 1920,
        'height'             => 1200,
        'uploads'            => true
    ) );
}

// Loads bootstrap a bit earlier (for child theme dependency etc)
function satisfy_load_early_resources () {
    wp_enqueue_style( 'bootstrap', SATISFY_THEME_URL . 'css/bootstrap/bootstrap.min.css', array(), SATISFY_THEME_VERSION );
}

// Loads styles and scripts
function satisfy_load_theme_resources () {
    wp_enqueue_style( 'satisfy-theme-style', get_stylesheet_uri(), array( 'bootstrap' ), SATISFY_THEME_VERSION );
    wp_enqueue_style( 'font-awesome', SATISFY_THEME_URL . 'css/font-awesome/css/font-awesome.min.css', array(), SATISFY_THEME_VERSION );

    $styles = get_theme_mod( 'satisfy', array() );

    // Only load google fonts if any of them is used.. TODO: improve when more google fonts will be added
    if ( ! isset( $styles['body_font_family'], $styles['headings_font_family'] ) || preg_grep( '/(Open|Roboto)/', array( $styles['body_font_family'], $styles['headings_font_family'] ) ) ) {
        wp_enqueue_style( 'satisfy-theme-google-fonts', '//fonts.googleapis.com/css?family=Open+Sans%7CRoboto+Slab', array(), null );
    }

    if ( is_singular() ) {
        wp_enqueue_script( 'comment-reply' );
    }

    wp_enqueue_script( 'satisfy-theme-script', SATISFY_THEME_URL . 'js/satisfy-theme-script.js', array( 'jquery' ), SATISFY_THEME_VERSION, true );
}

// Register sidebars and widgets
function satisfy_add_widgets_to_theme () {
    $sidebars = array(
        'sidebar-1' => __( 'Sidebar', 'satisfy' ),
        'footer-1'  => __( 'Footer 1 (left)', 'satisfy' ),
        'footer-2'  => __( 'Footer 2 (middle)', 'satisfy' ),
        'footer-3'  => __( 'Footer 3 (right)', 'satisfy' ),
        'home-1'    => __( 'Home Page', 'satisfy' )
    );

    foreach ( $sidebars as $id => $name ) {
        register_sidebar( array(
            'id'            => $id,
            'name'          => $name,
            'before_widget' => '<div class="widget-div">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>'
        ) );
    }

    register_widget( 'satisfy_image_widget' );
    register_widget( 'satisfy_latest_posts_widget' );
}

// Adds read more buttons - filter "excerpt_more"
function satisfy_excerpt_more ( $more ) {
    return sprintf(
        '..<p><a class="btn btn-default read-more" href="%s">%s <span class="fa fa-angle-right"></span></a></p>',
        esc_url( get_permalink( get_the_ID() ) ),
        __( 'Read more', 'satisfy' )
    );
}

// Removes hentry class if already set - filter "post_class"
function satisfy_post_class ( $classes ) {
    if ( is_singular() && satisfy_temp_option( 'has_banner' ) ) {
        $classes = array_diff( $classes, array( 'hentry' ) );
    }
    return $classes;
}

// Content class, 12 cols if no sidebar
if ( ! function_exists( 'satisfy_get_content_class' ) ) {
    function satisfy_get_content_class () {
        $sidebar = satisfy_temp_option( 'sidebar' );

        if ( false !== strpos( $sidebar, 'off' ) ) {
            return 'col-xs-12 ' . ('off-small' === $sidebar ? 'col-md-8 center-div' : 'satisfy-no-sidebar');
        }
        return 'col-md-8 col-sm-12';
    }
}

// Prints logo in header
if ( ! function_exists( 'satisfy_print_logo' ) ) {
    function satisfy_print_logo () {
        if ( function_exists( 'the_custom_logo' ) ) {
            the_custom_logo();
        } elseif ( $id = get_theme_mod( 'custom_logo' ) ) { ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( wp_get_attachment_url( $id ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="custom-logo">
            </a>
        <?php
        }

        if ( display_header_text() ) {
            $show_slogan = get_theme_mod( 'satisfy_show_menu_slogan' ); ?>

            <a id="site-title-wrap" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <div class="vertical-center<?php echo $show_slogan ? ' site-title-slogan' : ''; ?>">
                    <span class="site-title"><?php bloginfo( 'name' ); ?></span>
                    <?php if ( $show_slogan ) { ?>
                        <span class="site-slogan"><?php bloginfo( 'description' ); ?></span>
                    <?php } ?>
                </div>
            </a>
        <?php
        }
    }
}

// Prepares header banner and prints css classes for it
if ( ! function_exists( 'satisfy_prepare_banner' ) ) {
    function satisfy_prepare_banner () {
        $cl_name = '';
        $banner = array(
            'url' => false,
            'h1' => '',
            'slogan' => '',
            'is_page' => false
        );

        if ( is_404() ) {
            return;
        }

        if ( is_front_page() ) {
            $banner['url'] = get_custom_header()->url;
            $banner['slogan'] = get_theme_mod( 'satisfy_new_slogan' );
            $banner['h1'] = get_theme_mod( 'satisfy_tagline' );

        } elseif ( ( is_singular() || is_home() ) && 'full' === get_theme_mod( 'posts_featured_images' ) ) {
            $id = is_home() ? get_option( 'page_for_posts' ) : get_the_ID();

            if ( has_post_thumbnail( $id ) ) {
                $img_arr = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'full' );
                if ( $img_arr ) {
                    $banner['url'] = $img_arr[0];
                    $banner['h1'] = get_the_title( $id );
                    $banner['is_page'] = true;
                    $cl_name = 'hentry';
                }
            }
        }

        if ( $banner['url'] ) {
            satisfy_temp_option( 'has_banner', $banner );
            $cl_name .= ' satisfy-banner';
        }

        echo trim( $cl_name );
    }
}

// Prints header banner
if ( ! function_exists( 'satisfy_print_banner' ) ) {
    function satisfy_print_banner () {
        $banner = satisfy_temp_option( 'has_banner' );

        if ( ! is_array( $banner ) ) {
            return;
        }

        $styles = array_merge(
            array(
                'arrow'     => true,
                'size'      => 60,
                'page_size' => 60,
                'shadow'    => true
            ),
            get_theme_mod( 'satisfy_banner', array() )
        );
        $size = $banner['is_page'] ? $styles['page_size'] : $styles['size']; ?>

        <div class="cover-img" style="min-height:<?php echo absint( $size ); ?>vh;background-image:url(<?php echo esc_url( $banner['url'] ); ?>);<?php
            if ( $styles['shadow'] ) {
                echo 'text-shadow:1px 1px 3px rgba(0,0,0,0.9);';
            } ?>">
            <div class="vertical-table" style="height:<?php echo absint( $size ); ?>vh">
                <div class="vertical-center">
                    <div class="container-fluid">
                        <div class="content-wrapper">
                            <div class="col-xs-12">
                                <h1 class="entry-title"><?php echo satisfy_get_paged( $banner['h1'] ); ?></h1>
                                <p><?php echo satisfy_wp_kses( $banner['slogan'] ); ?></p>
                            </div>
                        </div>
                        <?php if ( $styles['arrow'] ) { ?>
                            <div class="hero-arrow"><span class="fa fa-chevron-down"></span></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php }
}

// Get featured image size for current page
if ( ! function_exists( 'satisfy_get_featured_image' ) ) {
    function satisfy_get_featured_image () {
        if ( is_singular() ) {
            if ( ! get_theme_mod( 'posts_featured_images' ) ) {
                return 'full';
            }
        } else {
            $img = get_theme_mod( 'featured_images' );

            if ( 'off' !== $img ) {
                return $img ? $img : 'satisfy-medium';
            }
        }
        return false;
    }
}

// Prints icon for posts
if ( ! function_exists( 'satisfy_post_icon' ) ) {
    function satisfy_post_icon () {
        echo 'fa fa-thumb-tack';
    }
}

// If post info for posts or pages should be displayed
if ( ! function_exists( 'satisfy_disp_post_info' ) ) {
    function satisfy_disp_post_info () {
        if ( ! get_theme_mod( 'post_info', true ) || ( is_page() && ! get_theme_mod( 'post_info_pages' ) ) ) {
            echo ' -satisfy-hidden';
        }
    }
}

// Html in footer top if no widgets are added there
if ( ! function_exists( 'satisfy_footer_top_info' ) ) {
    function satisfy_footer_top_info () {
        $text = get_theme_mod( 'footer_text' );

        if ( $text ) { ?>
            <div class="text-center"><p><?php echo satisfy_wp_kses( $text ); ?></p></div>
        <?php } else { ?>
            <div class="text-center">
                <p class="footer-info"><?php bloginfo( 'name' ); ?> &copy; <?php echo date_i18n( __( 'Y', 'satisfy' ) ); ?></p>
                <nav class="footer-nav">
                    <?php wp_nav_menu( array(
                        'theme_location' => 'footer'
                    ) ); ?>
                </nav>
            </div>
        <?php
        }
    }
}

// Title text for searches
if ( ! function_exists( 'satisfy_get_search_title' ) ) {
    function satisfy_get_search_title () {
        $search_title = satisfy_get_paged( get_search_query( false ) );

        if ( $search_title ) {
            return sprintf( '%s: %s', __( 'Search results for', 'satisfy' ), $search_title );
        }
        return sprintf( '%s %s', __( 'Search', 'satisfy' ), esc_html( get_bloginfo( 'name' ) ) );
    }
}

// Admin notice first time activating
function satisfy_welcome_message () {
    if ( ! get_theme_mod( 'been_welcomed' ) ) { ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e( 'Welcome to Satisfy! Info about the theme can be found at your', 'satisfy' ); ?>
                <a href="<?php echo esc_url( admin_url( 'themes.php?page=' . SATISFY_THEME_ADMIN ) ); ?>"><?php _e( 'theme info page', 'satisfy' ); ?></a>.
            </p>
        </div>
        <?php set_theme_mod( 'been_welcomed', true );
    }
}

// Footer bottom credit text
function satisfy_footer_bottom_info () {
    if ( apply_filters( 'satisfy_footer_info', true ) ) {
        printf( '<p>%s</p>', __( 'Theme Satisfy', 'satisfy' ) );
    }
}

// Set up for admin interface
function satisfy_admin_init () {
    $title = __( 'Satisfy Info', 'satisfy' );

    require_once SATISFY_THEME_DIR . 'inc/admin/satisfy-admin.php';

    add_theme_page( $title, $title, 'edit_theme_options', SATISFY_THEME_ADMIN, array( 'Satisfy_admin', 'settings_page' ) );
    add_action( 'admin_enqueue_scripts', array( 'Satisfy_admin', 'scripts_and_styles' ) );
    add_action( 'admin_notices', 'satisfy_welcome_message' );
}

// Woocommerce
function satisfy_woo_wrapper_start () {
    if ( 'left' === satisfy_temp_option( 'sidebar' ) ) {
        get_sidebar();
        satisfy_temp_option( 'sidebar', 'off' );
    }
    printf( '<div id="primary-content" class="%s satisfy-woo-commerce"><div id="main">', satisfy_get_content_class() );
}
function satisfy_woo_wrapper_end () {
    echo '</div></div>';
}
