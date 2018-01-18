<?php

	/**
	 * Database query factory class
	 */
	
	namespace Bytes\Components\Installer;

	class DatabaseQueryFactory {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Keep track of descriptions
	     * @var array
	     */
	    
	    private $Descriptions 			= [];

	    /* ======================================================================================================
	       GET DESCRIPTIONS
	    ====================================================================================================== */

	    /**
	     * Get the descriptions for various queries
	     *
	     * @access public
	     * @return array
	     */
	    
	    public function GetDescriptions ( ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Descriptions;

	    }

	    /* ======================================================================================================
	       QUERIES
	    ====================================================================================================== */

	    /**
	     * Analyzes the declaration output and returns all intended queries
	     *
	     * @access public
	     * @param array $Declarations List of declarations
	     * @param array $Existing Array structure with existing database schema
	     * @return array List of queries
	     */
	    
	    public function Queries ( array $Declarations , array $Existing ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $Queries 				= [];

	        /* ------------------------------------------------------------------------------------------------------
	           ANALYZE
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Declarations as $TableName => $Table ) {

	        	// Sometimes MySQL returns table names in their absolute name, and other times chronically
	        	// lowercased, so we have to traverse through the list to do a proper comparison

	        	$Found 				= False;

	        	foreach ( $Existing as $ExistingTableName => $ExistingTable ) {

	        		if ( strtolower( $ExistingTableName ) === strtolower( $TableName ) ) {

	        			$Found 		= True;

	        			break;

	        		}

	        	}

	        	// If the table has not been found, we'll add a query to the stack
	        	// which creates a table containing the basic of the first column
	        	//
	        	// The column will be altered by later procedures
	        	// 
	        	// Additionally

	        	if ( ! $Found ) {

		        	$Query 				= sprintf( 

		        							'CREATE TABLE `%s` (%s) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci',

		        							$TableName,
		        							$this -> ColumnDefinition( $Table[ 'Columns' ][ 0 ] )

		        						);

		        	// Push to queries result list

		        	array_push( $Queries , $Query );

		        }

	        	// Push queries related to columns

	        	$this -> ColumnQueries(

	        		$TableName,
	        		$Queries,
	        		$Table[ 'Columns' ],
	        		isset( $ExistingTable[ 'Columns' ] ) ? $ExistingTable[ 'Columns' ] : []

	        	);

	        	// Push queries related to indices
	        	
	        	$this -> IndexQueries(

	        		$TableName,
	        		$Queries,
	        		$Table[ 'Indices' ],
	        		isset( $ExistingTable[ 'Indices' ] ) ? $ExistingTable[ 'Indices' ] : []

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------

	           FOREIGN KEYS

	           We have to execute the foreign key queries last, because they may not only depend on other tables
	           and columns to be created, but also require that indices exist on the columns to be included

	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Declarations as $TableName => $Table ) {

	        	foreach ( $Table[ 'Columns' ] as $J => $Column ) {

	        		$ExistingColumn 		= [];

	        		foreach ( $Existing as $K => $ExistingTable ) {

	        			foreach ( $ExistingTable[ 'Columns' ] as $L => $ExistingColumnData ) {

	        				if ( strtolower( $ExistingColumnData[ 'Name' ] ) === strtolower( $Column[ 'Name' ] ) ) {

	        					$ExistingColumn 	= $ExistingColumnData;

	        				}

	        			}

	        		}

	        		$this -> ForeignKeyQueries( $TableName , $Queries , $Column , $ExistingColumn );

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Queries;

	    }

	    /* ======================================================================================================
	       COLUMN DEFINITION
	    ====================================================================================================== */

	    /**
	     * Returns a full column definition based on declarative inputs
	     *
	     * @access public
	     * @param array $Column
	     * @todo Throw an exception when we cannot safely insert the default value (using SafeParameter)
	     * @return string
	     */
	    
	    public function ColumnDefinition ( array $Column ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ValidateColumnDefinition( $Column );

	        /* ------------------------------------------------------------------------------------------------------
	           ARGUMENTS
	        ------------------------------------------------------------------------------------------------------ */

	        $Arguments 			= '';

	        foreach ( $Column[ 'Arguments' ] as $I => $Argument ) {

	        	$Argument 		= $this -> SafeParameter( $Argument );

	        	$Arguments 		.= ( $Arguments ? ', ' : '' ) 
	        						. 
	        						( is_numeric( $Argument ) 
	        							? $Argument 
	        							: sprintf( '"%s"' , $Argument ) 
	        						);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           PERFORM
	        ------------------------------------------------------------------------------------------------------ */

	        $Result 			= sprintf(

	        						'`%s` %s %s',

	        						$this -> SafeParameter( $Column[ 'Name' ] ),

	        							$this -> SafeParameter( $Column[ 'DataType' ] )
	        							. ( $Arguments ? '(' . $Arguments . ')' : '' )
	        							. ( $Column[ 'Unsigned' ] ? ' UNSIGNED' : '' ),

	        						$Column[ 'Null' ] ? 'NULL' : 'NOT NULL'

	        					);

	        if ( $Column[ 'AutoIncrement' ] ) {

	        	$Result 		.= ' AUTO_INCREMENT ' . ( $Column[ 'PrimaryKey' ] ? 'PRIMARY KEY' : 'UNIQUE' );

	        } else if ( $Column[ 'PrimaryKey' ] ) {

	        	$Result 		.= ' PRIMARY KEY';

	        } else if ( $Column[ 'Unique' ] ) {

	        	$Result 		.= ' UNIQUE';

	        }

	        if ( $Column[ 'Default' ] ) {

	        	// We cannot safely start removing random characters from a default value, so we do 
	        	// a little to make it safer

	        	$Result 		.= sprintf( ' DEFAULT "%s"' , addslashes( $Column[ 'Default' ] ) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $Result;

	    }

	    /* ======================================================================================================
	       VALIDATE COLUMN DEFINITION
	    ====================================================================================================== */

	    /**
	     * Validates inconsistencies, contradictions or problems in a column definition
	     *
	     * @access public
	     * @param array $Column
	     * @throws \Bytes\ComponentException Raised if AUTO_INCREMENT and NULL are true at the same time
	     * @throws \Bytes\ComponentException Raised if AUTO_INCREMENT and PRIMARY KEY are not true at the same time
	     * @return void
	     */
	    
	    public function ValidateColumnDefinition ( array $Column ) {

	        /* ------------------------------------------------------------------------------------------------------
	           AUTO_INCREMENT + NULL
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $Column[ 'AutoIncrement' ] && $Column[ 'Null' ] ) {

	        	throw new \Bytes\ComponentException( 'AUTO_INCREMENT and NULL cannot both be true for column ' . $Column[ 'Name' ] );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           AUTO_INCREMENT + PRIMARY KEY
	        ------------------------------------------------------------------------------------------------------ */

	        if ( $Column[ 'AutoIncrement' ] && ! $Column[ 'PrimaryKey' ] ) {

	        	throw new \Bytes\ComponentException( 'AUTO_INCREMENT and PRIMARY KEY both must be true for column ' . $Column[ 'Name' ] );

	        }

	    }

	    /* ======================================================================================================
	       COLUMN QUERIES
	    ====================================================================================================== */

	    /**
	     * Create column related queries
	     *
	     * @access protected
	     * @param string $TableName
	     * @param array $Queries Original queries array
	     * @param array $Columns
	     * @param array $ExistingColumns
	     * @return array
	     */
	    
	    public function ColumnQueries (

	    	string $TableName,
	    	array &$Queries,
	    	array $Columns,
	    	array $ExistingColumns

	    ) {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $TableName 				= $this -> SafeParameter( $TableName );

	        /* ------------------------------------------------------------------------------------------------------
	           PERFORM
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Columns as $I => $Column ) {

	        	// If the table is brand new (no existing columns) and it's the first column, it should
	        	// already have been inserted when we created the table - so let's skip it :)

	        	if ( empty( $ExistingColumns ) && $I == 0 ) {

	        		continue;

	        	}

	        	// Reset previous use of the existing column container

	        	$ExistingColumn 	= [];

	        	// Iterate over existing columns in this table, to see if we can find any existing columns
	        	// We need this later, to determine if we want to ADD or CHANGE columns

	        	foreach ( $ExistingColumns as $I => $ExistingColumnData ) {

	        		// If the column name are identical ...

	        		if ( strtolower( $ExistingColumnData[ 'Name' ] ) === strtolower( $Column[ 'Name' ] ) ) {

	        			// ... Put it in the container

	        			$ExistingColumn 	= $ExistingColumnData;

	        			// And end the loop

	        			break;

	        		}

	        	}

	        	// If the column exists, and it is specifically requested in the manifest that the column
	        	// must drop, we'll proceed to do that

	        	if ( $Column[ 'Drop' ] && $ExistingColumn ) {

	        		$Queries[] 		= sprintf(

	        							'ALTER TABLE `%s` DROP COLUMN `%s`',

	        							$TableName,
	        							$this -> SafeParameter( $Column[ 'Name' ] )

	        						);

	        		// Continue to next column
	        		// We don't need to consider ADD/CHANGE COLUMN situation

	        		continue;

	        	} else if ( $Column[ 'Drop' ] ) {

	        		// If the column is requested to be dropped, but isn't there, we continue
	        		// to avoid unintentionally create it

	        		continue;

	        	}

	        	// If the column exists and require a change, we'll add a CHANGE COLUMN query
	        	
	        	if ( $ExistingColumn ) {

	        		$Difference 		= $this -> ColumnsDiffer( $Column , $ExistingColumn );

					if ( $Difference ) {

			        	$Queries[] 			= sprintf(

			        							'ALTER TABLE `%s` CHANGE COLUMN `%s` %s',

			        							$TableName,
		        								$this -> SafeParameter( $Column[ 'Name' ] ),
			        							$this -> ColumnDefinition( $Column )

			        						);

			        	// Add the description of this difference
			        	
			        	$this -> Descriptions[ count( $Queries ) - 1 ] 	

			        						= $Difference;

			       	}

		        // If, however, it doesn't exist, we'll add the column

		        } else {

		        	$Queries[] 			= sprintf(

		        							'ALTER TABLE `%s` ADD COLUMN %s',

		        							$TableName,
		        							$this -> ColumnDefinition( $Column )

		        						);

		        }

	       	}

	    }

	    /* ======================================================================================================
	       FOREIGN KEY QUERIES
	    ====================================================================================================== */

	    /**
	     * Create foreign key queries
	     *
	     * @access public
	     * @param array $Column
	     * @param array $ExistingColumn
	     * @param array $Queries
	     * @return void
	     */
	    
	    public function ForeignKeyQueries (

	    	string $TableName,
	    	array &$Queries,
	    	array $Column,
	    	array $ExistingColumn

	    ) {

	        /* ------------------------------------------------------------------------------------------------------
	           
	        ------------------------------------------------------------------------------------------------------ */

	        $ForeignKey 				= $Column[ 'ForeignKey' ];
	        $ForeignKeyExists 			= ! empty( $ExistingColumn[ 'ForeignKey' ] ) ? True : False;
	        $Difference 				= False;

	        if ( $ForeignKeyExists && $ForeignKey ) {

	        	$Difference 			= $this -> ForeignKeysDiffer( $ForeignKey , $ExistingColumn[ 'ForeignKey' ] );

	        	if ( $Difference ) {

		        	// Add the description of this difference
		        	
		        	$this -> Descriptions[ count( $Queries ) - 1 ] 		= $Difference;

		        }

	        }

	        if ( $ForeignKey ) {

	        	$ForeignKeyName 		= $ForeignKey[ 'Name' ];

	        	// Ensure referenced table and column are set

	        	if ( ! $ForeignKey[ 'ReferenceTable' ] || ! $ForeignKey[ 'ReferenceColumn' ] ) {

	        		throw new \Bytes\ComponentException(

	        			$Column[ 'Name' ] . ' wants to use foreign keys, but misses reference to table and column' 

	        		);

	        	}

	        	if ( $ForeignKeyExists && ( $Difference || $ForeignKey[ 'Drop' ] ) ) {

	        		$Queries[] 				= sprintf(

		        								'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',

		        								$TableName,
		        								$ForeignKeyName

		        							);

	        		/* IF DELETE REQUEST - CONTINUE */

	        	}

	        	if ( ( ! $ForeignKeyExists || $Difference ) && ! $ForeignKey[ 'Drop' ] ) {

		        	$Queries[] 				= sprintf(

		        								'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES %s(`%s`) ON UPDATE %s ON DELETE %s',

		        								$TableName,
		        								$ForeignKeyName,
		        								$Column[ 'Name' ],
		        								$this -> SafeParameter( $ForeignKey[ 'ReferenceTable' ] ),
		        								$this -> SafeParameter( $ForeignKey[ 'ReferenceColumn' ] ),
		        								$this -> SafeParameter( $ForeignKey[ 'OnUpdate' ] ),
		        								$this -> SafeParameter( $ForeignKey[ 'OnDelete' ] )

		        							);

		        }

	        }

	    }

	    /* ======================================================================================================
	       FOREIGN KEYS DIFFER
	    ====================================================================================================== */

	    /**
	     * Determine if foreign key declaration and existing foreign key differ
	     *
	     * @access public
	     * @param array $ForeignKey Declaration
	     * @param array $ExistingForeignKey Existing
	     * @return mixed String which describes the difference, or False if no difference is found
	     */
	    
	    public function ForeignKeysDiffer ( array $ForeignKey , array $ExistingForeignKey ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $ForeignKey as $Key => $Value ) {

	        	if ( $Key == 'Name' ) {

	        		continue;

	        	}

	        	if ( strtolower( $Value ) != strtolower( $ExistingForeignKey[ $Key ] ) ) {

	        		$Existing 		= $ExistingForeignKey[ $Key ];

	        		// We have found a difference, we don't have to look further

	        		return sprintf(

	        			'Foreign key %s differs in the %s property from existing key ( %s => %s )',

	        			$ForeignKey[ 'Name' ],
	        			$Key,

	        			$Value === False 
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Value )
	        					 	? ( '[' . implode( ',' , $Value ) . ']' )
	        					 	: $Value


	        				),

	        			$Existing === False
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Existing )
	        					 	? ( '[' . implode( ',' , $Existing ) . ']' )
	        					 	: $Existing


	        				)

	        		);

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return False;

	    }

	    /* ======================================================================================================
	       COLUMNS DIFFER
	    ====================================================================================================== */

	    /**
	     * Determines if two columns are sufficiently different to require an update
	     *
	     * @access public
	     * @param array $Column
	     * @param array $ExistingColumn
	     * @return mixed String which describes the difference, or False if no difference is found
	     */
	    
	    public function ColumnsDiffer ( array $Column , array $ExistingColumn ) {

	        /* ------------------------------------------------------------------------------------------------------
	           COMPARE
	        ------------------------------------------------------------------------------------------------------ */

	        // We want to loop through the keys of the column declaration, and see how each key compares
	        // to the keys in the existing column
	        // 
	        // This is possible due to the way DatabaseAnalyst parses an array which match the one created
	        // by DatabaseSchema

	        foreach ( $Column as $Key => $Value ) {

	        	// There's no reason to compare the Drop option, and the ForeignKey option will be checked else where

	        	if ( in_array( $Key , [ 'Drop' , 'ForeignKey' ] ) ) {

	        		continue;

	        	}

	        	// Sometimes the declaration is not provided a value which is always returned by the analysis in MySQL
	        	// For instance you can define INT without informing of its default length of 11 (10 for unsigned),
	        	// so to avoid counting this as a difference which requires an update, we'll add the default value
	        	// into the arguments array

	        	if ( $Key == 'Arguments' ) {

	        		$Value 			= $this -> DefaultArguments( $Column );

	        	}

	        	// Compare this key against existing column

	        	if ( $Value != $ExistingColumn[ $Key ] ) {

	        		$Existing 		= $ExistingColumn[ $Key ];

	        		// We have found a difference, we don't have to look further

	        		return sprintf(

	        			'%s differs in the %s property from existing column ( %s => %s )',

	        			$Column[ 'Name' ],
	        			$Key,

	        			$Value === False 
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Value )
	        					 	? ( '[' . implode( ',' , $Value ) . ']' )
	        					 	: $Value


	        				),

	        			$Existing === False
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Existing )
	        					 	? ( '[' . implode( ',' , $Existing ) . ']' )
	        					 	: $Existing


	        				)

	        		);

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        // No differences were found

	        return False;

	    }

	    /* ======================================================================================================
	       DEFAULT ARGUMENTS
	    ====================================================================================================== */

	    /**
	     * If the arguments array is empty, we'll tank it up here to improve comparison code
	     *
	     * @access public
	     * @param array $Column
	     * @return array
	     */
	    
	    public function DefaultArguments ( array &$Column ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           EMPTY?
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! empty( $Column[ 'Arguments' ] ) ) {

	        	return $Column[ 'Arguments' ];

	        }
	        
	        /* ------------------------------------------------------------------------------------------------------
	           DEFAULTS
	        ------------------------------------------------------------------------------------------------------ */

	        switch ( $Column[ 'DataType' ] ) {

	        	case 'INT': 		return [ $Column[ 'Unsigned' ] ? 10 : 11 ];

	        	default: 			return [];

	        }

	    }

	    /* ======================================================================================================
	       INDEX QUERIES
	    ====================================================================================================== */

	    /**
	     * Create index related queries
	     *
	     * @access protected
	     * @param string $TableName
	     * @param array $Queries Original queries array
	     * @param array $Indices
	     * @param array $ExistingIndices
	     * @return array
	     */
	    
	    public function IndexQueries (

	    	string $TableName,
	    	array &$Queries,
	    	array $Indices,
	    	array $ExistingIndices

	    ) {

	        /* ------------------------------------------------------------------------------------------------------
	           INITIALIZE
	        ------------------------------------------------------------------------------------------------------ */

	        $TableName 				= $this -> SafeParameter( $TableName );

	        /* ------------------------------------------------------------------------------------------------------
	           PERFORM
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Indices as $I => $Index ) {

	        	// By default we assume the index does not differ (let's try to be proven wrong later)

	        	$IndexDiffers 		= False;

	        	// Import the index name
	        	
	        	$IndexName 			= $this -> SafeParameter( $Index[ 'Name' ] );

	        	// Store booleanly if the index exists

	        	$IndexExists 		= isset( $ExistingIndices[ $IndexName ] ) ? True : False;

	        	// ... And if it exists, we want to see if the declaration differs from the existing index
	        	// to see if we have a reason to change the index

	        	if ( $IndexExists ) {

	        		$IndexDiffers 	= $this -> IndexDiffers( $Index , $ExistingIndices[ $IndexName ] );

	        	}

	        	// If the index differs, we cannot apply a CHANGE operation, we'll have to drop the current
	        	// index and re-create it

				if ( $IndexExists && ( $IndexDiffers || $Index[ 'Drop' ] ) ) {

		        	$Queries[] 			= sprintf(

		        							'ALTER TABLE `%s` DROP INDEX `%s`',

		        							$TableName,
		        							$IndexName

		        						);

		        	// Add the description of this difference
		        	
		        	$this -> Descriptions[ count( $Queries ) - 1 ] 	

		        						= $IndexDiffers;

		        }

		        // If the index doesn't exist, or if it had differed (and will be dropped at this point),
		        // we create the index

		        if ( ( $IndexDiffers || ! $IndexExists ) && ! $Index[ 'Drop' ] ) {

			        $Queries[] 		= sprintf(

		        							'ALTER TABLE `%s` ADD %sINDEX %s',

		        							$TableName,
		        							$Index[ 'Unique' ] ? 'UNIQUE ' : '',
		        							$this -> IndexDefinition( $Index )

		        						);

			    }


	        }

	    }

	    /* ======================================================================================================
	       INDEX DIFFERS
	    ====================================================================================================== */

	    /**
	     * Determine if two indices differ sufficiently to require action
	     *
	     * @access public
	     * @param array $Index Declared index
	     * @param array $ExistingIndex Existing index
	     * @return mixed String which describes the difference, or False if no difference is found
	     */
	    
	    public function IndexDiffers ( array $Index , array $ExistingIndex ) {

	        /* ------------------------------------------------------------------------------------------------------
	           COMPARE
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $Index as $Key => $Value ) {

	        	// If the value of the declaration and the corresponding value in the existing index differ,
	        	// we'll proceed

	        	if ( $Value != $ExistingIndex[ $Key ] ) {

	        		// For easy reading, let's store the value of the existing index in a smaller-name variable

	        		$Existing 			= $ExistingIndex[ $Key ];

	        		// Return the description of how it differs

	        		return sprintf(

	        			'Index %s property %s differs from existing ( %s => %s )',

	        			$Index[ 'Name' ],
	        			$Key,

	        			$Value === False 
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Value )
	        					 	? ( '[' . implode( ',' , $Value ) . ']' )
	        					 	: $Value


	        				),

	        			$Existing === False
	        				? '0' 
	        				: (

	        					// If the return value is an array (columns, for instance), we'll use the
	        					// implode function to create a readable output
	        					
	        					is_array( $Existing )
	        					 	? ( '[' . implode( ',' , $Existing ) . ']' )
	        					 	: $Existing


	        				)


	        		);

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return False;

	    }

	    /* ======================================================================================================
	       INDEX DEFINITION
	    ====================================================================================================== */

	    /**
	     * Define an index based on input
	     *
	     * @access public
	     * @param array $Index
	     * @throws \Bytes\ImplementationException Raised if no columns are defined
	     * @return string
	     */
	    
	    public function IndexDefinition ( array $Index ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! count( $Index[ 'Columns' ] ) ) {

	        	throw new \Bytes\ImplementationException( 'Columns not defined for index ' . $Index[ 'Name' ] );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           
	        ------------------------------------------------------------------------------------------------------ */

	        $Columns 				= '';

	        foreach ( $Index[ 'Columns' ] as $I => $ColumnName ) {

	        	$Columns 			.= ( $I > 0 ? ', ' : '' ) . sprintf( '`%s`' , $ColumnName );

	        }

	        $Definition 			= sprintf(

	        							'`%s` (%s)',

	        							$Index[ 'Name' ],
	        							$Columns

	        						);

	        return $Definition;

	    }

	    /* ======================================================================================================
	       SAFE NAME
	    ====================================================================================================== */

	    /**
	     * Ensure a passed table/column/index name is safe to be passed.
	     * It will not actually be validated, but since we rely on query building, we have to make
	     * sure malicious characters aren't passed
	     *
	     * @access public
	     * @param string $TableName
	     * @todo Complete this method; Currently just a wrapper
	     * @return string
	     */
	    
	    public function SafeParameter ( string $TableName ): string {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return preg_replace( '/[^a-zA-Z0-9_\(\)\s]*/im' , '' , $TableName );

	    }

	}