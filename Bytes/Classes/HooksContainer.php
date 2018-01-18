<?php

	/**
	 * Hooks container
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @package Hooks
	 * @since v1.1.0
	 */
	
	namespace Bytes;

	class HooksContainer {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Declared hooks
	     * @var array
	     */
	    
	    private $DeclaredHooks 		= [];

	    /**
	     * Container
	     * @var array
	     */
	    
	    private $Container 			= [];

	    /* ======================================================================================================
	       DECLARE HOOKS FROM
	    ====================================================================================================== */

	    /**
	     * Adds a new directory to the list of known hooks, and scans the directory for files
	     *
	     * @access public
	     * @param string $Directory Directory to the hook(s)
	     * @param string $Namespace
	     * @throws \Bytes\FileNotFoundException Raised if the directory does not exist
	     * @throws \Bytes\ConfigurationException Raised if no namespace is given
	     * @return void
	     */
	    
	    public function DeclareHooksFrom ( string $Directory , string $Namespace ) {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_dir( $Directory ) ) {

	        	throw new FileNotFoundException( 'No directory found at : ' . $Directory );

	        } else if ( ! $Namespace ) {

	        	throw new ConfigurationException( 'Please provide a namespace when declaring hooks' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SCAN
	        ------------------------------------------------------------------------------------------------------ */

	        $AcceptedFiles 		= [];
	        $Files 				= glob( rtrim( $Directory , '/' ) . '/*' );

	        foreach ( $Files as $I => $Filename ) {

	        	$Basename 		= basename( $Filename );

	        	if ( is_file( $Filename ) && preg_match( '/^Hook\.[a-zA-Z0-9\.\-]+\.php$/' , $Basename ) ) {

	        		$HookName 						= substr( $Basename , 5 , -4 );

	        		$AcceptedFiles[ $HookName ] 	= [

	        											'Location' 			=> $Filename

	        										];

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD TO CONTAINER
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Container[ $Namespace ] 		= $AcceptedFiles;

	    }

	    /* ======================================================================================================
	       GET BY ID
	    ====================================================================================================== */

	    /**
	     * Return declaration information on a hook
	     *
	     * @access public
	     * @param string $Namespace
	     * @param string $HookName
	     * @return array
	     */
	    
	    public function GetById ( string $Namespace , string $HookName ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Container[ $Namespace ][ $HookName ];

	    }

	}