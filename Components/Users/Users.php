<?php

	/**
	 * Discovery component
	 */
	
	namespace Bytes\Components\Users;

	class Users extends \Bytes\Component {

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

	        $Options -> Declare( 'TestPassword' , True ) -> Commit();
	        $Options -> Declare( 'SessionName' ) -> AsRequired() -> Commit();

	    }

	    /* ======================================================================================================
	       CREATE ROUTES
	    ====================================================================================================== */

	    /**
	     * Define the routes used by this component
	     *
	     * @access protected
	     * @param \Bytes\Router $Router
	     * @return void
	     */

	    protected function CreateRoutes ( \Bytes\Router &$Router ) {

	        /* ------------------------------------------------------------------------------------------------------
	           INDEX
	        ------------------------------------------------------------------------------------------------------ */

	        $Router -> RequestType( 'POST' ) -> CreateRoute( '/^users\/login\/?$/' , 'Index' , 'Login' );	        
	        $Router -> RequestType( 'POST' ) -> CreateRoute( '/^users\/recover\-password\/?$/' , 'Index' , 'RecoverPassword' );	
	        $Router -> RequestType( 'GET' ) -> CreateRoute( '/^users\/recover\-password\/complete\/?$/' , 'Index' , 'CompleteRecoverPassword' );
	        $Router -> RequestType( 'GET' ) -> CreateRoute( '/^users\/logout\/?$/' , 'Index' , 'Logout' );
	        $Router -> RequestType( 'GET' ) -> CreateRoute( '/^users\/get\-details\/?$/' , 'Index' , 'GetAccountLoginDetails' );

	    }

	}