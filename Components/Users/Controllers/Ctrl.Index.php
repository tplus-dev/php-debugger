<?php

	namespace Bytes\Components\Users;

	class CtrlIndex extends \Bytes\Controller {

		/**
		 * IMPORTANT: We've had to alter the way options are injected into the Users model
		 * because the Bytes framework at this time, has an issue with passing options
		 */
		
		protected function UserModel ( ) {

	    	$MdlUser 		= $this -> Model( 'User' );

	    	$MdlUser -> SetConfiguration([
	    		'TestPassword' => $this -> Option( 'TestPassword' ),
	    		'SessionName' => $this -> Option( 'SessionName' )
	    	]);

	    	return $MdlUser;

		}

		/**
		 * 
		 */
		
		public function GetAccountLoginDetails ( $GlobalScope ): array {

			return [];

		}

	    /**
	     * 
	     */
	    
	    public function Login ( $GlobalScope ): array {

	    	$this -> UserModel() -> LoginWithCredentials(
	    		(string) $GlobalScope -> POST( 'Email' ),
	    		(string) $GlobalScope -> POST( 'Password' )
	    	);

	    	return [];

	    }

	    /**
	     * 
	     */
	    
	    public function Logout ( $GlobalScope ) {

	    	$_SESSION[ $this -> Option( 'SessionName' ) ] 	= Null;

	    	header( 'Location: ' . $this -> Environment() -> URL( '/' ) );
	    	exit;

	    }

	    /**
	     * 
	     */
	    
	    public function RecoverPassword ( ): array {

	    	ds(4334);

	    	return [];

	    }

	    /**
	     * 
	     */
	    
	    public function CompleteRecoverPassword ( ): array {

	    	ds(4334);

	    }

	}