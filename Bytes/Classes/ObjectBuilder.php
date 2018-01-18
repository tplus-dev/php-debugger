<?php

	/**
	 * Object builder class
	 * 
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	class ObjectBuilder {

	    /* ======================================================================================================
		   PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Container for InjectionContainer
	     * @var InjectionContainer
	     */
	    
	    private $InjectionContainer;

	    /**
	     * Hooks container
	     * @var HooksContainer
	     */
	    
	    private $HooksContainer;

	    /**
	     * Container for registered components. That's components we want to use, but not load right away
	     * @var array
	     */
	    
	    private $Implementations 	= [];

	    /**
	     * Definitions for the known instance types
	     * @var array
	     */
	    
	    private $InstanceTypes 		= [

	    								'Visual' 		=> [

	    													'Folder' 				=> 'Visuals',
	    													'Prefix' 				=> 'Visual',
	    													'ComponentLocation' 	=> True,
	    													'ResolveDependencies' 	=> True,
	    													'Header' 				=> True,
	    													'ClassNamespace' 		=> False,
	    													'Options' 				=> False

	    												],

	    								'Model' 		=> [

	    													'Folder' 				=> 'Models',
	    													'Prefix' 				=> 'Mdl',
	    													'ComponentLocation' 	=> True,
	    													'ResolveDependencies' 	=> True,
	    													'Header' 				=> False,
	    													'ClassNamespace' 		=> False,
	    													'Options' 				=> False

	    												],

	    								'Hook' 			=> [

	    													'Folder' 				=> False,
	    													'Prefix' 				=> 'Hook',
	    													'ComponentLocation' 	=> False,
	    													'ResolveDependencies' 	=> True,
	    													'Header' 				=> False,
	    													'ClassNamespace' 		=> '\\Bytes\\Hooks\\%s',
	    													'Options' 				=> False

	    												]

	    							];

	    /**
	     * Option instances container.
	     * Used for later injection.
	     * @var array
	     */
	    
	    private $Options 			= [];

	    /* ======================================================================================================
	       SET INJECTION CONTAINER
	    ====================================================================================================== */

	    /**
	     * Inject the InjectionContainer
	     *
	     * @access public
	     * @param InjectionContainer $InjectionContainer
	     * @return ObjectBuilder
	     */
	    
	    public function SetInjectionContainer ( InjectionContainer $InjectionContainer ): ObjectBuilder {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> InjectionContainer 		= $InjectionContainer;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SET HOOKS CONTAINER
	    ====================================================================================================== */

	    /**
	     * Inject the HooksContainer
	     *
	     * @access public
	     * @param HooksContainer $HooksContainer
	     * @return ObjectBuilder
	     */
	    
	    public function SetHooksContainer ( HooksContainer $HooksContainer ): ObjectBuilder {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> HooksContainer 		= $HooksContainer;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SET KNOWN COMPONENTS
	    ====================================================================================================== */

	    /**
	     * Set the known implementations
	     *
	     * @access public
	     * @param array $Implementations
	     * @return ObjectBuilder
	     */
	    
	    public function SetImplementations ( array $Implementations ): ObjectBuilder {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Implementations 			= $Implementations;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       CREATE INSTANCE
	    ====================================================================================================== */

	    /**
	     * Helper method to create an instance of a class, for instance visuals
	     *
	     * @final
	     * @access protected
	     * @param string $Type For instance "Visual"
	     * @param string $Name
	     * @todo Integrate with CreateImplementation
	     * @todo Integrate with CreateController
	     * @todo Ability to position files in sub-folders
	     * @throws \Bytes\ConfigurationException Raised if the $Type is unknown
	     * @uses ObjectBuilder::HandleDependencies
	     * @uses ObjectBuilder::HandleHeader
	     * @return mixed
	     */
	    
	    final protected function CreateInstance ( string $Type , string $Name , array $Options = [] ) {

	        /* ------------------------------------------------------------------------------------------------------

	           TYPE VALIDATE

	           Make sure we know the $Type

	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! array_key_exists( $Type , $this -> InstanceTypes ) ) {

	        	throw new ConfigurationException( sprintf( 'The instance type "%s" is unknown.' , $Type ) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $TypeOptions 			= $this -> InstanceTypes[ $Type ];

	        if ( $TypeOptions[ 'ComponentLocation' ] ) {

		        $Folder 				= $TypeOptions[ 'ComponentLocation' ] ? $Options[ 'ComponentLocation' ] : '';

		        $Location 				= sprintf(

		        							'%s/%s/%s.%s.php',

		        							rtrim( $Folder , '/' ),
		        							$TypeOptions[ 'Folder' ],
		        							$TypeOptions[ 'Prefix' ],
		        							$Name

		        						);

		    } else {

		    	$Location 				= $Options[ 'Location' ];

		    }

	        $ComponentName 			= basename( isset( $Options[ 'ComponentLocation'] ) ? $Options[ 'ComponentLocation'] : $Options[ 'Location' ] );

	        if ( $TypeOptions[ 'ClassNamespace' ] ) {

	        	$ClassName 			= sprintf( $TypeOptions[ 'ClassNamespace' ] , $Name );

	        } else {

		        $ClassName 				= sprintf(

		        							'Bytes\\Components\\%s\\%s',

		        							$ComponentName,
		        							$TypeOptions[ 'Prefix' ] . $Name

		        						);

		    }

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE + LOAD FILE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! file_exists( $Location ) ) {

	        	throw new FileNotFoundException( 'Could not locate file: ' . $Location );

	        }

	        // Load the file

	        require_once 			$Location;

	        /* ------------------------------------------------------------------------------------------------------
	           CLASS 
	        ------------------------------------------------------------------------------------------------------ */

	        $Instance 				= new $ClassName( $Options[ 'ComponentLocation' ] );

	        $Instance -> SetInjectionContainer( $this -> InjectionContainer );

	        /* ------------------------------------------------------------------------------------------------------
	           DEPENDENCIES
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $TypeOptions[ 'ResolveDependencies' ] ) {

	        	$this -> HandleDependencies( $Instance , [ 'Method' => '' ] );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           HEADER
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $TypeOptions[ 'Header' ] ) {

	        	$this -> HandleHeader( $Instance );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Instance;

	    }

	    /* ======================================================================================================
	       CREATE VISUAL
	    ====================================================================================================== */

	    /**
	     * Wrapper method for creating visuals
	     *
	     * @access public
	     * @param string $ComponentLocation
	     * @param string $Name
	     * @uses ObjectBuilder::CreateInstance
	     * @return \Bytes\Visual
	     */
	    
	    public function CreateVisual ( string $ComponentLocation , string $Name ): Visual {

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        $Instance 				= $this -> CreateInstance( 

	        							'Visual',
	        							(string) $Name,
	        							[
	        								'ComponentLocation' 	=> (string) $ComponentLocation
	        							]

	        						);

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Instance;

	    }

	    /* ======================================================================================================
	       CREATE MODEL
	    ====================================================================================================== */

	    /**
	     * Wrapper method for creating models
	     *
	     * @access public
	     * @param string $ComponentLocation
	     * @param string $Name
	     * @uses ObjectBuilder::CreateInstance
	     * @return \Bytes\Model
	     */
	    
	    public function CreateModel ( string $ComponentLocation , string $Name ): Model {

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        $Instance 				= $this -> CreateInstance( 

	        							'Model',
	        							(string) $Name,
	        							[
	        								'ComponentLocation' 	=> (string) $ComponentLocation
	        							]

	        						);

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Instance;

	    }

	    /* ======================================================================================================
	       CREATE HOOK
	    ====================================================================================================== */

	    /**
	     * Wrapper method for creating hooks
	     *
	     * @access public
	     * @param array $Hook
	     * @uses ObjectBuilder::CreateInstance
	     * @return \Bytes\Hook
	     */
	    
	    public function CreateHook ( array $Hook ): Hook {

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        $Instance 				= $this -> CreateInstance( 

	        							'Hook',

	        							// Strip away the "Hook." and ".php" part of the location basename
	        							// to form the proper class name to be called
	        							
	        							preg_replace( '/^Hook\.(.*?)\.php$/' , '$1' , basename( $Hook[ 'Location' ] ) ),

	        							[
	        								'Location' 		=> (string) $Hook[ 'Location' ]
	        							]

	        						);

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Instance;

	    }

	    /* ======================================================================================================

	       CREATE IMPLEMENTATION

	       Creates a component or service

	    ====================================================================================================== */

	    /**
	     * Constructs a new component/service and performs operations such as creating the routes, etc.
	     *
	     * @access public
	     * @param string $ComponentLocation Path to the component directory
	     * @param string $Type
	     * @throws ConfigurationException Raised when the component location is incorrect
	     * @throws FileNotFoundException Raised if the main file is not found inside the component
	     * @throws ComponentException Raised if the expected class is not found
	     * @uses ObjectBuilder::HandleOptions
	     * @uses Implementation::SetDirectory
	     * @uses Implementation::SetObjectBuilder
	     * @uses Implementation::SetInjectionContainer
	     * @uses Implementation::Prepare
	     * @return Component Instance of the component
	     */
	    
	    public function CreateImplementation ( string $ComponentLocation , string $Type = '' ): Implementation {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE DIRECTORY
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_dir( $ComponentLocation ) ) {

	        	throw new ConfigurationException( 'No component found at: ' . $ComponentLocation );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           LOAD
	        ------------------------------------------------------------------------------------------------------ */

	        // In every component directory, we expect to find a PHP file with the same name as the folder
	        // This is the starting point of the component

	        $FileLocation 			= rtrim( $ComponentLocation , '/' ) . '/' . basename( $ComponentLocation ) . '.php';

	        // Let's throw an exception in case this file is not found

	        if ( ! file_exists( $FileLocation ) ) {

	        	throw new FileNotFoundException( 'Component file not found at: ' . $FileLocation );

	        }

	        // Load the file

	        require_once 			$FileLocation;

	        /* ------------------------------------------------------------------------------------------------------
	           CLASS
	        ------------------------------------------------------------------------------------------------------ */

	        // The class name of the component is expected to be the same as the directory and the file

	        $ClassName 				= basename( $ComponentLocation );

	        // If it's a component, we have to look in the namespace for it

	        if ( $Type == 'Component' ) {

	        	$ClassName 			= sprintf( '\\Bytes\\Components\\%s\\%s' , $ClassName , $ClassName );

	        } else {

	        	$ClassName 			= sprintf( '\\Bytes\\Services\\%s' , $ClassName , $ClassName );

	        }

	        // Test if the class was created while loading the aforementioned file

	        if ( ! class_exists( $ClassName ) ) {

	        	throw new ComponentException( 'Implementation class not found: ' . $ClassName );

	        }

	        // Find the class type

	        $Parent 				= get_parent_class( $ClassName );

	        // Set working parameters
	        
	        $IsComponenet 			= stripos( $Parent , 'component' ) > 0 ? True : False;
	        $IsService 				= is_subclass_of( $ClassName , '\\Bytes\\Service' ) ? True : False;

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        // Create an instance of the class

	    	$Instance 				= new $ClassName();

	    	// Store the location

	    	$Instance -> SetDirectory( $ComponentLocation );

	    	// Object builder is injected, because the Component must be capable of creating controllers
	    	
	    	if ( $IsComponenet ) {

	    		$Instance -> SetObjectBuilder( $this );

	    	}

	    	// Set the Injection Container

	    	$Instance -> SetInjectionContainer( $this -> InjectionContainer );

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       HANDLE OPTIONS
	   	    ------------------------------------------------------------------------------------------------------ */

	        $this -> HandleOptions( $Instance );

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       INITIALIZE
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    if ( method_exists( $Instance , 'Prepare' ) ) {

	   	    	$Instance -> Prepare();

	   	    }

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       AUTOLOAD CLASSES
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    // Load files stored in the component's assets folde (automatically)

	   	    if ( is_dir( rtrim( $ComponentLocation , '/' ) . '/Classes/' ) ) {

	   	    	$Classes 			= glob( rtrim( $ComponentLocation , '/' ) . '/Classes/*' );

	   	    	foreach ( $Classes as $I => $ClassFilename ) {

	   	    		require_once 	$ClassFilename;

	   	    	}

	   	    }

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       RETURN
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    return $Instance;

	    }

	    /* ======================================================================================================
	       HANDLE OPTIONS
	    ====================================================================================================== */

	    /**
	     * Create an Options object, collect the declaration from the instance and configure options
	     * if needed
	     *
	     * @access protected
	     * @param \Bytes\Implementation $Instance
	     * @param string $FromImplementation Use options set previously by an implementation
	     * @uses \Bytes\Implementation::DeclareOptions
	     * @uses \Bytes\StandardClass::SetOptions
	     * @return void
	     */
	    
	    protected function HandleOptions ( \Bytes\StandardClass &$Instance , $UseFromImplementation = '' ) {

	        /* ------------------------------------------------------------------------------------------------------
	           OPTIONS OBJECT
	        ------------------------------------------------------------------------------------------------------ */

	        // If it is requested that we simply use an Options object, already created by an implementation (such
	        // as a component, in this scenario), we simply grab the copy that is already stored of it ...

	    	if ( $UseFromImplementation ) {

	    		$Options 				= $this -> Options[ $UseFromImplementation ];

	    	} else {

	    		// ... Otherwise, we create a new Options object

		    	$Options 				= new Options;

		    	// And check if the instance wants to declare any options

		   	    if ( method_exists( $Instance , 'DeclareOptions' ) ) {

		   	    	$Instance -> DeclareOptions( $Options );

		   	    }

		   	    // No matter if it has declared or not, we'll seal the Options object here,
		   	    // so it no longer accepts changes to its setup

		   	    $Options -> Seal();

		   	    // In order to find the options we need in the Implementations variable, we need the
		   	    // class name of the instance

		   	    $ClassName 				= basename( str_replace( '\\' , '/' , get_class( $Instance ) ) );

		   	    // If there's any information regarding the implementation, we can continue without
		   	    // casting notices

		   	    if ( isset( $this -> Implementations[ $ClassName ] ) ) {

		   	    	// Load the ConfigureOptions property into a variable for easy reading

			   	    $ConfigureOptions 	= $this -> Implementations[ $ClassName ][ 'ConfigureOptions' ];

			   	    // If it's a callable (anonymous function) ...

			   	    if ( is_callable( $ConfigureOptions ) ) {

			   	    	// Load environment into a variable, so we can pass it by reference

			   	    	$Environment 			= $this -> InjectionContainer -> Retrieve( 'Environment' );

			   	    	// ... We'll execute it, so it can set the declared options
			   	    	// Settings often depend on environment, so it's naturally passed as well

			   	    	$ConfigureOptions( $Options , $Environment );

			   	    }

			   	}

		   	    // Keep a copy of the Options object (for instance so they can be injected to related controllers)

		   	    $this -> Options[ basename( $ClassName ) ] 	= $Options;

			}

		    /* ------------------------------------------------------------------------------------------------------
		       POPULATE TRIGGERS
		    ------------------------------------------------------------------------------------------------------ */

		    $Options -> PopulateTriggers( $this -> HooksContainer , $this );

		    /* ------------------------------------------------------------------------------------------------------
		       SET (AND COPY) OPTIONS
		    ------------------------------------------------------------------------------------------------------ */

		   	// Inject the Options object (values are set at this point) into the instance

	   	    $Instance -> SetOptions( $Options );

	    }

	    /* ======================================================================================================
	       CREATE CONTROLLER
	    ====================================================================================================== */

	    /**
	     * Constructs a new controller
	     *
	     * @access public
	     * @param string $ComponenetLocation
	     * @param string $Controller
	     * @param array $Options Additional options
	     * @throws FileNotFoundException Raised if the main file is not found inside the component
	     * @throws ComponentException Raised if the expected class is not found
	     * @uses ObjectBuilder::HandleDependencies
	     * @uses ObjectBuilder::HandleOptions
	     * @return Component Instance of the component
	     */
	    
	    public function CreateController ( 

	    	string $ComponentLocation,
	    	string $Controller,
	    	array $Options = []

	    ): Controller {

	        /* ------------------------------------------------------------------------------------------------------
	           LOAD
	        ------------------------------------------------------------------------------------------------------ */

	        // Prepare component name

	        $Component 				= basename( $ComponentLocation );

	        // In every component directory, we expect to find a PHP file with the same name as the folder
	        // This is the starting point of the component

	        $FileLocation 			= rtrim( $ComponentLocation , '/' ) . '/Controllers/Ctrl.' . $Controller . '.php';

	        // Let's throw an exception in case this file is not found

	        if ( ! file_exists( $FileLocation ) ) {

	        	throw new FileNotFoundException( 'Controller not found at: ' . $FileLocation );

	        }

	        // Load the file

	        require_once 			$FileLocation;

	        /* ------------------------------------------------------------------------------------------------------
	           CLASS
	        ------------------------------------------------------------------------------------------------------ */

	        // The class name of the component is expected to be the same as the directory and the file

	        $ClassName 				= '\\Bytes\\Components\\' . $Component . '\\Ctrl' . $Controller;

	        // Test if the class was created while loading the aforementioned file

	        if ( ! class_exists( $ClassName ) ) {

	        	throw new ComponentException( 'Controller class not found: ' . $ClassName );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        ------------------------------------------------------------------------------------------------------ */

	        // Create an instance of the class

	    	$Instance 				= new $ClassName( $ComponentLocation , $this );

	    	$this -> HandleDependencies( $Instance , $Options );
	    	$this -> HandleOptions( $Instance , $Component );

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       RETURN
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    return $Instance;

	    }

	    /* ======================================================================================================
	       HANDLE DEPENDENCIES
	    ====================================================================================================== */

	    /**
	     * Handles dependencies for ExtendedClass instances
	     *
	     * @final
	     * @access protected
	     * @param mixed $Instance
	     * @param array $Options (Optional) Array with options
	     * @return void
	     */
	    
	    final protected function HandleDependencies ( &$Instance , array $Options = [] ) {

	        /* ------------------------------------------------------------------------------------------------------
	           GET DEPENDENCIES
	        ------------------------------------------------------------------------------------------------------ */

	        $Dependencies 			= new Dependencies;

	        // Load environment

	        $Environment 			= $this -> InjectionContainer -> Retrieve( 'Environment' );

	        // Call the "Dependencies" method on the instance
	        // $Dependencies which we created above, is injected as a reference, so any actions taken
	        // to it, are automatically applied on this very object

	        $Instance -> Dependencies(

	        	$Dependencies,
	        	$Environment,

	        	// Context array passed into the Dependencies method

	        	[
	        		'Method' 	=> (string) $Options[ 'Method' ]
	        	]

	        );

	        /* ------------------------------------------------------------------------------------------------------
	           REQUIREMENTS
	        ------------------------------------------------------------------------------------------------------ */

	        // List the requirements defined in the Dependencies method

	        $Requirements 			= $Dependencies -> ListRequirements();

	        // Work on groups

	        foreach ( $Requirements[ 'Groups' ] as $X => $Group ) {

	        	foreach ( $this -> Implementations as $Y => $Implementation ) {

	        		if ( ! is_array( $Implementation[ 'Groups' ] ) ) {

	        			continue;

	        		}

	        		if ( in_array( $Group[ 'Group' ] , $Implementation[ 'Groups' ] ) ) {

	        			$Requirements[ 'Implementations' ][] 	

	        							= [

											'Name' 					=> basename( $Implementation[ 'Location' ] ),
											'StrictRequirement' 	=> False,
											'Extra' 				=> [

																		'Groups' 	=> $Implementation[ 'Groups' ]

																	]

										];

	        		}

	        	}

	        }

	        /* --------------------------------------------------------------------------------------------------
	           INSTANTIATE
	        -------------------------------------------------------------------------------------------------- */

	        // Iterate over the required implementations

	        foreach ( $Requirements[ 'Implementations' ] as $I => $ImplementationData ) {

	        	// Set name

	        	$Implementation 	= $ImplementationData[ 'Name' ];

	        	// If the implementation is not registered, we cannot safely assume its location and we'll
	        	// fail to load it. So let's throw an Exception

	        	if ( ! array_key_exists( $Implementation , $this -> Implementations ) ) {

	        		if ( ! $ImplementationData[ 'StrictRequirement' ] ) {

	        			continue;

	        		}

	        		// Let the developer know the implementation has not been registered

	        		throw new \Bytes\ComponentException( sprintf(

	        			'%s has required the implementation %s. But it does not occur in the application registry.',

	        			get_class( $Instance ),
	        			$Implementation

	        		) );

	        	}

	        	// If the implementation has been registered, we'll create it via the CreateImplementation
	        	// method, and then attach it to the InjectionContainer
	        	
	        	$this -> InjectionContainer -> Attach(

	        		$Implementation,

	        		$this -> CreateImplementation(

	        			// The location of the implementation is stored in the Implementations array

	        			$this -> Implementations[ $Implementation ][ 'Location' ]

	        		),

	        		isset( $ImplementationData[ 'Extra' ] ) ? $ImplementationData[ 'Extra' ] : []

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD INJECTION CONTAINER
	        ------------------------------------------------------------------------------------------------------ */

	    	$Instance -> SetInjectionContainer( $this -> InjectionContainer );

	    }

	    /* ======================================================================================================
	       HANDLE HEADER
	    ====================================================================================================== */

	    /**
	     * Manages an instance that utilizes header (visuals, views)
	     *
	     * @final
	     * @access protected
	     * @param mixed $Instance
	     * @since v1.1.0
	     * @return void
	     */
	    
	    final protected function HandleHeader ( &$Instance ) {

	        /* ------------------------------------------------------------------------------------------------------
	           HANDLE
	        ------------------------------------------------------------------------------------------------------ */

	        $Header 				= $this -> InjectionContainer -> Retrieve( 'Header' );

	        $Instance -> Header( $Header );

	    }

	}