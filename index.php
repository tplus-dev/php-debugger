<?php

    /* ======================================================================================================
       SESSION
    ====================================================================================================== */

    session_start();

    /* ======================================================================================================
       LOAD BYTES FRAMEWORK
    ====================================================================================================== */

    require_once        __DIR__ . '/Functions.php';
    require_once 		__DIR__ . '/Bytes/Initialize.php';

    /* ======================================================================================================
       APPLICATION
    ====================================================================================================== */

    try {

        /* ------------------------------------------------------------------------------------------------------
           RUN APPLICATION
        ------------------------------------------------------------------------------------------------------ */

	    echo htmlspecialchars_decode( 

            ( new \Bytes\Application )

	    	// Run the basic configuration

	    	-> Configure( function ( &$Options ) {

            } )

            // Configure the environment

            -> ConfigureEnvironment( function ( &$Environment ) {

                // Set path

                $Environment -> SetPath( __DIR__ );

                switch ( (string) $_SERVER[ 'HTTP_HOST' ] ) {

                    case 'errorlog.local':
                        $Environment -> SetEnvironment( \Bytes\Environment::Development );
                        $Environment -> SetURL( 'http://errorlog.local/' );
                        break;

                    case 'localhost':
                        $Environment -> SetEnvironment( \Bytes\Environment::Development );
                        $Environment -> SetURL( 'http://localhost/errorlog/' );
                        break;

                }

            } )

	    	// Preload component

	    	-> PreloadComponent( __DIR__ . '/Components/ErrorLog/' )

            // Preload error handler

            -> PreloadComponent( __DIR__ . '/Components/ErrorHandler/' )

            // Load and configure database

            -> UseImplementation( __DIR__ . '/Bytes.Commons/Services/Database' , function ( &$Options , &$Env ) {

                // Configure the database credentials based on hostname

                switch ( (string) $_SERVER[ 'HTTP_HOST' ] ) {

                    case 'localhost':

                        // $Options -> Set( 'DatabaseName' , '' );
                        // $Options -> Set( 'User' , '' );
                        // $Options -> Set( 'Password' , '' );
                        // $Options -> Set( 'Host' , '' );

                        break;

                }

            }  )

            // Ready load of installer component

            -> UseImplementation( __DIR__ . '/Bytes.Commons/Components/Installer' , function ( &$Options ) {

                // Define access key

                $Options -> Set( 'Key' , '' );

                // Define implementations

                $Options -> Set( 'Implementations' , [
                    __DIR__ . '/Components/ErrorLog/'
                ] );

            } )

            // Parsers

            -> UseImplementation( __DIR__ . '/Services/Parsers/Money/' , Null , [ 'Groups' => [ 'Parsers' ] ] )
            -> UseImplementation( __DIR__ . '/Services/Parsers/Dates/' , Null , [ 'Groups' => [ 'Parsers' ] ] )
            -> UseImplementation( __DIR__ . '/Services/Parsers/Numbers/' , Null , [ 'Groups' => [ 'Parsers' ] ] )

            // Create the error handle

            -> AssignErrorHandler( __DIR__ . '/Components/ErrorHandler/' )

            // Start the application where the second argument of the CLI acts as the route

	    	-> Start( (string) $_GET[ 'Route' ] , function ( $App , $Output ) {

		    	return $App;

		    } )

		    // The Application object is returned by the Start method, and we can call the Render method here

		    -> Render() );

    } catch ( Exception $E ) {

    	echo [

            'Succss'    => False,
            'Error'     => $E -> getMessage()

        ];

    }