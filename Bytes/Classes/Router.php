<?php

	/**
	 * Router class
	 *
	 * @author Mark Hünermund Jensen
	 */

	namespace Bytes;

	class Router extends StandardClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Container for defined routes
	     * @var array
	     */
	    
	    private $Routes 			= [];

	    /**
	     * Defined request type
	     * @var string
	     */
	    
	    private $RequestType 		= 'GET';

	    /**
	     * Container for allowed request types
	     * @var array
	     */
	    
	    private $RequestTypes 		= [ 'GET' , 'POST' , 'PUT' , 'PATCH' , 'DELETE' ];

	    /**
	     * Regular expression for controller name pattern
	     * @var string
	     */
	    
	    private $RegExpController 	= '/^[a-z]+(\.[a-z]+){0,}$/i';

	    /**
	     * Regular expression for method name pattern
	     * @var string
	     */
	    
	    private $RegExpMethod 		= '/^[a-z][a-z0-9_]+$/i';

	    /**
	     * Store the active component for later retrieval
	     * @var string
	     */
	    
	    private $Component 			= '';

	    /**
	     * The current error handle
	     * @var string
	     */
	    
	    private $ErrorHandle 		= '';

	    /* ======================================================================================================
	       RESET
	    ====================================================================================================== */

	    /**
	     * Resets request type, etc.
	     * Ideally called before CreateRoutes, so the Router object, which is often a reference, does not carry
	     * configuration made by previous components
	     *
	     * @access public
	     * @return Router
	     */
	    
	    public function Reset ( ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ErrorHandler 		= '';
	        $this -> RequestType 		= 'GET';

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       REQUEST TYPE
	    ====================================================================================================== */

	    /**
	     * All following routes will apply this request type
	     *
	     * @access public
	     * @param string $RequestType One of GET, POST, PUT, PATCH or DELETE
	     * @throws ConfigurationException Raised if $RequestType is not an allowed request type
	     * @return \Bytes\Router
	     */

	    public function RequestType ( string $RequestType ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! in_array( $RequestType , $this -> RequestTypes ) ) {

	        	throw new ConfigurationException( 'Request type is not allowed: ' . $RequestType );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequestType 		= (string) $RequestType;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       NO REQUEST TYPE
	    ====================================================================================================== */

	    /**
	     * Ensures that following routes are declared with Request Type set to false.
	     * This is useful for CLI applications.
	     *
	     * @access public
	     * @return Router
	     */
	    
	    public function NoRequestType ( ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequestType 		= False;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       COMPONENT
	    ====================================================================================================== */

	    /**
	     * Register the active component, which is currently creating routes
	     *
	     * @access public
	     * @param string $Component 
	     * @return Router
	     */
	    
	    public function Component ( string $Component ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Component 			= (string) $Component;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       HANDLE ERRORS WITH
	    ====================================================================================================== */

	    /**
	     * Define the error handle you want to use for the upcoming route(s) to be defined.
	     *
	     * Note: This method does not test if the error handle exists.
	     *
	     * @access public
	     * @param string $ErrorHandle 
	     * @return Router
	     */
	    
	    public function HandleErrorsWith ( string $ErrorHandle ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ErrorHandle 		= (string) $ErrorHandle;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       CREATE ROUTE
	    ====================================================================================================== */

	    /**
	     * Creates a new route pointing to a controller
	     *
	     * @access public
	     * @param mixed $Route String for regular expression to match URI, or another Router instance
	     * @param string $Controller Controller class to be initialized
	     * @param string $Method Method to be called in the controller
	     * @throws ConfigurationException Raised if routes are created without a registered component
	     * @throws ConfigurationException Raised if Controller is not of a valid pattern
	     * @throws ConfigurationException Raised if Method is not of a valid pattern
	     * @return \Bytes\Router
	     */

	    public function CreateRoute (

	    	$Route,
	    	string $Controller,
	    	string $Method

	    ): Router {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        // Make sure there's an active component

	        if ( ! $this -> Component ) {

	        	throw new ConfigurationException( 'There must be an active component in CreateRoute' );

	        }

	        // Make sure the controller follows a pattern we like

	        if ( ! preg_match( $this -> RegExpController , $Controller ) ) {

	        	throw new ConfigurationException(

	        		'Controller is not a valid pattern in CreateRoute. Allowed pattern: '
	        		. $this -> RegExpController

	        	);

	        }

	        // Make sure the controller method name follows a pattern we like

	        if ( ! preg_match( $this -> RegExpMethod , $Method ) ) {

	        	throw new ConfigurationException(

	        		'Method name is not a valid pattern in CreateRoute. Allowed pattern: '
	        		. $this -> RegExpMethod

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ROUTER
	        ------------------------------------------------------------------------------------------------------ */

	        // If router is passed as a string, we convert it to the \Bytes\Router\RegExp object

	        if ( is_string( $Route ) ) {

	        	$RouteObject 		= new \Bytes\RouterGuides\RegExp( $Route );

	        // If $Route is an object inheriting \Bytes\RouterGuide, we pass it as-is

	        } else if ( is_subclass_of( $Route , '\\Bytes\\RouterGuide' ) ) {

	        	$RouteObject 		= $Route;

	        } else {

	        	throw new \Bytes\ComponentException( 'Routes (which are not strings) must be objects inheriting \\Bytes\\RouterGuide' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Routes[] 		= [

	        							// Apply the active request type

	        							'RequestType' 		=> $this -> RequestType,

	        							// Regular expression for URI matchin

	        							'Route' 			=> $RouteObject,

	        							// Controller and method names

	        							'Controller' 		=> (string) $Controller,
	        							'Method' 			=> (string) $Method,

	        							// Active component

	        							'Component' 		=> (string) $this -> Component,

	        							// Error handle (basic exception handler)

	        							'ErrorHandle' 		=> (string) $this -> ErrorHandle

	        						];

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       GET ROUTES
	    ====================================================================================================== */

	    /**
	     * Returns the list of created routes
	     *
	     * @access public
	     * @return array
	     */
	    
	    public function GetRoutes ( ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Routes;

	    }

	    /* ======================================================================================================
	       MATCH
	    ====================================================================================================== */

	    /**
	     * Match a URI string against the collected routes
	     *
	     * @access public
	     * @param string $URI
	     * @return mixed Array with found route information, or false if nothing is found
	     */
	    
	    public function Match ( string $URI ) {

	        /* ------------------------------------------------------------------------------------------------------
	           FIND MATCH
	        ------------------------------------------------------------------------------------------------------ */

	        // Iterate over the entire set of routes and attempt to compare

	        foreach ( $this -> Routes as $I => $Route ) {

	        	// NOTE: Calling the preg_match is intentionally placed last, so the comparison only executes
	        	// if the request types are compared with success

	        	// If no request type is set and REQUEST_METHOD is empty or null

	        	if ( ! $_SERVER[ 'REQUEST_METHOD' ] && ! $Route[ 'RequestType' ] && $Route[ 'Route' ] -> Match( $URI ) ) {

	        		// If there's a match on the route, and no request type in either request or route, let's
	        		// return the route information

	        		return $Route;

	        	}

	        	// We want to match route and request type

	        	if ( $_SERVER[ 'REQUEST_METHOD' ] == $Route[ 'RequestType' ] && $Route[ 'Route' ] -> Match( $URI ) ) {

	        		// If there's a match we return the route here
	        		// (and effectively end the execution of the this method)

	        		return $Route;

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        // Return false when no route has been matched

	        return False;

	    }

	}