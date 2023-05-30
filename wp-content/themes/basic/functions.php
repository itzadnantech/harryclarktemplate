<?php
require_once  ABSPATH . 'vendor/autoload.php';


/* ==========================================================================
 *  Theme settings
 * ========================================================================== */
if (!function_exists('basic_setup')) :
	function basic_setup()
	{

		if (!isset($content_width)) {
			$content_width = 725;
		}

		load_theme_textdomain('basic', get_template_directory() . '/languages');

		add_theme_support('woocommerce');
		add_theme_support('bbpress');

		add_theme_support('automatic-feed-links');
		add_theme_support('post-thumbnails');
		add_theme_support('title-tag');
		add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
		add_theme_support('custom-background', apply_filters('basic_custom_background_args', array('default-color' => 'ffffff')));
		add_theme_support('custom-header', array(
			'width'       => 1080,
			'height'      => 190,
			'flex-height' => true,
			'flex-width' => true,
		));

		register_nav_menus(array(
			'top'    => __('Main Menu', 'basic'),
			'bottom' => __('Footer Menu', 'basic')
		));


		// logo
		$args = array();
		$lpos = get_theme_mod('display_logo_and_title');
		if (false === $lpos || 'image' == $lpos) {
			$args['header-text'] = array('blog-name');
		}
		add_theme_support('custom-logo', $args);
	}
endif;
add_action('after_setup_theme', 'basic_setup');
/* ========================================================================== */


/* ==========================================================================
 *  Load scripts and styles
 * ========================================================================== */
if (!function_exists('basic_enqueue_style_and_script')) :
	function basic_enqueue_style_and_script()
	{

		global $post, $wp_query;

		// STYLES
		wp_enqueue_style('basic-fonts', '//fonts.googleapis.com/css?family=PT+Serif:400,700|Open+Sans:400,400italic,700,700italic&amp;subset=latin,cyrillic', array(), true);
		wp_enqueue_style('basic-style', get_stylesheet_uri(), array(), true);

		// SCRIPTS
		wp_enqueue_script('basic-html5shiv', get_template_directory_uri() . '/js/html5shiv.min.js', array(), '3.7.3', true);
		wp_script_add_data('basic-html5shiv', 'conditional', 'lt IE 9');

		wp_enqueue_script('basic-scripts', get_template_directory_uri() . '/js/functions.js', array('jquery'), true, true);

		if (is_singular()) {
			$socbtns = basic_get_theme_option('social_share', 'custom');

			if ('yandex' == $socbtns) {
				wp_enqueue_script('basic-yandexshare', '//yastatic.net/share2/share.js', array(), true, true);
			}

			if (comments_open() && get_option('thread_comments')) {
				wp_enqueue_script('comment-reply', false, true, true);
			}
		}
	}
endif;
add_action('wp_enqueue_scripts', 'basic_enqueue_style_and_script');
/* ========================================================================== */


/* ==========================================================================
 *  admin area
 * ========================================================================== */
if (!function_exists('basic_editor_styles')) :
	function basic_editor_styles()
	{
		add_editor_style('editor-style.css');
	}
endif;
add_action('admin_init', 'basic_editor_styles');
/* ========================================================================== */


/* ==========================================================================
 *  Register widget area
 * ========================================================================== */
if (!function_exists('basic_widgets_init')) :
	function basic_widgets_init()
	{

		register_sidebar(array(
			'name'          => __('Sidebar', 'basic'),
			'id'            => 'sidebar',
			'description'   => __('Add widgets here to appear in your sidebar.', 'basic'),
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<p class="wtitle">',
			'after_title'   => '</p>',
		));
	}
endif;
add_action('widgets_init', 'basic_widgets_init');


/* ==========================================================================
 *  Add Open Graph meta for singular pages
 * ========================================================================== */
if (!function_exists('basic_add_social')) :
	function basic_add_social($content)
	{
		global $post;

		if (is_singular() && basic_get_theme_option('add_social_meta')) {

			$aiod  = get_post_meta($post->ID, '_aioseop_description', true);
			$descr = (isset($aiod)) ? esc_html($aiod) : $post->post_excerpt;

			$title    = get_the_title();
			$url      = get_the_permalink();
			$blogname = get_bloginfo('name');
			$img      = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumbnail', false);

			echo <<<EOT
		
<!-- BEGIN social meta -->
<meta property="og:type" content="article"/>
<meta property="og:title" content="$title"/>
<meta property="og:description" content="$descr" />
<meta property="og:image" content="$img[0]"/>
<meta property="og:url" content="$url"/>
<meta property="og:site_name" content="$blogname"/>
<link rel="image_src" href="$img[0]" />
<!-- END social meta -->


EOT;
		}
	}
endif;
add_action('wp_head', 'basic_add_social');
/* ========================================================================== */


/* ========================================================================== *
 * default COMMENT callback
 * ========================================================================== */
if (!function_exists('basic_html5_comment')) :
	function basic_html5_comment($comment, $args, $depth)
	{

		$tag = ('div' === $args['style']) ? 'div' : 'li';
?>
		<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?>>
			<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">

				<footer class="comment-meta">
					<div class="comment-author">
						<?php if (0 != $args['avatar_size']) {
							echo get_avatar($comment, $args['avatar_size']);
						} ?>
						<b class="fn"><?php comment_author_link(); ?></b>
					</div>

					<div class="comment-metadata">
						<a href="<?php echo esc_url(get_comment_link($comment->comment_ID, $args)); ?>">
							<time datetime="<?php comment_time('c'); ?>">
								<?php printf(__('%1$s at %2$s', 'basic'), get_comment_date(), get_comment_time()); ?>
							</time>
						</a>
						<?php edit_comment_link(__('Edit', 'basic'), '<span class="edit-link">', '</span>'); ?>
					</div>

					<?php if ('0' == $comment->comment_approved) : ?>
						<p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'basic'); ?></p>
					<?php endif; ?>
				</footer>

				<div class="comment-content">
					<?php comment_text(); ?>
				</div>

				<div class="reply">
					<?php comment_reply_link(array_merge(
						$args,
						array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth']
						)
					)); ?>
				</div>

			</div>

		<?php

	}
endif;
/* ========================================================================== */


/* ==========================================================================
 *  Include libs
 * ========================================================================== */

// functions what display some page parts
require_once(get_template_directory() . '/inc/html-blocks.php');

// layout functions and filters
require_once(get_template_directory() . '/inc/layout.php');

// hooks
require_once(get_template_directory() . '/inc/hooks.php');
require_once(get_template_directory() . '/inc/woo-hooks.php');

// Schema.org markup
require_once(get_template_directory() . '/inc/schemaorg.php');

// theme options with Customizer API
require_once(get_template_directory() . '/inc/admin/options.php');
require_once(get_template_directory() . '/inc/customizer/customizer-controls.php');
require_once(get_template_directory() . '/inc/customizer/customizer-settings.php');
require_once(get_template_directory() . '/inc/customizer/customizer.php');


if (is_admin()) :

	// meta-box for layout control
	require_once(get_template_directory() . '/inc/admin/meta-boxes.php');

endif;

///Js code for Get A Quote function
add_action('wp_footer', 'custom_work_script');
function custom_work_script()
{
	///filter url
	$uri = $_SERVER['REQUEST_URI'];
	$uri = str_replace("/", "", $uri);
		?>
		<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
		<!-- SweetAlert library -->
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

		<script>
			jQuery(document).ready(function($) {
				///Post Quote form data to database 
				<?php if (str_contains($uri, '')) { ?>
					$('#forminator-module-7').submit(function(event) {
						event.preventDefault();
						event.stopPropagation();
						///Create Url for website
						var baseUrl = "https://harryclarktemplate.webxolutions.com/";
						let ajaxurl = baseUrl + "custom-work/php/quote-form.php";

						// Create a new FormData object
						var formData = new FormData(this);
						// Validate form fields
						let isValid = validateForm();
						if (isValid == true) {
							$('.forminator-button.forminator-button-submit').prop('disabled', true);
							$('.forminator-button.forminator-button-submit').text('Loading...');
							
							
							///remove cookies previous
							Cookies.remove("user_name");
							Cookies.remove("quote_id");



							///post ajax request
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								dataType: 'json',
								data: formData,
								processData: false,
								contentType: false,
								success: function(res) {
									$('.forminator-button.forminator-button-submit').prop('disabled', false);
									$('.forminator-button.forminator-button-submit').text('Send Message');
									
									switch (res.code) {

										case "success":
											// Clear the form on successful response
											$('#forminator-module-7')[0].reset();


											let name = res.data.name;
											let email = res.data.email;
											let stripePopUrl = `${baseUrl}asp-payment-box/?product_id=13`;
											swal({
												title: "Success",
												text: "Data submit successfully!",
												icon: "success",
												timer: 1500,
												buttons: false
											});
											setTimeout(function() {
												if (res.data.is_driver_license) {
													Cookies.set("user_name", res.data.name);
													Cookies.set("quote_id", res.data.quote_id); 
													window.location.href = stripePopUrl;
												} else {
													window.location.reload();
												}
											}, 2000);
											break;
										case "error":
											swal({
												title: "Error",
												text: res.message,
												icon: "error",
												timer: 2000,
												buttons: false
											});
											break;


									}
								}
							});
						}
					});

					function validateForm() {

						var isValid = true;
						// Validate form field 
						if ($('input[name="name-1"]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name="email-1"]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name="phone-1"]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name="number-1"]').val().trim() === '') {
							isValid = false;
						}
						if ($('select[name="select-1"] option:selected').length > 0) {
							isValid = false;
						}
						if ($('select[name="select-2"]').val().trim() === '') {
							isValid = false;
						}

						if ($('select[name="select-3"] option:selected').length > 0) {
							isValid = false;
						}
						return isValid;
					}
				<?php } ?>
				///Close Quote form data



				///Close Quote form data

				///Post Contact form data to database
				<?php if (str_contains($uri, 'contact-to-us')) { ?>
					$('#forminator-module-27').submit(function(event) {
						event.preventDefault();
						event.stopPropagation();



						///Create Url for website
						var baseUrl = "https://harryclarktemplate.webxolutions.com/";
						let ajaxurl = baseUrl + "custom-work/php/contact-us.php";

						// Create a new FormData object
						var formData = new FormData(this);
						let quote_id = Cookies.get("quote_id");
						formData.append("quote_id", quote_id);
						// Validate form fields
						let isValid = validateContactForm();


						if (isValid == true) {
							$('.forminator-button.forminator-button-submit').prop('disabled', true);
							$('.forminator-button.forminator-button-submit').text('Loading...');


							$.ajax({
								url: ajaxurl,
								type: 'POST',
								dataType: 'json',
								data: formData,
								processData: false,
								contentType: false,
								success: function(res) {
									$('.forminator-button.forminator-button-submit').prop('disabled', false);
									$('.forminator-button.forminator-button-submit').text('Please Submit');


									switch (res.code) {
										case "success":
											$('#forminator-module-27')[0].reset();
											Cookies.set("quote_id", 0);
											swal({
												title: "Success",
												text: res.message,
												icon: "success",
												buttons: false
											});
											setTimeout(function() {
												window.location.href = baseUrl;
											}, 2000)
											break;
										case "error":
											swal({
												title: "Error",
												text: res.message,
												icon: "error",
												timer: 2000,
												buttons: false
											});
											break;
									}
								}
							});
						}
					});

					function validateContactForm() {

						var isValid = true;
						// Validate form field 


						///for number
						for (let index = 1; index <= 1; index++) {
							if ($('input[name=number-' + index + ']').val().trim() === '') {
								isValid = false;
							}
						}
						///for text
						for (let index = 1; index <= 1; index++) {
							if ($('input[name=text-' + index + ']').val().trim() === '') {
								isValid = false;
							}
						}

						///for date
						for (let index = 1; index <= 4; index++) {
							if ($('input[name=date-' + index + ']').val().trim() === '') {
								isValid = false;
							}
						}
						///for select
						for (let index = 1; index <= 3; index++) {
							if ($('select[name=select-' + index + ']').val().trim() === '') {
								isValid = false;
							}
						}

						///license holder name validation
						if ($('input[name=name-1-first-name]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name=name-1-middle-name]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name=name-1-last-name]').val().trim() === '') {
							isValid = false;
						}

						///for image validation
						if ($('input[name=upload-1]').val().trim() === '') {
							isValid = false;
						}
						if ($('input[name=upload-2]').val().trim() === '') {
							isValid = false;
						}



						return isValid;
					}



				<?php } ?>
				///Close Contact form data


			});
		</script>
	<?php
}
