<?php

$staq_path = substr( __DIR__, 0, strrpos( __DIR__, '/Staq/' ) + 5 );
require_once( $staq_path . '/util/tests.php' );
include_once( $staq_path . '/include.php' );

// CONTEXT
$path = substr( __DIR__, strrpos( __DIR__, '/Staq/' ) + 1 );
$app = new \Staq\Application( $path );
$app->start( );

// TEST COLLECTION
$case = new \Staq\Util\Test_Case( 'Stack autoloading with an existing parent', [
	'Query an unknown stack give an empty stack' => function( ) {
		$stack = new \Stack\Machin\Coco;
		return ( \Staq\Util\stack_height( $stack ) == 0 );
	},
	'Query an unknown controller stack give a stack with the default controller' => function( ) {
		$stack = new \Stack\Controller\Coco;
		return ( \Staq\Util\stack_definition_contains( $stack, 'Staq\Ground\Stack\Controller\__Default' ) );
	},
	'Query a defined controller stack give a stack with the defined & default controller' => function( ) {
		$stack = new \Stack\Controller\About;
		return ( 
			\Staq\Util\stack_definition_contains( $stack, 'Staq\Test\Core\Autoloader\Parent\Stack\Controller\About' ) &&
			\Staq\Util\stack_definition_contains( $stack, 'Staq\Ground\Stack\Controller\__Default' ) 
		);
	},
	'Query a defined stack element without define parent give a stack with a height of 1' => function( ) {
		$stack = new \Stack\Machin\About;
		return ( \Staq\Util\stack_height( $stack ) == 1 );
	},
	'Query a redefined default exception give a stack with with the two default exception' => function( ) {
		$stack = new \Stack\Exception\Resource_Not_Found;
		return ( \Staq\Util\stack_height( $stack ) == 2 );
	}
] );

// RESULT
echo $case->to_html( );
return $case;