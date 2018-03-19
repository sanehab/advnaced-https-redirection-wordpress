<div>
	<label for="ahr-redirect" style="margin-bottom:-5px;">Redirection type:</label>
	<?php ahr_render_select_redirection (
		array(
			'name' => 'ahr-redirect',
			'id'   => 'ahr-redirect',
			'value' => get_post_custom($post->ID)['ahr-redirect'][0],
			'contains_default' => true
		) 
	); 
	
	$settings_page_url = admin_url( 'options-general.php?page=' . $data['ahr-slug'] );

	?>
	<p style="margin-top: 15px;">Redirect according to default settings means to redirect based on how the
	default front-end redirection is set 
	in the plugin's <a href="<?php echo $settings_page_url;  ?>">settings page</a>.</p>
</div>