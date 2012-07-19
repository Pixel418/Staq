<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Kernel\Module;

class Public_File {



	/*************************************************************************
	  ATTRIBUTES				   
	 *************************************************************************/
	public $type = 'Public_File';



	/*************************************************************************
	  ROUTE METHODS				   
	 *************************************************************************/
	public function handle_route( $route ) {
		foreach ( \Supersoniq::$EXTENSIONS as $extension ) {
			$file_path = SUPERSONIQ_ROOT_PATH . $extension . '/public' . $route;
			if ( is_file( $file_path ) ) {
				return [ 'render_static_file', [ $file_path ] ];
			}
		}
		return FALSE;
	}



	/*************************************************************************
	  SIDE METHODS				   
	 *************************************************************************/
	public function render_static_file( $file_path ) {
		$content_type = $this->get_content_type( $file_path );
		$resource     = fopen( $file_path, 'rb' );
		$cache_time   = $this->get_public_file_cache_time( );
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header('Cache-Control: private, max-age=' . ( $cache_time - time( ) ) . ', pre-check=' . ( $cache_time - time( ) ), true );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $cache_time ), true );
		fpassthru( $resource );
	}
	

	
	/**************************************************************************
				UTILS					 
	 *************************************************************************/
	private function get_content_type( $file_path ) {
		$extension = \Supersoniq\substr_after( $file_path, '.' );
		if ( in_array( $extension, [ 'html', 'css' ] ) ) {
			$content_type = 'text/' . $extension;
		} else if ( $extension == 'ico' ) {
			$content_type = 'image/png';
		} else {
			$finfo        = finfo_open( FILEINFO_MIME_TYPE );
			$content_type = finfo_file( $finfo, $file_path );
			finfo_close( $finfo );
		}
		return $content_type;
	}    

	private function get_public_file_cache_time( ) {
		$public_file_cache = ( new \Settings )
			->by_file( 'application' )
			->get( 'cache', 'public_file_cache' );
		if ( ! $public_file_cache_time = strtotime( $public_file_cache ) ) {
			$public_file_cache_time = strtotime( '+1 hour' );
		}
		return $public_file_cache_time;
	}
}