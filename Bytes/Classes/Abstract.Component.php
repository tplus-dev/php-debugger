<?php

	/**
	 * Component class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	abstract class Component extends Implementation {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Instance of object builder
	     * @var ObjectBuilder
	     */
	    
	    protected $ObjectBuilder;

	    /* ======================================================================================================
	       CREATE ROUTES
	    ====================================================================================================== */

	    /**
	     * A component will use the CreateRoutes method to declare and reserve routes pointing to its
	     * controllers
	     *
	     * Intention is that this function is overriden when inherited
	     *
	     * @access protected
	     * @param \Bytes\Router $Router
	     * @return void
	     */
	    
	    protected function CreateRoutes ( \Bytes\Router &$Router ) {

	    }

	    /* ======================================================================================================
	       SET OBJECT BUILDER
	    ====================================================================================================== */

	    /**
	     * Set an instance of the ObjectBuilder
	     *
	     * @final
	     * @access public
	     * @param ObjectBuilder
	     * @return Component
	     */
	    
	    final public function SetObjectBuilder ( ObjectBuilder $ObjectBuilder ): Component {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ObjectBuilder 		= $ObjectBuilder;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       INITIALIZE
	    ====================================================================================================== */

	    /**
	     * Prepares the component, for instance by initiating its CreateRoutes method
	     *
	     * @final
	     * @access public
	     * @param Router $Router
	     * @uses Component::GetName
	     * @uses Component::GetDirectory
	     * @uses Component::CreateRoutes
	     * @return void
	     */
	    
	    final public function Initialize ( &$Router ) {

	        /* ------------------------------------------------------------------------------------------------------
	           CREATE ROUTES
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $Router ) {

	        	// We have to reset request type, etc.

	        	$Router -> Reset();

		        // Register this component as the active component in the router

		        $Router -> Component( $this -> GetName() , $this -> GetDirectory() );

		        // Invoke the route creation

				$this -> CreateRoutes( $Router );

		    }

	    }

	    /* ======================================================================================================
	       CREATE CONTROLLER
	    ====================================================================================================== */

	    /**
	     * Instantiate a controller from the component scope
	     *
	     * @final
	     * @access public
	     * @param string $ControllerName
	     * @param string $Method
	     * @param array $Context
	     * @uses Component::GetDirectory
	     * @uses ObjectBuilder::CreateController
	     * @return mixed
	     */
	    
	    final public function Controller ( string $ControllerName , string $Method , array $Context = [] ) {

	        /* ------------------------------------------------------------------------------------------------------
	           EXECUTE
	        ------------------------------------------------------------------------------------------------------ */

	        $Controller 		= $this
	        						-> ObjectBuilder 
	        						-> CreateController(

							        	// Directory from where the controller will be looked for

							        	$this -> GetDirectory(),

							        	// Controller name

							        	$ControllerName,

							        	// Additional data

							        	[
							        		'Method' 		=> $Method
							        	]

							       	);

		    /* ------------------------------------------------------------------------------------------------------
		       TEST CONTROLLER
		    ------------------------------------------------------------------------------------------------------ */

		    // We need to make sure the controller has the requested method
		    
		    if ( ! method_exists( $Controller , $Method ) ) {

		    	throw new ComponentException( sprintf( 

		    		'Method %s not found in controller %s',

		    		$Method,
		    		$ControllerName

		    	) );

		    }

	        /* ------------------------------------------------------------------------------------------------------
	           CREATE
	        ------------------------------------------------------------------------------------------------------ */

	        return $Controller -> $Method(

	        	// The Data object is passed into every controller, regardless of whether they expect it or not

	        	( new \Bytes\GlobalScope ) -> SetRoute( isset( $Context[ 'Route' ] ) ? (string) $Context[ 'Route' ] : '' ),

	        	isset( $Context[ 'ErrorHandler' ] ) ? $Context[ 'Exception' ] : Null,
	        	isset( $Context[ 'ErrorHandler' ] ) ? $this -> __InjectionContainer -> Retrieve( 'Environment' ) : Null,
	        	isset( $Context[ 'ErrorHandler' ] ) ? $this -> __InjectionContainer -> Retrieve( 'Header' ) : Null

	        );

	    }

	}