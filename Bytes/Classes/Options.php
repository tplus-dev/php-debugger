<?php

	/**
	 * Options manager class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	class Options {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Container for options
	     * @var array
	     */
	    
	    private $Options 		= [];

	    /**
	     * When the configuration has been declared, the object will be sealed, and can no longer be changed
	     * @var bool
	     */
	    
	    private $Sealed 		= False;

	    /**
	     * Container for the option we're currently declaring
	     */
	    
	    private $Declaration 	= [

	    							'Active' 		=> False,
	    							'Name' 			=> '',
	    							'Default' 		=> Null,
	    							'Required' 		=> False,
	    							'ValidatedBy'	=> False,
	    							'Trigger' 		=> False

	    						];

	    /**
	     * Container for options set by the user
	     * @var array
	     */
	    
	    private $Customized 	= [];

	    /**
	     * Keep track of customized and default options have been merged
	     * @var array
	     */
	    
	    private $Merged;

	    /**
	     * Keep track of "On" for declaring triggers
	     * @var string
	     */
	    
	    private $On;

	    /**
	     * Populated triggers
	     * @var array
	     */
	    
	    private $Triggers;

	    /* ======================================================================================================
	       COMMIT
	    ====================================================================================================== */

	    /**
	     * Commits the current declaration
	     *
	     * @access public
	     * @throws \Bytes\ImplementationException Raised if the Options object is sealed
	     * @throws \Bytes\ImplementationException Raised if changes are attempted on a committed option
	     * @throws \Bytes\ImplementationException Raised if a required value also has a default value
	     * @return Options
	     */
	    
	    public function Commit ( ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Key 				= $this -> Declaration[ 'Name' ];

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       VALIDATE
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    // Make sure the object is not already sealed

	   	    if ( $this -> Sealed ) {

	   	    	throw new ImplementationException( 'Options are already sealed.' );

	   	    }

	   	    // Test for active declaration

	   	    $this -> MustBeActive();

	   	    // Make sure the options is not already declared and committed

	   	    if ( isset( $this -> Options[ $Key ] ) ) {

	   	    	throw new ImplementationException( sprintf(

	   	    		'Option %s is already declared in Options.',

	   	    		$Key

	   	    	) );

	   	    }

	   	    // Ensure that required options do not also have default always - as that makes no sense

	   	    if ( $this -> Declaration[ 'Required' ] && ! is_null( $this -> Declaration[ 'Default' ] ) ) {

	   	    	throw new ImplementationException( sprintf(

	   	    		'The option %s cannot be declared both as required and as having a default value.',

	   	    		$Key

	   	    	) );

	   	    }

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       ADD
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    $this -> Options[ $Key ] 	= $this -> Declaration;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SET
	    ====================================================================================================== */

	    /**
	     * Sets an option
	     *
	     * @access public
	     * @param string $Key
	     * @param mixed $Value
	     * @throws \Bytes\ImplementationException Raised if the object is not sealed
	     * @throws \Bytes\ImplementationException Raised if the option does not exist
	     * @throws \Bytes\ImplementationException Raised if the option is not valid (per declared validation)
	     * @uses Options::TestOption
	     * @return Options
	     */
	    
	    public function Set ( string $Key , $Value ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> TestOption( $Key );

	        $ValidatedBy 			= $this -> Options[ $Key ][ 'ValidatedBy' ];

	        if ( $ValidatedBy ) {

	        	if ( is_string( $ValidatedBy ) && ! preg_match( $ValidatedBy , $Value ) ) {

        			throw new ImplementationException( sprintf (

        				'Option %s is not given in a valid (data) format',

        				$Key

        			) );

	        	} else if ( is_callable( $ValidatedBy ) && $ValidatedBy( $Value ) === False ) {

	        		throw new ImplementationException( sprintf (

        				'Option %s is not given in a valid (data) format',

        				$Key

        			) );

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET VALUE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Customized[ $Key ] 	= $Value;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       TEST OPTION
	    ====================================================================================================== */

	    /**
	     * Helper method ensuring the object is not sealed and that the key exists
	     *
	     * @access protected
	     * @param string $Key
	     * @return void
	     */
	    
	    protected function TestOption ( string $Key ) {

	        /* ------------------------------------------------------------------------------------------------------
	           SEALED?
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> Sealed ) {

	        	throw new ImplementationException( 'Options object must be sealed, before you can set values.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           KEY DECLARED?
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! isset( $this -> Options[ $Key ] ) ) {

	        	throw new ImplementationException( sprintf(

	        		'You attempted to set option %s. But it is not declared.',

	        		$Key

	        	) );

	        }

	    }

	    /* ======================================================================================================
	       ON (BEGIN TRIGGER CONFIGURATION)
	    ====================================================================================================== */

	    /**
	     * Begins the ON clause
	     *
	     * @access public
	     * @param string $TriggerName
	     * @uses Options::TestOption
	     * @return Options
	     */
	    
	    public function On ( string $Trigger ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> TestOption( $Trigger );

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> On 			= $Trigger;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       FIRE (COMPLETE TRIGGER CONFIGURATION)
	    ====================================================================================================== */

	    /**
	     * Completes trigger configuration
	     *
	     * @access public
	     * @param string $Namespace
	     * @param string $HookName
	     * @throws \Bytes\ImplementationException Raised if ON is not set
	     * @uses Options::Set
	     * @return Options
	     */
	    
	    public function Fire ( string $Namespace , string $HookName ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> On )  {

	        	throw new ImplementationException( 'You must use method On( $Trigger ) before calling Fire' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Set( $this -> On , [

	        	'Namespace' 		=> $Namespace,
	        	'HookName' 			=> $HookName

	        ] );

	        /* ------------------------------------------------------------------------------------------------------
	           RESET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> On 			= '';

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       MUST BE ACTIVE
	    ====================================================================================================== */

	    /**
	     * Helper method that throws an Exception if the developer is not actively declaring while
	     * attempting to change declaration options
	     *
	     * @access protected
	     * @throws \Bytes\ImplementationException Raised if developer is not actively declaring an option
	     * @return void
	     */
	    
	    protected function MustBeActive ( ) {

	        /* ------------------------------------------------------------------------------------------------------

	           VALIDATE

	           Make sure we're actively declaring an option

	        ------------------------------------------------------------------------------------------------------ */

	   	    if ( ! $this -> Declaration[ 'Active' ] ) {

	   	    	throw new ImplementationException( 'You cannot commit without declaring first.' );

	   	    }

	    }

	    /* ======================================================================================================
	       VALIDATED BY
	    ====================================================================================================== */

	    /**
	     * Optionally define how an option is validated, either by a regular expression or an anonymous 
	     * function
	     *
	     * @access public
	     * @param mixed $Metric Callable or string (regular expression)
	     * @throws \Bytes\ImplementationException Raised if the validation metric is not callable or string
	     * @return Options
	     */
	    
	    public function ValidatedBy ( $Metric ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> MustBeActive();

	        // Check the data type

	        if ( ! is_callable( $Metric ) && ! is_string( $Metric ) ) {

	        	throw new ImplementationException( '$Metric in ValidatedBy must be callable or string' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           PREPARE DECLARATION
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Declaration[ 'ValidatedBy' ]	= $Metric;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       AS REQUIRED
	    ====================================================================================================== */

	    /**
	     * Set current declaration as required. Meaning that the user of the implementation, must configure
	     * the setting
	     *
	     * @access public
	     * @return Options
	     */
	    
	    public function AsRequired ( ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> MustBeActive();

	        /* ------------------------------------------------------------------------------------------------------
	           PREPARE DECLARATION
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Declaration[ 'Required' ]	= True;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       AS TRIGGER
	    ====================================================================================================== */

	    /**
	     * Set current declaration as a triggerable (event handler). To be fired by hooks.
	     *
	     * @access public
	     * @return Options
	     */
	    
	    public function AsTrigger ( ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> MustBeActive();

	        /* ------------------------------------------------------------------------------------------------------
	           PREPARE DECLARATION
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Declaration[ 'Trigger' ]	= True;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DECLARE
	    ====================================================================================================== */

	    /**
	     * Declare a new option
	     *
	     * @access public
	     * @param string $Key
	     * @param mixed $Default
	     * @return Options
	     */
	    
	    public function Declare ( string $Key , $Default = Null ): Options {

	        /* ------------------------------------------------------------------------------------------------------
	           PREPARE DECLARATION
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Declaration[ 'Active' ]		= True;
	        $this -> Declaration[ 'Name' ] 			= $Key;
	        $this -> Declaration[ 'Default' ] 		= $Default;
	        $this -> Declaration[ 'Trigger' ] 		= False;
	        $this -> Declaration[ 'Required' ] 		= False;
	        $this -> Declaration[ 'ValidatedBy' ] 	= False;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       SEAL
	    ====================================================================================================== */

	    /**
	     * Seal (lock) the object for further setup
	     *
	     * @access public
	     * @return void
	     */
	    
	    public function Seal ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Sealed 		= True;

	    }

	    /* ======================================================================================================
	       GET OPTION
	    ====================================================================================================== */

	    /**
	     * Returns an option
	     *
	     * @access public
	     * @param string $Key
	     * @uses Options::MergeSets
	     * @throws \Bytes\ImplementationException Raised when option is not declared
	     * @throws \Bytes\ImplementationException Raised if accessing a trigger
	     * @return mixed
	     */

	    public function GetOption ( string $Key ) {

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       MERGE
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    $this -> MergeSets();

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! isset( $this -> Options[ $Key ] ) ) {

	        	throw new ImplementationException( sprintf( 

	        		'Requested option %s is not declared.',

	        		$Key

	        	) );

	        }

	        // Check that the option is not a trigger

	        if ( $this -> Options[ $Key ][ 'Trigger' ] ) {

	        	throw new ImplementationException( 'You cannot use GetOption on triggers (event handlers)' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Merged[ $Key ];

	    }

	    /* ======================================================================================================
	       TRIGGER
	    ====================================================================================================== */

	    /**
	     * Executes a trigger
	     *
	     * @access public
	     * @param string $Key
	     * @param array $Arguments
	     * @uses Options::MergeSets
	     * @throws \Bytes\ImplementationException Raised when option is not declared
	     * @throws \Bytes\ImplementationException Raised if not a trigger
	     * @return mixed
	     */

	    public function Trigger ( string $Key , array $Arguments = [] ) {

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       MERGE
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    $this -> MergeSets();

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! isset( $this -> Triggers[ $Key ] ) ) {

	        	// If the trigger isn't defined, we just return NULL
	        	// We want to allow that a trigger is not defined

	        	return;

	        	// throw new ImplementationException( sprintf( 

	        	// 	'Trigger %s is not injected.',

	        	// 	$Key

	        	// ) );

	        }

	        // Check that the option is not a trigger

	        if ( ! $this -> Options[ $Key ][ 'Trigger' ] ) {

	        	throw new ImplementationException( 'You can only use triggers (event handlers) on Trigger' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Triggers[ $Key ] -> Fire( $Arguments );

	    }

	    /* ======================================================================================================
	       GET UNPOPULATED TRIGGERS
	    ====================================================================================================== */

	    /**
	     * Return a list of unpopulated triggers
	     *
	     * @access public
	     * @param \Bytes\HooksContainer $HooksContainer
	     * @throws \Bytes\ImplementationException Raised if Options are not sealed
	     * @return void
	     */
	    
	    public function PopulateTriggers ( HooksContainer $HooksContainer , ObjectBuilder $ObjectBuilder ) {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! is_null( $this -> Triggers ) ) {

	        	// Already populated - no need to repeat it :)

	        	return;

	        } else if ( ! $this -> Sealed ) {

	        	throw new ImplementationException( 'Options must be sealed first in Options::PopulateTriggers' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           FIND
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $this -> Customized as $Key => $Customized ) {

	        	if ( $this -> Options[ $Key ][ 'Trigger' ] ) {

	        		$Namespace 					= (string) $Customized[ 'Namespace' ];
	        		$HookName 					= (string) $Customized[ 'HookName' ];

	        		$this -> Triggers[ $Key ] 	= $ObjectBuilder -> CreateHook(

    												$HooksContainer -> GetById( $Namespace , $HookName )

    											);

	        	}

	        }

	    }

	    /* ======================================================================================================
	       MERGE SETS
	    ====================================================================================================== */

	    /**
	     * Merges default values together with user-customized values.
	     * It will also verify that required options are set.
	     *
	     * @access protected
	     * @throws \Bytes\ImplementationException Raised if a required option has not been defined
	     * @return void
	     */
	    
	    protected function MergeSets ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           ENSURE UNDONE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( is_array( $this -> Merged ) || ! $this -> Sealed ) {

	        	return;

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           MERGE
	        ------------------------------------------------------------------------------------------------------ */
	        
	        foreach ( $this -> Options as $DeclaredKey => $Declaration ) {

	        	if ( $Declaration[ 'Required' ] && ! isset( $this -> Customized[ $DeclaredKey ] ) ) {

	        		throw new ImplementationException( sprintf(

	        			'You are required to define the option: %s',

	        			$DeclaredKey

	        		) );

	        	}

	        	$this -> Merged[ $DeclaredKey ] 	= isset( $this -> Customized[ $DeclaredKey ] )
	        											? $this -> Customized[ $DeclaredKey ]
	        											: $Declaration[ 'Default' ];

	        }

	    }

	}