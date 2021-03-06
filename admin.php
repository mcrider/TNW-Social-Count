<?php

if ( ! current_user_can('manage_options') ){
    wp_die( __('You do not have sufficient permissions to access this page.', 'tnwsc') );
}

global $tnwsc_config;

$postdata = $_POST;

// save posted settings
if( isset( $postdata['tnwsc_save'] ) ) {
	$updated_tnwsc_services = array();
	$tnwsc_services = get_option( 'tnwsc_services' );
	foreach( $tnwsc_services as $name => $v ) {
		if( is_array( $postdata['tnwsc_services'] ) ) {
			if( in_array( $name, $postdata['tnwsc_services'] ) ) {
				$updated_tnwsc_services[$name] = 1;
			} else {
				$updated_tnwsc_services[$name] = 0;
			}
		} else {
			if( $name == $postdata['tnwsc_services'] ) {
				$updated_tnwsc_services[$name] = 1;
			} else {
				$updated_tnwsc_services[$name] = 0;
			}
		}
	}

	update_option( 'tnwsc_services', $updated_tnwsc_services );
	update_option( 'tnwsc_post_range', $postdata['tnwsc_post_range'] );
	update_option( 'tnwsc_sync_frequency', $postdata['tnwsc_sync_frequency'] );
	update_option( 'tnwsc_active_sync', (int) ! empty($postdata['tnwsc_active_sync']) );
	update_option( 'tnwsc_debug', (int) ! empty($postdata['tnwsc_debug']) );
	update_option( 'tnwsc_log_path', $postdata['tnwsc_log_path'] );

	if( get_option( 'tnwsc_active_sync' ) == 1 ) {
		tnwsc_schedule_sync(true);
	}
}

if ( isset( $postdata['tnwsc_sync_now'] ) ) {
	$all_posts = true;
	$count = tnwsc_process( $all_posts );
	echo "<div class='updated fade'><p><strong>" . sprintf(__( '%s posts updated' , 'tnwsc'), $count) . '</strong></p></div>';

}

$active_sync = get_option( 'tnwsc_active_sync' );
$sync_frequency = get_option( 'tnwsc_sync_frequency' );
$post_range = get_option( 'tnwsc_post_range' );
$tnwsc_services = get_option( 'tnwsc_services' );
$debug = get_option( 'tnwsc_debug' );
$log_path = get_option( 'tnwsc_log_path' );


?>

	<div class="wrap">
		<h2><?php _e( 'TNW Social Count admin', 'tnwsc' ); ?></h2>
		
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="application/x-www-form-urlencoded">
		
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label>Query the following services:</label></th>
						<td>
					<?php foreach($tnwsc_services as $service => $enabled) { ?>
						<label><input type="checkbox" name="tnwsc_services[]" value="<?php echo $service; ?>" <?php echo $enabled?' checked="checked"':''?> /> <?php echo ucwords($service); ?> </label>
						<br />
						<?php } ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label>Auto sync:</label></th>
						<td><input type="checkbox" name="tnwsc_active_sync" value="1"<?php echo $active_sync ? ' checked="checked"' : ''; ?> /> Enable automatic check to update social count</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label>Debug mode:</label></th>
						<td><input type="checkbox" name="tnwsc_debug" value="1"<?php echo $debug ? ' checked="checked"' : ''; ?> /> Enable debug mode to write to a log file instead of to the database</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label>Path to log file:</label></th>
						<td><input type="text" name="tnwsc_log_path" value="<?php echo esc_html($log_path)?>" size="30" /> (Only if debug mode is active - Make sure it is writable)</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label>Frequency of update:</label></th>
						<td><input type="text" name="tnwsc_sync_frequency" value="<?php echo esc_html($sync_frequency)?>" size="10" /> seconds (Default: One check per hour)</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label>Sync social count for posts published before:</label></th>
						<td><input type="text" name="tnwsc_post_range" value="<?php echo esc_html($post_range)?>" size="10" /> seconds (Default: 7 days)</td>
					</tr>
				</tbody>
			</table>

			<p class="submit"><input type="submit" class="button-primary" value="Save config" name="tnwsc_save" />

		</form>

		<h3>Sync social count</h3>
		
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="application/x-www-form-urlencoded">
			<p>Yes, I want to sync the social count for ALL the articles of this blog. Please bear in mind that this might take several minutes to complete depending on the number or articles published.</p>
			<p class="submit"><input type="submit" class="button-primary" value="Sync now" name="tnwsc_sync_now" />
		</form>
	</div>
