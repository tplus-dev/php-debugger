<?php

	/**
	 * Visual abstraction class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	abstract class Visual extends ExtendedClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Store the location of the component that the controller belongs to
	     * @var string
	     */
	    
	    protected $__ComponentLocation;

	    /**
	     * Container for embedded data
	     * @var array
	     */
	    
	    protected $__EmbeddedData			= [];

	    /* ======================================================================================================
	       ABSTRACT METHODS
	    ====================================================================================================== */

	    /* ------------------------------------------------------------------------------------------------------
	       RENDER
	    ------------------------------------------------------------------------------------------------------ */

	    /**
	     * Function that, using various internal method, outputs a string
	     *
	     * @abstract
	     * @access public
	     * @return string
	     */
	    
	    abstract public function Render ( ): string;

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * Prepare the visual class
	     * 
	     * @final Make sure the constructor method cannot be overriden
	     * @access public
	     * @return void
	     */
	    
	    final public function __construct ( string $ComponentLocation ) {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	    	$this -> __ComponentLocation 		= (string) $ComponentLocation;

	    }

	    /* ======================================================================================================
	       HEADER
	    ====================================================================================================== */

	    /**
	     * Configure the header suitable to the view.
	     * Reconfigure the header for this view.
	     *
	     * @access public
	     * @param \Bytes\Header $Header
	     * @return void
	     */
	    
	    public function Header ( \Bytes\Header &$Header ) { 

	    	// This method can be overriden
	    	// For now it does nothing

	    }

	    /* ======================================================================================================
	       DEPENDENCIES
	    ====================================================================================================== */

	    /**
	     * Define dependencies
	     *
	     * @final
	     * @param \Bytes\Dependencies $Dependencies
	     * @access public
	     * @return void
	     */

	    final public function Dependencies (

	    	\Bytes\Dependencies &$Dependencies,
	    	\Bytes\Environment &$Environment,
	    	array $Options = []

	    ) {

	        /* ------------------------------------------------------------------------------------------------------
	           DECLARE
	        ------------------------------------------------------------------------------------------------------ */

	    	$Dependencies -> IfAvailable() -> ProvideGroup( 'Parsers' );

	    }

	    /* ======================================================================================================
	       EMBED
	    ====================================================================================================== */

	    /**
	     * Embed data in the visual
	     *
	     * @access public
	     * @param string $Key
	     * @param mixed $Value
	     * @return Visual
	     */
	    
	    public function Embed ( string $Key , $Value ): Visual {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> __EmbeddedData[ $Key ] 	= $Value;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SCOPE
	    ====================================================================================================== */

	    /**
	     * Creates an instance of the Scope class from this component
	     *
	     * @final
	     * @access protected
	     * @param string $ScopeName
	     * @since v1.3.0
	     * @return \Bytes\Scope
	     */
	    
	    final protected function Scope ( string $ScopeName ): \Bytes\Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $Location 			= sprintf(

	    							'%s/Scopes/Scope.%s.php',

	    							rtrim( $this -> __ComponentLocation , '/' ),
	    							$ScopeName

	    						);

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        $Instance 			= new \Bytes\Scope(

	        						$Location,
	        						$this -> __EmbeddedData,
	        						$this -> __InjectionContainer -> Retrieve( 'Environment' ),
	        						$this -> __InjectionContainer -> Retrieve( 'Header' )

	        					);

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Instance;

	    }

	    /* ======================================================================================================
	       LOAD TEMPLATE
	    ====================================================================================================== */

	    /**
	     * Loads a template, passes embedded data and let's PHP parse it
	     *
	     * @access protected
	     * @param string $Template
	     * @throws \Bytes\FileNotFoundException Raised if the template is not found
	     * @return string
	     */
	    
	    protected function LoadTemplate ( string $Template ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $TemplateLocation 		= sprintf(

	        							'%s/Templates/Template.%s.php',

	        							$this -> __ComponentLocation,
	        							$Template

	        						);

	        // Ensure the file exists

	        if ( ! file_exists( $TemplateLocation ) ) {

	        	throw new FileNotFoundException( 'Template not found at: ' . $TemplateLocation );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        // We execute this in an isolated anonymous function, so that the template file cannot access
	        // this object

	        return ( function ( $Filename , $Data , $Parsers , $Env ) {
	        	
	        	ob_start();

	        	// The template must be callable multiple times, so we use require instead of require_once

	        	require 		$Filename;

	        	// Return the output buffer content

	        	return ob_get_clean();

	        } )(

	        	$TemplateLocation,
	        	$this -> __EmbeddedData,
	        	$this -> __InjectionContainer -> RetrieveGroup( 'Parsers' ),
	        	$this -> __InjectionContainer -> Retrieve( 'Environment' )

	        );

	    }

	}