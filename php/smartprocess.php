<?php 

	if (!isset($_SESSION)) session_start(); 
	if(!$_POST) exit;
	
	include dirname(__FILE__).'/settings/settings.php';
	include dirname(__FILE__).'/functions/emailValidation.php';
	
	
	/* Current Date Year
	------------------------------- */		
	$currYear = date("Y");		
	
/*	---------------------------------------------------------------------------
	: Register all form field variables here
	--------------------------------------------------------------------------- */
	$firstname = strip_tags(trim($_POST["firstname"]));	
	$lastname = strip_tags(trim($_POST["lastname"]));
	$emailaddress = strip_tags(trim($_POST["emailaddress"]));
	$telephone = strip_tags(trim($_POST["telephone"]));
	$address1 = strip_tags(trim($_POST["address1"]));
	$address2 = strip_tags(trim($_POST["address2"]));
	$radio1 = strip_tags(trim($_POST["radio1"]));
	$buildingsize = strip_tags(trim($_POST["buildingsize"]));
	$numberoffloors = strip_tags(trim($_POST["numberoffloors"]));
	$servicecheckbox = $_POST["servicecheckbox"];
	if ($servicecheckbox[0]!=""){
		$servicecheckbox_list = implode( '<br/>', $servicecheckbox);
	}
	$sendermessage = strip_tags(trim($_POST["sendermessage"]));
	$file1 = strip_tags(trim($_POST["file1"]));
	$file2 = strip_tags(trim($_POST["file2"]));
	$file3 = strip_tags(trim($_POST["file3"]));
	$file4 = strip_tags(trim($_POST["file4"]));
	$file5 = strip_tags(trim($_POST["file5"]));
	$file6 = strip_tags(trim($_POST["file6"]));
	$file7 = strip_tags(trim($_POST["file7"]));
	$leadsource = strip_tags(trim($_POST["leadsource"]));
    $captcha = strtoupper(strip_tags(trim($_POST["captcha"])));
	
/*	----------------------------------------------------------------------
	: Prepare form field variables for CSV export
	----------------------------------------------------------------------- */	
	if($generateCSV == true){
		$csvFile = $csvFileName;	
		$csvData = array(
			"$sendername",
			"$emailaddress",
			"$sendersubject"
		);
	}

/*	-------------------------------------------------------------------------
	: Prepare serverside validation 
	------------------------------------------------------------------------- */ 
	$errors = array();
	 //validate first name
	if(isset($_POST["firstname"])){
			if (!$firstname) {
				$errors[] = "Please enter your first name.";
			} elseif(strlen($firstname) < 2)  {
				$errors[] = "Name must be at least 2 characters.";
			}
	}

	//validate last name
	if(isset($_POST["lastname"])){
		if (!$lastname) {
			$errors[] = "Please enter your last name.";
		} else if (strlen($lastname) < 2) {
			$errors[] = "Name must be at least 2 characters.";
		}
	}

	//validate email address
	if(isset($_POST["emailaddress"])){
		if (!$emailaddress) {
			$errors[] = "Please enter your Email address.";
		} else if (!validEmail($emailaddress)) {
			$errors[] = "You must enter a valid Email address.";
		}
	}
	
	//validate telephone
	if(isset($_POST["telephone"])){
			if (!$telephone) {
				$errors[] = "Please enter your telephone number.";
			} elseif(strlen($telephone) < 7)  {
				$errors[] = "Telephone number must include at least 7 digits.";
			}
	}

	//validate physical address
	if(isset($_POST["address1"])){
			if (!$address1) {
				$errors[] = "Please enter your physical address.";
			} elseif(strlen($address1) < 7)  {
				$errors[] = "Address must be at least 7 characters.";
			}
	}
	
	//validate building size
	if(isset($_POST["buildingsize"])){
			if (!$buildingsize) {
				$errors[] = "Please select the size range for your building.";
			} 
	}

	//validate number of floors
	if(isset($_POST["numberoffloors"])){
			if (!$numberoffloors) {
				$errors[] = "Please select the number of floors for your building.";
			} 
	}
	
	// validate security captcha 
	if(isset($_POST["captcha"])){
		if (!$captcha) {
			$errors[] = "You must enter the captcha code";
		} else if (($captcha) != $_SESSION['gfm_captcha']) {
			$errors[] = "Captcha code is incorrect";
		}
	}
	
	if ($errors) {
		//Output errors in a list
		$errortext = "";
		foreach ($errors as $error) {
			$errortext .= '<li>'. $error . "</li>";
		}
	
		echo '<div class="alert notification alert-error">The following errors occured:<br><ul>'. $errortext .'</ul></div>';
	
	} else{
	
		include dirname(__FILE__).'/phpmailer/PHPMailerAutoload.php';
		include dirname(__FILE__).'/templates/smartmessage.php';
			
		$mail = new PHPMailer();
		$mail->isSendmail();
		$mail->IsHTML(true);
		$mail->setFrom($emailaddress,$sendername);
		$mail->CharSet = "UTF-8";
		$mail->Encoding = "base64";
		$mail->Timeout = 200;
		$mail->ContentType = "text/html";
		$mail->addAddress($receiver_email, $receiver_name);
		$mail->Subject = $receiver_subject;
		$mail->Body = $message;
		$mail->AltBody = "Use an HTML compatible email client";
				
		// For multiple email recepients from the form 
		// Simply change recepients from false to true
		// Then enter the recipients email addresses
		// echo $message;
		$recipients = false;
		if($recipients == true){
			$recipients = array(
				"address@example.com" => "Recipient Name",
				"address@example.com" => "Recipient Name"
			);
			
			foreach($recipients as $email => $name){
				$mail->AddBCC($email, $name);
			}	
		}
		
		if($mail->Send()) {
			/*	-----------------------------------------------------------------
				: Generate the CSV file and post values if its true
				----------------------------------------------------------------- */		
				if($generateCSV == true){	
					if (file_exists($csvFile)) {
						$csvFileData = fopen($csvFile, 'a');
						fputcsv($csvFileData, $csvData );
					} else {
						$csvFileData = fopen($csvFile, 'a'); 
						$headerRowFields = array(
							"Guest Name",
							"Email Address",
							"Subject"									
						);
						fputcsv($csvFileData,$headerRowFields);
						fputcsv($csvFileData, $csvData );
					}
					fclose($csvFileData);
				}	
				
			/*	---------------------------------------------------------------------
				: Send the auto responder message if its true
				--------------------------------------------------------------------- */
				if($autoResponder == true){
				
					include dirname(__FILE__).'/templates/autoresponder.php';
					
					$automail = new PHPMailer();
					$automail->isSendmail();
					$automail->setFrom($receiver_email,$receiver_name);
					$automail->isHTML(true);                                 
					$automail->CharSet = "UTF-8";
					$automail->Encoding = "base64";
					$automail->Timeout = 200;
					$automail->ContentType = "text/html";
					$automail->AddAddress($emailaddress, $sendername);
					$automail->Subject = "Thank you for contacting us";
					$automail->Body = $automessage;
					$automail->AltBody = "Use an HTML compatible email client";
					$automail->Send();	 
				}
				
				if($redirectForm == true){
					echo '<script>setTimeout(function () { window.location.replace("'.$redirectForm_url.'") }, 8000); </script>';
				}
							
			  	echo '<div class="alert notification alert-success">Message has been sent successfully!</div>';
				} 
				else {
				  echo '<div class="alert notification alert-error">Message not sent - server error occured!</div>';	
				}
	}
?>