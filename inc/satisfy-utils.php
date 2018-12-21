<?php defined( 'ABSPATH' ) or die;

/*
 * Theme util functions
 */

// If home or front page
function satisfy_is_home () {
    return is_home() || is_front_page();
}

// Paged info
function satisfy_get_paged ( $title ) {
    $title = satisfy_trim( $title );
    $paged = get_query_var( 'paged' );

    if ( is_int( $paged ) && $paged > 1 ) {
        $title = sprintf( '%s - %s %d', $title, __( 'page', 'satisfy' ), $paged );
    }
    return esc_html( $title );
}

// Returns string of allowed html tags
function satisfy_get_allowed_html_tags () {
    return implode( ', ', array_keys( wp_kses_allowed_html() ) );
}

// Wp_kses with br tags
function satisfy_wp_kses ( $str ) {
    return nl2br( wp_kses( $str, wp_kses_allowed_html() ) );
}

// Trims and removes double whitespaces
function satisfy_trim ( $str ) {
    return trim( preg_replace( '/\s+/', ' ', $str ) );
}

// Gets or sets a temporary option
function satisfy_temp_option ( $key, $val = null ) {
    static $options;

    if ( ! isset( $options ) ) {
        $options = array();
    }

    if ( null !== $val ) { // Set
        $options[ $key ] = $val;

    } else { // Get
        if ( ! isset( $options[ $key ] ) ) {
            $options[ $key ] = get_theme_mod( $key );
        }
        return $options[ $key ] ? $options[ $key ] : '';
    }
}

// If is a post or page (deprecated)
function satisfy_is_post () {
    return is_singular();
}

// If key in array is set and not empty (deprecated)
function satisfy_not_empty ( $key, &$arr ) {
    return ! empty( $arr[ $key ] );
}
