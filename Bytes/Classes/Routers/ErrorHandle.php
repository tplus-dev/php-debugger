<?php

	/**
	 * Error handle route type
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @package Routers
	 * @since v1.2.0
	 */
	
	namespace Bytes\RouterGuides;

	class ErrorHandle extends \Bytes\RouterGuide {

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * Class constructor
	     *
	     * @access public
	     * @param string $Pattern
	     * @return void
	     */
	    
	    public function __construct ( string $Pattern ) {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Pattern 		= 'ERROR/' . $Pattern;

	    }

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

	        return $this -> Pattern == $Input ? True : False;

	    }
		
	}