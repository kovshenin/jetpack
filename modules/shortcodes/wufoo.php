<?php
/*
Plugin Name: Wufoo Shortcode Plugin
Description: Enables shortcode to embed Wufoo forms. Usage: [wufoo username="chriscoyier" formhash="x7w3w3" autoresize="true" height="458" header="show" ssl="true"]
Author: Chris Coyier / Wufoo, evansolomon

Based on http://wordpress.org/extend/plugins/wufoo-shortcode/
http://wufoo.com/docs/code-manager/wordpress-shortcode-plugin/
*/


function wufoo_shortcode( $atts ) {
	$attr = shortcode_atts(
		array(
			'username'   => '',
			'formhash'   => '',
			'autoresize' => true,
			'height'     => '500',
			'header'     => 'show',
			'ssl'        => '',
		), $atts
	);

	// Check username and formhash to ensure they only have alphanumeric characters or underscores, and aren't empty.
	if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $attr['username'] ) || ! preg_match( '/^[a-zA-Z0-9_]+$/', $attr['formhash'] ) ) {

		/**
		 * Return an error to the users with instructions if one of these params is invalid
		 * They don't have default values because they are user/form-specific
		 */
		$return_error = sprintf( __( 'Something is wrong with your Wufoo shortcode. If you copy and paste it from the %sWufoo Code Manager%s, you should be golden.', 'jetpack' ), '<a href="http://wufoo.com/docs/code-manager/">', '</a>' );

		return '
			<div style="border: 20px solid red; border-radius: 40px; padding: 40px; margin: 50px 0 70px;">
				<h3>Uh oh!</h3>
				<p style="margin: 0;">' . $return_error . '</p>
			</div>';
	}

	/**
	 * Required parameters are present.
	 * An error will be returned inside the form if they are invalid.
	 */
	$js_embed = '<script type="text/javascript">var host = (("https:" == document.location.protocol) ? "https://secure." : "http://");document.write(unescape("%3Cscript src=\'" + host + "wufoo.com/scripts/embed/form.js\'  type=\'text/javascript\'%3E%3C/script%3E"));</script>';
	$js_embed .= "<script type='text/javascript'>";
	$js_embed .= 'var wufoo_' . $attr['formhash'] . ' = new WufooForm();';
	$js_embed .= 'wufoo_' . $attr['formhash'] . ' .initialize({';
	$js_embed .= "'userName':'" . $attr['username'] . "', ";
	$js_embed .= "'formHash':'" . $attr['formhash'] . "', ";
	$js_embed .= "'autoResize':" . (bool) ( $attr['autoresize'] ) . ',';
	$js_embed .= "'height':'" . (int) $attr['height'] . "',";
	$js_embed .= "'header':'" . esc_js( $attr['header'] ) . "' ";

	/**
	 * Only output SSL value if passes as param.
	 * Lower tier plans don't show this param (don't offer SSL).
	 */
	$js_embed .= ( $attr['ssl'] ) ? ",'ssl':" . (bool) $attr['ssl'] : '';
	$js_embed .= '});';
	$js_embed .= 'wufoo_' . $attr['formhash'] . '.display();';
	$js_embed .= '</script>';

	/**
	 * iframe embed, loaded inside <noscript> tags.
	 */
	$iframe_embed = '<iframe ';
	$iframe_embed .= 'height="' . (int) $attr['height'] . '" ';
	$iframe_embed .= 'allowTransparency="true" frameborder="0" scrolling="no" style="width:100%;border:none;"';
	$iframe_embed .= 'src="https://' . $attr['username'] . '.wufoo.com/embed/' . $attr['formhash'] . '/">';
	$iframe_embed .= '<a href="https://' . $attr['username'] . '.wufoo.com/forms/' . $attr['formhash'] . '/" ';
	$iframe_embed .= 'rel="nofollow">' . __( 'Fill out my Wufoo form!', 'jetpack' ) . '</a></iframe>';

	/** This action is already documented in modules/widgets/gravatar-profile.php */
	do_action( 'jetpack_stats_extra', 'embeds', 'wufoo' );

	/**
	 * Return embed in JS and iframe.
	 */
	return "$js_embed <noscript> $iframe_embed </noscript>";
}

add_shortcode( 'wufoo', 'wufoo_shortcode' );
