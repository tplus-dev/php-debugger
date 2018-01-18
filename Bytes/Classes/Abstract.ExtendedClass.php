<?php

	/**
	 * Extended class.
	 * Almost identical to StandardClass, but supports Dependency Injection
	 *
	 * @author Mark Hünermund Jensen
	 */

	namespace Bytes;

	abstract class ExtendedClass extends StandardClass {

	    /* ======================================================================================================
	       DEPENDENCIES
	    ====================================================================================================== */

	    /**
	     * This method can be overridden to define dependencies for the class.
	     * These dependencies will then be injected by the ObjectBuilder.
	     *
	     * @access public
	     * @param \Bytes\Dependencies $Dependencies
	     * @param \Bytes\Environment $Environment
	     * @param array $Context
	     * @return void
	     */
	    
	    public function Dependencies (

	    	\Bytes\Dependencies &$Dependencies,
	    	\Bytes\Environment &$Environment,
	    	array $Context = [] 

	    ) {

	    }

	    /* ======================================================================================================
	       EMPLOY
	    ====================================================================================================== */

	    /**
	     * Shorthand for retrieving an object from the InjectionContainer
	     *
	     * @final
	     * @access protected
	     * @param string $Id
	     * @return mixed
	     */
	    
	    final protected function Employ ( string $Id ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __InjectionContainer -> Retrieve( $Id );

	    }

	}