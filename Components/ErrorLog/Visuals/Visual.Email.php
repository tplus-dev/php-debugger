<?php

	/**
	 * Email visual
	 *
	 * @author Mark <m@m.dk>
	 * @package Email
	 */

	namespace Bytes\Components\Discovery;

	class VisualEmail extends \Bytes\Visual {

	    /* ======================================================================================================
	       RENDER
	    ====================================================================================================== */

	    /**
	     * Load and render the Email visual
	     *
	     * @access public
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

	        $Template 			= 'Email.' . $this -> __EmbeddedData[ 'Template' ];
	        $Scope 				= $this -> Scope( 'Email' );

	        $Scope -> Query( '#Main' ) -> HTML(

	        	$this -> LoadTemplate( $Template )

	        );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Scope -> Render();

	    }

	}