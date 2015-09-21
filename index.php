<?php
/**
 * Routes file
 * 
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 */
error_reporting(E_ALL);
ini_set( "display_errors", "On" );

// # Give 3 minutes to allow process completion
@ set_time_limit( 180 );

// # Grant some extra memory for charts creation
@ ini_set( "memory_limit", "256M" );

// # Setting charset to iso
@ header( "Content-Type: text/html; charset=utf-8" );

// # Setting locale for dates, coins etc
@ setlocale ( LC_ALL, "it_IT" );

// # Setting timezone
@ date_default_timezone_set( "Europe/Rome" );

// # Mssql charset and required settings
@ ini_set( "mssql.charset", "UTF-8" );
@ ini_set( "mssql.textlimit", "2147483647" );
@ ini_set( "mssql.textsize",  "2147483647" );

// # Import needed Namespaces
use Sunchemical\Suncharts\Core\PropertyManager;
use Sunchemical\Suncharts\Dao\DAOConnection;
use Sunchemical\Suncharts\Dao\DAO;
use Sunchemical\Suncharts\Charts\ChartsBuilder;
use Sunchemical\Suncharts\Pdf\SunchemicalPdf;
use Sunchemical\Suncharts\Response\ServiceResponse;
use Sunchemical\Suncharts\Utils\Utils;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;

// # Commons Required
require_once "vendor/autoload.php";

// # Create a log channel
$log = new Logger( 'SunCharts-Sunchemical' );
$log->pushHandler( new RotatingFileHandler( 'logs/errors', 1, Logger::ERROR ) );
$log->pushProcessor( new IntrospectionProcessor( Logger::ERROR ) );

// # Get instance and load props
$PM = new PropertyManager();

// # DAOConnection instance
$DAOConn = new DAOConnection( $PM );

// # Mailer instance
$Mailer = new PHPMailer();

// # ChartsBuilder instance
$CB = new ChartsBuilder( $PM, $log );

// # Initializing router
$router = new AltoRouter();

// # This app is deployed in production as a subfolder and not a virtualhost
// # so we need to set a basepath for prod environment
if( $PM->getproperty( 'env' ) == 'prod' ) {
	$router->setBasePath( '/suncharts/' );
}

// # $Params default
$defaults = array( 
				   'y' => date( 'Y' ), 
				   'm' => date( 'm'), 
				   'oTypeID' => 1, 
				   'oID' => 'All', 
				   'techID' => 'All',
				   'oVendorID' => 'All', 
				   'countryID' => 'IT' 
				 );

// # Config interface
$router->map( 'GET', '/', function() { 
	// # Shows the page
	require_once "config.html";
});

// # Cron based call ( all params are set to defaults )
$router->map( 'GET', 'worker/default', function() use ( $PM, $DAOConn, $log, $CB, $defaults  ) {

	// # Unlink latest charts files
	$CB->clearPast();

	// # Response
	$SR = new ServiceResponse();

	try {

		// # Connect to db
		$DAO = new DAO( $DAOConn, $log );

		// # re
		$RSByoID = $DAO->getLabWorkloadByoID( $defaults[ 'y' ], $defaults[ 'oTypeID' ], $defaults[ 'oID' ], $defaults[ 'countryID' ] );
		$RSByTechnician = $DAO->getLabWorkloadByTechnician( $defaults[ 'y' ], $defaults[ 'm' ], $defaults[ 'countryID' ] );
		$RSByMonth = $DAO->getLabWorkloadByMonth( $defaults[ 'y' ], $defaults[ 'm' ], $defaults[ 'countryID' ] );

		// # Build chart
		$CB->buildChartByCustomer( $RSByoID );
		$CB->buildChartByTechnician( $RSByTechnician );
		$CB->buildChartByMonth( $RSByMonth );

		// # OK
		$SR->status = ServiceResponse::STATUS_OK;

	} catch( Exception $e ) {

		// # No way
		$log->addError( $e->getMessage() );
	}
});


// # Forces report sending, passes all required parameters
$router->map( 'POST', 'manual-push', function() {

	if( isset( $_POST[ "recipients" ] ) && !empty( $_POST[ "recipients" ] ) ) {
		file_put_contents( "config/mail/recipients", $_POST[ "recipients" ] );
	}

	// # Unlink latest charts files
	$CB->clearPast();

	// # Response
	$SR = new ServiceResponse();

	try {

		// # Connect to db
		$DAO = new DAO( $DAOConn, $log );

		// # re
		$RSByoID = $DAO->getLabWorkloadByoID( $_POST[ 'customer_y' ], $_POST[ 'customer_oTypeID' ], $_POST[ 'customer_oID' ], $_POST[ 'customer_countryID' ] );
		$RSByTechnician = $DAO->getLabWorkloadByTechnician( $_POST[ 'technician_y' ], $_POST[ 'technician_technicianID' ], $_POST[ 'technician_countryID' ] );
		$RSByMonth = $DAO->getLabWorkloadByMonth( $_POST[ 'month_y' ], $_POST[ 'month_m' ], $_POST[ 'month_countryID' ] );

		// # Build chart
		$CB->buildChartByCustomer( $RSByoID );
		$CB->buildChartByTechnician( $RSByTechnician );
		$CB->buildChartByMonth( $RSByMonth );

		// # OK
		$SR->status = ServiceResponse::STATUS_OK;
		echo json_encode( $SR );

	} catch( Exception $e ) {

		// # No way
		$log->addError( $e->getMessage() );
	}	
});

// # Mail sender route
$router->map( 'GET', 'sender', function() use ( $PM, $log )  {
	
	// # Mail sender instance
	$MS = new MailSender();
	$SR = $MS->send();

 	// # Response
 	echo json_encode( $SR );
});

// # CONFIG Page ajax ROUTES

// # Get recipients API
$router->map( 'GET', 'recipients', function() {
	echo json_encode( file_get_contents( "config/mail/recipients" ) );
});

// # Get technicians list
$router->map( 'GET', 'technicians', function() use ( $DAOConn, $log ) {

	try {
		// # Connect to db
		$DAO = new DAO( $DAOConn, $log, array() );
		echo json_encode( $DAO->getTechnicians() );
	} catch( Exception $e ) {

		// # No way
		$log->addError( $e->getMessage() );
		echo json_encode( null );
		return;
	}
});

// # Get oid customer list
$router->map( 'GET', 'oidCustomer', function() use ( $DAOConn, $log ) {

	try {
		// # Connect to db
		$DAO = new DAO( $DAOConn, $log, array() );
		echo json_encode( $DAO->getOIDCustomer() );
	} catch( Exception $e ) {

		// # No way
		$log->addError( $e->getMessage() );
		echo json_encode( null );
		return;
	}
});

// # Get oid vendor list
$router->map( 'GET', 'oidVendor', function() use ( $DAOConn, $log ) {

	try {
		// # Connect to db
		$DAO = new DAO( $DAOConn, $log, array() );
		echo json_encode( $DAO->getOIDVendor() );
	} catch( Exception $e ) {

		// # No way
		$log->addError( $e->getMessage() );
		echo json_encode( null );
		return;
	}
});

// // # Route matching
$match = $router->match();

// # Call closure or throw 404 status
if( !$match || !is_callable( $match[ 'target' ] ) ) {
 	// # No route was matched
 	$log->addError( "Unable to find route!" );
 	return false;
}

// // # Invoking callback
call_user_func_array( $match[ 'target' ], $match[ 'params' ] );
?>