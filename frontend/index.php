<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

add_action( 'wp', 'ahr_handle_redirections', 0 );

add_filter( 'post_link', 'ahr_post_link', 10, 3 );
add_filter( 'term_link', 'ahr_term_link', 10, 3 );
add_filter( 'feed_link', 'ahr_feed_link', 10, 3 );
add_filter( 'get_archives_link', 'ahr_archive_link', 10, 3 );

/**
 * redirect from http to https if necessary
 * 
 * A callback function to the wp event
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_handle_redirections(){
	
	// Homepage or front page ( website root url )
	if ( is_home() || is_front_page() ) {
		ahr_redirect( get_option( 'ahr-redirect-homepage' ) );
		return;
	}
	
	// post including custom posts
	$post_id = url_to_postid( $_SERVER['REQUEST_URI'] );
	if ( $post_id !== 0 ) {
	
		$redirection_status = get_post_custom( $post_id )['ahr-redirect'][0];
		$redirection_status = ahr_filter_redirection_status( $redirection_status );
		ahr_redirect( $redirection_status );
		return;
	
	}	
	
	// terms including custom terms
	$term_id = get_queried_object_id();
	if ( $term_id !== 0 ) {
		
		$redirection_status = get_term_meta( $term_id , 'ahr-redirect', true );
		$redirection_status = ahr_filter_redirection_status( $redirection_status );
		ahr_redirect( $redirection_status );
		
	}
	
	// month archive pages and all other front-end pages
	else {
		
		$redirection_status = get_option( 'ahr-redirect-frontend-default' );
		ahr_redirect( $redirection_status );
		
	}
	
}

/**
 * if necessary, inherit the $redirection_status from the global settings
 *   
 * @param   string $redirection_status
 * @version 1.0
 * @return  string $redirection_status
 ***/
function ahr_filter_redirection_status( $redirection_status ) {
	
	global $AHR_REDIRECTION_STATUSES;
	
	// if the user did not set the redirection_status or the redirection_status is set to default
	if ( $redirection_status === 'default' || ! in_array( $redirection_status, $AHR_REDIRECTION_STATUSES ) ) {
		return get_option( 'ahr-redirect-frontend-default' );
	}
	
	return $redirection_status;
	
}

/**
 * Make sure that post url is based on it's ahr redirection status meta 
 *   
 * A callback function for the post_link filter
 * 
 * @param   string $url
 * @param   object $post
 * @version 1.0
 * @return  string $url
 ***/
function ahr_post_link( $url, $post ) {
	
	$post_id = $post->ID;
	
	$redirection_status = get_post_custom( $post->ID )['ahr-redirect'][0];
	
	$redirection_status = ahr_filter_redirection_status( $redirection_status );
	
	
	$url_components = parse_url( $url );

	if ( $url_components['scheme'] !== $redirection_status && $redirection_status !== 'none'){
	
		$url = ahr_str_replace_first( $url_components['scheme'] ,$redirection_status, $url );
		
	}
	
	return $url;
}


/**
 * Make sure that taxonomy url is based on it's ahr redirection status meta 
 *   
 * A callback function for the term_link filter
 * 
 * @param   string $url
 * @param   object $term
 * @version 1.0
 * @return  string $url
 ***/
function ahr_term_link( $url, $term ) {
	
	$term_id = $term->term_id;
	
	$redirection_status = get_term_meta( $term_id , 'ahr-redirect', true );
	
	$redirection_status = ahr_filter_redirection_status( $redirection_status );
	
	$url_components = parse_url( $url );

	if ( $url_components['scheme'] !== $redirection_status && $redirection_status !== 'none' ){
	
		$url = ahr_str_replace_first( $url_components['scheme'] ,$redirection_status, $url );
		
	}
	
	return $url;
}

/**
 * Make sure that archives $link_html is based on the global front-end redirection setting
 *   
 * A callback function for the get_archives_link filter
 * 
 * @param   string $link_html
 * @version 1.0
 * @return  string $link_html
 ***/
function ahr_archive_link( $link_html ){
	
	$redirection_status = get_option( 'ahr-redirect-frontend-default' );
	
	preg_match( "/href='(.*?)'/", $link_html, $match);
	
	if ( $match[1] !== null ) {
		
		$url_components = parse_url( $match[1] );
	
		if ( $url_components['scheme'] !== $redirection_status && $redirection_status !== 'none' ){
		
			$url = ahr_str_replace_first( $url_components['scheme'] ,$redirection_status, $match[1] );
	
			$link_html = ahr_str_replace_first ( $match[1], $url, $link_html  );
		}
		
		return $link_html;
		
	}
	
	return $link_html;
	
}

/**
 * Make sure that feeds url is based on the global front-end redirection setting
 *   
 * A callback function for the feed_link filter
 * 
 * @param   string $url
 * @version 1.0
 * @return  string $url
 ***/
function ahr_feed_link( $url ){
	
	$redirection_status = get_option( 'ahr-redirect-frontend-default' );
	
	$url_components = parse_url( $url );
	
	if ( $url_components['scheme'] !== $redirection_status && $redirection_status !== 'none' ){
		
		$url = ahr_str_replace_first( $url_components['scheme'] ,$redirection_status, $url );

	}
	
	return $url;
}

/**
 * Replace the first occurrence of $search with $replace in $subject 
 * 
 * @param   string $search
 * @param   string $replace
 * @param   string $subject
 * @version 1.0
 * @return  string $subject
 ***/
function ahr_str_replace_first( $search, $replace, $subject ) {

    $pos = strpos( $subject, $search );
	
    if ( $pos !== false ) 
        return substr_replace( $subject, $replace, $pos, strlen($search) );
    
    return $subject;
	
}