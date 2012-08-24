<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Packadata\Kernel\Data_Type;

class Relation extends \Data_Type\__Base {


	/*************************************************************************
	  ATTRIBUTES                 
	 *************************************************************************/
	protected $initialized = FALSE;
	protected $definition;
	protected $relations = [ ];


	/*************************************************************************
	  USER GETTER & SETTER             
	 *************************************************************************/
	public function get_ids( ) {
		$ids = [ ];
		$relateds = $this->get( );
		foreach ( $relateds as $related ) {
			$ids[ ] = $related->id;
		}
		return $ids;
	}

	public function get( ) {
		$this->check_initialized( );
		$relateds = [ ];
		foreach ( $this->relations as $relation ) {
			$relateds[ ] = $relation->get( );
		}
		return new \Object_List( $relateds );
	}

	public function set( $relateds ) {
		if ( ! empty( $relateds ) ) {
			$this->initialized = TRUE;
			\Supersoniq\must_be_array( $relateds );
			$this->relations = [ ];
			foreach ( $relateds as $related ) {
				$this->add( $related );
			}
		}
	}

	public function add( $related ) {
		$this->check_initialized( );
		if ( ! is_object( $related ) ) {
			$related = ( new \Model )
				->by_type( $this->definition->related_model_type )
				->by_id( $related );
			if ( ! $related->exists( ) ) {
				throw new \Exception( 'Relation setted with an unexisting model' );
			}
		}
		$this->definition->set( $related );
		$this->relations[ ] = clone $this->definition;
	}
	public function get_related_model( ) {
		return ( new \Model )
			->by_type( $this->definition->related_model_type )
			->all( );
	}


	/*************************************************************************
	  DATABASE GETTER & SETTER             
	 *************************************************************************/
	public function value( ) {
		return NULL;
	}
	public function init( $value ) {
	}


	/*************************************************************************
	  MODEL EVENT METHODS             
	 *************************************************************************/
	public function model_initialized( $model ) {
		$this->definition->set_model( $model );
	}
	public function model_saved( $model ) {
		$this->definition
			->set_model( $model )
			->delete_all( );
		foreach ( $this->relations as $relation ) {
			$relation->set_model( $model );
			$relation->save( );
		}
	}
	public function model_deleted( $model ) {
		$this->definition
			->set_model( $model )
			->delete_all( );
	}
	public function model_deleted_all( $model ) {
		$this->definition->set_model( $model )
			->model_deleted_all( $model );
	}


	/*************************************************************************
	  CONSTRUCTOR
	 *************************************************************************/
	public function __construct( $definition ) {
		parent::__construct( );
		$this->definition = $definition;
	}


	/*************************************************************************
	  PROTECTED METHODS  
	 *************************************************************************/
	protected function check_initialized( ) {
		if ( ! $this->initialized ) {
			$this->relations = $this->definition->all( );
			$this->initialized = TRUE;
		}
	}

}
