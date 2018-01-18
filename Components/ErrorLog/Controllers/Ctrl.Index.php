<?php

	namespace Bytes\Components\ErrorLog;

	class CtrlIndex extends \Bytes\Controller {

	    /**
	     * This method can be overridden to define dependencies for the class.
	     * These dependencies will then be injected by the ObjectBuilder.
	     *
	     * @access public
	     * @param \Bytes\Dependencies $Dependencies
	     * @return void
	     */
	    
	    public function Dependencies (

	    	\Bytes\Dependencies &$Dependencies,
	    	\Bytes\Environment &$Environment,
	    	array $Context = [] 

	    ) {

	    	switch ( $Context[ 'Method' ] ) {

	    		case 'Install':
	    			$Dependencies -> Must() -> Provide( 'Installer' );
	    			break;

	    	}

	    }

	    /**
	     * Activate the database installer
	     *
	     * NOTE: It is highly recommended to protect this method by ak ey, IP or similar
	     *
	     * @access public
	     * @return string
	     */
	    
	    public function Install ( ): string {

	        $View = $this -> Employ( 'Installer' ) -> Controller( 'Installer' , 'ShowIntentions' );
	    	return $View;

	    }

	    /**
	     * 
	     */
	    
	    public function Index ( ) {

	    	$View = $this -> Visual( 'Default' );
	    	$View -> Embed( 'Template' , 'Frontpage' );

	    	return $View -> Render();

	    }

	}