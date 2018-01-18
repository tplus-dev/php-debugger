<?php

    /* ======================================================================================================
       INITIALIZE
    ====================================================================================================== */

    namespace Bytes;
    
    /* ======================================================================================================
       CLASSES
    ====================================================================================================== */

    // Load abstraction classes

    require_once 		__DIR__ . '/Classes/Abstract.StdClass.php';
    require_once        __DIR__ . '/Classes/Abstract.ExtendedClass.php';
    require_once		__DIR__ . '/Classes/Abstract.Controller.php';
    require_once		__DIR__ . '/Classes/Abstract.Model.php';
    require_once        __DIR__ . '/Classes/Abstract.Visual.php';
    require_once        __DIR__ . '/Classes/Abstract.Implementation.php';
    require_once        __DIR__ . '/Classes/Abstract.Component.php';
    require_once        __DIR__ . '/Classes/Abstract.Service.php';
    require_once        __DIR__ . '/Classes/Abstract.Service.Parser.php';
    require_once        __DIR__ . '/Classes/Abstract.Hook.php';
    require_once        __DIR__ . '/Classes/Abstract.RouterGuide.php';

    // Load misc. classes used more or less everywhere

    require_once		__DIR__ . '/Classes/ObjectBuilder.php';
    require_once		__DIR__ . '/Classes/Environment.php';
    require_once 		__DIR__ . '/Classes/InjectionContainer.php';
    require_once        __DIR__ . '/Classes/Dependencies.php';
    require_once        __DIR__ . '/Classes/Router.php';
    require_once 		__DIR__ . '/Classes/Application.php';
    require_once        __DIR__ . '/Classes/GlobalScope.php';
    require_once        __DIR__ . '/Classes/Options.php';
    require_once        __DIR__ . '/Classes/Header.php';
    require_once        __DIR__ . '/Classes/Scope.php';
    require_once        __DIR__ . '/Classes/HooksContainer.php';

    // Routers (special routing types)
    
    require_once        __DIR__ . '/Classes/Routers/ErrorHandle.php';
    require_once        __DIR__ . '/Classes/Routers/RegExp.php';

    /* ======================================================================================================
       EXCEPTIONS
    ====================================================================================================== */

    class Exception extends \Exception {

        /* ======================================================================================================
           PROPERTIES
        ====================================================================================================== */

        /**
         * Error code
         * @var string
         */
        
        private $ErrorCode 			= '';

        /**
         * Options
         * @var array
         */
        
        private $Options            = [];

        /* ======================================================================================================
           CONSTRUCTOR
        ====================================================================================================== */

        /**
         * Replace original Exception constructor
         *
         * @access public
         * @param string $Message Error message
         * @param mixed $ErrorCode Error code. You can also pass for instance 404 (as int) to automatize HTTP header
         * @param array $Options Additional setup, for instance HTTP header codes (404, etc.)
         * @return void
         */
        
    	public function __construct ( string $Message , $ErrorCode = '' , array $Options = array() ) {

            /* ------------------------------------------------------------------------------------------------------
               DEFINE VARIABLES
            ------------------------------------------------------------------------------------------------------ */

    		$this -> message 		= (string) $Message;
    		$this -> ErrorCode 		= $ErrorCode;
            $this -> Options        = $Options;

            /* ------------------------------------------------------------------------------------------------------
               HEADER
            ------------------------------------------------------------------------------------------------------ */

            // If the headers aren't sent, and the $ErrorCode is passed as a 3 digit integer, we'll
            // work by the assumption that the user wants to create errors like 404 Not Found

            if ( is_int( $ErrorCode ) && strlen( $ErrorCode ) === 3 && ! headers_sent() ) {

                http_response_code( $ErrorCode );

            }

    	}

        /* ======================================================================================================
           GET ERROR CODE
        ====================================================================================================== */

        /**
         * Returns the error code
         *
         * @access public
         * @return mixed
         */

        public function GetErrorCode ( ) {

            /* ------------------------------------------------------------------------------------------------------
               RETURN
            ------------------------------------------------------------------------------------------------------ */

            return $this -> ErrorCode;

        }

        /* ======================================================================================================
           GET OPTIONS
        ====================================================================================================== */

        /**
         * Returns the options that were passed to the Exception
         *
         * @access public
         * @return array
         */
        
        public function GetOptions ( ): array {

            /* ------------------------------------------------------------------------------------------------------
               RETURN
            ------------------------------------------------------------------------------------------------------ */

            return $this -> Options;

        }

    }

    // Exceptions that extend the \Bytes\Exception class

    class FileNotFoundException extends Exception { }
    
    // The ConfigurationException is thrown for the developer of an application
    // and is regarding the start-up process of an application

    class ConfigurationException extends Exception { }

    // For the developer of a component the ComponentException is thrown
    
    class ComponentException extends Exception { }
    class ServiceException extends Exception { }
    class ImplementationException extends Exception { }
    class CLIException extends Exception { }