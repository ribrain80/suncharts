<?php

namespace Sunchemical\Suncharts\Response;

/**
 *
 * ServiceResponse class
 * Wraps response coming from external services, provides status, data and optionally a description
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sole24Ore
 * @version 1.0
 * @namespace Helpers
 *
 */
class ServiceResponse {
	
	/**
	 * Status: 200 OK
	 * @var string
	 */
	const STATUS_OK = "OK";
	
	/**
	 * Status: 500 KO
	 * @var string
	 */
	const STATUS_KO = "KO";
	
	/**
	 * Status: 404 NOT FOUND
	 * @var string
	 */
	const STATUS_NOT_FOUND = "NOT_FOUND";
	
	/**
	 * Status: 504 gateway timeout
	 * @var string
	 */
	const STATUS_GATEWAY_TIMEOUT = "TIMEOUT";
	
	/**
	 * Response status
	 * @var string
	 */
	public $status;
	
	/**
	 * Response Body
	 * @var mixed
	 */
	public $data;
	
	/**
	 * Error description ( if any )
	 * @var string
	 */
	public $description;

	/**
	 * inforesponse code
	 * @var int
	 */
	public $code;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		$this->status = self::STATUS_KO;
		$this->data = null;
		$this->code = 200;
		$this->description = "";
	}
	
}