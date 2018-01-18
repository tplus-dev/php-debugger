<?php

	/**
	 * Database analyzer model class
	 *
	 * @author Mark Hünermund Jensen
	 */
	
	namespace Bytes\Components\Installer;

	class MdlDatabaseAnalyst extends \Bytes\Model {

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

	    	$Dependencies -> Must() -> Provide( 'Database' );

	    }

	    /* ======================================================================================================
	       ANALYZE SCHEMA
	    ====================================================================================================== */

	    /**
	     * Collect relevant information on tables, columns, indices, etc. and deliver it in a format
	     * compatible with Installer declaration
	     *
	     * @access public
	     * @return array
	     */
	    
	    public function AnalyzeSchema ( ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Result 				= [];

	        /* ------------------------------------------------------------------------------------------------------
	           TABLES
	        ------------------------------------------------------------------------------------------------------ */

	        $Tables 				= $this -> Employ( 'Database' ) -> Query( 'SHOW TABLES' );

	        foreach ( $Tables as $I => $Table ) {

	        	$TableName 				= array_values( $Table )[ 0 ];

	        	$Result[ $TableName ] 	= [

											'Columns' 	=> $this -> Columns( $TableName ),
											'Indices' 	=> $this -> Indices( $TableName )

										];

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Result;

	    }

	    /* ======================================================================================================
	       COLUMNS
	    ====================================================================================================== */

	    /**
	     * Analyze columns in a table, and parse it to match the declaration structure of Installer
	     * DatabaseSchema
	     *
	     * @access public
	     * @param string $TableName
	     * @return array
	     */

	    public function Columns ( string $TableName ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Result 				= [];

	        $Columns 				= $this -> Employ( 'Database' ) -> Query(

	        							sprintf( 'SHOW COLUMNS IN %s' , $this -> SafeParameter( $TableName ) )

	        						);

	        $ForeignKeys 			= $this -> Employ( 'Database' ) -> Query( 'SELECT 

											*

										FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS

										WHERE
										  	TABLE_NAME = :Table',

										[
											':Table' 		=> $TableName
										]

	        						);

	        /* ------------------------------------------------------------------------------------------------------
	           ANALYZE COLUMNS
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Columns as $I => $Column ) {

	        	$Arguments 			= [];
	        	$ForeignKey 		= [];

	        	foreach ( $ForeignKeys as $J => $FK ) {

	        		$FKTableName 	= $FK[ 'TABLE_NAME' ];
	        		$FKColumnName 	= $FK[ 'UNIQUE_CONSTRAINT_SCHEMA' ];

	        		if (

	        				strtolower( $FKTableName ) == strtolower( $TableName )
	        			&&
	        				strtolower( $FKColumnName ) == strtolower( $Column[ 'Field' ] )

	        		) {

	        			$ForeignKey 	= [

	        								'ReferenceTable' 	=> $FK[ 'REFERENCED_TABLE_NAME' ],
	        								'ReferenceColumn' 	=> $FK[ 'UNIQUE_CONSTRAINT_NAME' ],
	        								'OnUpdate' 			=> $FK[ 'UPDATE_RULE' ],
	        								'OnDelete' 			=> $FK[ 'DELETE_RULE' ],
	        								'Drop' 				=> False

	        							];

	        		}

	        	}

	        	if ( stripos( $Column[ 'Type' ] , '(' ) !== False ) {

		        	$Quotes 				= [ '"' , '\'' ];
		        	$Arguments 				= explode( ',' , preg_replace( '/^.*?\(([^\)]+)\).*?$/' , '$1' , $Column[ 'Type' ] ) );

		        }

	        	// Make sure that string arguments, are stripped for the prepended and appended quotes

	        	foreach ( $Arguments as $K => $Argument ) {

	        		if ( in_array( $Argument[ 0 ] , $Quotes ) && in_array( $Argument[ strlen( $Argument ) - 1 ] , $Quotes ) ) {

	        			$Arguments[ $K ] 	= (string) substr( $Argument , 1 , strlen( $Argument ) - 2 );

	        		}

	        	}

	        	array_push( $Result , [

	        		'Name' 				=> $Column[ 'Field' ],
	        		'DataType' 			=> strtoupper( preg_replace( '/^([a-zA-Z]+).*?$/' , '$1' , $Column[ 'Type' ] ) ),
					'Unsigned' 			=> stripos( $Column[ 'Type' ] , 'unsigned' ) !== False ? True : False,
					'Arguments' 		=> $Arguments,
	        		'Null' 				=> $Column[ 'Null' ] === 'YES' ? True : False,
	        		'AutoIncrement' 	=> $Column[ 'Extra' ] === 'auto_increment' ? True : False,
					'Default' 			=> $Column[ 'Default' ] ? $Column[ 'Default' ] : Null,
					'PrimaryKey' 		=> $Column[ 'Key' ] === 'PRI' ? True : False,
					'ForeignKey' 		=> $ForeignKey,
					'Unique' 			=> False

	        	] );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Result;

	    }

	    /* ======================================================================================================
	       INDICES
	    ====================================================================================================== */

	    /**
	     * Analyze columns in a table, and parse it to match the declaration structure of Installer
	     * DatabaseSchema
	     *
	     * @access public
	     * @param string $TableName
	     * @return array
	     */

	    public function Indices ( string $TableName ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Result 				= [];

	        $Indices 				= $this -> Employ( 'Database' ) -> Query(

	        							sprintf( 'SHOW INDEXES IN %s' , $this -> SafeParameter( $TableName ) )

	        						);

	        /* ------------------------------------------------------------------------------------------------------
	           ANALYZE COLUMNS
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Indices as $I => $Index ) {

	        	// Import the basic values for readability

	        	$IndexName 				= (string) $Index[ 'Key_name' ];
	        	$Column 				= (string) $Index[ 'Column_name' ];
	        	$Unique 				= $Index[ 'Non_unique' ] ? False : True;

	        	// If the index name is already in the array it means the index consists of multiple
	        	// columns

	        	if ( ! isset( $Result[ $IndexName ][ 'Columns' ] ) ) {

		        	$Result[ $IndexName ]  	= [

								        		'Name' 			=> $IndexName,
								        		'Unique' 		=> $Unique,
								        		'Columns' 		=> [],
								        		'Drop' 			=> False

								        	];

				}

				// The column is "sorted" (position) by the Seq_in_index key

				$Result[ $IndexName ][ 'Columns' ][ $Index[ 'Seq_in_index' ] - 1 ] 	

											= $Column;

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	    	return $Result;

	    }

	    /* ======================================================================================================
	       SAFE NAME
	    ====================================================================================================== */

	    /**
	     * Access helper method
	     *
	     * @access protected
	     * @param string $Name
	     * @return string
	     */
	    
	    protected function SafeParameter ( string $Name ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Employ( 'QueryFactory' ) -> SafeParameter( $Name );

	    }

	}