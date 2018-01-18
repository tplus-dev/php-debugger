<?php

	namespace Bytes\Services;

	class Money extends \Bytes\Parser {

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
	    
	    public function Currency ( $Amount , $Currency , $Decimals = 2 ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        $Thousands 			= $Currency == 'SEK' ? ' ' : '.';

	    	return $Currency . ' ' . number_format( $Amount , $Decimals , ',' , $Thousands );

	    }

	}