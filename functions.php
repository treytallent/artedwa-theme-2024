<?php

// Removes all but these blocks from Gutenberg editor
function set_allowed_blocks($allowed_block_types, $block_editor_context)
{
	if ('volunteer-role' === $block_editor_context->post->post_type) {
		$allowed_block_types = array(
			'core/heading',
			'core/paragraph',
			'core/image',
			'core/list',
		);
	}
	return $allowed_block_types;
};
add_filter('allowed_block_types_all', 'set_allowed_blocks', 10, 2);


// Unregisters default block styles & registers block variations
// This can only be done using JS because it requires dependencies
function editor_assets()
{
	$dependencies = array('wp-blocks', 'wp-dom-ready');
	if (is_object(get_current_screen())) {
		if (get_current_screen()->id == 'site-editor') {
			$dependencies[] = 'wp-edit-site';
		} elseif (get_current_screen()->id == 'widgets') {
			$dependencies[] = 'wp-edit-widgets';
		} else {
			$dependencies[] = 'wp-edit-post';
		}
	} else {
		$dependencies[] = 'wp-edit-post';
	}
	wp_enqueue_script(
		'unregister-script',
		get_template_directory_uri() . '/assets/js/unregister.js',
		$dependencies
	);
	wp_enqueue_script('block-variations', get_template_directory_uri() . '/assets/js/variations.js', array('wp-blocks'));
}
add_action('enqueue_block_editor_assets', 'editor_assets');


// Handles the registration and unregistration of block styles
function register_block_styles()
{
	register_block_style(
		'core/button',
		array(
			'name' => 'primary',
			'label' => 'Primary',
			'is_default' => true
		)
	);
	register_block_style(
		'core/button',
		array(
			'name' => 'secondary',
			'label' => 'Secondary',
		)
	);
	register_block_style(
		'core/group',
		array(
			'name' => 'secondary',
			'label' => 'Secondary',
		)
	);
	register_block_style(
		'core/image',
		array(
			'name' => 'splatter',
			'label' => 'Splatter',
			'is_default' => true
		)
	);
	register_block_style(
		'core/navigation',
		array(
			'name' => 'primary',
			'label' => 'Primary',
			'is_default' => true
		)
	);
	register_block_style(
		'artedwa-blocks/event-card',
		array(
			'name' => 'upcoming',
			'label' => 'Upcoming Event',
			'is_default' => true
		)
	);
}
add_action('init', 'register_block_styles');

// Modifies the query for upcoming_date_query_loop_name on the front-end
add_filter('pre_render_block', 'upcoming_date_query_loop_name_pre_render_block', 10, 2);
function upcoming_date_query_loop_name_pre_render_block($pre_render, $parsed_block)
{
	if (!empty($parsed_block['attrs']['namespace']) && "upcoming-date-query-loop" === $parsed_block['attrs']['namespace']) {
		add_filter(
			'query_loop_block_query_vars',
			function ($query, $block) {
				if ($query['post_type'] === 'event') {
					$today = date('Ymd');
					$query['meta_key'] = 'event-fields-end-date';
					$query['meta_value'] = $today;
					$query['meta_compare'] = '>=';
					$query['orderby'] = 'meta_value';
					$query['order'] = 'ASC';
					return $query;
				}
				return $query;
			},
			10,
			2
		);
	}
	return $pre_render;
}


// Modifies the query for past_date_query_loop_name on the front-end
add_filter('pre_render_block', 'past_date_query_loop_name_pre_render_block', 10, 2);
function past_date_query_loop_name_pre_render_block($pre_render, $parsed_block)
{
	if (!empty($parsed_block['attrs']['namespace']) && 'past-date-query-loop' === $parsed_block['attrs']['namespace']) {
		add_filter(
			'query_loop_block_query_vars',
			function ($query, $block) {
				if ($query['post_type'] === 'event') {
					$today = date('Ymd');
					$query['meta_key'] = 'event-fields-end-date';
					$query['meta_value'] = $today;
					$query['meta_compare'] = '<';
					$query['orderby'] = 'meta_value';
					$query['order'] = 'ASC';
					return $query;
				}
				return $query;
			},
			10,
			2
		);
	}
	return $pre_render;
}

// Enqueues global css stylesheets on the front.
if (! function_exists('enqueue_stylesheet')) :

	function enqueue_stylesheet()
	{
		wp_enqueue_style(
			'style-css',
			get_parent_theme_file_uri('style.css'),
		);
		wp_enqueue_style(
			'layouts-css',
			get_parent_theme_file_uri('assets/css/layouts.css')
		);
	}
endif;
add_action('wp_enqueue_scripts', 'enqueue_stylesheet');

// Enqueues editor-style.css in the editor & block styles.
if (! function_exists('enqueue_after_theme')) :

	function enqueue_after_theme()
	{
		add_editor_style(get_parent_theme_file_uri('assets/css/editor-style.css'));
		add_editor_style(get_parent_theme_file_uri('assets/css/layouts.css'));

		wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'buttons-css',
				'src' => get_template_directory_uri() . '/assets/css/button.css',
				'path' => get_template_directory_uri() . '/assets/css/button.css',
			)
		);
		wp_enqueue_block_style(
			'core/group',
			array(
				'handle' => 'block-variations-css-groups',
				'src' => get_template_directory_uri() . '/assets/css/group.css',
				'path' => get_template_directory_uri() . '/assets/css/group.css',
			)
		);
		wp_enqueue_block_style(
			'core/image',
			array(
				'handle' => 'block-variations-css-images',
				'src' => get_template_directory_uri() . '/assets/css/image.css',
				'path' => get_template_directory_uri() . '/assets/css/image.css',
			)
		);
		wp_enqueue_block_style(
			'core/navigation',
			array(
				'handle' => 'block-variations-css-navigation',
				'src' => get_template_directory_uri() . '/assets/css/navigation.css',
				'path' => get_template_directory_uri() . '/assets/css/navigation.css',
			)
		);
	}
endif;
add_action('after_setup_theme', 'enqueue_after_theme');

// Registers ACF Fields on the server to use the block binding API. As of now in 6.7 this is the only way to bind images to blocks
add_action('acf/include_fields', function () {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}
	// Committee member fields
	acf_add_local_field_group(array(
		'key' => 'committee-fields',
		'title' => 'Committee Fields',
		'fields' => array(
			array(
				'key' => 'committee-fields-name',
				'label' => 'Name',
				'name' => 'committee-fields-name',
				'type' => 'text',
				'required' => 1,
			),
			array(
				'key' => 'committee-fields-role',
				'label' => 'Role',
				'name' => 'committee-fields-role',
				'type' => 'text',
				'required' => 0,
			),
			array(
				'key' => 'committee-fields-short-description',
				'label' => 'Short Description',
				'name' => 'committee-fields-short-description',
				'type' => 'textarea',
				'required' => 0,
			),
			array(
				'key' => 'committee-fields-img',
				'label' => 'Image',
				'name' => 'committee-fields-img',
				'type' => 'image',
				'required' => 1,
				'return_format' => 'url',
				'library' => 'uploadedTo',
				'preview_size' => 'medium',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'committee-member',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));

	// Event fields
	acf_add_local_field_group(array(
		'key' => 'event-fields',
		'title' => 'Event Fields',
		'fields' => array(
			array(
				'key' => 'event-fields-title',
				'label' => 'Event Name',
				'name' => 'event-fields-event-name',
				'type' => 'text',
				'instructions' => 'Limited characters to avoid layout inconsistencies.',
				'required' => 1,
				'conditional_logic' => 0,
			),
			array(
				'key' => 'event-fields-short-description',
				'label' => 'Short Description',
				'name' => 'event-fields-short-description',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'maxlength' => '400',
			),
			array(
				'key' => 'event-fields-start-date',
				'label' => 'Start Date',
				'name' => 'event-fields-start-date',
				'type' => 'date_picker',
				'instructions' => 'What date does the event start?',
				'required' => 1,
				'display_format' => 'F j, Y',
				'return_format' => 'F j',
			),
			array(
				'key' => 'event-fields-end-date',
				'label' => 'End Date',
				'name' => 'event-fields-end-date',
				'type' => 'date_picker',
				'instructions' => 'What date does the event end?',
				'required' => 1,
				'display_format' => 'F j, Y',
				'return_format' => 'F j',
			),
			array(
				'key' => 'event-fields-img',
				'label' => 'Image',
				'name' => 'event-fields-img',
				'type' => 'image',
				'required' => 1,
				'instructions' => 'Image height/width must be at least 400px and no more than 600px.',
				'return_format' => 'url',
				'library' => 'uploadedTo',
				'min_width' => '200',
				'min_height' => '200',
				'max_width' => '600',
				'max_height' => '600',
				'preview_size' => 'medium',
			),
			array(
				'key' => 'event-fields-url',
				'label' => 'Read More URL',
				'name' => 'event-fields-url',
				'type' => 'url',
				'instructions' => 'Link to a page for more details on the event, such as Eventbrite',
				'required' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));

	// Organisations We Support Fields
	acf_add_local_field_group(array(
		'key' => 'we-support-fields',
		'title' => 'Organisations We Support Fields',
		'fields' => array(
			array(
				'key' => 'we-support-fields-title',
				'label' => 'Organisation Name',
				'name' => 'we-support-fields-title',
				'type' => 'text',
				'required' => 1,
			),
			array(
				'key' => 'we-support-fields-img',
				'label' => 'Logo',
				'name' => 'we-support-fields-img',
				'aria-label' => '',
				'type' => 'image',
				'instructions' => 'These images should be small. Ideally, they should be a webp file extension with a height of 60px and an automatic width that allows the image to maintain its original aspect ratio with a height of 60px.',
				'required' => 1,
				'return_format' => 'url',
				'library' => 'uploadedTo',
				'min_width' => '80',
				'min_height' => '80',
				'max_width' => '800',
				'max_height' => '200',
				'preview_size' => 'medium',
			),
			array(
				'key' => 'we-support-fields-url',
				'label' => 'Link',
				'name' => 'we-support-fields-url',
				'type' => 'url',
				'instructions' => 'Link to learn more about the organisation. This can be their website or social media.',
				'required' => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'organisation',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));

	// Volunteer Role Fields
	acf_add_local_field_group(array(
		'key' => 'volunteer-role-fields',
		'title' => 'Volunteer Role Fields',
		'fields' => array(
			array(
				'key' => 'volunteer-role-fields-title',
				'label' => 'Role Title',
				'name' => 'volunteer-role-fields-title',
				'type' => 'text',
				'instructions' => 'Displayed as the title of the job opening on the "Join Us" page.',
				'required' => 1,
				'maxlength' => '90',
			),
			array(
				'key' => 'volunteer-role-fields-short-description',
				'label' => 'Short Description',
				'name' => 'volunteer-role-fields-short-description',
				'type' => 'textarea',
				'instructions' => 'Displayed as the job description on the "Join Us" page.',
				'required' => 1,
				'maxlength' => '400',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'volunteer-role',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));
});
