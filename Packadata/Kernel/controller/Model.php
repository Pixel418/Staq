<?php

namespace Supersoniq\Packadata\Kernel\Controller;

class Model extends \Controller\Model_Unversioned {


	/*************************************************************************
	 ACTION METHODS
	*************************************************************************/
	public function archives( $type = NULL ) {
		$archives = ( new \Model_Archive( ) )->all( $type );
		return $archives;
	}
	public function archive( $id ) {
		$model = $this->model( );
		$model->init_by_id( $id );
		$archives = ( new \Model_Archive( ) )->get_model_history( $id, $this->type );
		return $archives;
	}
	public function see( $id, $versions ) {
		$archive = ( new \Model_Archive( ) )->get_model_version( $id, $this->type, array( 'attributes' => $versions ) );
		return $archive;
	}
	public function erase ( $id, $versions = NULL ) {
		if ( isset( $versions ) ) {
			$archives = ( new \Model_Archive( ) )->get_model_version( $id, $this->type, array( 'attributes' => $versions  ) );
		} else {
			$archives = ( new \Model_Archive( ) )->get_model_history( $id, $this->type);
		}
		if ( $archives ) {
			if ( is_array( $archives ) ) {
				foreach ( $archives as $archive ) {
					$archive->delete( );
				}
				\Notification::push( 'Archives of this ' . $this->type . ' deleted with success ! ', \Notification::SUCCESS );
				return TRUE;
			} else {
				$deleted_version = $archives->model_type_version . '.' . $archives->model_attributes_version;
				$archives->delete( );
				\Notification::push( 'Version ' . $deleted_version . ' of this ' . $this->type . ' deleted with success ! ', \Notification::SUCCESS );
				return TRUE;
			}
		} else {
			\Notification::push( 'Archives not found !', \Notification::ERROR );
			return FALSE;
		}
	}
	public function restore ( $id, $versions ) {
		$force_insert = FALSE;
		if ( $archive = ( new \Model_Archive( ) )->get_model_version( $id, $this->type, array( 'attributes' => $versions ) ) ) {
			$model_restore = 'Model\\' . $this->type;
			$model = new $model_restore;
			if ( ! $model->init_by_id( $archive->model_id ) ) {
				$model->id = $archive->model_id;
				$model->type_version = $archive->model_type_version;
				$archive = $archive->last_version( $archive->model_id, $this->type );
				$model->attributes_version = $archive->model_attributes_version;
				$force_insert = TRUE;
			}
			foreach ( $archive->model_attributes as $attribute => $value ) {
				$model->set( $attribute, $value );
			}
			//TODO Decide if we keep the archives or not and what to do with the versions of the restored model
			$restored_version = $archive->model_type_version . '.' . $archive->model_attributes_version;
			if ( $model->save( $force_insert ) ) {
				\Notification::push( $this->type . ' version ' . $restored_version . ' restored with success ! ', \Notification::SUCCESS );
				return TRUE;
			}
			\Notification::push( $this->type . ' not restored !', \Notification::ERROR );
			return FALSE;
		} else {
			\Notification::push( 'Archive not found !', \Notification::ERROR );
			return FALSE;
		}
	}
}
