<?php

	/**
	 * Model abstracttion class
	 * 
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes;

	abstract class Model extends ExtendedClass {

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
	    
	    final public function __construct ( ) {



	    }

	}