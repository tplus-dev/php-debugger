<?php

	namespace Bytes\Components\Users;

	class Manifest extends \Bytes\Components\Installer\Manifest {

	    /* ======================================================================================================
	       DATABASE SCHEMA
	    ====================================================================================================== */

	    /**
	     * Design and define the database schema using the DatabaseSchema object
	     *
	     * @access public
	     * @param \Bytes\Components\Installer\DatabaseSchema $DatabaseSchema
	     * @return void
	     */
	    
	    public function DatabaseSchema ( \Bytes\Components\Installer\DatabaseSchema &$DatabaseSchema ) {

	        /* ------------------------------------------------------------------------------------------------------
	           USERS
	        ------------------------------------------------------------------------------------------------------ */

	    	$DatabaseSchema -> Table( 'Users' );

	    	$DatabaseSchema -> Column( 'UserId' , 'INT' , 10 ) -> PrimaryKey() -> AutoIncrement() -> Unsigned() -> NotNull();
	    	$DatabaseSchema -> Column( 'Email' , 'VARCHAR' , 255 );
	    	$DatabaseSchema -> Column( 'Password' , 'VARCHAR' , 255 );

	        /* ------------------------------------------------------------------------------------------------------
	           USER DATA
	        ------------------------------------------------------------------------------------------------------ */

	        $DatabaseSchema -> Table( 'Users_Authentications' );

	    	$DatabaseSchema
	    		-> Column( 'UserId' , 'INT' , 10 )
	    			-> Unsigned()
	    			-> NotNull()
	    		-> ForeignKey( 'FK_Authencations_Users' )
	    			-> References( 'Users' , 'UserId' )
	    			-> OnDelete( 'CASCADE' )
	    			-> OnUpdate( 'CASCADE' );

	    	$DatabaseSchema -> Column( 'Platform' , 'VARCHAR' , 20 );
	    	$DatabaseSchema -> Column( 'PlatformId' , 'VARCHAR' , 32 );

	        /* ------------------------------------------------------------------------------------------------------
	           USER DATA
	        ------------------------------------------------------------------------------------------------------ */

	        $DatabaseSchema -> Table( 'Users_Profiles' );

	    	$DatabaseSchema
	    		-> Column( 'UserId' , 'INT' , 10 )
	    			-> Unsigned()
	    			-> NotNull()
	    		-> ForeignKey( 'FK_UserId_Users' )
	    			-> References( 'Users' , 'UserId' )
	    			-> OnDelete( 'CASCADE' )
	    			-> OnUpdate( 'CASCADE' );

	    	$DatabaseSchema -> Column( 'FirstName' , 'VARCHAR' , 50 );
	    	$DatabaseSchema -> Column( 'LastName' , 'VARCHAR' , 50 );
	    	$DatabaseSchema -> Column( 'ProfilePhotoURL' , 'VARCHAR' , 255 );
	    	$DatabaseSchema -> Column( 'Gender' , 'CHAR' , 1 );

	        /* ------------------------------------------------------------------------------------------------------
	           USER DATA
	        ------------------------------------------------------------------------------------------------------ */

	        $DatabaseSchema -> Table( 'Users_PasswordRecoveries' );

	    	$DatabaseSchema
	    		-> Column( 'UserId' , 'INT' , 10 )
	    			-> Unsigned()
	    			-> NotNull()
	    		-> ForeignKey( 'FK_Users_PasswordRecoveries_UserId' )
	    			-> References( 'Users' , 'UserId' )
	    			-> OnDelete( 'CASCADE' )
	    			-> OnUpdate( 'CASCADE' );

	    	$DatabaseSchema -> Column( 'Code' , 'VARCHAR' , 255 );
	    	$DatabaseSchema -> Column( 'DateCreated' , 'DATETIME' );

	    }

	}