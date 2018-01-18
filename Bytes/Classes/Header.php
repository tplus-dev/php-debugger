<?php

	/**
	 * Header class
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @since v1.1.0
	 */
	
	namespace Bytes;

	class Header {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Content type
	     * @var string
	     */
	    
	    private static $ContentType 		= 'text/html';

	    /**
	     * Charset
	     * @var string
	     */
	    
	    private static $Charset 			= 'utf-8';

	    /* ======================================================================================================
	       TEST HEADER
	    ====================================================================================================== */

	    /**
	     * Tests if headers are sent
	     *
	     * @access protected
	     * @throws \Bytes\ConfigurationException Raised if headers are sent
	     * @return void
	     */
	    
	    protected function TestHeader ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        if ( headers_sent() ) {

	        	throw new ConfigurationException( 'Cannot alter headers. They are already sent to the client.' );

	        }

	    }

	    /* ======================================================================================================
	       CONTENT TYPE
	    ====================================================================================================== */

	    /**
	     * Declare the content type
	     * 
	     * @access public
	     * @param string $ContentType
	     * @param string $Charset (Optional) Change the character encoding
	     * @uses Header::TestHeader
	     * @return Header
	     */
	    
	    public function ContentType ( string $ContentType , string $Charset = '' ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST HEADER
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> TestHeader();

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        self::$ContentType 	= $ContentType;

	        if ( $Charset ) {

	        	self::$Charset 	= $Charset;

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET HEADER
	        ------------------------------------------------------------------------------------------------------ */

	        header( sprintf( 'Content-Type: %s; charset=%s' , self::$ContentType , self::$Charset ) );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	}