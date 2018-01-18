<?php

	/**
	 * Implementation abstract class
	 *
	 * @author Mark Hünermund Jensen
	 */

	namespace Bytes;

	abstract class Implementation extends StandardClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Store the directory for later retrieval
	     * @var string
	     */
	    
	    protected $__Directory 			= '';

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * Construct the object, and receive the Options object
	     *
	     * @final
	     * @access public
	     * @param \Bytes\Options $Options
	     * @return void
	     */

	    final public function __construct ( ) {

	    }

	    /* ======================================================================================================
	       SET DIRECTORY
	    ====================================================================================================== */

	    /**
	     * Store the working directory of the implementation for later use
	     *
	     * @final
	     * @access public
	     * @param string $Location
	     * @return Component
	     */
	    
	    final public function SetDirectory ( string $Location ): Implementation {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> __Directory 		= (string) $Location;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       GET DIRECTORY
	    ====================================================================================================== */

	    /**
	     * Get the directory of the component (files and sub-directories can be optionally appended)
	     *
	     * @final
	     * @access public
	     * @param string $Path (Optional) File or sub-directory
	     * @return string
	     */

	    final public function GetDirectory ( string $Path = '' ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return rtrim( $this -> __Directory , '/' )  . '/' . ltrim( $Path , '/' );

	    }

	    /* ======================================================================================================
	       GET NAME
	    ====================================================================================================== */

	    /**
	     * Helper method to get the component name
	     *
	     * @final
	     * @access protected
	     * @return string
	     */
	    
	    final protected function GetName ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return get_class( $this );

	    }

	    /* ======================================================================================================
	       LOAD ASSET
	    ====================================================================================================== */

	    /**
	     * Loads an asset from the implementation folder
	     *
	     * @final
	     * @access protected
	     * @param string $Filename Filename of the asset (from the root of the implementation)
	     * @uses \Bytes\Implementation::GetDirectory
	     * @throws \Bytes\ImplementationException Raised if the asset cannot be found
	     * @return \Bytes\Implementation
	     */
	    
	    final protected function LoadAsset ( string $Filename ): Implementation {

	        /* ------------------------------------------------------------------------------------------------------
	           PATH
	        ------------------------------------------------------------------------------------------------------ */

	        $Path 				= $this -> GetDirectory( $Filename );

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK IF FILE EXISTS
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_file( $Path ) ) {

	        	throw new ImplementationException( 'No asset found at: ' . $Path );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           LOAD
	        ------------------------------------------------------------------------------------------------------ */

	        require_once 		$Path;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	}