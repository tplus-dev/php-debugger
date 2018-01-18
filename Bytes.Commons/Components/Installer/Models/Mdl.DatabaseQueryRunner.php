<?php

	/**
	 * Database query factory class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes\Components\Installer;

	class MdlDatabaseQueryRunner extends \Bytes\Model {

	    /* ======================================================================================================
	       DEPENDENCIES
	    ====================================================================================================== */

	    /**
	     * Define the dependencies required under various circumstances in this controller
	     *
	     * @access public
	     * @param \Bytes\Dependencies $Dependencies
	     * @param \Bytes\Environment $Environment
	     * @param array $Context
	     * @return void
	     */
	    
	    public function Dependencies (

	    	\Bytes\Dependencies &$Dependencies,
	    	\Bytes\Environment &$Environment,
	    	array $Context = [] 

	    ) {

	    	$Dependencies -> Must() ->  Provide( 'Database' );

	    }

	    /* ======================================================================================================
	       INVOKE QUERIES
	    ====================================================================================================== */

	    /**
	     * Blindly executes an array of queries. Returns a list with queries, and eventual error codes
	     *
	     * @access public
	     * @param array $Queries List of queries to be executed
	     * @return array
	     */
	    
	    public function RunQueries ( array $Queries ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Results 				= [];

	        /* ------------------------------------------------------------------------------------------------------
	           EXECUTE
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Queries as $I => $Query ) {

	        	try {

	        		$this -> Employ( 'Database' ) -> Query( $Query );

	        		$Error 			= $this -> Employ( 'Database' ) -> GetError();

	        		if ( $Error ) {

	        			throw new \Exception( $Error[ 'Message' ] );

	        		}

	        		array_push( $Results , [

	        			'Query' 		=> $Query,
	        			'Error' 		=> False

	        		] );

	        	} catch ( \Exception $E ) {

	        		array_push( $Results , [

	        			'Query' 		=> $Query,
	        			'Error' 		=> $E -> getMessage()

	        		] );

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Results;

	    }

	}