<?php

	/**
	 * Error handler component example
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @package ErrorHandler
	 */

	namespace Bytes\Components\ErrorHandler;

	use Bytes\RouterGuides\ErrorHandle AS ErrorHandle;

	class ErrorHandler extends \Bytes\Component {

		protected function CreateRoutes ( \Bytes\Router &$Router ) {

	    	// $Router -> CreateRoute( new ErrorHandle( 'JSON' ) , 'Index' , 'JSONError' );

	    }

	}