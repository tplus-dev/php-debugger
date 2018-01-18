<?php

	namespace Bytes\Services;

	class Numbers extends \Bytes\Parser {

	    /* ======================================================================================================
	       MONEY
	    ====================================================================================================== */

	    /**
	     * Example method to parse money
	     *
	     * @access public
	     * @param mixed $Amount
	     * @param string $Currency
	     * @return string
	     */
	    
	    public function NumberFormat ( $Amount , int $Decimals = 0 ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return number_format( $Amount , 0 , ',' , '.' );

	    }

	}