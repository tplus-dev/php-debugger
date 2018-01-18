<?php

	/**
	 * Files services
	 *
	 * @author Mark Hünermund Jensen <mark@hunermund.dk>
	 * @since v1.0.0
	 * @package Services
	 * @subpackage Files
	 */

	namespace Bytes\Services;

	class Files extends \Bytes\Service {

	    /* ======================================================================================================
	       TRIM TRAILING
	    ====================================================================================================== */

	    /**
	     * Helper function to fully trim trailing slashes
	     *
	     * @access protected
	     * @param string $Path
	     * @return string
	     */
	    
	    protected function TrimTrailing ( string $Path ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return rtrim( rtrim( $Path , '/' ) , '\\' );

	    }

	    /* ======================================================================================================
	       COPY DIRECTORY
	    ====================================================================================================== */

	    /**
	     * Recursively copies a directory to a destination
	     *
	     * @access public
	     * @param string $Source Source directory
	     * @param string $Destination Where to copy the source (this location must exist)
	     * @return Files
	     */
	    
	    public function CopyDirectory ( string $Source , string $Destination ): Files { 

	        /* ------------------------------------------------------------------------------------------------------
	           FILES
	        ------------------------------------------------------------------------------------------------------ */

	        $Files 				= array_merge(

	        						// List all files and folders

	        						glob( $this -> TrimTrailing( $Source ) . '/*' ),

	        						// We have to explicity request hidden files, otherwise they are not
	        						// returned by glob, meaning that .htaccess files would not be found
	        						
	        						glob( $this -> TrimTrailing( $Source ) . '/.*' )

	        					);

	        // Set the target directory where the final slash is trimmed

	        $TargetDirectory 	= $this -> TrimTrailing( $Destination );

	        foreach ( $Files as $I => $Filename ) {

	        	$Basename 		= basename( $Filename );

	        	// Skip . and .. to avoid recursive issues

	        	if ( $Basename === '.' || $Basename === '..' ) {

	        		continue;

	        	}

	        	// Set the destination filename

	        	$DestinationFilename 	= $TargetDirectory . '/' . $Basename;

	        	// If the source target is directory, we'll create it and run this method again (recursively)
	        	// to copy the content of that particular folder

	        	if ( is_dir( $Filename ) ) {

	        		// Create new target folder

	        		mkdir( $DestinationFilename );

	        		// Recursively copy the new sub-folder

	        		$this -> CopyDirectory( $Filename , $DestinationFilename );

	        		// echo PHP_EOL . $Filename . PHP_EOL . $DestinationFilename . PHP_EOL;

	        	} else {

	        		// If it's a file, copy it

	        		copy( $Filename , $DestinationFilename );

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       UNSCRAWL FILES
	    ====================================================================================================== */

	    /**
	     * Replaces placeholders in all files in the listed directory.
	     * Method work recursively in sub-folders, so caution is advised, because the method can be
	     * performance intensive.
	     *
	     * @access public
	     * @param string $Directory
	     * @param array $Data
	     * @throws \Bytes\Exception Raised if $Directory is not a directory
	     * @uses Files::UnscrawlFile
	     * @return Files
	     */
	    
	    public function UnscrawlFiles ( string $Directory , array $Data ): Files {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        // Throw an exception if the directory is not found

	        if ( ! is_dir( $Directory ) ) {

	        	throw new Exception( $Directory . ' is not a directory.' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           LOAD FILES
	        ------------------------------------------------------------------------------------------------------ */

	        // Load the files in the directory

	        $Files 					= glob( $this -> TrimTrailing( $Directory ) . '/*' );

	        foreach ( $Files as $I => $Filename ) {

	        	// If the target is a directory, we call this method again

	        	if ( is_dir( $Filename ) ) {

	        		$this -> UnscrawlFiles( $Filename , $Data );

	        	} else {

	        		// If it's a file, unscrawl it

	        		$this -> UnscrawlFile( $Filename , $Data );

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       UNSCRAWL FILE
	    ====================================================================================================== */

	    /**
	     * Do what UnscrawlFiles does, only to a single file
	     *
	     * @access public
	     * @param string $Filename
	     * @param array $Data
	     * @throws \Bytes\Exception Raised if $Filename does not exist
	     * @return Files
	     */
	    
	    public function UnscrawlFile ( string $Filename , array $Data ): Files {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! file_exists( $Filename ) ) {

	        	throw new \Bytes\Exception( 'No file found at: ' . $Filename );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           REPLACE
	        ------------------------------------------------------------------------------------------------------ */

    		$Content 		= file_get_contents( $Filename );

    		// Replace the placeholders in the file data, with the passed data (key/value pairs)

    		foreach ( $Data as $Find => $ReplaceWith ) {

    			$Content 		= str_ireplace( '*{' . $Find . '}*' , $ReplaceWith , $Content );

    		}

    		// Save the data in the file again

    		file_put_contents( $Filename , $Content );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	}