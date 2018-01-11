<?php
/*
Plugin Name: Contact Form 7 Elements Quiz
Plugin URI: 
Description: Plugin created to add support to Contact Form 7 to perform element tests.
Version: 0.1
Author: Jesús Magallón
Author URI: http://yosoydev.net
License: GNU GPLv3
License URI: https://choosealicense.com/licenses/gpl-3.0/
*/

if ( ! function_exists('cf7_eq_post_type') ) {

// Register Custom Post Type
function cf7_eq_post_type() {

	$labels = array(
		'name'                  => _x( 'CF7 Elements Quiz', 'Post Type General Name', 'cf7eq' ),
		'singular_name'         => _x( 'CF7 Elements Quiz', 'Post Type Singular Name', 'cf7eq' ),
		'menu_name'             => __( 'CF7 Elements Quiz', 'cf7eq' ),
		'name_admin_bar'        => __( 'CF7 Elements Quiz', 'cf7eq' ),
		'archives'              => __( 'Item Archives', 'cf7eq' ),
		'attributes'            => __( 'Item Attributes', 'cf7eq' ),
		'parent_item_colon'     => __( 'Parent Item:', 'cf7eq' ),
		'all_items'             => __( 'All Items', 'cf7eq' ),
		'add_new_item'          => __( 'Add New Item', 'cf7eq' ),
		'add_new'               => __( 'Add New', 'cf7eq' ),
		'new_item'              => __( 'New Item', 'cf7eq' ),
		'edit_item'             => __( 'Edit Item', 'cf7eq' ),
		'update_item'           => __( 'Update Item', 'cf7eq' ),
		'view_item'             => __( 'View Item', 'cf7eq' ),
		'view_items'            => __( 'View Items', 'cf7eq' ),
		'search_items'          => __( 'Search Item', 'cf7eq' ),
		'not_found'             => __( 'Not found', 'cf7eq' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'cf7eq' ),
		'featured_image'        => __( 'Featured Image', 'cf7eq' ),
		'set_featured_image'    => __( 'Set featured image', 'cf7eq' ),
		'remove_featured_image' => __( 'Remove featured image', 'cf7eq' ),
		'use_featured_image'    => __( 'Use as featured image', 'cf7eq' ),
		'insert_into_item'      => __( 'Insert into item', 'cf7eq' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'cf7eq' ),
		'items_list'            => __( 'Items list', 'cf7eq' ),
		'items_list_navigation' => __( 'Items list navigation', 'cf7eq' ),
		'filter_items_list'     => __( 'Filter items list', 'cf7eq' ),
	);
	$args = array(
		'label'                 => __( 'CF7 Elements Quiz', 'cf7eq' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => false,
		'show_in_menu'          => false,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-feedback',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => false,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
	);
	register_post_type( 'cf7_eq', $args );

}
add_action( 'init', 'cf7_eq_post_type', 0 );

}

add_action('wpcf7_mail_sent', function ($cf7) {
	$submission = WPCF7_Submission::get_instance();
	$elements = array(
		1 => "Madera",
		2 => "Fuego",
		3 => "Terra",
		4 => "Metal",
		5 => "Agua",
		6 => "Tendons",
		7 => "Muscles",
		8 => "Agua",
		9 => "Os",
		10 => "Arteres"
  	);
  	$counter = [];
  	$quizPrefix = "eq-";
  	foreach ($elements as $key => $value) {
  		$counter[$value] = 0;
  	}

	if($cf7->id == 724 && $submission) {
		$pd = $submission->get_posted_data();
		unset($pd['_wpcf7']);
		unset($pd['_wpcf7_version']);
		unset($pd['_wpcf7_locale']);
		unset($pd['_wpcf7_unit_tag']);
		unset($pd['_wpcf7_container_post']);

		foreach ($pd as $key => $value) {
			if( strpos($key, $quizPrefix) !== false ) {
				$counter[$elements[$value[0]]] = $counter[$elements[$value[0]]]+1;
				unset($pd[$key]);
			}
		}

		if(count($counter) > 0) {
			arsort($counter);
			$counter = array_chunk($counter, 2, true)[0];
			$pd = array_merge($pd, array("elements" => $counter));
			$res = wp_insert_post(array(
				'post_content' => json_encode($pd),
				'post_status' => 'publish',
				'post_type' => 'cf7_eq'
			));
		}
	}
});

add_action( 'admin_menu', 'cf7_eq_add_admin_menu' );
add_action( 'admin_init', 'cf7_eq_settings_init' );


function cf7_eq_add_admin_menu(  ) { 
	add_menu_page( 'CF7 EQ', 'CF7 EQ', 'manage_options', 'cf7_eq', 'cf7_eq_options_page' );
}


function cf7_eq_settings_init(  ) { 
	register_setting( 'cf7_eq', 'cf7_eq_settings' );
}


function cf7_eq_options_page(  ) { ?>
		<h2>CF7 Elements Quiz</h2>
		<p>Here the results of the elements form will be registered. To use, use the prefix <code>eq-</code> as the name of the radio buttons in any contact form.</p>

		<?php
		$args = array(
			'post_type'		=> array( 'cf7_eq' ),
			'post_status'	=> array( 'publish' ),
		);

		$results = new WP_Query( $args );
		if ( $results->have_posts() ) { ?>
		<table class="eq-table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Data</th>
					<th>Elements</th>
				</tr>
			</thead>
			<tbody> <?php
				while ( $results->have_posts() ) {
					$results->the_post();
					$data = json_decode(get_the_content());
					$cf7_data = $el_results = "";
					foreach ($data as $key => $value) {
						if($key !== 'elements') {
							$cf7_data .= "<b>$key</b>: $value<br/>";
						} else {
							$n = 1;
							foreach ($data->elements as $k => $val) {
								$el_results .= "<b>$n</b> - $k ($val replies)<br/>";
								$n++;
							}
						}
					} ?>
					<tr>
						<td><?= get_the_ID() ?></td>
						<td><?= $cf7_data ?></td>
						<td><?= $el_results ?></td>
					</tr> <?php
				} ?>
			</tbody>
		</table> <?php
		} else { ?>
			<h4>No results.</h4> <?php
		}
		wp_reset_postdata();
}

add_action( 'admin_enqueue_scripts', 'load_admin_style' );
function load_admin_style() {
	wp_enqueue_style( 'dataTable', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css', false, '1.10.16' );
	wp_enqueue_script( 'dataTable', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'cf7eq', plugin_dir_url( __FILE__ ) .'js/main.js', array( 'dataTable' ) );
}