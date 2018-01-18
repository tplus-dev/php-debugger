<?php

	/**
	 * Application class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	class Application extends StandardClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Container for registered implementations. That's components/services we want to use, but not load right away
	     * @var array
	     */
	    
	    private $Implementations 		= [];

	    /**
	     * Container for components requested for load, when the application launches
	     * @var array
	     */

	    private $PreloadComponents 		= [];

	    /**
	     * Container of instantiated components
	     * @var array
	     */
	    
	    private $Components 			= [];

	    /**
	     * Container for the Environment object
	     * @var Environment
	     */
	    
	    private $Environment;

	    /**
	     * Container for the Router object
	     * @var Router
	     */
	    
	    private $Router;

	    /**
	     * Container for the Header object
	     * @var Header
	     */
	    
	    private $Header;

	    /**
	     * Store output from the controller
	     * @var array
	     */
	    
	    private $ControllerOutput;

	    /**
	     * Container of error handles
	     * @var array
	     */
	    
	    private $ErrorHandles 			= [];

	    /**
	     * Keep track of error handler component information
	     * @var array
	     */
	    
	    private $AssignedErrorHandler;

	    /**
	     * Hooks container
	     * @var HooksContainer
	     */
	    
	    private $HooksContainer;

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * Initialize various objects such as Environment
	     *
	     * @access public
	     * @return void
	     */
	    
	    public function __construct ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           ENVIRONMENT + ROUTER
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Environment 		= new Environment;
	        $this -> Router 			= new Router;
	        $this -> Header 			= new Header;
	        $this -> HooksContainer 	= new HooksContainer;

	    }

	    /* ======================================================================================================
	       CONFIGURE
	    ====================================================================================================== */

	    /**
	     * Use the framework's default configuration.
	     * You can leave this method out, if you prefer doing your own PHP configuration.
	     * Remember, that ConfigureEnvironment might be an ideal place to alter certain configurations
	     * based on for instance if you're working in Development or Production
	     *
	     * @access public
	     * @param callable $ConfigureOptions (Optional)
	     * @param bool $PHPOptions Enabled/disable standard PHP setup (such as error reporting)
	     * @since v1.0.0 Added
	     * @since v1.1.0 Ability to configure options and enable/disable default PHP options
	     * @return Application
	     */
	    
	    public function Configure ( $ConfigureOptions = Null , $PHPOptions = True ): Application {

	        /* ------------------------------------------------------------------------------------------------------

	           OPTIONS

	           Just like Implementations, the Application can contain various options and triggers/hooks

	        ------------------------------------------------------------------------------------------------------ */

	        // Create an options object

	    	$Options 					= new Options;

	    	// Declare the options

	    	/* None yet */

	    	// Seal the options

	    	$Options -> Seal();

	        /* ------------------------------------------------------------------------------------------------------
	           CONFIGURE OPTIONS
	        ------------------------------------------------------------------------------------------------------ */

	    	if ( is_callable( $ConfigureOptions ) ) {

	    		$ConfigureOptions( $Options );

	    	}

	    	$this -> SetOptions( $Options );

	        /* ------------------------------------------------------------------------------------------------------
	           EXECUTE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $PHPOptions ) {

		        error_reporting( E_ALL ^ E_NOTICE );

		        ini_set( 'display_errors' , False );

		    }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       CONFIGURE ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Use an anonymous function to pre-configure the environmente (URLs, paths, env. type, etc.)
	     *
	     * @access public
	     * @param callable $Handle Function that handles the configuration
	     * @return Application
	     */
	    
	    public function ConfigureEnvironment ( callable $Handle ): Application {

	        /* ------------------------------------------------------------------------------------------------------
	           EXECUTE
	        ------------------------------------------------------------------------------------------------------ */

	        $Handle( $this -> Environment , new GlobalScope );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Access the environment to set or get data
	     *
	     * @access public
	     * @return Environment Reference to the environment object
	     */
	    
	    public function Environment ( ): Environment {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Environment;

	    }

	    /* ======================================================================================================
	       PRELOAD COMPONENT
	    ====================================================================================================== */

	    /**
	     * Add a component to the list of components that will be loaded as the application starts
	     *
	     * @access public
	     * @param string $Location Physical, absolute path to the component
	     * @param callable $ConfigureOptions (Optional) Pass an anonymous function to configure the implementation
	     * @throws \Bytes\FileNotFoundException Raised when the component is not found
	     * @todo Make sure this is actually a component
	     * @uses Application::UseImplementation
	     * @return Application Returns self for chaining
	     */
	    
	    public function PreloadComponent ( string $Location , $ConfigureOptions = Null , array $Extra = [] ): Application {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_dir( $Location ) ) {

	        	throw new FileNotFoundException( 'No directory found at: ' . $Location );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD TO LIST
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Components[] 		= $Location;

	        // Add to implementations list

	        $this -> UseImplementation( $Location , $ConfigureOptions , $Extra );

	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        return $this;

	    }

	    /* ======================================================================================================
	       REGISTER COMPONENT
	    ====================================================================================================== */

	    /**
	     * Add an implementation to the list of components that are accessible later in the execution
	     *
	     * @access public
	     * @param string $Location Physical, absolute path to the implementation
	     * @param callable $ConfigureOptions (Optional) Pass an anonymous function to configure the implementation
	     * @param array $Extra Additional options such as Group
	     * @throws \Bytes\FileNotFoundException Raised when the implementation is not found
	     * @throws \Bytes\ConfigurationException Raised if two implementations have the same name
	     * @since v1.2.0 Added a restriction against overlapping names between services and components
	     * @return Application
	     */
	    
	    public function UseImplementation ( string $Location , $ConfigureOptions = Null , array $Extra = [] ): Application {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_dir( $Location ) ) {

	        	throw new FileNotFoundException( 'No directory found at: ' . $Location );

	        }

	        // Ensure Groups is an array (if it is set)

	        if ( isset( $Extra[ 'Groups' ] ) && ! is_array( $Extra[ 'Groups' ] ) ) {

	        	throw new ConfigurationException( sprintf( 

	        		'Passed group(s) for implementation %s must be an array',

	        		basename( $Location )

	        	) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET?
	        ------------------------------------------------------------------------------------------------------ */

	        if ( isset( $this -> Implementations[ basename( $Location ) ]  ) ) {

	        	throw new ConfigurationException(

	        		'There are two implementations (services or components) carrying the same name: '
	        		. basename( $Location )

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD TO LIST
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Implementations[ basename( $Location ) ] 	

	        						= [

	        							'Location' 			=> $Location,

	        							'ConfigureOptions' 	=> is_callable( $ConfigureOptions )
	        													? $ConfigureOptions
	        													: False,

	        							'Groups' 			=> isset( $Extra[ 'Groups' ] ) ? $Extra[ 'Groups' ] : False

	        						];
	        						
	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        return $this;

	    }

	    /* ======================================================================================================
	       USE HOOKS FROM
	    ====================================================================================================== */

	    /**
	     * Scan provided directory for accepted hook filenames, and adds them to the container, for later use
	     *
	     * @access public
	     * @param string $Directory
	     * @param string $Namespace
	     * @return Application
	     */
	    
	    public function UseHooksFrom ( string $Directory , string $Namespace ): Application {

	        /* ------------------------------------------------------------------------------------------------------
	           DECLARE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> HooksContainer -> DeclareHooksFrom( $Directory , $Namespace );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       START
	    ====================================================================================================== */

	    /**
	     * Starts the application
	     *
	     * @access public
	     * @param string $URI URI for the router to compare against
	     * @param callable $Callback
	     * @return mixed Whatever comes out of $Callback
	     */
	    
	    public function Start ( string $URI , callable $Callback ) {
	    	
	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $InjectionContainer 		= new InjectionContainer;
	        $ObjectBuilder 				= new ObjectBuilder;

	        $InjectionContainer -> Attach( 'Environment' , $this -> Environment );
	        $InjectionContainer -> Attach( 'Header' , $this -> Header );

	        $ObjectBuilder -> SetInjectionContainer( $InjectionContainer );
	        $ObjectBuilder -> SetHooksContainer( $this -> HooksContainer );
	        $ObjectBuilder -> SetImplementations( $this -> Implementations );

	        // Populate triggers/hook for the application object

	    	$this -> __Options -> PopulateTriggers( $this -> HooksContainer , $ObjectBuilder );

	        /* ------------------------------------------------------------------------------------------------------
	           LOAD COMPONENTS
	        ------------------------------------------------------------------------------------------------------ */

	        // Iterate over all preloaded components

	        foreach ( $this -> Components as $I => $ComponentName ) {

	        	// Use the object builder to create the component and work with the router and environment
	        	
	        	$Component 			= $ObjectBuilder -> CreateImplementation( $ComponentName , 'Component' );

	        	// Initialize the component (create its routes, etc.)

	        	$Component -> Initialize( $this -> Router );

	        	// Store the component for later reference

	        	$this -> Components[ basename( $ComponentName ) ] 	= $Component;
	        	
	        }

	        /* ------------------------------------------------------------------------------------------------------
	           FIND ROUTE + EXECUTE CONTROLLER
	        ------------------------------------------------------------------------------------------------------ */

	        try {

	        	// Find information on controller and method, using the Router's Match method

	        	$Route 						= $this -> Router -> Match( $URI );

	        	// If no route is found, we'll invoke a 404 error

		        if ( ! $Route ) {

		        	throw new Exception( 'Not Found' , 404 );

		        }

	        	// Retrieve output from the controller's requested method
	        	// String replacement is necessary because of Windows/Linux indifference in understanding slashes
	        	
				$ComponentId 				= basename( str_replace( '\\' , '/' , $Route[ 'Component' ] ) );

	        	$Output 					= $this -> Components[ $ComponentId ] -> Controller(

			        							(string) $Route[ 'Controller' ],
			        							(string) $Route[ 'Method' ],

			        							[
			        								'Route' 		=> $URI
			        							]

			        						);

	        	// If we successfully get to this point, we store the output
	        	// This enables us to use it both inside the custom callback method, as well as
	        	// in other methods, such as Application::Render

		        $this -> ControllerOutput 	= $Output;

		    } catch ( \Exception $E ) {

		    	// Load environment

		    	$Env 						= $this -> Environment();

		    	// In case of exception, we'll look to the router, and see if it requests to use
		    	// a custom error handle

		    	if ( $this -> AssignedErrorHandler ) {

		    		$Handle 					= 'ERROR/' . (string) $Route[ 'ErrorHandle' ];

		    		$ErrorHandlerComponent 		= basename( $this -> AssignedErrorHandler[ 'Location' ] );
		    		$ErrorHandler 				= $this -> Components[ $ErrorHandlerComponent ];

		    		$ErrorRoute					= $this -> Router -> Match( $Handle );

		    		$ErrorController 			= (string) $ErrorRoute[ 'Controller' ];
		    		$ErrorMethod 				= (string) $ErrorRoute[ 'Method' ];

		    		if ( ! $ErrorRoute ) {

		    			$ErrorController 		= 'Index';
		    			$ErrorMethod 			= 'Default';

		    		}

		    		$Output 					= $ErrorHandler -> Controller( $ErrorController , $ErrorMethod , [

		    										'ErrorHandler' 		=> True,
		    										'Exception' 		=> $E

		    									] );

		    		$this -> ControllerOutput 	= $Output;

		    	} else {

		    		// If there's no custom error handle and no default, we'll do a fallback to a simple JSON message
		    		// (JSON applied, if Application::Render is used)

	    			$this -> ControllerOutput 	= [

		    										'Error' 		=> $E -> getMessage()

		    									];

		    	}

		    }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Callback( $this , $this -> ControllerOutput );

	    }

	    /* ======================================================================================================
	       RENDER
	    ====================================================================================================== */

	    /**
	     * Render the output using the interpretation of the framework.
	     * You can build your own interpretation, by not calling this function in the Application::Start
	     * method.
	     *
	     * @access public
	     * @todo We have no place to define headers - that's a standing issue
	     * @return string The rendering of the controller output
	     */
	    
	    public function Render ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           JSON OUTPUT
	        ------------------------------------------------------------------------------------------------------ */

	        if ( is_array( $this -> ControllerOutput ) ) {

	        	// The headers should not have been sent at this point, but we cannot rest entirely on that
	        	// assumption. Furthermore, PHPUnit will complain.

	        	if ( ! headers_sent() ) {

	        		header( 'Content-Type: application/json; charset=utf-8' );

	        	}

	        	return json_encode( $this -> ControllerOutput );

	        } else {

	        	return (string) $this -> ControllerOutput;

	        }

	    }

	    /* ======================================================================================================
	       ASSIGN ERROR HANDLE
	    ====================================================================================================== */

	    /**
	     * Assigns a component as the error handler
	     *
	     * @access public
	     * @param string $ComponentLocation Path to the component
	     * @since v1.2.0
	     * @return Application
	     */
	    
	    public function AssignErrorHandler ( string $ComponentLocation ): Application {

	        /* --------------------------------------------------------------------------------------------------
	           SET
	        -------------------------------------------------------------------------------------------------- */

	        $this -> AssignedErrorHandler 	= [

	        									'Location' 		=> $ComponentLocation

	        								];

	        /* --------------------------------------------------------------------------------------------------
	           RETURN
	        -------------------------------------------------------------------------------------------------- */

	        return $this;

	    }

	}