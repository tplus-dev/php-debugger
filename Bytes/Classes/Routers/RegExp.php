<?php

	/**
	 * Regular expression router guide
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @package Routers
	 * @since v1.2.0
	 */
	
	namespace Bytes\RouterGuides;

	class RegExp extends \Bytes\RouterGuide {

	    /* ======================================================================================================
	       MATCH
	    ====================================================================================================== */

	    /**
	     * @access public
	     * @param string $Input
	     * @return bool
	     */
	    
	    public function Match ( string $Input ): bool {

	        /* ------------------------------------------------------------------------------------------------------
	           COMPARE
	        ------------------------------------------------------------------------------------------------------ */

	        return preg_match( $this -> Pattern , $Input ) ? True : False;

	    }
		
	}