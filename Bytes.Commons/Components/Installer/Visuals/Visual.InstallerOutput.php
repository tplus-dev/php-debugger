<?php

	namespace Bytes\Components\Installer;

	class VisualInstallerOutput extends \Bytes\Visual {

	    /* ======================================================================================================
	       RENDER
	    ====================================================================================================== */

	    /**
	     * Load and render the InstallerOutput template
	     *
	     * @access public
	     * @return string
	     */
	    
	    public function Render ( ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $this -> LoadTemplate( 'InstallerOutput' );

	    }

	}