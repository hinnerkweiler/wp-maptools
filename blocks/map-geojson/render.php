<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attachment_id = isset( $attributes['attachmentId'] ) ? absint( $attributes['attachmentId'] ) : 0;
$button_text   = isset( $attributes['buttonText'] ) && $attributes['buttonText'] !== ''
	? $attributes['buttonText']
	: 'Download GeoJSON';
$open_in_new   = ! empty( $attributes['openInNewTab'] );

if ( ! $attachment_id ) {
	return;
}

$file_url = wp_get_attachment_url( $attachment_id );

if ( ! $file_url ) {
	return;
}

$target = $open_in_new ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<a class="wp-block-button__link wp-element-button"
	   href="<?php echo esc_url( $file_url ); ?>"
	   download
		<?php echo $target; ?>>
		<?php echo esc_html( $button_text ); ?>
	</a>
</div>