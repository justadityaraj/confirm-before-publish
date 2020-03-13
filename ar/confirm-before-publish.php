<?php

/** Quit */
defined('ABSPATH') || exit;

/**
 * confirm_before_publish
 *
 * @since 0.0.1
 */
class confirm_before_publish
{

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Get instance.
	 *
	 * @return confirm_before_publish
	 */
	public static function get_instance()
	{

		if (!self::$instance instanceof self) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * confirm_before_publish constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Run the plugin working instance.
	 */
	public function setup()
	{

		// Check user role.
		if (!current_user_can('publish_posts')) {
			return;
		}

		$this->localize();

		foreach (array('post-new.php', 'post.php') as $page) {
			add_action('admin_footer-' . $page, array($this, 'inject_js'), 11);
		}
	}

	/**
	 * Validate for the allowed post types.
	 */
	private static function validate_post_type()
	{

		// Filter published posts.
		if (get_post()->post_status === 'publish') {
			return;
		}

		// Optionally include/exclude post types.
		$current_pt = get_post()->post_type;

		// Filter post types.
		$include_pts = apply_filters(
			'confirm_before_publish_post_types',
			get_post_types()
		);

		// Bail if current PT is not in PT stack.
		if (!in_array($current_pt, (array) $include_pts, true)) {
			return;
		}
	}

	/**
	 * Load language file.
	 *
	 * @since   2020-03-10
	 * @version 20120-03-14
	 */
	public function localize()
	{

		load_plugin_textdomain('confirm-before-publish');
	}

	/**
	 * Message popup.
	 *
	 * @return string
	 */
	private static function get_message()
	{

		// Message.
		return apply_filters(
			'confirm_before_publish_message',
			esc_attr__('Are you sure you want to publish this right now?', 'confirm-before-publish')
		);
	}

	/**
	 * JS code integration
	 *
	 * @since   0.0.1
	 * @version 0.0.2
	 *
	 * @hook    array  confirm_before_publish_message
	 */
	public static function inject_js()
	{

		self::validate_post_type();

		// Is jQuery loaded.
		if (!wp_script_is('jquery', 'done')) {
			return;
		}

		// Print js.
		self::_print_js(self::get_message());
	}

	/**
	 * Prints the JS code into the footer
	 *
	 * @since   0.0.1
	 * @version -11-30
	 *
	 * @param   string $msg JS confirm message.
	 */
	private static function _print_js($msg)
	{

?>
		<script type="text/javascript">
			jQuery(document).ready(
				function($) {
					var scheduleLabel = postL10n.schedule;
					$('#publish').on(
						'click',
						function(event) {
							if ($(this).attr('name') !== 'publish' || $(this).attr('value') === scheduleLabel) {
								return;
							}
							if (!confirm(<?php echo wp_json_encode($msg) ?>)) {
								event.preventDefault();
							}
						}
					);
				}
			);
		</script>
<?php }
}
