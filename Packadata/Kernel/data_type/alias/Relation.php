<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Packadata\Kernel\Data_Type\Alias;

class Relation extends Relation\__Parent {



	/*************************************************************************
	  ATTRIBUTES                 
	 *************************************************************************/
	public $definition;



	/*************************************************************************
	  CONSTRUCTOR
	 *************************************************************************/
	public function __construct( $definition, $provider = NULL ) {
		$this->definition = $definition;
		if ( is_null( $provider ) ) {
			$provider = function( $model ) {
				$relateds = new \Object_List;
				$relations = $this->definition->set_model( $model )->all( );
				foreach ( $relations as $relation ) {
					$relateds[ ] = $relation->get( );
				}
				return $relateds;
			};
		}
		parent::__construct( $provider, 'Relation' );
	}
}