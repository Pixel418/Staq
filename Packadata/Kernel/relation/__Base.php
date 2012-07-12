<?php

namespace Supersoniq\Packadata\Kernel\Relation;

abstract class __Base extends \Database_Table {


	/*************************************************************************
	  ATTRIBUTES                 
	 *************************************************************************/
	public static $autoload_create_child = 'Relation\\__Base';
	const REVERSED = TRUE;
	protected $model;
	protected $related_model;
	protected $type;
	protected $_number;
	protected $_related_number;


	/*************************************************************************
	  GETTER & SETTER             
	 *************************************************************************/
	public function get( ) {
		return $this->related_model;
	}
	public function set( $related_model ) {
		$this->related_model = $related_model;
	}


	/*************************************************************************
	  CONSTRUCTOR
	 *************************************************************************/
	public function __construct( $is_reverse = FALSE ) {
		parent::__construct( );
		$this->set_is_reverse( $is_reverse );
		$this->type = \String::substr_after_last( get_class( $this ), '\\' );
		$this->_database->table_fields = array( 'id', 'model_id_1', 'model_type_1', 'model_id_2', 'model_type_2', 'type' );
		$this->_database->table_name = 'relations';
	}
	private function set_is_reverse( $is_reverse ) {
		$number = ( $is_reverse ) ? 2 : 1;
		$this->_number = $number;
		$this->_related_number = 3 - $number;
	}
	private function is_reverse( ) {
		return ( $this->_number == 2 );
	}


	/*************************************************************************
	  INITIALIZATION
	 *************************************************************************/
	public function set_model( $model ) {
		$this->model = $model;
	}

	
	/*************************************************************************
	  PUBLIC LIST METHODS
	 *************************************************************************/
	public function all( ) {
		if ( ! is_object( $this->model ) ) {
			return array( );
		}
		return $this->list_by_fields( array( 
			'type' => $this->type, 
			'model_id_' . $this->_number => $this->model->id,
			'model_type_' . $this->_number => $this->model->type,
		) );
	}

	
	/*************************************************************************
	  EXTENDED METHODS
	 *************************************************************************/
	protected function init_by_data( $data ) {
		if ( isset( $data[ 'type' ] ) && $data[ 'type' ] != $this->type ) {
			throw new \Exception( 'Try to initialize a "' . $this->type . '" relation with "' . $data[ 'type' ] . '" data.' );
		}
		if ( 
			( isset( $data[ 'model_id_'   . $this->_number ] ) && $data[ 'model_id_'   . $this->_number ] != $this->model->id   ) ||
			( isset( $data[ 'model_type_' . $this->_number ] ) && $data[ 'model_type_' . $this->_number ] != $this->model->type )
		) {
			throw new \Exception( 'Try to initialize a relation from "' . $this->model->type . ':' . $this->model->id . '" with "' . $data[ 'model_type_' . $this->_number ] . ':' . $data[ 'model_id_'   . $this->_number ] . '" data.' );
		}
		if ( isset( $data[ 'model_id_' . $this->_related_number ] ) && isset( $data[ 'model_type_' . $this->_related_number ] ) ) {
			$related_class_name = '\\Model\\' . $data[ 'model_type_' . $this->_related_number ];
			$this->related_model = new $related_class_name( );
			$this->related_model->init_by_id( $data[ 'model_id_' . $this->_related_number ] );
		}
		return parent::init_by_data( $data );
	}
	protected function table_fields_value( $field_name, $field_value = NULL ) {
		if ( $field_name == 'type' ) {
			return $this->type;
		} else if ( $field_name == 'model_id_' . $this->_number ) {
			return $this->model->id;
		} else if ( $field_name == 'model_type_' . $this->_number ) {
			return $this->model->type;
		} else if ( $field_name == 'model_id_' . $this->_related_number ) {
			return $this->related_model->id;
		} else if ( $field_name == 'model_type_' . $this->_related_number ) {
			return $this->related_model->type;
		}
		return parent::table_fields_value( $field_name ); 
	}
	protected function new_entity( ) {
		$class = get_class( $this );
		$relation = new $class( $this->is_reverse( ) );
		$relation->set_model( $this->model );
		return $relation;
	}
}