<?php

	/**
	 * Discovery visual
	 *
	 * @author *{Author}*
	 */

	namespace Bytes\Components\ErrorLog;

	class VisualDefault extends \Bytes\Visual {

	    /* ======================================================================================================
	       RENDER
	    ====================================================================================================== */

	    /**
	     * Load and render the Default visual
	     *
	     * @access public
	     * @throws \Bytes\Exception Raised if no template is set
	     * @return string
	     */
	    
	    public function Render ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! isset( $this -> __EmbeddedData[ 'Template' ] ) ) {

	        	throw new \Bytes\Exception( 'Please define a template to be injected into the scope.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SCOPE
	        ------------------------------------------------------------------------------------------------------ */

	        $Template 			= $this -> __EmbeddedData[ 'Template' ];
	        $Scope 				= $this -> Scope( 'Default' );

	        $Scope -> Query( 'main' ) -> HTML(

	        	$this -> LoadTemplate( $Template )

	        );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Scope -> Render();

	    }

	}