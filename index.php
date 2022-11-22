<?php

function add_article($text, $link, $filename, $tmp_filename)
{
    $index_file = fopen($filename, 'r');
    $tmp_file = fopen($tmp_filename, 'w');
    fclose($index_file);
    fclose($tmp_file);
    rename($tmp_filename, $filename);
}

function output_index($filename, $headers_only = false)
{
    $stylesheet_line = <<<EOT
<?xml-stylesheet type="text/xsl" href="tohtml5.xsl"?>

EOT;
    $content_length = filesize($filename) + strlen($stylesheet_line);
    $last_modified_format = 'D, d M Y H:i:s \\G\\M\\T';
    $last_modified = date($last_modified_format, filemtime($filename));

    header('Content-Type: application/xml');
    header('Content-Length: ' . $content_length);
    header('Last-Modified: ' . $last_modified);

    if ($headers_only === true)
        exit;

    $index_file = fopen($filename, 'r');
    echo fgets($index_file);  # Output declaration line
    echo $stylesheet_line;    # Output stylesheet line
    fpassthru($index_file);   # Output the rest of the index file and close it
    fclose($index_file);
    exit;
}

output_index('index.xml');

