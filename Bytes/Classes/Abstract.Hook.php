<?php

	/**
	 * Hooks abstraction class
	 *
	 * @abstract
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @package Hooks
	 * @since v1.1.0
	 */
	
	namespace Bytes;

	abstract class Hook extends ExtendedClass {

	    /* ======================================================================================================
	       ABSTRACT METHODS
	    ====================================================================================================== */

	    /* ------------------------------------------------------------------------------------------------------
	       ON FIRE
	    ------------------------------------------------------------------------------------------------------ */

	    /**
	     * The OnFire method is called when the hook is fired
	     *
	     * @abstract
	     * @access protected
	     * @param array $Arguments
	     * @param Environment $Environment
	     * @return mixed
	     */
	    
	    abstract protected function OnFire ( Environment &$Environment ,  array $Arguments = [] );

	    /* ======================================================================================================
	       TRIGGER
	    ====================================================================================================== */

	    /**
	     * Trigger the handle
	     *
	     * @final
	     * @access public
	     * @param array $Arguments
	     * @return mixed
	     */
	    
	    final public function Fire ( array $Arguments ) {

	   	    /* ------------------------------------------------------------------------------------------------------
	   	       INITIALIZE
	   	    ------------------------------------------------------------------------------------------------------ */

	   	    $Environment 			= $this -> __InjectionContainer -> Retrieve( 'Environment' );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> OnFire( $Environment , $Arguments );

	    }

	}