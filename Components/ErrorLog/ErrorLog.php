<?php

	/**
	 * Discovery component
	 */
	
	namespace Bytes\Components\ErrorLog;

	class ErrorLog extends \Bytes\Component {

	    /**
	     * Define the routes used by this component
	     *
	     * @access protected
	     * @param \Bytes\Router $Router
	     * @return void
	     */

	    protected function CreateRoutes ( \Bytes\Router &$Router ) {

	        $Router -> RequestType( 'GET' ) -> CreateRoute( '/^\/?$/' , 'Index' , 'Index' );
	        $Router -> RequestType( 'GET' ) -> CreateRoute( '/^db\-install\/?$/' , 'Index' , 'Install' );


	    }

	}