<?php

	/**
	 * Error handler component
	 */

	namespace Bytes\Components\ErrorHandler;

	final class CtrlIndex extends \Bytes\Controller {

	    /* ======================================================================================================
	       DEFAULT
	    ====================================================================================================== */

	    /**
	     * The default method for handling errors
	     *
	     * @access public
	     * @param \Bytes\GlobalScope $GlobalScope
	     * @param \Exception $Exception
	     * @param \Bytes\Environment $Environment
	     * @param \Bytes\Header $Header
	     * @return string
	     */

		public function Default ( $GlobalScope , $Exception = Null , $Environment = Null , $Header = Null ) {

		    /* ------------------------------------------------------------------------------------------------------
		       RETURN
		    ------------------------------------------------------------------------------------------------------ */

			return [

				'Success' 		=> False,
				'Error' 		=> $Exception -> getMessage()

			];

		}

	}