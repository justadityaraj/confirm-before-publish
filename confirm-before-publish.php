<?php

defined('ABSPATH') || exit;

if (!is_admin()) {
	return;
}

require_once dirname(__FILE__) . '/ar/confirm-before-publish.php';

add_action('admin_init', array(Publish_Confirm::get_instance(), 'setup'));
