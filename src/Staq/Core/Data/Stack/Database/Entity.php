<?php

/* This file is part of the Staq project, which is under MIT license */


namespace Staq\Core\Data\Stack\Database;

class Entity {


	/*************************************************************************
	  ATTRIBUTES                 
	 *************************************************************************/
	protected $settings;
	protected $name;
	protected $table;
	protected $id_field;
	protected $fields;



	/*************************************************************************
	  CONSTRUCTOR
	 *************************************************************************/
	public function __construct( ) {
		$this->settings = ( new \Stack\Setting )->parse( $this );
		$this->name     = strtolower( str_replace( '\\', '_', \Staq\Util::stack_sub_query( $this ) ) );
		$this->table    = $this->settings->get( 'database.table', $this->name );
		$this->id_field = $this->settings->get( 'database.id_field', 'id' );
		$this->fields   = $this->settings->get_as_array( 'database.id_field' );
	}


	/*************************************************************************
	  INITIALIZATION          
	 *************************************************************************/
	public function get_id_by_data( $data ) {
		if( isset( $data[ $this->id_field ] ) ) {
			return $data[ $this->id_field ];
		}
	}

	public function get_data_by_id( $id ) {
		return $this->get_data_by_fields( [ $this->id_field => $id ] );
	}

	public function get_data_by_fields( $fields = [ ] ) {
		$datas = $this->get_datas_by_fields( $fields );
		if ( isset( $datas[ 0 ] ) ) {
			return $datas[ 0 ];
		}
		return FALSE;
	}

	public function get_datas_by_fields( $fields = [ ] ) {
		$parameters = [ ];
		$sql = 'SELECT * FROM ' . $this->table . $this->get_clause_by_fields( $fields, $parameters );
		$request = new Request( $sql );
		return $request->execute( $parameters );
	}

	public function delete_by_fields( $fields ) {
		$parameters = [ ];
		$sql = 'DELETE FROM ' . $this->table . $this->get_clause_by_fields( $fields, $parameters );
		$request = new Request( $sql );
		return $request->execute( $parameters );
	}


	/*************************************************************************
	  PUBLIC DATABASE REQUEST
	 *************************************************************************/
	public function delete( $model ) {
		if ( $model->exists( ) ) {
			$sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->id_field . '=:id;';
			$request = new Request( $sql );
			return $request->execute_one( array( ':id' => $model->id ) );
		}
	}

	public function save( $model ) {
		if ( $model->exists( ) ) {
			$sql = 'UPDATE ' . $this->table
			. ' SET ' . $this->get_set_request( $model )
			. ' WHERE `' . $this->id_field . '` = :' . $this->id_field . ' ;';
			$request = new Request( $sql );
			$request->execute_one( $this->get_bind_params( $model ) );
			return $model->id;
		} else {
			$sql = 'INSERT INTO ' . $this->table
			. ' (`' . implode( '`, `', $this->fields ) . '`) VALUES'
			. ' (:' . implode( ', :', $this->fields ) . ');';
			$request = new Request( $sql );
			$request->execute_one( $this->get_bind_params( $model ) );
			return $request->last_insert_id( );
		}
	}

	
	/*************************************************************************
	  PRIVATE METHODS
	 *************************************************************************/
	protected function get_clause_by_fields( $fields, &$parameters ) {
		$where = [ ];
		$limit = NULL;
		foreach ( $fields as $fields_name => $field_value ) {
			if ( is_array( $field_value ) ) {
				if ( isset( $field_value[ 'parameters' ] ) ) {
					$parameters = array_merge( $parameters, $field_value[ 'parameters' ] );
				}
				if ( isset( $field_value[ 'limit' ] ) ) {
					$limit = $field_value[ 'limit' ];
				} else if ( isset( $field_value[ 'where' ] ) ) {
					$where[ ] = '( ' . $field_value[ 'where' ] . ' )';
				} else if ( isset( $field_value[ 0 ] ) && isset( $field_value[ 1 ] ) ) {
					$where[ ] = $fields_name . $field_value[ 0 ] . ':' . $fields_name;
					$parameters[ ':' . $fields_name ] = $field_value[ 1 ];
				}
			} else {
				$where[ ] = $fields_name . '=:' . $fields_name;
				$parameters[ ':' . $fields_name ] = $field_value;
			}
		}
		$sql = '';
		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode ( ' AND ', $where );
		}
		if ( ! is_null( $limit ) ) {
			$sql .= ' LIMIT ' . $limit;
		}
		return $sql . ';';
	}

	protected function get_set_request( ) {
		$request = [ ];
		foreach ( $this->fields as $field_name ) {
			$request[ ] = '`' . $field_name . '` = :' . $field_name;
		}
		return implde( ', ', $request );
	}

	protected function get_bind_params( $model ) {
		$bind_params = [ ];
		foreach ( $this->get_current_data( $model ) as $field_name => $field_value ) {
			$bind_params[ ':' . $field_name ] = $field_value;
		}
		return $bind_params;
	}

	protected function get_current_data( $model ) {
		return $model->getArrayCopy( );
		// DO: Manage serializes extra fields here ;)
	}
}
