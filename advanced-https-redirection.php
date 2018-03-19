<?php
/***
 ** Plugin Name: Advanced Https Redirection
 ** Author: Ehab Alsharif
 ** Description: Completely redirect your website from http to https or redirect certain pages, posts, or category pages ( works with custom post types and custom taxonomies )
 ** Version: 1.0
 ** License: GNU GPL v2 or later
 ***/

if ( !defined( 'ABSPATH' ) ) {
    return;
}

$AHR_REDIRECTION_STATUSES = array(
    'default',
    'none',
    'https',
    'http' 
);

$AHR_REDIRECTION_TYPES = array(
    '301',
    '302',
    '303',
    '307' 
);

$AHR_SLUG = 'advanced-https-redirection';

if ( is_admin() )
    include_once( 'admin/index.php' );

else
    include_once( 'frontend/index.php' );


/**
 * Redirect according to the $redirection_status and $redirection_type
 *   
 * @param   string $redirection_status - possible values ['https', 'http', 'none']
 * @param   string optional $redirection_type - possible values ['301', '302', '303', '307']
 * @version 1.0
 * @return  void 
 ***/
function ahr_redirect( $redirection_status, $redirection_type = null ) {
    
	// get global redirection_type if $redirection_type is not set
    if ( $redirection_type === null )
        $redirection_type = get_option( 'ahr-redirection-type' );
    
    $redirection_type = ahr_filter_redirection_type( $redirection_type );
    
    switch ( $redirection_status ) {
        // if we should redirect to https and request is http
        case 'https':
            if ( !ahr_is_secure() ) {
                
                header( "Location: https://" . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ], true, $redirection_type );
                exit();
            }
            break;
			
        // if we should redirect to http and request is https
        case 'http':
            if ( ahr_is_secure() ) {
                
                header( "Location: http://" . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ], true, $redirection_type );
                exit();
            }
            break;
			
        // in case of 'none' we do nothing so no point in adding that case
            
    }
    
}

/**
 * if $redirection_type is not valid set it to '302'
 *   
 * @param   mixed $redirection_type 
 * @version 1.0
 * @return  string $redirection_type
 ***/
function ahr_filter_redirection_type( $redirection_type ) {
    
    global $AHR_REDIRECTION_TYPES;
    
    if ( !in_array( $redirection_type, $AHR_REDIRECTION_TYPES ) )
        $redirection_type = '302';
    
    return $redirection_type;
    
}

/**
 * returns true if the current request is through https otherwise false
 *   
 * @version 1.0
 * @return  boolean $is_secure
 ***/
function ahr_is_secure() {
    
    $is_secure = false;
	
    if ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ) {
        $is_secure = true;
    } elseif ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' || !empty( $_SERVER[ 'HTTP_X_FORWARDED_SSL' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_SSL' ] === 'on' ) {
        $is_secure = true;
    }
    
    return $is_secure;
    
}




