<?php

	/**
	 * Database builder class
	 */
	
	namespace Bytes\Components\Installer;

	class DatabaseSchema {

	    /* ======================================================================================================
	       PROPERTIES
	    ====================================================================================================== */

	    /**
	     * Complete structure of tables, columns, indices, etc.
	     * @var array
	     */
	    
	    private $Tables 		= [];

	    /**
	     * Keep track of the table we're working on
	     * @var string
	     */
	    
	    private $ActiveTable 	= '';

	    /**
	     * Keep track of the column we're working on
	     * @var string
	     */
	    
	    private $ActiveColumn 	= '';

	    /**
	     * Keep track of the active index
	     * @var string
	     */
	    
	    private $ActiveIndex 	= '';
	    
	    /**
	     * Keep track of the foreign key we're working on
	     * @var array
	     */
	    
	    private $ActiveFK 		= '';

	    /**
	     * List of known data types
	     * @var array
	     */
	    
	    private $DataTypes 		= [

	    							// Numbers, bits & boolean

	    							'TINYINT',
	    							'SMALLINT',
	    							'MEDIUMINT',
	    							'INT',
	    							'BIGINT',
	    							'BOOL',
	    							'BIT',
	    							'SERIAL',

	    							'DECIMAL',
	    							'FLOAT',
	    							'DOUBLE',
	    							'REAL',

	    							// Strings

	    							'CHAR',
	    							'VARCHAR',

	    							'TINYTEXT',
	    							'TEXT',
	    							'MEDIUMTEXT',
	    							'LONGTEXT',

	    							'BINARY',
	    							'VARBINARY',

	    							'TINYBLOB',
	    							'MEDIUMBLOB',
	    							'BLOB',
	    							'LONGBLOB',

	    							'ENUM',
	    							'SET',

	    							// Date & time

	    							'DATE',
	    							'DATETIME',
	    							'TIMESTAMP',
	    							'TIME',
	    							'YEAR',

	    							// Geometry

	    							'GEOMETRY',
	    							'POINT',
	    							'LINESTRING',
	    							'POLYGON',
	    							'MULTIPOINT',
	    							'MULTILINESTRING',
	    							'MULTIPOLYGON',
	    							'GEOMETRYCOLLECTION'

	    						];

	    /**
	     * Allowed settings for ON UPDATE and ON DELETE rules in foreign keys
	     * @var array
	     */
	    
	    private $FKRules 		= [ 'CASCADE' , 'SET NULL' , 'NO ACTION' , 'RESTRICT' ];

	    /* ======================================================================================================
	       GET DECLARATIONS
	    ====================================================================================================== */

	    /**
	     * Returns the declarations
	     *
	     * @access public
	     * @return array
	     */
	    
	    public function GetDeclarations ( ): array {

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this -> Tables;

	    }

	    /* ======================================================================================================
	       DECLARE TABLE
	    ====================================================================================================== */

	    /**
	     * Declares a table
	     *
	     * @access public
	     * @param string $Name
	     * @throws \Bytes\ComponentException Raised if table is already declared
	     * @return DatabaseSchema
	     */
	    
	    public function Table ( string $Name ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( isset( $this -> Tables[ $Name ] ) ) {

	        	throw new \Bytes\ComponentException( sprintf( 'Table %s already declared.' , $Name ) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           DECLARE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Tables[ $Name ] 	= [

	        								'Columns' 		=> [],
	        								'Indices' 		=> [],
	        								'ForeignKeys' 	=> []

	        							];

	        $this -> ActiveTable 		= $Name;
	        $this -> ActiveColumn 		= '';
	        $this -> ActiveIndex 		= '';
	        $this -> ActiveFK 			= '';


	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       COLUMN
	    ====================================================================================================== */

	    /**
	     * Add a column, on the active table
	     *
	     * @access public
	     * @param string $Name
	     * @param string $DataType
	     * @param mixed $Options int is max length, and array passes multiple arguments (for instance for DOUBLE or ENUM)
	     * @throws \Bytes\ComponentException Raised if there's no active table
	     * @throws \Bytes\ComponentException Raised if the data type is unknown
	     * @throws \Bytes\ComponentException Raised if $Name is empty
	     * @return DatabaseSchema
	     */
	    
	    public function Column ( string $Name , string $DataType , $Arguments = Null ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveTable ) {

	        	throw new \Bytes\ComponentException( 'You must choose a table, before you can define columns' );

	        }

	        // Ensure the column is not already added
	        // We apply traversing, instead of direct indexing, to compare with no respect to case-sensitivty

	        foreach ( $this -> Tables[ $this -> ActiveTable ][ 'Columns' ] as $ColumnName => $Column ) {

	        	if ( strtolower( $ColumnName ) == strtolower( $Name ) ) {

	        		throw new \Bytes\ComponentException( sprintf( 'Column %s already defined' , $Name ) );

	        	}

	        }

	        // Check that the column has been given a name

	        if ( ! preg_match( '/[a-zA-Z]/' , $Name ) ) {

	        	throw new \Bytes\ComponentException( 'You must choose a column name' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK DATA TYPE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! in_array( $DataType , $this -> DataTypes ) ) {

	        	throw new \Bytes\ComponentException( sprintf(

	        		'Unfortunately, the data type %s is not one we currently support. Please choose from: %s',

	        		$DataType,
	        		implode( ', ' , $this -> DataTypes )

	        	) );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ARGUMENTS
	        ------------------------------------------------------------------------------------------------------ */

	        if ( is_null( $Arguments ) ) {

	        	$Arguments 			= [];

	        } else if ( ! is_array( $Arguments ) ) {

	        	$Arguments 			= [ $Arguments ];

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Tables[ $this -> ActiveTable ][ 'Columns' ][]

	        					= [

	        						'Name' 				=> $Name,
	        						'DataType' 			=> $DataType,
	        						'Arguments' 		=> $Arguments,
	        						'Unsigned' 			=> False,
	        						'AutoIncrement' 	=> False,
	        						'Null' 				=> True,
	        						'Default' 			=> False,
	        						'PrimaryKey' 		=> False,
	        						'Unique' 			=> False,
	        						'ForeignKey' 		=> False,
	        						'Drop' 				=> False

	        					];

	        // Set column as active

	        $this -> ActiveColumn 	= $Name;
	        $this -> ActiveIndex 	= '';
	        $this -> ActiveFK 		= '';

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DEFAULT MAX LENGTH
	    ====================================================================================================== */

	    /**
	     * Returns a default max length (if any) based on data type
	     *
	     * @access public
	     * @param string $DataType
	     * @return int
	     */
	    
	    public function DefaultMaxLength ( string $DataType ): int {

	        /* ------------------------------------------------------------------------------------------------------
	           SWITCH + RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        switch ( strtoupper( $DataType ) ) {

	        	case 'INT': 		return 11;

	        	default: 			return 0;

	        }

	    }

	    /* ======================================================================================================
	       REQUIRE ACTIVE COLUMN
	    ====================================================================================================== */

	    /**
	     * Ensures that a column is actively selected for editing
	     *
	     * @access protected
	     * @throws \Bytes\ComponentException Raised if no column is active
	     * @return void
	     */
	    
	    protected function RequireActiveColumn ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveColumn ) {

	        	throw new \Bytes\ComponentException( 'You cannot perform this action without an active column.' );

	        }

	    }

	    /* ======================================================================================================
	       CHANGE ACTIVE COLUMN
	    ====================================================================================================== */

	    /**
	     * Changes a property of the active column
	     *
	     * @access protected
	     * @param string $Key
	     * @param mixed $Value
	     * @return void
	     */
	    
	    protected function ChangeActiveColumn ( string $Key , $Value ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequireActiveColumn();

	        /* ------------------------------------------------------------------------------------------------------
	           FIND + SET
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $this -> Tables[ $this -> ActiveTable ][ 'Columns' ] as $I => $Column ) {

	        	if ( $Column[ 'Name' ] == $this -> ActiveColumn ) {

	        		// This shit has so many dimensions even Einstein is all like "Please stop! Just stop!" ;-(

	        		$this -> Tables[ $this -> ActiveTable ][ 'Columns' ][ $I ][ $Key ] 	= $Value;

	        	}

	        }

	    }

	    /* ======================================================================================================
	       REQUIRE ACTIVE INDEX
	    ====================================================================================================== */

	    /**
	     * Ensures that a index is actively selected for editing
	     *
	     * @access protected
	     * @throws \Bytes\ComponentException Raised if no index is active
	     * @return void
	     */
	    
	    protected function RequireActiveIndex ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveIndex ) {

	        	throw new \Bytes\ComponentException( 'You cannot perform this action without an active index.' );

	        }

	    }

	    /* ======================================================================================================
	       CHANGE ACTIVE INDEX
	    ====================================================================================================== */

	    /**
	     * Changes a property of the active index
	     *
	     * @access protected
	     * @param string $Key
	     * @param mixed $Value
	     * @return void
	     */
	    
	    protected function ChangeActiveIndex ( string $Key , $Value ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequireActiveIndex();

	        /* ------------------------------------------------------------------------------------------------------
	           FIND + SET
	        ------------------------------------------------------------------------------------------------------ */

	        foreach ( $this -> Tables[ $this -> ActiveTable ][ 'Indices' ] as $IndexName => $Index ) {

	        	if ( $IndexName == $this -> ActiveIndex ) {

	        		// Repeat previous Einstein comment :)

	        		$this -> Tables[ $this -> ActiveTable ][ 'Indices' ][ $IndexName ][ $Key ] 	= $Value;

	        	}

	        }

	    }

	    /* ======================================================================================================
	       PRIMARY KEY
	    ====================================================================================================== */

	    /**
	     * Set active column as PRIMARY KEY
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function PrimaryKey ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn( 'PrimaryKey' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DROP (COLUMN)
	    ====================================================================================================== */

	    /**
	     * Drops active column
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function Drop ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn( 'Drop' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DROP FOREIGN KEY
	    ====================================================================================================== */

	    /**
	     * Drops foreign key on active column
	     *
	     * @access public
	     * @since v1.3.0
	     * @return DatabaseSchema
	     */
	    
	    public function DropForeignKey ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           DROP
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveForeignKey( 'Drop' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DROP INDEX
	    ====================================================================================================== */

	    /**
	     * Drops active index
	     *
	     * @access public
	     * @since v1.3.0
	     * @return DatabaseSchema
	     */
	    
	    public function DropIndex ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           DROP
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveIndex( 'Drop' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       UNIQUE
	    ====================================================================================================== */

	    /**
	     * Create a UNIQUE INDEX on the active column
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function Unique ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveIndex( 'Unique' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       NOT NULL
	    ====================================================================================================== */

	    /**
	     * Set active column as NOT NULL
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function NotNull ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn( 'Null' , False );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       UNSIGNED
	    ====================================================================================================== */

	    /**
	     * Set active column as UNSIGNED
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function Unsigned ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn( 'Unsigned' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       AUTO INCREMENT
	    ====================================================================================================== */

	    /**
     	 * Instruct auto increment for the active column
	     *
	     * @access public
	     * @return DatabaseSchema
	     */
	    
	    public function AutoIncrement ( ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn( 'AutoIncrement' , True );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       DEFAULT
	    ====================================================================================================== */

	    /**
	     * Set a default value for the active column
	     *
	     * @access public
	     * @param string $Value
	     * @return DatabaseSchema
	     */
	    
	    public function Default ( $Value ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        // Typecasted to string, because some newer versions of MySQL can be quite unhappy with for instance
	        // integer default values

	        $this -> ChangeActiveColumn( 'Default' , $Value );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       INDEX
	    ====================================================================================================== */

	    /**
	     * Add an index, on the active table
	     *
	     * @access public
	     * @param string $Name
	     * @throws \Bytes\ComponentException Raised if there's no active table
	     * @throws \Bytes\ComponentException Raised if $Name is empty
	     * @return DatabaseSchema
	     */
	    
	    public function Index ( string $Name ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveTable ) {

	        	throw new \Bytes\ComponentException( 'You must choose a table, before you can define an index' );

	        }

	        // Check that the column has been given a name

	        if ( ! preg_match( '/[a-zA-Z]/' , $Name ) ) {

	        	throw new \Bytes\ComponentException( 'You must choose a column name' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> Tables[ $this -> ActiveTable ][ 'Indices' ][ $Name ]

		        					= [

		        						'Name' 				=> $Name,
		        						'Unique' 			=> False,
		        						'Columns' 			=> [],
		        						'Drop' 				=> False

		        					];

	        // Set column as active

	        $this -> ActiveIndex 	= $Name;
	        $this -> ActiveColumn 	= '';
	        $this -> ActiveFK 		= '';

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ON COLUMNS
	    ====================================================================================================== */

	    /**
	     * Add an index, on the active table
	     *
	     * @access public
	     * @param array $Columns
	     * @throws \Bytes\ComponentException Raised if $Columns is empty
	     * @return DatabaseSchema
	     */
	    
	    public function OnColumns ( array $Columns ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK
	        ------------------------------------------------------------------------------------------------------ */

	        if ( empty( $Columns ) ) {

	        	throw new \Bytes\ComponentException( 'You must define at least one column for the index' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveIndex( 'Columns' , $Columns );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       FOREIGN KEY
	    ====================================================================================================== */

	    /**
	     * Add a foreign key on the active table
	     *
	     * @access public
	     * @param string $Name
	     * @throws \Bytes\ComponentException Raised if there's no active table
	     * @throws \Bytes\ComponentException Raised if $Name is empty
	     * @return DatabaseSchema
	     */
	    
	    public function ForeignKey ( string $Name ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           CHECK
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveColumn ) {

	        	throw new \Bytes\ComponentException(

	        		'You must choose an active column, before you can define a foreign key' 

	        	);

	        }

	        // Check that the foreign key has been given a name

	        if ( ! preg_match( '/[a-zA-Z_]/' , $Name ) ) {

	        	throw new \Bytes\ComponentException( 'You must choose a foreign key name' );

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           ADD
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveColumn(

	        	'ForeignKey',

				[

					'Name' 				=> $Name,
					'ReferenceTable'	=> False,
					'ReferenceColumn'	=> False, 
					'OnDelete' 			=> 'CASCADE',
					'OnUpdate' 			=> 'CASCADE',
		        	'Drop' 				=> False

				]

			);

	        // Set column as active

	        $this -> ActiveFK 		= $Name;

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       CHANGE ACTIVE FOREIGN KEY
	    ====================================================================================================== */

	    /**
	     * Changes the actively selected foreign key
	     *
	     * @access public
	     * @param string $Key
	     * @param string $Value
	     * @return DatabaseSchema
	     */
	    
	    public function ChangeActiveForeignKey ( string $Key , string $Value ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequireActiveFK();

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        // Find active column

	        foreach ( $this -> Tables[ $this -> ActiveTable ][ 'Columns' ] as $I => $Column ) {

	        	// If this iteration has the active column, we'll proceed to add the information

	        	if ( $Column[ 'Name' ] == $this -> ActiveColumn ) {

	        		// Set table

	        		$this -> Tables[ $this -> ActiveTable ][ 'Columns' ][ $I ][ 'ForeignKey' ][ $Key ] 	= $Value;

	        	}

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       REFERENCES
	    ====================================================================================================== */

	    /**
	     * Reference table and column for a foreign key
	     *
	     * @access public
	     * @param string $ReferenceTable
	     * @param string $ReferenceColumn
	     * @return DatabaseSchema
	     */
	    
	    public function References ( string $ReferenceTable , string $ReferenceColumn ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> RequireActiveFK();

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveForeignKey( 'ReferenceTable' , $ReferenceTable );
	        $this -> ChangeActiveForeignKey( 'ReferenceColumn' , $ReferenceColumn );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ON UPDATE
	    ====================================================================================================== */

	    /**
	     * Changes the ON UPDATE rule for the active foreign key
	     *
	     * @access public
	     * @param string $Rule
	     * @throws \Bytes\Exception Raised if rule is unknown
	     * @return DatabaseSchema
	     */
	    
	    public function OnUpdate ( string $Rule ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! in_array( $Rule , $this -> FKRules ) ) {

	        	throw new \Bytes\Exception(

	        		$Rule . ' is not a known setting for foreign key rules. Choose among: ' . implode( ', ' , $this -> FKRules )

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveForeignKey( 'OnUpdate' , $Rule );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       ON DELETE
	    ====================================================================================================== */

	    /**
	     * Changes the ON DELETE rule for the active foreign key
	     *
	     * @access public
	     * @param string $Rule
	     * @throws \Bytes\Exception Raised if rule is unknown
	     * @return DatabaseSchema
	     */
	    
	    public function OnDelete ( string $Rule ): DatabaseSchema {

	        /* ------------------------------------------------------------------------------------------------------
	           VALIDATE
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! in_array( $Rule , $this -> FKRules ) ) {

	        	throw new \Bytes\Exception(

	        		$Rule . ' is not a known setting for foreign key rules. Choose among: ' . implode( ', ' , $this -> FKRules )

	        	);

	        }

	        /* ------------------------------------------------------------------------------------------------------
	           SET
	        ------------------------------------------------------------------------------------------------------ */

	        $this -> ChangeActiveForeignKey( 'OnDelete' , $Rule );

	        /* ------------------------------------------------------------------------------------------------------
	           RETURN
	        ------------------------------------------------------------------------------------------------------ */

	        return $this;

	    }

	    /* ======================================================================================================
	       REQUIRE ACTIVE FOREIGN KEY
	    ====================================================================================================== */

	    /**
	     * Ensures that a foreign key is actively selected for editing
	     *
	     * @access protected
	     * @throws \Bytes\ComponentException Raised if foreign key is not active
	     * @return void
	     */
	    
	    protected function RequireActiveFK ( ) {

	        /* ------------------------------------------------------------------------------------------------------
	           TEST
	        ------------------------------------------------------------------------------------------------------ */

	        if ( ! $this -> ActiveFK ) {

	        	throw new \Bytes\ComponentException( 'You cannot perform this action without an active foreign key.' );

	        }

	    }

	}