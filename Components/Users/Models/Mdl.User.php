<?php

	/**
	 * GraphNodes model
	 *
	 * @author Mark <m@m.dk>
	 * @package GraphNodes
	 */

	namespace Bytes\Components\Users;

	class MdlUser extends \Bytes\Model {

		/**
		 * 
		 */
		
		protected $AltOptions = [];

		/**
		 * 
		 */
		
		public function SetConfiguration ( $Options ) {

			$this -> AltOptions = $Options;

		}

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

	    /**
	     * 
	     */
	    
	    public function CreatePasswordRecovery ( string $Email ): array {

	    	$Account = $this -> GetAccountLoginDetails( $Email );

	    	if ( ! $Account ) {
	    		throw new \Bytes\Exception( 'No account found with email: ' . $Email );
	    	}

	    	if ( $Account[ 'Mode' ] != 'Login' ) {
	    		throw new \Bytes\Exception( 'This account type cannot request password recoveries' );
	    	}

	    	$Code = hash( 'Whirlpool' , time() . rand(999999,10000000) . '¤%&/¤%&/¤%&/(45674567' . sha1( microtime() ) );

	    	$this -> Employ( 'Database' ) -> Query( 'INSERT INTO

	    			Users_PasswordRecoveries
	    			(
	    				UserId,
	    				Code,
	    				DateCreated
	    			)

	    		VALUES

	    			(
	    				:UserId,
	    				:Code,
	    				NOW()
	    			)' , [

	    		':UserId' 		=> $Account[ 'UserId' ],
	    		':Code' 		=> $Code

	    	] );

	    	return [
	    		'Code' => $Code
	    	];

	    }

	    /**
	     * 
	     */
	    
	    public function CleanPasswordRecoveries ( ) {

	    	$this -> Employ( 'Database' ) -> Query( 
	    		'DELETE FROM 
	    			Users_PasswordRecoveries 
	    		WHERE
	    			DateCreated < DATE_SUB(NOW(),INTERVAL 1 HOUR)'
	    	);

	    }
 
	    /**
	     * 
	     */
	    
	    public function ProcessPasswordRecovery ( string $Code ) {

	    	// Clean records

	    	$this -> CleanPasswordRecoveries();

	    	// Find recovery record

	    	$Recovery = $this -> Employ( 'Database' ) -> Query( 
	    		'SELECT 
	    			Users_PasswordRecoveries.*,
	    			Users.Email
	    		FROM Users_PasswordRecoveries 
	    		INNER JOIN Users ON Users.UserId = Users_PasswordRecoveries.UserId
	    		WHERE Code = :Code AND DateCreated > DATE_SUB(NOW(),INTERVAL 1 HOUR)',
	    		[
	    			':Code' => $Code
	    		]
	    	)[ 0 ];

	    	if ( ! $Recovery ) {
	    		throw new \Bytes\Exception( 'Recovery does not exist or has expired' );
	    	}

	    	// Create temporary password to log in

	    	$TemporaryPassword 	= rand(100000,999999);

	    	$_SESSION[ 'NodeByNode.TemporaryPassword' ] = $TemporaryPassword;

	    	$this -> Employ( 'Database' ) -> Query( 'UPDATE

	  				Users

	  			SET
	  				Password = :Password

	  			WHERE
	  				UserId = :UserId' , [

	  			':Password' 	=> $this -> HashPassword( $TemporaryPassword ),
	  			':UserId' 		=> $Recovery[ 'UserId' ]

	  		] );

	  		$this -> LoginWithCredentials( $Recovery[ 'Email' ] , $TemporaryPassword );

	  		$this -> Employ( 'Database' ) -> Query( 'DELETE FROM
	  				Users_PasswordRecoveries
	  			WHERE
	  				Code = :Code' , [
	  			':Code' => $Code
	  		] );

	    }

	    /**
	     *
	     */
	    
	    public function GetAccountLoginDetails ( string $Email ) {

	    	$Account 			= $this -> Employ( 'Database' ) -> Query( 'SELECT

	    								UserId,
	    								( SELECT 
	    										COUNT(*) 
	    									FROM Users_Authentications 
	    									WHERE 
	    										Users_Authentications.UserId = Users.UserId
	    									LIMIT 1
	    								) AS _SocialLogin

	    							FROM Users

	    							WHERE
	    								LOWER( Email ) = LOWER( :Email )' , [

	    							':Email' 		=> $Email

	    						] )[ 0 ];	   

	    	if ( ! $Account[ 'UserId' ] ) {

	    		return [ 'Mode' => 'AccountNotFound' ];

	    	} else if ( $Account[ '_SocialLogin' ] ) {

	    		return [ 
	    			'Mode' => 'SocialLogin',
	    			'UserId' => $Account[ 'UserId' ] 
	    		];

	    	} else {

	    		return [ 
	    			'Mode' => 'Login',
	    			'UserId' => $Account[ 'UserId' ] 
	    		];

	    	}

	    }

	  	/**
	  	 * 
	  	 */
	  	
	  	public function ChangePassword ( int $UserId , string $Current , string $New , string $Confirm ) {

	  		$User 			= $this -> FetchById( $UserId );

	  		if ( $User[ 'Password' ] != $this -> HashPassword( $Current ) ) {
	  			throw new \Bytes\Exception( 'Your current password is not correct' );
	  		}

	  		$this -> TestPassword( $New );

	  		if ( $New != $Confirm ) {
	  			throw new \Bytes\Exception( 'New and confirmed password fields do not match' );
	  		}

	  		$this -> Employ( 'Database' ) -> Query( 'UPDATE

	  				Users

	  			SET
	  				Password = :Password

	  			WHERE
	  				UserId = :UserId' , [

	  			':Password' 	=> $this -> HashPassword( $New ),
	  			':UserId' 		=> $UserId

	  		] );

	  	}

	    /**
	     * 
	     */
	    
	    public function LoginWithCredentials ( string $Email , string $Password ) {

	    	// Validate email 

	    	if ( ! filter_var( $Email , FILTER_VALIDATE_EMAIL ) ) {
	    		throw new \Bytes\Exception( 'Please enter an email in a valid format' );
	    	}

	    	// Find the user

	    	$User 				= $this -> Employ( 'Database' ) -> Query(
	    							'SELECT * FROM Users WHERE LOWER( Email ) = LOWER( :Email )',
	    							[
	    								':Email' 	=> $Email
	    							]
	    						)[ 0 ];

	    	if ( ! $User ) {

	    		$this -> TestPassword( $Password );

	    		$this -> Employ( 'Database' ) -> Query( 
	    			'INSERT INTO Users ( Email , Password ) VALUES ( :Email , :Password )' ,
					[
						':Email' 		=> $Email,
						':Password' 	=> $this -> HashPassword( $Password )
					]
	    		);

	    		$UserId 		= (int) $this -> Employ( 'Database' ) -> LastInsertId();

	    	} else if ( $User[ 'Password' ] != $this -> HashPassword( $Password ) ) {

	    		throw new \Bytes\Exception( 'Password is unfortunately not correct' );

	    	} else {

	    		$UserId 		= (int) $User[ 'UserId' ];

	    	}

	    	$this -> Login( $UserId );

	    }

	    /**
	     * 
	     */
	    
	    public function TestPassword ( string $Password ) {

	    	if ( ! $this -> AltOptions[ 'TestPassword' ] ) {
	    		return;
	    	}

	    	if ( strlen( $Password ) < 10 ) {
	    		throw new \Bytes\Exception( 'Password must be at least 10 characters' );
	    	}

	    	if ( ! preg_match( '/[A-Z]/' , $Password ) ) {
	    		throw new \Bytes\Exception( 'Password must contain at least one capital letter' );
	    	}

	    	if ( ! preg_match( '/[0-9]/' , $Password ) ) {
	    		throw new \Bytes\Exception( 'Password must contain at least one number' );
	    	}

	    	if ( ! preg_match( '/[!\@\#\$\%\&\*\.,;]/' , $Password ) ) {
	    		throw new \Bytes\Exception( 'Password must contain at least one special character' );
	    	}

	    }

	    /**
	     * 
	     */
	    
	    public function HashPassword ( string $Password ): string {

	    	return hash( 'Whirlpool' , $Password . sha1( $Password ) );

	    }

	    /**
	     * Returns active user ID
	     *
	     * @access public
	     * @return int
	     */
	    
	    public function GetUserId ( ): int {

	        return (int) $_SESSION[ $this -> AltOptions[ 'SessionName' ] ][ 'UserId' ];

	    }

	    /**
	     * Returns active user ID
	     *
	     * @access public
	     * @return int
	     */
	    
	    public function Login ( int $UserId ) {

	        $_SESSION[ $this -> AltOptions[ 'SessionName' ] ] 		= $this -> FetchById( $UserId );

	    }

	    /**
	     * 
	     */
	    
	    public function ReceivedEmail ( int $SubscriberId ) {

	    	$this -> Employ( 'Database' ) -> Query( 'UPDATE

	    			Newsletter_SignUps

	    		SET
	    			DateLastEmail = NOW()

	    		WHERE
	    			NewsletterSignUpId = :NewsletterSignUpId' , [

	    		':NewsletterSignUpId' 	=> $SubscriberId

	    	] );

	    }

	    /**
	     * Returns active user ID
	     *
	     * @access public
	     * @return int
	     */
	    
	    public function FetchById ( int $UserId ): array {

	        $User 			= $this -> Employ( 'Database' ) -> Query( 'SELECT

					        		Users.*,

					        		Users_Profiles.FirstName,
					        		Users_Profiles.LastName,
					        		Users_Profiles.Gender,
					        		Users_Profiles.ProfilePhotoURL

					        	FROM Users

					        	LEFT JOIN Users_Profiles
					        		ON Users_Profiles.UserId = Users.UserId

					        	WHERE
					        		Users.UserId = :UserId',

					        	[
					        		':UserId' 		=> $UserId
					        	]

					        )[ 0 ];

	       	if ( ! $User ) {

	       		throw new \Exception( 'User not found' );

	       	}

	       	return $User;

	    }

	    /**
	     * 
	     */
	    
	    public function Create ( string $Email ): int {

	    	$Existing = $this -> GetAccountLoginDetails( $Email );

	    	if ( $Existing[ 'UserId' ] ) {
	    		return (int) $Existing[ 'UserId' ];
	    	}

	    	$this -> Employ( 'Database' ) -> Query( 'INSERT INTO

	    			Users
	    			(
	    				Email
	    			)

	    		VALUES

	    			(
	    				:Email
	    			)',

	    		[
	    			':Email' 			=> $Email
	    		]

	    	);

	    	return $this -> Employ( 'Database' ) -> LastInsertId();

	    }

	    /**
	     * 
	     */
	    
	    public function UpdateProfile ( int $UserId , string $FirstName , string $LastName , string $Gender ) {

	    	$this -> Employ( 'Database' ) -> Query( 'INSERT INTO

	    			Users_Profiles
	    			(
	    				UserId
	    			)

	    		VALUES

	    			(
	    				:UserId
	    			)',

	    		[
	    			':UserId' 		=> $UserId
	    		]

	    	);

	    	$this -> Employ( 'Database' ) -> Query( 'UPDATE

	    			Users_Profiles

	    		SET
	    			FirstName 			= :FirstName,
	    			LastName 			= :LastName,
	    			Gender 				= :Gender

	    		WHERE
	    			Users_Profiles.UserId = :UserId',

	    		[
	    			':FirstName' 		=> (string) $FirstName,
	    			':LastName' 		=> (string) $LastName,
	    			':Gender' 			=> (string) $Gender,
	    			':UserId' 			=> (int) $UserId
	    		]

	    	);

	    }

	    /**
	     * 
	     */
	    
	    public function UpdateProfilePhoto ( int $UserId , string $ProfilePhotoURL ) {

	        $Curl 				 = curl_init( $ProfilePhotoURL );

	        curl_setopt( $Curl , CURLOPT_RETURNTRANSFER , True );
	        curl_setopt( $Curl , CURLOPT_SSL_VERIFYPEER , False );
	        curl_setopt( $Curl , CURLOPT_SSL_VERIFYHOST , False );

	        $Source 			= curl_exec( $Curl );

	        $RelativePath 		= '/Uploads/Profiles/' . $UserId . '.jpg';

	    	$Path 				= $this -> Environment() -> Path( $RelativePath );
	    	$URL 				= $this -> Environment() -> URL( $RelativePath );

	    	file_put_contents( $Path , $Source );

	    	$this -> Employ( 'Database' ) -> Query( 'UPDATE

	    			Users_Profiles

	    		SET
	    			ProfilePhotoURL 	= :ProfilePhotoURL

	    		WHERE
	    			Users_Profiles.UserId = :UserId',

	    		[
	    			':ProfilePhotoURL' 	=> $URL,
	    			':UserId' 			=> $UserId
	    		]

	    	);

	    }

	    /**
	     * 
	     */
	    
	    public function ConnectToPlatform ( string $Platform , int $UserId , string $PlatformId ) {

	    	### MAKE SURE THIS IS UNIQUE !!!!

	    	$this -> Employ( 'Database' ) -> Query( 'INSERT INTO

	    			Users_Authentications
	    			(
	    				UserId,
	    				Platform,
	    				PlatformId
	    			)

	    		VALUES

	    			(
	    				:UserId,
	    				:Platform,
	    				:PlatformId
	    			)',

	    		[
	    			':Platform' 		=> $Platform,
	    			':UserId' 			=> $UserId,
	    			':PlatformId' 		=> $PlatformId
	    		]

	    	);

	    }

	}