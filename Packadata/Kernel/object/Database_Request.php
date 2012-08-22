<?php

/* This file is part of the Supersoniq project.
 * Supersoniq is a free and unencumbered software released into the public domain.
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Supersoniq\Packadata\Kernel\Object;

class Database_Request {


	/*************************************************************************
	  ATTRIBUTES                 
	 *************************************************************************/
	public static $database_driver;
	public static $database_host;
	public static $database_name;
	public static $database_user;
	public static $database_password;
	public $request;
	private $PDObject;
	private $last_insert_id = false;
	public static $count = 0;


	/*************************************************************************
	  CONSTRUCTOR                   
	 *************************************************************************/
	public function __construct( $request = '' ) {
		// echo $request . HTML_EOL;
		$this->request = $request;
	}
	
	

	/*************************************************************************
	  PUBLIC METHODS                   
	 *************************************************************************/
	public function execute_one( $arguments = array( ) ) {
		$result = $this->execute( $arguments );
		if ( count( $result ) > 0 ) {
			$result = $result[ 0 ];
		}
		return $result;
	}
	public function execute( $arguments = array( ) ) {
		// self::$count++;
		// echo 'Request ' . self::$count . HTML_EOL;
		if ( empty( $this->request ) ) {
			throw new \Exception\Database( 'The SQL request is empty.' );
		}
		
		$result = array( );
		try {

			// Prepare the request
			$this->connect( );
			$statement = $this->PDObject->prepare( $this->request );
			foreach( $arguments as $parameter => $value ) {
				$statement->bindValue( $parameter, $value );
			}
			$result = $statement->execute( );

			// Execute the request
			if ( ! $result ) {
				throw new \Exception\Database( 'Error with the SQL request : ' . $this->request, $statement->errorInfo( ) );
			}
		
			if ( 
				\Supersoniq\i_starts_with($this->request, "SELECT") || 
				\Supersoniq\i_starts_with($this->request, "SHOW") || 
				\Supersoniq\i_starts_with($this->request, "DESCRIBE") || 
				\Supersoniq\i_starts_with($this->request, "EXPLAIN ")
			) {
				$result = $statement->fetchAll( \PDO::FETCH_ASSOC );
			} else if ( \Supersoniq\i_starts_with( $this->request, "INSERT" ) ) {
				$id = $this->PDObject->lastInsertId( );
				if ( $id == '0' ) {
					// Error Case or a table without autoincrementation
					$id = false;
					$result = false;
				} else {
					$result = true;
				}
				$this->last_insert_id = $id;
			}
			
		} catch ( PDOException $exception ) {
			throw new Database_Exception( $exception->getMessage( ) );
		}
		$this->disconnect( );
		return $result;
	}
	
	public function last_insert_id( ) {
		return $this->last_insert_id;
	}


	/*************************************************************************
	  PRIVATE METHODS                   
	 *************************************************************************/
	private function connect( ) {
		$db = ( new \Settings )->by_file( 'database' );
		$this->PDObject = new \PDO(
			$db->get( 'access', 'driver' ) . ':host=' . $db->get( 'access', 'host' ) . ';dbname=' . $db->get( 'access', 'name' ),
			$db->get( 'access', 'user' ),
			$db->get( 'access', 'password' ),
			array( \PDO::ATTR_PERSISTENT => true )
		);
		$this->PDObject->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
	}
	private function disconnect( ) {
		$this->PDObject = null;
	}
}




