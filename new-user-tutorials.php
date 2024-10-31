<?php
/*
Plugin Name: New User Tutorials 
Plugin URI:  plugins.rockwellgrowth.com/new-user-tutorials
Description: Easy to create tutorials to guide your users through actions & on screen flows
Version:     0.1
Author:      Andrew Rockwell
Author URI:  http://www.rockwellgrowth.com/
License:     GPL2v2
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );



//---- Add settings link to plugins page
function add_new_user_tutorials_links( $links ) {
	$links[] = '<a href="' . admin_url( 'options-general.php?page=new-user-tutorials' ) . '">Settings</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_new_user_tutorials_links' );



//---- Add admin script
function add_new_user_tutorials_admin_script () {
	$screen = get_current_screen();
	if( $screen->id == 'new-user-tutorial' ) {
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_style( 'new_user_tutorials_admin_style', plugins_url( 'css/style-admin.css', __FILE__ ) );

        wp_enqueue_style( 'wp-color-picker' ); 
        wp_enqueue_script( 'new_user_tutorials_admin_script', plugins_url( 'js/script-admin.js', __FILE__ ), array('wp-color-picker'), false, true ); 
        wp_enqueue_script( 'new_user_tutorials_admin_script', plugins_url( 'js/script.js', __FILE__ ), array(), false, true ); 
	}
}
add_action( 'admin_enqueue_scripts', 'add_new_user_tutorials_admin_script' );



//---- Add frontend script & style
function add_new_user_tutorials_frontend_script () {
	wp_enqueue_script( 'new_user_tutorials_frontend_script', plugins_url( 'js/script.js', __FILE__ ) );
	wp_enqueue_style( 'new_user_tutorials_frontend_style', plugins_url( 'css/style-admin.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'add_new_user_tutorials_frontend_script', 999 );



//---- Load the tutorial data if need be
function testing_admin_popup() {
	$tuts = get_posts('post_type=new-user-tutorial&post_status=publish');
	$matching_tuts = array();
	$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	foreach ($tuts as $tut) {
		global $post;
		$intro_page_type = get_post_meta($tut->ID, 'nut-intro-page-type', true);
		$tut_page = get_post_meta( $tut->ID, 'new_user_tutorials_intro_page_meta', true );
		if( ( 'url' == $intro_page_type && strpos( $current_url, $tut_page ) != false ) || ( 'post' == $intro_page_type && is_object( $post ) && $post->ID == $tut_page ) ) {
			$matching_tuts[] = get_post_meta( $tut->ID, 'new_user_tutorials_steps_meta', true );
			$json_encoded_value = htmlspecialchars( get_post_meta( $tut->ID, 'new_user_tutorials_steps_meta', true ) );
		}
	}

	if( ! empty( $matching_tuts ) ) {

		echo '<input type="hidden" id="nut-tutorial-steps" value="' . $json_encoded_value . '">';

		echo '<div class="nut-material-popup">';
		echo '	<div class="nut-text">I\'m the first step of this plugin!</div>';
		echo '	<div class="nut-buttons">';
		echo '		<button class="nut-ok">Continuef</button>';
		echo '		<button class="nut-cancel">Close</button>';
		echo '	</div>';
		echo '</div>';

		echo '<div class="nut-highlight-item">';
		echo '</div>';

		echo '<div class="nut-highlight-overlay">';
		echo '</div>';
	}

}
add_filter( 'admin_footer', 'testing_admin_popup' );
add_filter( 'wp_footer', 'testing_admin_popup' );



//---- Tutorial post type
function new_user_tutorials_tutorial_post_type () {

	$labels = array(
		'name'                => __( 'NU Tutorials', 'new-user-tutorials' ),
		'singular_name'       => __( 'NU Tutorial', 'new-user-tutorials' ),
		'add_new'             => _x( 'Add New NU Tutorial', 'new-user-tutorials', 'new-user-tutorials' ),
		'add_new_item'        => __( 'Add New NU Tutorial', 'new-user-tutorials' ),
		'edit_item'           => __( 'Edit NU Tutorial', 'new-user-tutorials' ),
		'new_item'            => __( 'New NU Tutorial', 'new-user-tutorials' ),
		'view_item'           => __( 'View NU Tutorial', 'new-user-tutorials' ),
		'search_items'        => __( 'Search NU Tutorials', 'new-user-tutorials' ),
		'not_found'           => __( 'No NU Tutorials found', 'new-user-tutorials' ),
		'not_found_in_trash'  => __( 'No NU Tutorials found in Trash', 'new-user-tutorials' ),
		'parent_item_colon'   => __( 'Parent NU Tutorial:', 'new-user-tutorials' ),
		'menu_name'           => __( 'NU Tutorials', 'new-user-tutorials' ),
	);

	$args = array(
		'labels'                   => $labels,
		'hierarchical'        => false,
		'description'         => 'description',
		'taxonomies'          => array(),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => null,
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'thumbnail' )
	);

	register_post_type( 'new-user-tutorial', $args );
}
add_action( 'init', 'new_user_tutorials_tutorial_post_type' );



//---- Tutorial post type meta
function add_new_user_tutorials_metaboxes() {
	add_meta_box('new_user_tutorials_intro_page_meta', 'Tutorial Intro Page', 'new_user_tutorials_intro_page_meta', 'new-user-tutorial', 'normal', 'default');
	add_meta_box('new_user_tutorials_steps_meta', 'Tutorial Steps', 'new_user_tutorials_steps_meta', 'new-user-tutorial', 'normal', 'high');
}
add_action( 'add_meta_boxes', 'add_new_user_tutorials_metaboxes' );

function new_user_tutorials_intro_page_meta() {
	global $post;
	
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="new_user_tutorials_nonce" id="new_user_tutorials_nonce" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
	// Get the location data if its already been entered
	$intro_page_type = get_post_meta($post->ID, 'nut-intro-page-type', true);
	$intro_page = get_post_meta($post->ID, 'new_user_tutorials_intro_page_meta', true);
	
	// Echo out the field
	?>

	<table>
		<tbody>
			<tr>
				<td>
					<select id="nut-intro-page-type" name="nut-intro-page-type" class="nut-intro-page-type">
						<option value="post" <?php if( 'post' == $intro_page_type ) { echo 'selected'; } ?>>Post ID</option>
						<option value="url" <?php if( 'url' == $intro_page_type ) { echo 'selected'; } ?>>URL contains . . .</option>
					</select>
				</td>
				<td>
					<input type="text" id="new_user_tutorials_intro_page_meta" name="new_user_tutorials_intro_page_meta" value="<?php echo $intro_page; ?>" class="widefat" />
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}

function new_user_tutorials_steps_meta() {
	global $post;

	//---- Types of rows in the steps meta box
	function nut_meta_box_dialog_slides($slides) {
		$dialog_slides_table_row = '<td><select class="nut-select-step-type"><option value="dialog-slides" selected>Dialog Slides</option><option value="action-click">Click Action</option></select></td>';
		$dialog_slides_table_row .= '<td>';
		foreach ($slides as $slide) {
			$dialog_slides_table_row .= '<input type="text" class="widefat nut-dialog-slide" value="' . $slide . '" placeholder="Dialog Text">';
		}
		$dialog_slides_table_row .= '<div class="nut-add-dialog-slide button button-secondary button-large" />Add Dialog Slide</div>';
		$dialog_slides_table_row .= '</td>';
		return $dialog_slides_table_row;
	}

	function nut_meta_box_action_click($step_data) {
		//---- Click outline shape options
		$outline_options = array(
			'box' => 'Box',
			'circle' => 'Circle',
		);

		//---- Build the row
		$action_click_table_row = '<td><select class="nut-select-step-type"><option value="dialog-slides">Dialog Slides</option><option value="action-click" selected>Click Action</option></select></td>';
		$action_click_table_row .= '<td><input type="text" value="' . $step_data['element'] . '" class="nut-action-click" placeholder="CSS selector to click">';
		$action_click_table_row .= '<select class="nut-action-click-outline-type">';

		foreach ($outline_options as $key => $value) {
			$select_string = ( $key==$step_data['shape'] ? 'selected' : '' );
			$action_click_table_row .= '<option value="' . $key . '" ' . $select_string . '>' . $value . '</option>';
		}

		$action_click_table_row .= '</select>';
		$action_click_table_row .= '</td>';
		return $action_click_table_row;
	}
	
	// Get the steps data if its already been entered
	$tutorial_steps = json_decode( get_post_meta($post->ID, 'new_user_tutorials_steps_meta', true), true );

	$nut_meta_box_output = '';
	if( $tutorial_steps != '' ) {
		foreach ($tutorial_steps as $step ) {
			$nut_meta_box_output .= '<tr><td></td>';
			if( $step['type'] == 'dialog-slides' ) {
				$nut_meta_box_output .= nut_meta_box_dialog_slides( $step['data'] );
			} elseif( $step['type'] == 'action-click' ) {
				$nut_meta_box_output .= nut_meta_box_action_click( $step['data'] );
			}
			$nut_meta_box_output .= '<td></td></tr>';
		}
	}
	
	// Echo out the fields
	?>
		<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="<?php wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
		<input type="hidden" id="nut_steps_meta_val" name="new_user_tutorials_steps_meta" value="<?php echo htmlspecialchars(json_encode($tutorial_steps));?>">
		<div class="step-container">
			<table>
				<thead>
					<tr>
						<td></td>
						<td>Type</td>
						<td>Values</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					<?php
						if( isset($tutorial_steps) && isset($tutorial_steps[0]) && isset($tutorial_steps[0]['type']) && $tutorial_steps[0]['type'] != '' ) {
							echo $nut_meta_box_output;
						} else {
					?>
						<tr>
							<td></td>
							<td>
								<select class="nut-select-step-type">
									<option value="dialog-slides">Dialog Slides</option>
									<option value="action-click">Click Action</option>
								</select>
							</td>
							<td>
								<input type="text" class="widefat nut-dialog-slide" placeholder="Dialog Text">
								<div class="nut-add-dialog-slide button button-secondary button-large" />Add Dialog Slide</div>
							</td>
							<td></td>
						</tr>
					<?php
						}
					?>
				</tbody>
			</table>
		</div>
		<div class="add-step-container">
			<div id="nut-add-step" class="button button-primary button-large" />Add Step</div>
		</div>

	<?php
}



//---- Save the Metabox Data
function wpt_save_events_meta($post_id, $post) {
	// verify this came from the our screen and with proper authorization
	if ( ! isset( $_POST['new_user_tutorials_nonce'] ) || ! wp_verify_nonce( $_POST['new_user_tutorials_nonce'], plugin_basename(__FILE__) )) {
		return $post->ID;
	}

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// Set up meta to be saved
	$events_meta['nut-intro-page-type'] = $_POST['nut-intro-page-type'];
	$events_meta['new_user_tutorials_intro_page_meta'] = $_POST['new_user_tutorials_intro_page_meta'];
	$events_meta['new_user_tutorials_steps_meta'] = $_POST['new_user_tutorials_steps_meta'];

	// Add values custom fields
	foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}

}
add_action('save_post', 'wpt_save_events_meta', 1, 2); // save the custom fields
