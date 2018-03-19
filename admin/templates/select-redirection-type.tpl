<select name="<?php echo $data['name'] ?>" id="<?php echo $data['id'] ?>" size="1">
	<?php if ( $data['contains_default'] ) { ?>
		<option value="default">Redirect according to default settings</option>
	<?php } ?>
	<option value="none" <?php if ( $data['value'] === 'none' ) echo 'selected'; ?>>Do not redirect</option>
	<option value="https" <?php if ( $data['value'] === 'https' ) echo 'selected'; ?>>Redirect to https</option>
	<option value="http" <?php if ( $data['value'] === 'http' ) echo 'selected'; ?>>Redirect to http</option>
</select>
