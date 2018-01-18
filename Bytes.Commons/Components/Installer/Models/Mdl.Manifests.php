<?php

	/**
	 * 
	 */
	
	namespace Bytes\Components\Installer;

	class MdlManifests extends \Bytes\Model {

	    /* ======================================================================================================
	       COLLECT
	    ====================================================================================================== */

	    /**
	     * Collect the manifests from the provided list of implementations to be installed
	     *
	     * @access public
	     * @param array $Implementations
	     * @return array
	     */
	    
	    public function Collect ( array $Implementations ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $List 							= [];

	        /* ------------------------------------------------------------------------------------------------------
	           SCAN LOCATIONS
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Implementations as $I => $Implementation ) {

	        	$ExpectedLocation 			= sprintf(

		        								'%s/Blueprints/Manifest.%s.php',

		        								rtrim( $Implementation , '/' ),
		        								basename( $Implementation )

		        							);

	        	if ( file_exists( $ExpectedLocation ) ) {

	        		$List[ basename( $Implementation ) ] 	= $ExpectedLocation;

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $List;

	    }

	}