<?php



function send_more_files($from, $to, $subject, $message, $files) {
	
	
	// email fields: to, from, subject, and so on
	
	$headers = "From: $from";
	 
	// boundary 
	$semi_rand = md5(time()); 
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
	 
	// headers for attachment 
	$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
	 
	// multipart boundary 
	$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n"; 
	$message .= "--{$mime_boundary}\n";
	 
	// preparing attachments
	for($x=0;$x<count($files);$x++){
		$file = fopen($files[$x],"rb");
		$data = fread($file,filesize($files[$x]));
		fclose($file);
		$data = chunk_split(base64_encode($data));
		$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"vstupenka.pdf\"\n" . 
		"Content-Disposition: attachment;\n" . " filename=\"vstupenka.pdf\"\n" . 
		"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
		$message .= "--{$mime_boundary}\n";
	}
	 
	// send
	 
	mail($to, $subject, $message, $headers);
	mail("kb@karelbilek.com", "[matfyzak vstupenky] ".$subject, $message, $headers);
}



?>
