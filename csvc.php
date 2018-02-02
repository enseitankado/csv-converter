#!/usr/bin/php -q
<?php
	set_time_limit(0);
	error_reporting(E_ERROR);
	
	/*
	Written by  code at ashleyhunt dot co dot uk
	at http://php.net/manual/en/function.fgetcsv.php
	*/
	function analyse_file($file, $capture_limit_in_kb = 10) {
		// capture starting memory usage
		$output['peak_mem']['start']    = memory_get_peak_usage(true);

		// log the limit how much of the file was sampled (in Kb)
		$output['read_kb']                 = $capture_limit_in_kb;
		
		// read in file
		$fh = fopen($file, 'r');
			$contents = fread($fh, ($capture_limit_in_kb * 1024)); // in KB
		fclose($fh);
		
		// specify allowed field delimiters
		$delimiters = array(
			'comma'     => ',',
			'semicolon' => ';',
			'tab'         => "\t",
			'pipe'         => '|',
			'colon'     => ':'
		);
		
		// specify allowed line endings
		$line_endings = array(
			'rn'         => "\r\n",
			'n'         => "\n",
			'r'         => "\r",
			'nr'         => "\n\r",
			'pipe'         => '|',
		);
		
		// loop and count each line ending instance
		foreach ($line_endings as $key => $value) {
			$line_result[$key] = substr_count($contents, $value);
		}
		
		// sort by largest array value
		asort($line_result);
		
		// log to output array
		$output['line_ending']['results']     = $line_result;
		$output['line_ending']['count']     = end($line_result);
		$output['line_ending']['key']         = key($line_result);
		$output['line_ending']['value']     = $line_endings[$output['line_ending']['key']];
		$lines = explode($output['line_ending']['value'], $contents);
		
		// remove last line of array, as this maybe incomplete?
		array_pop($lines);
		
		// create a string from the legal lines
		$complete_lines = implode(' ', $lines);
		
		// log statistics to output array
		$output['lines']['count']     = count($lines);
		$output['lines']['length']     = strlen($complete_lines);
		
		// loop and count each delimiter instance
		foreach ($delimiters as $delimiter_key => $delimiter) {
			$delimiter_result[$delimiter_key] = substr_count($complete_lines, $delimiter);
		}
		
		// sort by largest array value
		asort($delimiter_result);
		
		// log statistics to output array with largest counts as the value
		$output['delimiter']['results']     = $delimiter_result;
		$output['delimiter']['count']         = end($delimiter_result);
		$output['delimiter']['key']         = key($delimiter_result);
		$output['delimiter']['value']         = $delimiters[$output['delimiter']['key']];
		
		// capture ending memory usage
		$output['peak_mem']['end'] = memory_get_peak_usage(true);
		return $output;
	}
	
	/*
	Written by  eric dot brison at anakeen dot com 
	at http://php.net/manual/en/features.commandline.php	
	*/
	function arguments($argv) {
		$_ARG = array();
		foreach ($argv as $arg) {
		  if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
			$_ARG[$reg[1]] = $reg[2];
		  } elseif(ereg('-([a-zA-Z0-9])',$arg,$reg)) {
				$_ARG[$reg[1]] = 'true';
			}
	  
		}
		return $_ARG;
	} 

	/*
	Written by Özgür Koca
	at https://github.com/enseitankado
	*/
	function parse_csv(
		$file,
		$outfile,
		$limit, 
		$out_line_ending,
		$out_delimiter,
		$create_report = false,
		$list_header = false,
		$clear_crlf = false,
		$line_ending = "\r\n",
		$delimiter = ",",
		$verbose = false, 
		$trim_fields = false,
		$to_encoding=null, 
		$from_encoding=null		
		)
	{
		global $time_start;
		
		if ($outfile)
		if (($out_handle = fopen($outfile, "w")) == FALSE) {
			die("fopen error:".error_get_last()."\n");
		}
	
		
		$arr = array();
		$rec = 1;
		
		if (($handle = fopen($file, "r")) !== FALSE) {
			
			
			while (($line = stream_get_line($handle, 4096, $line_ending)) !== FALSE) {
						
				$data = str_getcsv($line, $delimiter);				
				$num = count($data);
				unset($arr);	
				for ($c=0; $c < $num; $c++) {
					
					if ($clear_crlf)
						$data[$c] = preg_replace('/[\r\n]+/','', $data[$c]);
					
					if ($trim_fields)
						$data[$c] = trim($data[$c]);
					
					if (!empty($to_encoding) && !empty($from_encoding))
						$arr[] = mb_convert_encoding($data[$c], $to_encoding, $from_encoding);
					else
						$arr[] = $data[$c];
				}
				
				if ($list_header && $rec == 1)
				{
					echo "\n\033[32mListing header:\033[0m\n";
					echo implode(',', $arr)."\n\n";
				}
				
				if (2 == $rec) {
					echo "\033[32mProcessing.... To break press ctrl^c.\033[0m\n";
				}
				
				if ($out_handle) {
					if ( ($fwrite = fwrite($out_handle, implode($out_delimiter, $arr).$out_line_ending)) == FALSE)
					{
						die("fwrite error to $outfile:".error_get_last()."\n");
					}
				} 
				if (null == $outfile)
					die("Output file not specified. Exiting...\n");
				
				if ($verbose)
				if ($rec % rand(900, 1000) == 0) {
					echo sprintf("\033[32m[%'.06d] ", $secs = (microtime(true) - $time_start));
					echo sprintf("Speed: %d,", $rec / $secs);
					echo ' Rec: '.number_format($rec, 0, ".", ",").',';
					echo ' Mem: '.sprintf("%.2f", memory_get_peak_usage(true)/1048576).'MB, ';
					echo " Wrote to: '$outfile'";					
					echo " # \033[0m".implode(',', $arr)."\n";				
				}
				$rec++;
				
				if (null != $limit)
				if ($rec > $limit) {
					fclose($handle);
					die("Finished.");				
				}
			 }
			fclose($handle);
		}
		else echo "fopen error!\n";
		
		if ($create_report) {
			$r .= sprintf("[%'.06d] ", $secs = (microtime(true) - $time_start));
			$r .= sprintf("Speed: %d,", $rec / $secs);
			$r .= ' Rec: '.number_format($rec, 0, ".", ",").',';
			$r .= ' Mem: '.sprintf("%.2f", memory_get_peak_usage(true)/1048576).'MB';								
			file_put_contents($file.'.report', $r);
		}
	}
	
	$time_start = microtime(true); 
	$args = arguments($argv);
	
	// Print usage
	if ( count($args) == 0)
	{
		echo "\n Converts CSV formats to another one with new encoding and delimiters.\n";
		echo "\nUsage: \n  php csvc.php --file=source.csv --input_encoding=ISO-8859-9 --output_encoding=UTF-8 --input_delimiter=',' --input_line_ending='\\r\\n'\n";
		$lwidth = 36;
		$format = "\n\t%-20.s\t %s";
		echo sprintf($format, "--file","File name of input file.");
		echo sprintf($format, "--out_file","File name of output file. Default <filename>.csv");
		echo sprintf($format, "--limit", "Only process first X lines from file.");
		echo sprintf($format, "--input_encoding", "Encoding of the input file. If not specified, it is tried to be detected from the source file.");
		echo sprintf($format, "--output_encoding", "Encoding of the output file. If not specified, it is assumed to be utf8.");
		echo sprintf($format, "--input_line_ending", "Line ending char(s) of input file. Otherwise it will be autodetected. The char(s) must be specified in single quotes.");
		echo sprintf($format, "--input_delimiter", "Delimiter char(s) of input file. Otherwise it will be autodetected. The char(s) must be specified in single quotes. ");
		echo sprintf($format, "--output_line_ending", "Line ending char(s) of output file. Default: \\r\\n");
		echo sprintf($format, "--output_delimiter", "Delimiter char(s) of fields. Default: ,");
		echo sprintf($format, "--capture_limit", "Capture limit for delimiter end line ending analsis. Default: 10KB ");
		echo sprintf($format, "-t", "Trim field values");
		echo sprintf($format, "-h", "Show header. Header dumped from first line.");
		echo sprintf($format, "-c", "Clear CRLF (Carie return/Line feed) in fields.");
		echo sprintf($format, "-v", "Verbose mode. Random rows printed out in processing");
		echo sprintf($format, "-r", "Create report. Named with <filename>.report");
		die("\n");
	}
	
	$capture_limit = isset($args['capture_limit']) ? $args['capture_limit'] : 10;
	$output_encoding = isset($args['output_encoding']) ? $args['output_encoding'] : null;
	$input_encoding = isset($args['input_encoding']) ? $args['input_encoding'] : null;
	$limit = isset($args['limit']) ? $args['limit'] : null;
	$out_line_ending = isset($args['output_line_ending']) ? $args['output_line_ending'] : "\r\n";
	$out_delimiter = isset($args['output_delimiter']) ? $args['output_delimiter'] : ",";
	$line_ending_value = isset($args['input_line_ending']) ? str_replace(array('\r','\n','\t'),array("\r","\n","\t"),$args['input_line_ending']) : null; 
	$delimiter_value = isset($args['input_delimiter']) ? str_replace(array('\r','\n','\t'),array("\r","\n","\t"),$args['input_delimiter']) : null;
	
	$verbose = isset($args['v']) ? true : false;
	$fname = $args['file'];
	$outfile = isset($args['out_file']) ? $args['out_file'] : null;
	$trim_fields = $args['t'];
	$list_header = $args['h'];
	$clear_crlf = $args['c'];
	$create_report = $args['r'];
	
	echo "\n";
	$mexec = ini_get("max_execution_time");	
	if ((int) $mexec != 0)
		echo sprintf("Max PHP execution time: \033[31m%s\033[0m\n\n", $mexec);
	
	if ($input_encoding == null && $output_encoding == null)
	{
		echo "input_encoding and output_encoding doesnt specified.\n";		
		$ret = exec("file --mime-encoding --mime-type $fname");
		$from_encoding = substr($ret, strpos($ret, 'charset=')+8, strlen($ret));
		$to_encoding = 'UTF-8';
		echo "Automaticaly detected input_encoding=\033[32m$from_encoding\033[0m and output_encoding=\033[32m$to_encoding\033[0m will be used.\n";
	}
	
	if ($line_ending_value == null && $delimiter_value == null)
	{
		echo "\nInput line ending and delimiter doesnt specified.\n";
		echo "First $capture_limit KB of '$fname' will be analysed..."; 	
		$report = analyse_file($fname, $capture_limit);		
		$line_ending_key = $report['line_ending']['key'];
		$delimiter_key = "{$report['delimiter']['key']}";
		$line_ending_value = "{$report['line_ending']['value']}";
		$delimiter_value = $report['delimiter']['value'];
		echo "\nAuto detected input_line_ending=\033[32m$line_ending_key($line_ending_value)\033[0m,";
		echo " input_delimiter=\033[32m$delimiter_key($delimiter_value)\033[0m\n";
	}
	
	parse_csv(
		$fname, 
		$outfile, 
		$limit, 
		$out_line_ending,
		$out_delimiter,
		$create_report,
		$list_header, 
		$clear_crlf, 
		$line_ending_value, 
		$delimiter_value, 
		$verbose, 
		$trim_fields, 
		$output_encoding, 
		$input_encoding
	);
	
	echo "The end!\n";
?>