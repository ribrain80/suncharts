<?php
/**
 * DAOConnectionInterface file
 */
namespace Sunchemical\Suncharts\Dao;

/**
 * Interface for DAOConnection Class
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 * @package Sunchemical
 *
 */
interface DAOConnectionInterface {
	
	/**
	 * Connects to the SQLSERVER DB host
	 */
	public function connect();

	/**
	 * Returns the linkD ( connection handle ) resource
	 */
	public function getLinkD();
}
?>