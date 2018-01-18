<?php

	/**
	 * Scope class
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @since v1.3.0
	 */
	
	namespace Bytes;

	class Scope {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Container for the DOM Document
	     * @var DOMDocument
	     */

	    private $DOMDocument;

	    /**
	     * Keep track of the DOM selection
	     * @var string
	     */
	    
	    private $Selector 			= '';

	    /**
	     * Data provided by visual
	     * @var array
	     */
	    
	    private $__EmbeddedData;

	    /**
	     * Environment
	     * @var Environment
	     */
	    
	    private $Environment;

	    /**
	     * Header
	     * @var Header
	     */
	    
	    private $Header;

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * @access public
	     * @param string $Filename
	     * @throws \Bytes\FileNotFoundException Raised if scope cannot be found
	     * @return void
	     */
	    
	    public function __construct (

	    	string $Filename,
	    	array $EmbeddedData = [],
	    	Environment $Environment,
	    	Header $Header

	    ) {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	    	if ( ! file_exists( $Filename ) ) {

	        	throw new FileNotFoundException( 'Scope not found at: ' . $Filename );

	        }

	        // Ensure DOM Document is installed

	        if ( ! class_exists( '\DOMDocument' ) ) {

	        	throw new \Bytes\ConfigurationException( 'DOMDocument is not installed on this server.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> __EmbeddedData 	= $EmbeddedData;
	        $this -> Environment 		= $Environment;
	        $this -> Header 			= $Header;

	        /* ------------------------------------------------------------------------------------------------------
	           HTML
	        ------------------------------------------------------------------------------------------------------ */

	        // We execute the scope HTML (which is actually PHP) inside an anonymous function to protect the class
	        // itself

	        $HTML 				= ( function ( $Filename , $Data , $Env ) {

	        						ob_start();

	        						require $Filename;

	        						return ob_get_clean();

	        					} )( $Filename , $EmbeddedData , $Environment );

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> DOMDocument 		= new \DOMDocument;

	        // We temporarily disable the error reporting, because the DOMDocument doesn't support even
	        // the common HTML5 tags (such as main, section, nav, etc.)

			libxml_use_internal_errors( True );

	        $this -> DOMDocument -> loadHTML( $HTML );

			libxml_clear_errors();

	    }

	    /* ======================================================================================================
	       CONFIGURE HEADER
	    ====================================================================================================== */

	    /**
	     * Runs configuration of header
	     *
	     * @access public
	     * @param callable $Handle
	     * @return Scope
	     */
	    
	    public function Header ( callable $Handle ): Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           HANDLE
	        ------------------------------------------------------------------------------------------------------ */

	        $Handle( $this -> Header );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       RENDER
	    ====================================================================================================== */

	    /**
	     * Render the DOM document
	     *
	     * @access public
	     * @return string
	     */
	    
	    public function Render ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> DOMDocument -> saveHTML();

	    }

	    /* ======================================================================================================
	       QUERY
	    ====================================================================================================== */

	    /**
	     * Select object(s) in the DOM for further processing
	     *
	     * @access public
	     * @param string $Query
	     * @return Scope
	     */
	    
	    public function Query ( string $Query ): Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Selector 		= $Query;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       REQUIRE SELECTOR
	    ====================================================================================================== */

	    /**
	     * Helper method to ensure the selector is set
	     *
	     * @access protected
	     * @throws \Bytes\ConfigurationException Raised if selector is not set
	     * @uses \DOMXpath::query
	     * @uses Scope::RewriteQuery
	     * @return array
	     */
	    
	    protected function GetElements ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           THROW
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> Selector ) {

	        	throw new \Bytes\ConfigurationException( 'Please set a selector first in the scope.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           FIND
	        ------------------------------------------------------------------------------------------------------ */

	        $Xpath 				= new \DOMXPath( $this -> DOMDocument );

	        $Elements 			= $Xpath -> query( $this -> RewriteQuery() );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Elements;

	    }

	    /* ======================================================================================================
	       REWRITE QUERY
	    ====================================================================================================== */

	    /**
	     * Rewrites a CSS/jQuery'esque selector into an xpath query
	     *
	     * @access protected
	     * @return string
	     */
	    
	    protected function RewriteQuery ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           REGULAR EXPRESSIONS
	        ------------------------------------------------------------------------------------------------------ */

	        $Result 				= '';

	        $RegExpId 				= '/^#([a-zA-Z_0-9]+)$/';
	        $RegExpTag 				= '/^([a-zA-Z\-]+)$/';

	        /* ------------------------------------------------------------------------------------------------------
	           INTERPRET
	        ------------------------------------------------------------------------------------------------------ */

	        if ( preg_match( $RegExpId , $this -> Selector ) ) {

	        	$Result 			= sprintf( '//*[@id=\'%s\']' , substr( $this -> Selector , 1 ) );

	        } else if ( preg_match( $RegExpTag , $this -> Selector ) ) {

	        	$Result 			= sprintf( '*/%s' , $this -> Selector );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Result;

	    }

	    /* ======================================================================================================
	       HTML
	    ====================================================================================================== */

	    /**
	     * Set selected objects with specified HTML
	     *
	     * @access public
	     * @param string $HTML
	     * @uses Scope::GetElements
	     * @return Scope
	     */
	    
	    public function HTML ( string $HTML ): Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $Elements 				= $this -> GetElements();

	        foreach ( $Elements as $I => $Element ) {

	        	$NewNode 			= $this -> DOMDocument -> createTextNode( $HTML );

	        	$Element -> nodeValue = '';

	        	$Element -> appendChild( $NewNode );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ADD CLASS
	    ====================================================================================================== */

	    /**
	     * Add a class to the selected elements
	     *
	     * @access public
	     * @param string $ClassName
	     * @uses Scope::GetElements
	     * @since v1.4.0
	     * @return Scope
	     */
	    
	    public function AddClass ( string $ClassName ): Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $Elements 				= $this -> GetElements();

	        foreach ( $Elements as $I => $Element ) {

	        	$ClassAttribute	 		= (string) $Element -> getAttribute( 'class' );
	        	$Classes 				= explode( ' ' , $ClassAttribute );

	        	if ( ! in_array( $ClassName , $Classes ) ) {

	        		$ClassAttribute 	.= ( $ClassAttribute ? ' ' : '' ) . $ClassName;

	        	}

	        	$Element -> setAttribute( 'class' , $ClassAttribute );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       REMOVE CLASS
	    ====================================================================================================== */

	    /**
	     * Removes a class from the selected elements
	     *
	     * @access public
	     * @param string $ClassName
	     * @uses Scope::GetElements
	     * @since v1.4.0
	     * @return Scope
	     */
	    
	    public function RemoveClass ( string $ClassName ): Scope {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $Elements 				= $this -> GetElements();

	        foreach ( $Elements as $I => $Element ) {

	        	$ClassAttribute	 		= (string) $Element -> getAttribute( 'class' );
	        	$Classes 				= explode( ' ' , $ClassAttribute );

	        	foreach ( $Classes as $J => $Class ) {

	        		if ( $Class == $ClassName ) {

	        			unset( $Classes[ $J ] );

	        		}

	        	}

	        	$Element -> setAttribute( 'class' , implode( ' ' , $Classes ) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }
	    


	}