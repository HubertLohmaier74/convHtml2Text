<?php
/**********************************************************************
** 10.06.2021 Hubert Lohmaier, License: public domain
**
** Convert HTML 2 TEXT
**
** V1.0 - 10.06.2021
**********************************************************************/


define('_REPLACER_', '[#br#]');


class convHtml2Text {
	
	private $html = NULL;
	private $msg = array();


	// *************************************************************
	function __construct($filename) {
		if ( ($this->html = file_get_contents($filename)) === FALSE)
			$this->$msg[] = array("ERR" => "Cannot read file: $filename");
    }
	
	
	// -------------------------------------------------------------
	// Call this function to process & convert your HTML-Body
	// -------------------------------------------------------------
	public function go() {
		$start	= "<body>";
		$stop 	= "</body>";
		if ($this->squeezeBody($start, $stop))
			$this->squeezeTags(_REPLACER_);
	}
	
	
	// -------------------------------------------------------------
	// Call this function to eliminate all empty lines 
	// (besides those preceeding a line of data)
	// -------------------------------------------------------------
	public function keepOnly1EmptyLine() {
		$squeezed_text = array();
//		$lines = explode("\r\n", $this->html);
		$lines = explode(_REPLACER_, $this->html);
		$cnt = 0;

		foreach ($lines As $line) {
			if ( count($lines) > $cnt+1 ) {
				$nextline = trim($lines[$cnt+1]);
				// 1) line empty but next isnt => keep empty line
				if ( trim($line) == _REPLACER_ && trim($nextline) != _REPLACER_ ) {
					$squeezed_text[] = $line;
	//				echo "<br>AWAITING DATA IN NEXT LINE: KEEP EMPTY LINE BEFORE";
				}
				// 2) and keep all text
				else if ($line != _REPLACER_) { 
					// skip all line breaks at end of line
					if (substr($line, strlen($line)-strlen(_REPLACER_)) == _REPLACER_)
						$line = substr($line, 0, strlen($line)-strlen(_REPLACER_));
					
					if (trim($line) != "")
						$squeezed_text[] = $line;
	//				echo "<br>DATA: $line";
				}
				$cnt++;
			} 
			else break;
		}
		
		return implode("\r\n", $squeezed_text);	
	}
	
	
	// -------------------------------------------------------------
	// squeeze repeating replacers
	// -------------------------------------------------------------
	private function squeezeEmptyLines() {
		$squeezed_text = array();
		$lines = explode("\r\n", $this->html);
		foreach ($lines As $line) {
			while (strstr($line, _REPLACER_._REPLACER_)) {
				$line = str_replace(_REPLACER_._REPLACER_, _REPLACER_, $line);
			}
			$squeezed_text[] = $line;
		}

		// any lines processed?
		if ( count($squeezed_text) > 0 ) {
			$this->html = implode("\r\n", $squeezed_text);
		}
		else
			$this->registerError("No replacers squeezed");

		
	}
	
	
	// -------------------------------------------------------------
	// Call this function to get your converted text
	// -------------------------------------------------------------
	public function getConvText($bKeepEmptyLines=true) {
		if (!$bKeepEmptyLines)
			$this->squeezeEmptyLines();

		return $this->html;
	}
	

	// -------------------------------------------------------------
	// Call this function to get the processing messages
	// -------------------------------------------------------------
	public function getMessage() {
		return $this->msg;
	}

	
	// .............................................................
	// register a new error / message
	// - Parm $msg: Message text (string)
	// - Parm $line is for debugging purpose and shows the line within body block where the error occured (string)
	// .............................................................
	private function registerError($msg, $line=NULL) {
		$err = $msg;
		if ($line != NULL)
			$err .= " [line:$line]";
		$this->msg[] = array("ERR" => $err);
	}


	// .............................................................
	// Squeezes Body-Section to a certain block between $start + $stop
	//
	// - Eliminates all before $start (string)
	// - Eliminates all after $stop (string)
	// .............................................................
	private function squeezeBody($start, $stop) {
		$bSqueezed = FALSE;
		$tmp = strtolower($this->html);
		$start_pos	= strpos($tmp, $start);
		$stop_pos 	= strpos($tmp, $stop);
		if ($start_pos !== FALSE && $stop_pos !== FALSE) {
			// add length of search string to start position = new start position
			if ( strlen($this->html) >= strlen($start) ) {
				$start_pos += 	strlen($start);
				// squeeze it first time (at beginning)
				$this->html = 	substr($this->html, $start_pos);
				
				if ( strlen($this->html) >= strlen($stop) ) {
					// !!! string is shorter now: calculate again
					$tmp = strtolower($this->html);
					$stop_pos 	= strpos($tmp, $stop);
					// squeeze it second time (at end)
					$this->html = substr($this->html, 0, $stop_pos);
					$bSqueezed = TRUE;
				} else
					$this->registerError("Squeezed to nothing left while squeezing $start till $stop");
			} else 
				$this->registerError("Line too short while squeezing $start till $stop");
		} 
		return $bSqueezed;
	}
	
	
	// .............................................................
	// Search a single line and replace all TAGs from it
	// $html (string)
	// $replace (string)
	// $cnt_line (string)
	// .............................................................
	private function squeezeLine($html, $replace, $cnt_line) {
		$start 	= "<";
		$stop	= ">";
//		echo "<hr>";
		do {
			$html = trim($html);
			$start_pos	= strpos($html, $start);
			$stop_pos 	= strpos($html, $stop);
//			echo "<br>Squeezing: {" . strip_tags($html) . "}";
			if ($start_pos !== FALSE && $stop_pos !== FALSE) {
//				echo "<br>TAG: " . substr($html, $start_pos+1, $stop_pos-1);
//				echo "<br>1: ["; var_dump($start_pos); echo "] / 2: ["; var_dump($stop_pos); echo "]";
				// Vor dem TAG
				$length_before 	= $start_pos;
				$str_before		= substr($html, 0, $length_before);
//				echo "<br>BEFORE TAG: $str_before";
				// Nach dem TAG
				$str_after		= substr($html, $stop_pos + 1);
//				echo "<br>AFTER TAG: $str_after";
				$html = trim($str_before) . $replace . trim($str_after);
			} 
		} while ($start_pos !== FALSE);
		
		return $html;
	}


	// .............................................................
	// Squeezes a line while removing all tags replacing them with 
	// Parm: $replace (string)
	// .............................................................
	private function squeezeTAGs($replace = "") {
		$squeezed_text = array();
		$cnt_line = 0;
		$lines = explode("\r\n", $this->html);
		
		foreach ($lines AS $line) {
			$cnt_line++;
			$squeezed_text[] = $this->squeezeLine($line, $replace, $cnt_line);
		}
		
		// any lines processed?
		if ( count($squeezed_text) >0 )
			$this->html = implode("\r\n", $squeezed_text);
		else
			$this->registerError("No lines squeezed");
	}

}

// CLASS END 
// ..............................................................................................
?>
