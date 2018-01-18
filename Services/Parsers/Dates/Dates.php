<?php

	namespace Bytes\Services;

	class Dates extends \Bytes\Parser {

	    /* ======================================================================================================
	       MONEY
	    ====================================================================================================== */

	    /**
	     * Example method to parse money
	     *
	     * Zoom levels:
	     * 	0 		Timestamp with seconds
	     * 	20 		Month and year, unless relatively close; Then date, month and year
	     *
	     * @access public
	     * @param mixed $Amount
	     * @param int $Zoom
	     * @return string
	     */
	    
	    public function DateFormat ( $Input , int $Zoom = 0 ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        $Timestamp 			= strtotime( $Input );
	        $DaysFromNow 		= abs( $Timestamp - time() ) / ( 24 * 60 * 60 );

	        switch ( $Zoom ) {

	        	case 20:
	        		return $DaysFromNow > 50 ? date( 'F Y' , $Timestamp ) : date( 'F jS Y' , $Timestamp );

	        	default:
	        		return date( 'F jS, Y' , $Timestamp );

	        }

	    }

	}