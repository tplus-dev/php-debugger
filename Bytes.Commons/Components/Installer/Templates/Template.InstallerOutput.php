<pre>
<?php

	$Execution 			= False;

	foreach ( $Data[ 'Output' ] as $I => $Line ) {

		if ( isset( $Line[ 'Query' ] ) ) {

			$Execution 	= True;

			echo $Line[ 'Query' ]
				. (
					isset( $Line[ 'Error' ] )
						? 
							PHP_EOL
							. ' > ' . ( $Line[ 'Error' ] ? $Line[ 'Error' ] : 'OK!' )
							. PHP_EOL . PHP_EOL
						: '' );

		} else {

			echo $Line . PHP_EOL;

			if ( isset( $Data[ 'Descriptions' ][ $I ] ) ) {

				echo ' ?? ' . $Data[ 'Descriptions' ][ $I ] . str_repeat( PHP_EOL , 2 );

			}

		}

	}

	if ( empty( $Data[ 'Output' ] ) ) {

		echo 'No queries to be executed.';

	} else if ( ! $Execution ) {

		echo PHP_EOL . PHP_EOL . str_repeat( '=' , 60 ) . PHP_EOL
			. sprintf( '<a href="?Confirm=1">CONFIRM</a>' );

	}

?>
</pre>