<?php

	/**
	 * Environment class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	class Environment extends StandardClass {

	    /* ======================================================================================================
	       COSTANTS
	    ====================================================================================================== */

	    const Development 			= 1 << 0;
	    const Test 					= 1 << 1;
	    const Staging 				= 1 << 2;
	    const Production 			= 1 << 3;

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Stores the defined URL
	     * @var mixed String when defined, null otherwise
	     */
	    
	    private $URL;

	    /**
	     * Stores the defined physical filepath
	     * @var mixed String when defined, null otherwise
	     */
	    
	    private $Path;

	    /**
	     * Container for paths with IDs
	     * @var array
	     */
	    
	    private $Paths 				= [];

	    /**
	     * Stores the the selected environment (default is development)
	     * @var int
	     */
	    
	    private $Environment 		= self::Development;

	    /**
	     * Storage of various "global" variables
	     * @var array
	     */
	    
	    private $Data 				= [];

	    /* ======================================================================================================
	       REGISTER PATH
	    ====================================================================================================== */

	    /**
	     * Registers a path by ID
	     *
	     * @since v1.2.0
	     * @access public
	     * @param string $PathId ID, for instance "Public" or "Private"
	     * @param string $Path
	     * @throws \Bytes\ConfigurationException Raised  if the $Path does not exist on current server
	     * @return Environment
	     */
	    
	    public function RegisterPath ( string $PathId , string $Path ): Environment {

	        /* --------------------------------------------------------------------------------------------------
	           VALIDATE
	        -------------------------------------------------------------------------------------------------- */

	        if ( ! is_dir( $Path ) ) {

	        	throw new ConfigurationException( 'No directory found at: ' . $Path );
 
	        }

	        /* --------------------------------------------------------------------------------------------------
	           SET
	        -------------------------------------------------------------------------------------------------- */

	        $this -> Paths[ $PathId ] 		= $Path;

	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        return $this;

	    }

	    /* ======================================================================================================
	       GET (DATA)
	    ====================================================================================================== */

	    /**
	     * Retrieves data from the "global" storage
	     *
	     * @access public
	     * @param string $Key Key for the value you want to retrieve
	     * @return mixed
	     */
	    
	    public function Get ( string $Key ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Data[ $Key ];

	    }

	    /* ======================================================================================================
	       SET (DATA)
	    ====================================================================================================== */

	    /**
	     * Sets data in the "global" storage
	     *
	     * @access public
	     * @param string $Key Key
	     * @param mixed $Value Content for the storage
	     * @return Environment
	     */
	    
	    public function Set ( string $Key , $Value ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Data[ $Key ] 	= $Value;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SET URL
	    ====================================================================================================== */

	    /**
	     * Define a URL for the application
	     *
	     * @access public
	     * @param string $URL The URL root for the your project
	     * @throws \Bytes\ConfigurationException Raised when the URL is not valid
	     * @return Environment Chainable
	     */
	    
	    public function SetURL ( string $URL ): Environment {

	        /* --------------------------------------------------------------------------------------------------
	           VALIDATE
	        -------------------------------------------------------------------------------------------------- */

	        if ( ! filter_var( $URL , FILTER_VALIDATE_URL ) ) {

	        	throw new ConfigurationException(

	        		'URL for environment is not valid.',
	        		'Environment.SetURL.Invalid'

	        	);

	        }

	        /* --------------------------------------------------------------------------------------------------
	           SET
	        -------------------------------------------------------------------------------------------------- */

	        $this -> URL 			= (string) $URL;

	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        return $this;

	    }

	    /* ======================================================================================================
	       URL
	    ====================================================================================================== */

	    /**
	     * Return the defined URL with an optional URI
	     *
	     * @access public
	     * @param string $URI
	     * @throws \Bytes\ConfigurationException Raised when the URL is not defined
	     * @return string
	     */
	    
	    public function URL ( string $URI = '' ): string {

	        /* --------------------------------------------------------------------------------------------------
	           VALIDATE
	        -------------------------------------------------------------------------------------------------- */

	        // If the URL is not defined, we want to raise an exception
	        
	        if ( is_null( $this -> URL ) ) {

	        	throw new ConfigurationException(

	        		'You cannot use Environment::URL before configuring a URL.',
	        		'Environment.URL.NotDefined'

	        	);

	        }

	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        // Ensure there can be a slash between URL and URI, and then return the result

	        return rtrim( $this -> URL , '/' ) . '/' . ltrim( $URI , '/' );

	    }

	    /* ======================================================================================================
	       SET PATH
	    ====================================================================================================== */

	    /**
	     * Sets the absolute path to project root
	     *
	     * @access public
	     * @param string $Directory Directory to use as project root
	     * @throws \Bytes\ConfigurationException Raised when the directory does not exist
	     * @return Environment Chainable
	     */
	    
	    public function SetPath ( string $Directory ): Environment {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_dir( $Directory ) ) {

	        	throw new ConfigurationException( sprintf(

	        		'Cannot set %s as root project directory.',

	        		$Directory

	        	) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Path 			= (string) $Directory;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       PATH
	    ====================================================================================================== */

	    /**
	     * Returns a path with an eventual sub-directory location
	     *
	     * @access public
	     * @param string $SubDirectory (Optional) A deeper path inside the project's root directory
	     * @throws \Bytes\ConfigurationException Raised if the root path has not been defined
	     * @throws \Bytes\Exception Raised if the $SubDirectory contains illegal characters, such as ".."
	     * @return string
	     */
	    
	    public function Path ( string $SubDirectory = '' ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        // Check that the root directory has been defined

	        if ( is_null( $this -> Path ) ) {

	        	throw new ConfigurationException( 'You have not defined a default path in Environment' );

	        }

	        // Check for illegal character ".."

	        if ( strpos( $SubDirectory , '..' ) !== False ) {

	        	throw new Exception( 'You cannot use ".." in directory paths.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        // Create with respect to slash between root and sub-directory

	        return rtrim( $this -> Path , '/' ) . '/' . ltrim( $SubDirectory , '/' );

	    }

	    /* ======================================================================================================
	       PATH BY ID
	    ====================================================================================================== */

	    /**
	     * Retrive a path based on an ID
	     *
	     * @since v1.2.0
	     * @access public
	     * @param string $Id
	     * @param string $SubDirectory
	     * @throws \Bytes\ConfigurationException Raised if the path ID is not defined
	     * @throws \Bytes\Exception Raised if the $SubDirectory contains illegal characters, such as ".."
	     * @return string
	     */
	    
	    public function PathById ( string $PathId , string $SubDirectory = '' ): string {

	        /* --------------------------------------------------------------------------------------------------
	           VALIDATE
	        -------------------------------------------------------------------------------------------------- */

	        if ( ! isset( $this -> Paths[ $PathId ] ) ) {

	        	throw new ConfigurationException( 'No path has been defined by ID: ' . $PathId );

	        }

	        // Check for illegal character ".."

	        if ( strpos( $SubDirectory , '..' ) !== False ) {

	        	throw new Exception( 'You cannot use ".." in directory paths.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        // Create with respect to slash between root and sub-directory

	        return rtrim( $this -> Paths[ $PathId ] , '/' ) . '/' . ltrim( $SubDirectory , '/' );

	    }

	    /* ======================================================================================================
	       SET ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Defines the environment (such as development, staging or production) to carry out different
	     * measurements through-out the project.
	     *
	     * @access public
	     * @param int $Environment One of the available environment flags (see list of constants)
	     * @throws \Bytes\ConfigurationException Raised when flag is not a known constant/value
	     * @return Environment
	     */
	    
	    public function SetEnvironment ( int $Environment ): Environment {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! in_array( $Environment , [ self::Development , self::Test , self::Staging , self::Production ] ) ) {

        		throw new ConfigurationException( '$Environment in Environment::SetEnvironment is unknown.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Environment 		= (int) $Environment;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Returns the integer/flag value of the current environment
	     *
	     * @access public
	     * @return int Flag for the environment
	     */
	    
	    public function GetEnvironment ( ): int {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment;

	    }

	    /* ======================================================================================================
	       IS DEVELOPMENT
	    ====================================================================================================== */

	    /**
	     * Returns true if environment is configured as development
	     *
	     * @access public
	     * @return bool
	     */
	    
	    public function IsDevelopment ( ): bool {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment === self::Development ? True : False;

	    }

	    /* ======================================================================================================
	       IS TEST
	    ====================================================================================================== */

	    /**
	     * Returns true if environment is configured as test
	     *
	     * @access public
	     * @return bool
	     */
	    
	    public function IsTest ( ): bool {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment === self::Test ? True : False;

	    }

	    /* ======================================================================================================
	       IS STAGING
	    ====================================================================================================== */

	    /**
	     * Returns true if environment is configured as staging
	     *
	     * @access public
	     * @return bool
	     */
	    
	    public function IsStaging ( ): bool {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment === self::Staging ? True : False;

	    }

	    /* ======================================================================================================
	       IS PRODUCTION
	    ====================================================================================================== */

	    /**
	     * Returns true if environment is configured as production
	     *
	     * @access public
	     * @return bool
	     */
	    
	    public function IsProduction ( ): bool {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment === self::Production ? True : False;

	    }

	}