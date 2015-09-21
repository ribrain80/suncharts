<?php
/**
 * DAOConnection Class file(filename)
 */

namespace Sunchemical\Suncharts\Dao;
use Sunchemical\Suncharts\Dao\DAOConnectionInterface;
use Sunchemical\Suncharts\Core\PropertyManager;

/**
 * Database Connection
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 * @package Sunchemical
 *
 */
class DAOConnection implements DAOConnectionInterface {

	/**
	 * DB ENGINE
	 * 
	 * @var string
	 */
	const SQL_ENGINE = "mssqlnative";
	
	/**
	 * Connection Handle
	 * 
	 * @var resource
	 */
	private $linkD = false;

	/**
	 * App properties
	 */
    private $properties;
	
	/**
     * Private ( singleton ) constructor
     */
    public function __construct( PropertyManager $PM ) { 
    	$this->properties = $PM->getProperties();
    	$this->connect();
    }

	/**
	 * Connects to the SQLSERVER DB host
	 */
	public function connect() {
		
		// # Local vs prod sqlserver drivers
		$engine = $this->properties[ "env" ] == "prod" ? self::SQL_ENGINE : "mssql";

		// # Initializing connection
		$this->linkD = @ NewADOConnection( $engine );	

		// # Check handle
		if( !$this->linkD ) {
			return false;
		}

		// # Set connection chartset
		$this->linkD->SetCharSet( 'utf8' );
		
		// # Connecting to DB
		$connResponse = @ $this->linkD->Connect(
			$this->properties[ "dbhost" ], $this->properties[ "dbusername" ],
			$this->properties[ "dbpassword" ], $this->properties[ "dbname" ]
		);

		// # Connection fails
		if( !$connResponse ) {
			return false;
		}

		// # Setting fetch mode once and for all
		$this->linkD->SetFetchMode( ADODB_FETCH_ASSOC );
		
	}

	/**
	 * Returns the linkD ( connection handle ) resource
	 */
	public function getLinkD() {
		return $this->linkD;
	}
}	
?>