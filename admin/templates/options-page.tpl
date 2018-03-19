<div class="wrap">
	<h2>Advanced Https Redirection</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
		<table class="form-table">
			<tbody>
				<?php if ( $prev_writing_htaccess === 1 ) {  ?>
					<div>
						<p style="color: #a94442; background-color: #f2dede; border-color: #ebccd1; padding:10px;">
							The previous attempt to write to htaccess failed! static resources will not be directed, try to change that option again and see if that works for you.
							If that does not work for you, then you should add these lines to htaccess yourself or handle static resources redirection by yourself.
						</p>
					</div>
				<?php } ?>
				<div style="color: #31708f; background-color: #d9edf7; padding:10px;">
					<p>This plugin will enable you to redirect your whole website from/to http to/from https, or just redirect certain pages,posts, custom post types,categories, custom taxonomies, or any other page.</p>
					<p>Before forcing a certain redirect from http to https, make sure that https works correctly on your site by visiting the pages you want to redirect, or by visiting some of them.</p>
					<p>If you are testing around its preferable to use 302 redirections, so web browsers won't cache the redirections locally</p>
					<p>For seo its better to use 301 redirections, and it's better to avoid the Do not redirect option</p>
					<p style="color:darkblue; font-weight:bold;">In each front-end pages like post, category, or custom taxonomy, etc..., there will be an option of how you want to redirect, that option will override the option of the Default front-end redirection</p>
					<p style="color:darkblue; font-weight:bold;">Only change of the static resources redirection is going to cause changes to the .htaccess file, we will try to back up the htaccess file. In any case if you decide to redirect static resources I encourage you to manually backup your htaccess file</p>
				</div>
				<tr>
					<td valign="top" width="30%">
						<div>
							<p style="display:inline"><strong>Redirection type:</strong></p>
							<div class="tooltip">
							<img src="<?php echo $data['question-mark-url'] ?>" style="width:15px; height:15px;">
							<span class="tooltiptext">What type of redirection do you want across all the pages?</span>
							</div>
						</div>
					</td>
					<td valign="top">
						<select name="ahr-redirection-type" id="ahr-redirection-type" >
							<option value="301">301 (Moved Permanently)</option>
							<option value="302" <?php if ( get_option( 'ahr-redirection-type' ) === '302' ) echo selected ?>>302 (Found)</option>
							<option value="303" <?php if ( get_option( 'ahr-redirection-type' ) === '303' ) echo selected ?>>303 (See Other)</option>
							<option value="307" <?php if ( get_option( 'ahr-redirection-type' ) === '307' ) echo selected ?>>307 (Temporary Redirect)</option>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top" width="30%">
					<div>
						<p style="display:inline"><strong>Homepage redirection:</strong></p>
						<div class="tooltip">
						<img src="<?php echo $data['question-mark-url'] ?>" style="width:15px; height:15px;">
						<span class="tooltiptext">how do you want to redirect the homepage or the front-page?</span>
						</div>
					</div>
					</td>
					<td valign="top">
						<?php ahr_render_select_redirection (
							array(
								'name' => 'ahr-redirect-homepage',
								'value' => get_option('ahr-redirect-homepage'),
								'contains_default' => false
							) 
						); ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="30%">
					<div>
						<p style="display:inline"><strong>Default frontend redirection:</strong></p>
						<div class="tooltip">
						<img src="<?php echo $data['question-mark-url'] ?>" style="width:15px; height:15px;">
						<span class="tooltiptext">What is the default redirection for front-end pages like posts? notice that you can always override this option in posts or tags pages.</span>
						</div>
					</div>
					</td>
					<td valign="top">
						<?php ahr_render_select_redirection (
							array(
								'name' => 'ahr-redirect-frontend-default',
								'value' => get_option('ahr-redirect-frontend-default'),
								'contains_default' => false
							) 
						); ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="30%">
					<div>
						<p style="display:inline"><strong>Default admin redirection:</strong></p>
						<div class="tooltip">
						<img src="<?php echo $data['question-mark-url'] ?>" style="width:15px; height:15px;">
						<span class="tooltiptext">How do you want to redirect admin pages?</span>
						</div>
					</div>
					</td>
					<td valign="top">
						<?php ahr_render_select_redirection (
							array(
								'name' => 'ahr-redirect-admin-default',
								'value' => get_option('ahr-redirect-admin-default'),
								'contains_default' => false
							) 
						); ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="30%">
					<div>
						<p style="display:inline"><strong>Static resources redirection</strong></p>
						<div class="tooltip">
						<img src="<?php echo $data['question-mark-url'] ?>" style="width:15px; height:15px;">
						<span class="tooltiptext">How do you want to redirect static resources like images and music? This option will work by editing the .htaccess file</span>
						</div>
					</div>
					</td>
					<td valign="top">
						<?php ahr_render_select_redirection (
							array(
								'name' => 'ahr-redirect-static-resources',
								'id'   => 'ahr-redirect-static-resources',
								'value' => get_option('ahr-redirect-static-resources'),
								'contains_default' => false
							) 
						); ?>
					</td>
				</tr>
				<tr>
					<td style="display:block; width: 330%;" >
						<p id="ahr-htaccess-errors" style="display:none; color: #a94442; background-color: #f2dede; border-color: #ebccd1; padding:10px;">
							<?php ahr_handle_htaccess_errors(); ?>
						</p>
						<div id="ahr-htaccess-rules" style="display:none; color:#8a6d3b; background-color:#fcf8e3; padding:10px;">
							<p>The following lines are going to be writeen to .htaccess file<p>
							<p id="ahr-htaccess-written-rules" style="padding: 10px; border:solid; font-weight: bold;"></p>
							<h4 style="font-weight:900; color:darkred;">If changing Static resources redirection causes errors then set it back to DO not redirect and save: this will remove the lines that were written by this plugin.</h4>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- The Modal -->
		<div id="ahr-modal" class="modal">

		  <!-- Modal content -->
		  <div class="modal-content">
			<div class="modal-header">
			  <span class="close" id="ahr-modal-close">&times;</span>
			  <h2 id="ahr-modal-header">Backing .htaccess file</h2>
			</div>
			<div class="modal-body">
			  <p id="ahr-modal-p">Please wait while we are backing the .htaccess just for one time</p>
			  <div style="text-align: center;">
				<div class="loader"  id="ahr-modal-loader"></div>
			  </div>
			</div>
		  </div>

		</div>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="Save Changes">
		</p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="ahr-redirection-type, ahr-redirect-homepage, ahr-redirect-frontend-default,ahr-redirect-admin-default, ahr-redirect-static-resources" />
	</form>
</div>