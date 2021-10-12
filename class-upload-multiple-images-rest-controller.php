<?php
/**
 * Plugin Name: Upload multiple media by Api.
 * Description: Upload multiple media by Api.
 * Author: Sandeep jain
 * Author URI:http://sandeepjain.me/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
 * Plugin URI:http://sandeepjain.me/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Version:1.0
 * License: GPL2
 *
 * @package   mmbyapi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * Add an endpoint to upload multiple images.
 *
 * @package mmbyapi
 **/
class Upload_Multiple_Images_REST_Controller {
	/**
	 * Add an endpoint to upload multiple images.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'upload_multiple_images' ) );
	}
	/**
	 * Upload multiple images.
	 */
	public function upload_multiple_images() {
		$route_namespace = apply_filters( 'mmbyapi_route_namespace', 'mmbyapi/v1' );
		register_rest_route(
			$route_namespace,
			'/upload-multiple-images',
			array(
				'methods'             => 'POST',
				'callback'            => function( $data ) {
					$items = array();
					if ( isset( $_FILES['mmbyapi_file_upload'] )
						&& ! empty( $_FILES['mmbyapi_file_upload'] ) ) {
						$files = wp_unslash( $_FILES['mmbyapi_file_upload'] );
						foreach ( $files['name'] as $key => $value ) {
							if ( $files['name'][ $key ] ) {
								$file = array(
									'name'     => $files['name'][ $key ],
									'type'     => $files['type'][ $key ],
									'tmp_name' => $files['tmp_name'][ $key ],
									'error'    => $files['error'][ $key ],
									'size'     => $files['size'][ $key ],
								);
								$_FILES = array( 'mmbyapi_file_upload' => $file );
								foreach ( $_FILES as $file => $array ) {
									$newupload = $this->mmbyapi_handle_attachment( $file );
									$items[] = $newupload;
								}
							}
						}
					}
					return $items;
				},
				'permission_callback' => function() {
					return true;
				},

			)
		);
	}
	/**
	 * Upload multiple images.
	 *
	 * @param arr     $file_handler file handler.
	 * @param boolean $set_thu set for thumbnail.
	 */
	public function mmbyapi_handle_attachment( $file_handler, $set_thu = false ) {
		// Check to make sure its a successful upload.
		if ( isset( $_FILES[ $file_handler ]['error'] )
		&& ! empty( $_FILES[ $file_handler ]['error'] )
		&& UPLOAD_ERR_OK !== $_FILES[ $file_handler ]['error'] ) {
			return false;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$attach_id = media_handle_upload( $file_handler, $post_id );
		return $attach_id;
	}
}
$mmbyapi_upload_multiple_images = new Upload_Multiple_Images_REST_Controller();
