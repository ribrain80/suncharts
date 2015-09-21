<?php
/**
 * Mailsender Class file
 */

namespace Sunchemical\Suncharts\Mail;

use Sunchemical\Suncharts\Response\ServiceResponse;

/**
 * Mail sender Object
 *
 * @author Riccardo Brambilla aka ribrain <riccardobra@gmail.com> <Riccardo.Brambilla@nttdata.com>
 * @copyright Sunchemical
 * @version 1.0
 * @package Suncharts
 *
 */
class MailSender {

	/**
	 * Default constructor
	 */
	public function __construct() {}

	/**
	 * Actually sends the email
	 * @return ServiceResponse response
	 */
	public function send() {

		// # Response
		$SR = new ServiceResponse();
		
		// # Mailer settings
	 	$Mailer->isSMTP();  //  Set mailer to use SMTP
	 	$Mailer->Host = $PM->getproperty( "smtphost" ); // Specify main and backup SMTP servers
	 	$Mailer->Port = 25;                                  // TCP port to connect to

	 	// # Retrieve Recipients
		$recipientsCommas = file_get_contents( "config/mail/recipients" );
		$recipients = explode( ",", $recipientsCommas );

		// # Add a recipient
		foreach( $recipients as $recipient ) {
	 		$Mailer->addAddress( $recipient );     
		}

		// # Set From
	 	$Mailer->SetFrom( "lamservice@sunchemical.com", "LAM" );

	 	// # Mail subject
	 	$Mailer->Subject = 'LAM Laboratory Workload analysis';

	 	// # Body/AltBody
	 	$Mailer->Body    = $Mailer->AltBody = 'See attached analysis to evaluate the Laboratory Workload';

	 	// # Attachments
	 	$Mailer->AddAttachment( "imagedata/lab_effort_by_customer.png", "lab_effort_by_customer.png" );
	 	$Mailer->AddAttachment( "imagedata/lab_effort_by_technician.png", "lab_effort_by_technician.png" );
	 	$Mailer->AddAttachment( "imagedata/lab_effort_by_month.png", "lab_effort_by_month.png" );
		
		// # Actually send it
	 	if( !$Mailer->send() ) {
	 		$log->addError( "Mailer Error: " . $Mailer->ErrorInfo );
	 	} else {
	 		// # OK
			$SR->status = ServiceResponse::STATUS_OK;
	 	    $log->addError( "Mailer Success, mail sent");
	 	}		

	}
}