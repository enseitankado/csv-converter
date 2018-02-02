# csv-converter
Converts CSV formats to another with new encoding and delimiters 

# Some features
 - This is a PHP script. 
 - Can detect encoding of input file.
 -  Can detect field delimiter and  line ending of input CSV. 
 - Low memory consumption with large CSV files.
 - Has a switch that trim fields and clear CRLF of source CSV.

**Also you can specify:**
 - Encoding of input file.
 - Delimiter and line ending of input file. 
 - Encoding of output file.
 - Delimiter and line ending of output file. 

# Command Line Usage
 

    Converts CSV formats to another with new encoding and delimiters.
    
    Usage:
      php csvc.php --file source.csv --input_encoding=ISO-8859-9 --output_encoding=UTF-8 --input_delimiter=',' --input_line_ending='\r\n'
    
            --file                   File name of input file.
            --out_file               File name of output file. Default <filename>.csv
            --limit                  Only process first X lines from file.
            --input_encoding         Encoding of the input file. If not specified, it is tried to be detected from the source file.
            --output_encoding        Encoding of the output file. If not specified, it is assumed to be utf8.
            --input_line_ending      Line ending char(s) of input file. Otherwise it will be autodetected. The char(s) must be specified in single quotes.
            --input_delimiter        Delimiter char(s) of input file. Otherwise it will be autodetected. The char(s) must be specified in single quotes.
            --output_line_ending     Line ending char(s) of output file. Default: \r\n
            --output_delimiter       Delimiter char(s) of fields. Default: ,
            --capture_limit          Capture limit for delimiter end line ending analsis. Default: 10KB
            -t                       Trim field values
            -h                       Show header. Header dumped from first line.
            -c                       Clear CRLF (Carie return/Line feed) in fields.
            -v                       Verbose mode. Random rows printed out in processing
            -r                       Create report. Named with <filename>.report

# Examples

    $ php csvp.php --file=input.csv --input_delimiter='\t' --input_line_ending='\r\n' -v -t -h -c --limit=1
    
    input_encoding and output_encoding doesnt specified.
    Automaticaly detected input_encoding=iso-8859-1 and output_encoding=UTF-8 will be used.
    
    Listing header:
	   ID     MEMBER_NAME 	MEMBER_SURNAME	MEMBER_EMAIL	VALID
    
    Finished.


----------

    $  php csvp.php --file=dump.csv --input_delimiter=',' --input_line_ending='\n' --input_encoding=ISO-8859-9 --output_encoding=UTF-8 -v -t -h -c -r
    
    
    input_encoding and output_encoding doesnt specified.
    Automaticaly detected input_encoding=iso-8859-1 and output_encoding=UTF-8 will be used.
    Encoding converting will be applied: 'ISO-8859-9' --> 'UTF-8'
    
    Listing header:
    ID,MEMBER_NAME,MEMBER_SURNAME,MEMBER_EMAIL,VALID
    
    Processing.... To break press ctrl^c.
    [000000] Speed: 1226, Rec: 993, Mem: 0.25MB # 
    [000001] Speed: 1298, Rec: 1,980, Mem: 0.25MB # 
    [000003] Speed: 1368, Rec: 4,560, Mem: 0.25MB # 


----------

    $  php csvc.php --file=input.csv -v -t -h -c -r
    
    input_encoding and output_encoding doesnt specified.
    Automaticaly detected input_encoding=iso-8859-1 and output_encoding=UTF-8 will be used.
    
    Input line ending and delimiter doesnt specified.
    First 10 KB of 'input.csv' will be analysed...
    Auto detected input_line_ending=pipe(|), input_delimiter=tab(   )
    
    Listing header:
    ID,MEMBER_NAME,MEMBER_SURNAME,MEMBER_EMAIL,VALID
    
    Output file not specified. Exiting...


