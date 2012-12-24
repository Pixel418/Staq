<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Packadata\Kernel\Object;

class Template extends Template\__Parent {



	/*************************************************************************
	  ACCESSOR                 
	 *************************************************************************/
	public function by_model( $model, $mode ) {
		$type = \Supersoniq\class_subtype( $model );
		return $this->by_name( 'Model\\' . ucfirst( $mode ) . '\\' . $type )
			->set( 'content', $model );
	}

	public function by_data_type( $data_type, $mode ) {
		return $this->by_name( 'Data_Type\\' . ucfirst( $mode ) . '\\' . $data_type->get_data_type( ) )
			->set( 'content', $data_type );
	}
}