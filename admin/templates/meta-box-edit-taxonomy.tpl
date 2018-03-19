<tr class="form-field">
	<th scope="row" valign="top"><label for="ahr-redirect" style="margin-bottom:-5px;">Redirection type:</label></th>
	<td>
		<?php ahr_render_select_redirection (
			array(
				'name' => 'ahr-redirect',
				'id'   => 'ahr-redirect',
				'value' => get_term_meta( $data['term_id'] , 'ahr-redirect', true ),
				'contains_default' => true
			) 
		);
		?>
	</td>
</tr>