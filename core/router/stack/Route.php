<?php

/* This file is part of the Staq project, which is under MIT license */


namespace Staq\Core\Router\Stack ;

class Route {



	/*************************************************************************
	 ATTRIBUTES
	 *************************************************************************/
	protected $callable;
	protected $match_uri;
	protected $match_exception;
	protected $parameters = [ ];
	protected $aliases    = [ ];



	/*************************************************************************
	  CONSTRUCTOR            
	 *************************************************************************/
	public function __construct( $callable, $match_uri, $match_exception = NULL , $aliases = [ ] ) {
		$this->callable        = $callable;
		$this->match_uri       = $match_uri;
		$this->match_exception = $match_exception;
		$this->aliases         = $aliases;
	}



	/*************************************************************************
	  PUBLIC METHODS             
	 *************************************************************************/
	public function call_action( ) {
		if ( is_array( $this->callable ) ) {
			$reflection = new \ReflectionMethod( $this->callable[ 0 ], $this->callable[ 1 ] );
		} else {
			$reflection = new \ReflectionFunction( $this->callable );
		}
		$parameters = [ ];
		foreach( $reflection->getParameters( ) as $parameter ) {
			if ( ! $parameter->canBePassedByValue( ) ) {
				throw new \Stack\Exception\Controller_Definition( 'A controller could not have parameter passed by reference' );
			}
			if ( isset( $this->parameters[ $parameter->name ] ) ) {
				$parameters[ ] = $this->parameters[ $parameter->name ];
			} else if ( $parameter->isDefaultValueAvailable( ) ) {
				$parameters[ ] = $parameter->getDefaultValue( );
			} else {
				throw new \Stack\Exception\Controller_Definition( 'The current uri does not provide a value for the parameter "' . $parameter->name . '"' );
			}
		}
		return call_user_func_array( $this->callable, $parameters );
	}
	public function match_uri( $uri ) {
		$pattern = str_replace( [ '.', '+', '?' ],  [ '\.', '\+', '\?' ], $this->match_uri ); 
		$pattern = preg_replace( '#\(([^)]*)\)#', '(?:\1)?', $pattern ); 
		$pattern = preg_replace( '#\:(\w+)#', '(?<\1>\w+)', $pattern ); 
		$pattern = '#^' . $pattern . '/?$#';
		$parameters = [ ];
		$result = preg_match( $pattern, $uri, $parameters );
		if ( $result ) {
			foreach ( array_keys( $parameters ) as $key ) {
				if ( is_numeric( $key ) ) {
					unset( $parameters[ $key ] );
				}
			}
		} else {
			$parameters = [ ];
		}
		$this->parameters = $parameters;
		return $result;
	}
	public function match_exception( $exception ) {
		return FALSE;
	}



	/*************************************************************************
	  PRIVATE METHODS             
	 *************************************************************************/
}