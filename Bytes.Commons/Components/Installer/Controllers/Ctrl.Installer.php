<?php

	/**
	 * Installer controller
	 */
	
	namespace Bytes\Components\Installer;

	class CtrlInstaller extends \Bytes\Controller {

	    /* ======================================================================================================
	       DEPENDENCIES
	    ====================================================================================================== */

	    /**
	     * Define the dependencies required under various circumstances in this controller
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
	       GET INTENTIONS
	    ====================================================================================================== */

	    /**
	     * Helper method to retrieve the list of intentions
	     *
	     * @access protected
	     * @return array
	     */
	    
	    protected function GetIntentions ( ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Queries 					= [];

			$Implementations 			= $this -> Option( 'Implementations' );

			$QueryFactory 				= new DatabaseQueryFactory;

			$IJ 						= new \Bytes\InjectionContainer;

			$IJ -> Attach( 'QueryFactory' , $QueryFactory );

			$Manifests 					= $this -> Model( 'Manifests' ) -> Collect( $Implementations );
			$ExistingDatabase 			= $this -> Model( 'DatabaseAnalyst' ) -> Inject( $IJ ) -> AnalyzeSchema( );

		    /* ------------------------------------------------------------------------------------------------------
		       OPERATE
		    ------------------------------------------------------------------------------------------------------ */

		    foreach ( $Manifests as $ImplementationName => $ManifestFilename ) {

		    	$ClassName 				= sprintf( '\\Bytes\\Components\\%s\\Manifest' , $ImplementationName );

		    	require_once $ManifestFilename;

		    	$Manifest 				= new $ClassName;

		    	$DatabaseSchema 		= new DatabaseSchema;

		    	$Manifest -> DatabaseSchema( $DatabaseSchema );

		    	$Declarations 			= $DatabaseSchema -> GetDeclarations();

		    	$TheseQueries 			= $QueryFactory -> Queries( $Declarations , $ExistingDatabase );

		    	foreach ( $TheseQueries as $I => $ThisQuery ) {
		    		$Queries[] 			= $ThisQuery;
		    	}

		    }

		    /* ------------------------------------------------------------------------------------------------------
		       RETURN
		    ------------------------------------------------------------------------------------------------------ */

		    return [

		    	'Queries' 		=> $Queries,
		    	'Descriptions' 	=> $QueryFactory -> GetDescriptions()

		    ];

	    }

	    /* ======================================================================================================
	       INDEX
	    ====================================================================================================== */

	    /**
	     * Show the end user what we're intending to do
	     *
	     * @access public
	     * @return string
	     */
	    
	    public function ShowIntentions ( \Bytes\GlobalScope $GlobalScope ): string {

		    /* ------------------------------------------------------------------------------------------------------
		       CREATE VISUAL
		    ------------------------------------------------------------------------------------------------------ */

		    $Visual 					= $this -> Visual( 'InstallerOutput' );

		    /* ------------------------------------------------------------------------------------------------------
		       OUTPUT
		    ------------------------------------------------------------------------------------------------------ */

		    if ( $GlobalScope -> GET( 'Confirm' ) === '1' ) {

			    $Visual -> Embed(

			    	'Output',

			    	$this -> Model( 'DatabaseQueryRunner' ) -> RunQueries(

			    		$this -> GetIntentions()[ 'Queries' ]

			    	)

			    );

		    } else {

		    	$Output 				= $this -> GetIntentions();

		    	$Visual -> Embed( 'Output' , $Output[ 'Queries' ] );
			    $Visual -> Embed( 'Descriptions' , $Output[ 'Descriptions' ] );

		    }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Visual -> Render();

	    }

	}