<?php

	/**
	 * Controller abstraction class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;
	
	abstract class Controller extends ExtendedClass {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Store the location of the component that the controller belongs to
	     * @var string
	     */
	    
	    protected $__ComponentLocation;

	    /**
	     * Object builder
	     * @var \Bytes\ObjectBuilder
	     */
	    
	    protected $__ObjectBuilder;

	    /* ======================================================================================================
	       CONSTRUCTOR
	    ====================================================================================================== */

	    /**
	     * Prepare the controller class
	     * 
	     * @final Make sure the constructor method cannot be overriden
	     * @access public
	     * @return void
	     */
	    
	    final public function __construct ( string $ComponentLocation , \Bytes\ObjectBuilder $ObjectBuilder ) {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	    	$this -> __ComponentLocation 		= (string) $ComponentLocation;
	    	$this -> __ObjectBuilder 			= $ObjectBuilder;

	    }

	    /* ======================================================================================================
	       GET COMPONENT LOCATION
	    ====================================================================================================== */

	    /**
	     * Return the component location
	     *
	     * @since v1.2.0
	     * @access public
	     * @return string
	     */
	    
	    public function GetComponentLocation ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __ComponentLocation;

	    }

	    /* ======================================================================================================
	       ENVIRONMENT
	    ====================================================================================================== */

	    /**
	     * Access the Environment object via InjectionContainer
	     *
	     * @final
	     * @access protected
	     * @return Environment
	     */
	    
	    final protected function Environment ( ): Environment {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __InjectionContainer -> Retrieve( 'Environment' );

	    }

	    /* ======================================================================================================
	       VISUAL(IZE)
	    ====================================================================================================== */

	    /**
	     * Create a visualization object
	     *
	     * @final
	     * @access protected
	     * @param string $Name
	     * @return \Bytes\Visual
	     */
	    
	    final protected function Visual ( string $Name ): Visual {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __ObjectBuilder -> CreateVisual( $this -> __ComponentLocation , $Name );

	    }

	    /* ======================================================================================================
	       MODEL
	    ====================================================================================================== */

	    /**
	     * Create a model object
	     *
	     * @final
	     * @access protected
	     * @param string $Name
	     * @return \Bytes\Model
	     */
	    
	    final protected function Model ( string $Name ): Model {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> __ObjectBuilder -> CreateModel( $this -> __ComponentLocation , $Name );

	    }

	    /* ======================================================================================================
	       CONTROLLER
	    ====================================================================================================== */

	    /**
	     * Call a controller from inside the component
	     *
	     * @since v1.2.0
	     * @final
	     * @access protected
	     * @param string $Controller
	     * @param string $Method
	     * @return mixed
	     */
	    
	    final protected function Controller ( string $Controller , string $Method ) {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Employ( basename( $this -> __ComponentLocation ) ) -> Controller( $Controller , $Method );

	    }

	}