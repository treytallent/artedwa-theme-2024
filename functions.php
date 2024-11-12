<?php

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
function block_variations_register()
{
	register_block_style(
		'core/button',
		array(
			'name' => 'primary',
			'label' => 'Primary',
			'block_variations_register',
			'is_default' => true
		)
	);
	register_block_style(
		'core/button',
		array(
			'name' => 'secondary',
			'label' => 'Secondary',
			'block_variations_register',
		)
	);
	register_block_style(
		'core/group',
		array(
			'name' => 'secondary',
			'label' => 'Secondary',
			'block_variations_register',
		)
	);
	register_block_style(
		'core/image',
		array(
			'name' => 'splatter',
			'label' => 'Splatter',
			'block_variations_register',
			'is_default' => true
		)
	);
	register_block_style(
		'core/navigation',
		array(
			'name' => 'primary',
			'label' => 'Primary',
			'block_variations_register',
			'is_default' => true
		)
	);
	register_block_style(
		'artedwa-blocks/event-card',
		array(
			'name' => 'upcoming',
			'label' => 'Upcoming Event',
			'block_variations_register',
			'is_default' => true
		)
	);
	register_block_style(
		'artedwa-blocks/event-card',
		array(
			'name' => 'past',
			'label' => 'Past Event',
			'block_variations_register',
		)
	);
}
add_action('enqueue_block_editor_assets', 'block_variations_register');

// Enqeue block style variation css
function block_variations_css()
{
	wp_enqueue_style('shared-css', get_template_directory_uri() . '/assets/css/shared.css');

	wp_enqueue_block_style(
		'core/button',
		array(
			'handle' => 'block-variations-css-buttons',
			'src' => get_template_directory_uri() . '/assets/css/buttons.css',
			'path' => get_template_directory_uri() . '/assets/css/buttons.css',
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
	wp_enqueue_block_style(
		'artedwa-blocks/event-card',
		array(
			'handle' => 'block-variations-css-cards',
			'src' => get_template_directory_uri() . '/assets/css/card.css',
			'path' => get_template_directory_uri() . '/assets/css/card.css',
		)
	);
	wp_enqueue_block_style(
		'artedwa-blocks/tabs-wrapper',
		array(
			'handle' => 'block-variations-tabs-wrapper',
			'src' => get_template_directory_uri() . '/assets/css/tabs.css',
			'path' => get_template_directory_uri() . '/assets/css/tabs.css',
		)
	);
	wp_enqueue_block_style(
		'artedwa-blocks/carousel-wrapper',
		array(
			'handle' => 'block-variations-carousel-wrapper',
			'src' => get_template_directory_uri() . '/assets/css/carousel.css',
			'path' => get_template_directory_uri() . '/assets/css/carousel.css',
		)
	);
	wp_enqueue_block_style(
		'artedwa-blocks/scrolling-carousel',
		array(
			'handle' => 'block-variations-scrolling-carousel',
			'src' => get_template_directory_uri() . '/assets/css/scrolling-carousel.css',
			'path' => get_template_directory_uri() . '/assets/css/scrolling-carousel.css',
		)
	);
}
add_action('init', 'block_variations_css');


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
					$query['meta_key'] = 'end_date';
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
					$query['meta_key'] = 'end_date';
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
