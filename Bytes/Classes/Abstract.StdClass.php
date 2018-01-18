<?php

	/**
	 * Standard class
	 * 
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;
	
	abstract class StandardClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Injection container
	     * @var InjectionContainer
	     */
	    
	    protected $__InjectionContainer;

	    /**
	     * Container for the Options object
	     * @var \Bytes\Options
	     */
	    
	    protected $__Options;

	    /* ======================================================================================================
	       SET INJECTION CONTAINER
	    ====================================================================================================== */

	    /**
	     * Adds the injection container to the object
	     *
	     * @access public
	     * @param InjectionContainer $InjectionContainer
	     * @return StandardClass
	     */
	    
	    public function SetInjectionContainer ( InjectionContainer $InjectionContainer ): StandardClass {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        if ( is_null( $this -> __InjectionContainer ) ) {

	        	$this -> __InjectionContainer 	= $InjectionContainer;

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       INJECT
	    ====================================================================================================== */

	    /**
	     * Insert additional objects
	     *
	     * @final
	     * @access public
	     * @param \Bytes\InjectionContainer $InjectionContainer
	     * @todo Make sure the additionally injected objects are only injected into this instance, and not all IJs
	     * @return StandardClass
	     */
	    
	    final public function Inject ( \Bytes\InjectionContainer $InjectionContainer ): StandardClass {

	        /* ------------------------------------------------------------------------------------------------------
	           MERGE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> __InjectionContainer -> MergeWith( $InjectionContainer );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Access the environment
	     *
	     * @access protected
	     * @return Environment
	     */
	    
	    protected function Environment ( ): Environment {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __InjectionContainer -> Retrieve( 'Environment' );

	    }

	    /* ======================================================================================================
	       (GET) OPTION
	    ====================================================================================================== */

	    /**
	     * Prettier helper method that returns an option from the Options object
	     *
	     * @final
	     * @access protected
	     * @param string $Key
	     * @return mixed
	     */
	    
	    final protected function Option ( string $Key ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __Options -> GetOption( $Key );

	    }

	    /* ======================================================================================================
	       TRIGGER
	    ====================================================================================================== */

	    /**
	     * Helper method to fire a trigger (event handler / hook)
	     *
	     * @final
	     * @access protected
	     * @param string $Key
	     * @param arary $Arguments
	     * @return mixed
	     */
	    
	    final protected function Trigger ( string $Key , array $Arguments = [] ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __Options -> Trigger( $Key , $Arguments );

	    }

	    /* ======================================================================================================
	       SET OPTIONS
	    ====================================================================================================== */

	    /**
	     * Store the Options instance
	     *
	     * @final
	     * @access public
	     * @param \Bytes\Options
	     * @return Component
	     */
	    
	    final public function SetOptions ( \Bytes\Options $Options ): StandardClass {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        if ( is_null( $this -> __Options ) ) {

	        	$this -> __Options 		= $Options;

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	}