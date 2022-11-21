<?php
$last_modified_format = 'D, d M Y H:i:s \\G\\M\\T';
$stylesheet_line = <<<EOT
<?xml-stylesheet type="text/xsl" href="tohtml5.xsl"?>

EOT;
header('Content-Type: application/xml');
header('Content-Length: ' . filesize('index.xml') + strlen($stylesheet_line));
header('Last-Modified: ' . date($last_modified_format, filemtime('index.xml')));
$index_file = fopen('index.xml', 'r');
echo fgets($index_file);  # Output declaration line
echo $stylesheet_line;    # Output stylesheet line
fpassthru($index_file);   # Output the rest of the index file and close it
fclose($index_file);

