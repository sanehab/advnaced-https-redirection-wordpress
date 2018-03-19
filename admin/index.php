<?php

if ( !defined( 'ABSPATH' ) ) {
    return;
}

add_action( 'init', 'ahr_add_tax_meta_box' );
add_action( 'init', 'ahr_handle_redirections' );
add_action( 'add_meta_boxes', 'ahr_add_meta_box' );
add_action( 'save_post', 'ahr_save_meta_box' );
add_action( 'wp_ajax_backup_htaccess', 'ahr_ajax_backup_htaccess' );
add_action( 'admin_menu', 'ahr_menu' );

add_filter( 'pre_update_option_ahr-redirect-static-resources', 'ahr_static_res_redirection_change', 10, 2 );
add_filter( "plugin_action_links_$AHR_SLUG/$AHR_SLUG.php", 'ahr_add_settings_link' );


// a random number that will be used to anchor the written text in .htaccess
$random_anchor_number = 3417031;

/**
 * Add the options page to the settings menu
 * 
 * A callback function to the admin_menu event 
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_menu() {
    
	global $AHR_SLUG;
	
    add_options_page( 'Advanced Https Redirection', 'Advanced Https Redirection', 'manage_options', $AHR_SLUG, 'ahr_render_options_page' );
    
}

/**
 * Append a settings link to the plugin actions links array
 * 
 * A callback function to the plugin_action_links filter event 
 *  
 * @param array $links
 * @version 1.0
 * @return  void
 ***/
function ahr_add_settings_link( $links ) {
    
	global $AHR_SLUG;
	
    $settings_link = "<a href='options-general.php?page=$AHR_SLUG'>" . __( 'Settings' ) . '</a>';
    array_unshift( $links, $settings_link );
    
    return $links;
    
}

/**
 * Render the options page
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_render_options_page() {
    
    global $random_anchor_number;
    
    // generate a random number for the first time, and use it later for anchoring text
    $random_anchor_number = get_option( 'ahr-random-anchor-number' );
    
    if ( $random_anchor_number )
        $random_anchor_number = (int) $random_anchor_number;
    
    else {
        
        $random_anchor_number = mt_rand( 9999, 999999999 );
        update_option( 'ahr-random-anchor-number', $random_anchor_number );
    }
    
    echo "<script>var ahrAnchorNumber = $random_anchor_number;</script>";
    
    $is_htaccess_backed = get_option( 'ahr-htaccess-backed' );
    
    if ( $is_htaccess_backed )
        echo '<script>var ahrHtaccessBacked = true;</script>';
    else
        echo '<script>var ahrHtaccessBacked = false;</script>';
    
    // this option value will be 1 if previous attempt to write to htaccess failed
    $prev_writing_htaccess = get_option( 'ahr-htaccess-writing-failed' );
    
    if ( $prev_writing_htaccess === 1 )
        update_option( 'ahr-htaccess-writing-failed', 0 );
    
    $data = array(
        'question-mark-url' => plugin_dir_url( __file__ ) . '/images/question-mark.png',
        'prev-writing-htaccess' => $prev_writing_htaccess 
    );
    
    ahr_render_tpl( '/templates/options-page.tpl', $data );
    
    wp_enqueue_script( 'options-page-js', plugin_dir_url( __file__ ) . '/scripts/options-page.js' );
    wp_enqueue_style( 'options-page-css', plugin_dir_url( __file__ ) . '/css/options-page.css' );
    
}

/**
 * Add the meta box in all post pages
 * 
 * A callback function to the add_meta_boxes event 
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_add_meta_box() {
    
    add_meta_box( 'ahr', 'Advaned https redirect', 'ahr_render_meta_box', null, 'normal', 'high', null );
    
}

/**
 * Render the meta box
 * 
 * Echo the content of the meta box
 * 
 * @param   WP_Post object $post
 * @version 1.0
 * @return  void
 ***/
function ahr_render_meta_box( $post ) {
    
	global $AHR_SLUG;
	
    wp_nonce_field( 'ahr-meta-box-nonce-save', 'ahr-meta-box-nonce' );
    
	$data = array(
		'ahr-slug' => $AHR_SLUG
	);
	
    ahr_render_tpl( '/templates/meta-box.tpl', $data );
    
}

/**
 * Update posts meta fields 
 * 
 * A callback function to the save_post event 
 *  
 * @param   int $post_id
 * @version 1.0
 * @return  void
 ***/
function ahr_save_meta_box( $post_id ) {
    
    global $AHR_REDIRECTION_STATUSES;
    
    // Bail if we're doing an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
    
    // Bail if we can't verify the nonce
    if ( !isset( $_POST[ 'ahr-meta-box-nonce' ] ) || !wp_verify_nonce( $_POST[ 'ahr-meta-box-nonce' ], 'ahr-meta-box-nonce-save' ) )
        return;
    
    // If our current user can't edit this post, bail
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
    
    // Ensure we only write allowed values into DB
    if ( in_array( $_POST[ 'ahr-redirect' ], $AHR_REDIRECTION_STATUSES ) )
        update_post_meta( $post_id, 'ahr-redirect', $_POST[ 'ahr-redirect' ] );
    
}

/**
 * Add the meta box in all taxonomy pages
 * 
 * A callback function to the init event 
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_add_tax_meta_box() {
    
    $taxonomies = get_taxonomies();
    $slugs      = array();
    
    // Add only taxonomies that have pages attached to them
    foreach ( $taxonomies as $tax ) {
        if ( 'nav_menu' !== $tax && 'post_format' !== $tax && 'link_category' !== $tax && 'wpforms_log_type' !== $tax ) {
            array_push( $slugs, $tax );
        }
    }
    
    foreach ( $slugs as $slug ) {
        add_action( "{$slug}_edit_form_fields", 'ahr_tax_edit_form' );
        add_action( "edited_{$slug}", 'ahr_tax_edited_form' );
        
        add_action( "{$slug}_add_form_fields", 'ahr_tax_add_form' );
        add_action( "create_{$slug}", 'ahr_tax_edited_form' );
    }
    
}


/**
 * Render the meta box in all edit taxonomy pages
 * 
 * A callback function to the {$slug}_edit_form_fields event 
 *
 * @param   WP_Term object $term
 * @version 1.0
 * @return  void
 ***/
function ahr_tax_edit_form( $term ) {
    
    wp_nonce_field( 'ahr-meta-box-nonce-save', 'ahr-meta-box-nonce' );
    
    $data = array(
         'term_id' => $term->term_id 
    );
    
    ahr_render_tpl( '/templates/meta-box-edit-taxonomy.tpl', $data );
    
}

/**
 * Handle saving in all edit taxonomy pages and handle saving for newly created taxonomies
 * 
 * A callback function to the edited_{$slug} action 
 *  
 * @param   int $term_id
 * @version 1.0
 * @return  void
 ***/
function ahr_tax_edited_form( $term_id ) {
    
    global $AHR_REDIRECTION_STATUSES;
    
    // Bail if we can't verify the nonce
    if ( !isset( $_POST[ 'ahr-meta-box-nonce' ] ) || !wp_verify_nonce( $_POST[ 'ahr-meta-box-nonce' ], 'ahr-meta-box-nonce-save' ) )
        return;
    
    // Ensure we only write allowed values into DB
    if ( in_array( $_POST[ 'ahr-redirect' ], $AHR_REDIRECTION_STATUSES ) )
        update_term_meta( $term_id, 'ahr-redirect', $_POST[ 'ahr-redirect' ] );
    
}

/**
 * Render the meta box in the add term page
 * 
 * A callback function to the {$slug}_add_form_fields, create_{$slug} actions
 *  
 * @param   string $term term name
 * @version 1.0
 * @return  void
 ***/
function ahr_tax_add_form( $term ) {
    
    wp_enqueue_script( 'ahr-add-tax', plugin_dir_url( __file__ ) . '/scripts/add-tax.js' );
    
    // Bail if we can't verify the nonce
    wp_nonce_field( 'ahr-meta-box-nonce-save', 'ahr-meta-box-nonce' );
    
    ahr_render_tpl( '/templates/meta-box-add-taxonomy.tpl' );
    
}

/**
 * Render a template for html select element 
 *
 * @param   associative array $data
 * @version 1.0
 * @return  void
 ***/
function ahr_render_select_redirection( $data ) {
    
    ahr_render_tpl( '/templates/select-redirection-type.tpl', $data );
    
}



/**
 * Redirect from http to https if necessary
 * 
 * A callback function to the init event
 *  
 * @version 1.0
 * @return  void
 ***/
function ahr_handle_redirections() {
    
    ahr_redirect( get_option( 'ahr-redirect-admin-default' ) );
    
}

/**
 * Check if htaccess both exist and writeable if not show a note
 * 
 * @version 1.0
 * @return  void
 ***/
function ahr_handle_htaccess_errors() {
    
    $root_dir               = get_home_path();
    $htaccess_filename_path = $root_dir . '.htaccess';
    
    $is_htaccess = file_exists( $htaccess_filename_path );
    
    if ( !$is_htaccess ) {
        echo '<p>There is no htaccess file in your root directory, it could be that you are not using Apache as a server, or that your server does not use htaccess files.</p>
		<p>If you are using Apache, you can try to create a new htaccess file, then try changing static resources redirection and see if it works.</p>';
        return;
    }
    
    $is_htaccess_writable = is_writable( $htaccess_filename_path );
    
    if ( !$is_htaccess_writable )
        echo '<p>Htaccess is not writable check your permissions.</p>';
    
}

/**
 * Handle the change of the static resources redirection option
 * Write to the htaccess file and remove previously written rules 
 *
 * A callback function for the pre_update_option filter
 *
 * @param string $new_val
 * @param string $old_val
 * @version 1.0
 * @return  string $new_val
 ***/
function ahr_static_res_redirection_change( $new_val, $old_val ) {
    
    
    if ( $new_val === 'https' || $new_val === 'http' ) {
        
        // remove previously written rules
        ahr_remove_rules_htaccess();
        
        // add new rules
        $redirection_type = $_POST[ 'ahr-redirection-type' ];
        $redirection_type = ahr_filter_redirection_type( $redirection_type );
        $write_result     = ahr_write_htaccess( $new_val, $redirection_type );
        
        // if writing failed we will notify the user by saving this option
        if ( !$write_result ) {
            update_option( 'ahr-htaccess-writing-failed', 1 );
            return 'none';
        }
    }
    
    if ( $new_val === 'none' )
        ahr_remove_rules_htaccess();
    
    return $new_val;
}

/**
 * Remove previously written rules from htaccess file - if they exist
 * 
 * @version 1.0
 * @return  void
 ***/
function ahr_remove_rules_htaccess() {
    
    $root_dir     = get_home_path();
    $file         = $root_dir . '.htaccess';
    $file_content = file_get_contents( $file );
    
    $random_anchor_number = get_option( 'ahr-random-anchor-number' );
    $start_string         = "# Begin Advanced https redirection $random_anchor_number";
    $end_string           = "# End Advanced https redirection $random_anchor_number";
    $regex_pattern        = "/$start_string(.*?)$end_string/s";
    
    preg_match( $regex_pattern, $file_content, $match );
    
    if ( $match[ 1 ] !== null ) {
        
        $file_content = str_replace( $match[ 1 ], '', $file_content );
        $file_content = str_replace( $start_string, '', $file_content );
        $file_content = str_replace( $end_string, '', $file_content );
        
    }
    
    $file_content = rtrim( $file_content );
    file_put_contents( $file, $file_content );
    
}

/**
 * Write the redirection rules to the htaccess file
 * 
 * Return true in case of successful writing, otherwise false
 *
 * @param   string $redirection_status - possible values ['https', 'http', 'none']
 * @param   string $redirection_type - possible values ['301', '302', '303', '307']
 * @version 1.0
 * @return  boolean
 ***/
function ahr_write_htaccess( $redirection_status, $redirection_type ) {
    
    $random_anchor_number = get_option( 'ahr-random-anchor-number' );
    
    $https_status = $redirection_status === 'https' ? 'off' : 'on';
    
    $written_rules = "\n\n" . "# Begin Advanced https redirection " . $random_anchor_number . "\n" . "<IfModule mod_rewrite.c> \n" . "RewriteEngine On \n" . "RewriteCond %{HTTPS} $https_status \n" . "RewriteCond %{REQUEST_FILENAME} -f \n" . "RewriteCond %{REQUEST_FILENAME} !\.php$ \n" . "RewriteRule .* $redirection_status://%{HTTP_HOST}%{REQUEST_URI} [L,R=$redirection_type] \n" . "</IfModule> \n" . "# End Advanced https redirection " . $random_anchor_number . "\n";
    
    
    $root_dir               = get_home_path();
    $htaccess_filename_path = $root_dir . '.htaccess';
    
    
    $write_result = file_put_contents( $htaccess_filename_path, $written_rules . PHP_EOL, FILE_APPEND | LOCK_EX );
    
    if ( $write_result ) {
        return true;
    }
    
    return false;
    
}

/**
 * Copy the .htaccess file to the htaccess-backup directory
 * 
 * At the moment the function only allow for the first time backup
 *
 * @version 1.0
 * @return  void
 ***/
function ahr_ajax_backup_htaccess() {
    
    // we only want to try to backup once
    update_option( 'ahr-htaccess-backed', true );
    
    $root_dir               = get_home_path();
    $htaccess_filename_path = $root_dir . '.htaccess';
    
    $plugin_path = plugin_dir_path( __DIR__ );
    $dest_path   = $plugin_path . 'htaccess-backup/' . '.htaccess';
    
    if ( file_exists( $dest_path ) ) {
        echo 'true';
        die();
    }
    
    if ( copy( $htaccess_filename_path, $dest_path ) ) {
        echo 'true';
        die();
    }
    
    echo 'false';
    die();
    
}

/**
 * Render a tpl file
 *   
 * @param   string $path tpl file path
 * @param   associative array $data
 * @version 1.0
 * @return  void
 ***/
function ahr_render_tpl( $path, $data = array() ) {
    
    ob_start();
    include( $path );
    $output = ob_get_clean();
    echo $output;
    
}





