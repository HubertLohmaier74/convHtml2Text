<!DOCTYPE HTML> 
<html>
<head>
<title>HTML 2 TEXT CONVERTER</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>



<?php
	// 1. To use this tool you have to save your html to disk first e.g. using file_put_contents("test.txt", $html_data);
	
	// 2. This tool does not change encoding. It depends on how you have retrieved and stored your mailtext.
	// But this index.php shows it in UTF-8 format on screen. 
	// So if you have stored it another way display may differ from real. 
	// If you like to check that store the converted text to a file and open it in a editor that can handle encodings like Notepad++

	require_once("convHtml2Text.php");
	$mail_filename = "test.txt"; // at least contains a <body> ... </body> - Block

	if ( file_exists($mail_filename) ) {
		$conv = new convHtml2Text($mail_filename);
		$conv->go();
		$text = $conv->getConvText(FALSE);
		$text = $conv->keepOnly1EmptyLine();
		
		echo "<pre>";
		if ($text != NULL) {
			echo "Converted:<hr>";
			$text = str_replace(_REPLACER_, "<br>", $text);
			print_r($text);
			file_put_contents("testout.txt", $text);
			echo "<hr>";
		}
		else {
			echo "Errors:<hr>";
			var_dump($conv->getMessage());

		}
		echo "</pre>";
		
	} else 
		echo "File missing: $mail_filename";
?>

</body>
</html>
