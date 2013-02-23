<?php

namespace Test\Staq;

require_once( __DIR__ . '/../../../vendor/autoload.php' );

class RouterTest extends WebTestCase {




	/*************************************************************************
	  GLOBAL METHODS			 
	 *************************************************************************/
	protected function setUp( ) {
		parent::setUp( );
		$this->get_request_url( 'http://localhost/coco' );
		$app = \Staq\App::create( $this->project_namespace )
			->setPlatform( 'local' );
	}




	/*************************************************************************
	  ERROR CONTROLLER TEST METHODS             
	 *************************************************************************/
	public function test_extended_error_controller( ) {
		\Staq::App()->run( );
        $this->expectOutputHtmlContent( 'error 404' );
	}




	/*************************************************************************
	  ANONYMOUS CONTROLLER TEST METHODS             
	 *************************************************************************/
	public function test_anonymous_controller__magic_route( ) {
		\Staq::App()->addController( '/*', function( ) {
				return 'hello';
			})
			->run( );
        $this->expectOutputHtmlContent( 'hello' );
	}

	public function test_anonymous_controller__simple_route__no_match( ) {
		\Staq::App()->addController( '/hello', function( ) {
				return 'hello';
			})
			->run( );
        $this->expectOutputHtmlContent( 'error 404' );
	}

	public function test_anonymous_controller__simple_route__match( ) {
		\Staq::App()->addController( '/coco', function( ) {
				return 'hello';
			})
			->run( );
        $this->expectOutputHtmlContent( 'hello' );
	}

	public function test_anonymous_controller__param_route__wrong_definition( ) {
		\Staq::App()->addController( '/:coco', function( $world ) {
				return 'hello ' . $world;
			})
			->run( );
        $this->expectOutputHtmlContent( 'error 500' );
	}

	public function test_anonymous_controller__conditionnal_controller( ) {
		\Staq::App()->addController( '/*', function( ) {
				if ( \Staq::App()->get_current_uri( ) == '/coco' ) {
					return NULL;
				}
			})
			->run( );
        $this->expectOutputHtmlContent( 'error 404' );
	}




	/*************************************************************************
	  PUBLIC FILE CONTROLLER TEST METHODS             
	 *************************************************************************/
	public function test_public_controller__match( ) {
		$this->get_request_url( 'http://localhost/static.txt' );
		\Staq\App::create( $this->project_namespace )
			->setPlatform( 'local' )
			->run( );
        $this->expectOutputHtmlContent( 'This is an example of static file' );
	}
}