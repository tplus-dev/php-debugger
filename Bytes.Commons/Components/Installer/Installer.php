<?php

	/**
	 * Installer service
	 *
	 * @author Mark Hünermund Jensen
	 * @package Installer
	 */
	
	namespace Bytes\Services;

	class Installer extends \Bytes\Component {

	    /* ======================================================================================================
	       DECLARE OPTIONS
	    ====================================================================================================== */

	    /**
	     * Option method to declare expected options
	     *
	     * @access public
	     * @param \Bytes\Options $Options
	     * @return void
	     */
	    
	    public function DeclareOptions ( \Bytes\Options &$Options ) {

	        /* ------------------------------------------------------------------------------------------------------
	           DECLARE
	        ------------------------------------------------------------------------------------------------------ */

	        $Options -> Declare( 'Implementations' , [] ) -> Commit();
	        $Options -> Declare( 'Key' ) -> AsRequired() -> Commit();

	    }

	}