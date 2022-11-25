<?php
error_reporting(E_ALL);

function update_channel($in_file, $out_file, $date, &$line_after_item)
{
    # Parse channel elements
    while (($line = fgets($in_file)) !== false)
    {
        $trimmed_line = trim($line);
        if (strpos($trimmed_line, '<lastBuildDate>') !== false)
        {
            $line = '<lastBuildDate>' . $date . '</lastBuildDate>' . PHP_EOL;
        }
        elseif (strpos($trimmed_line, '<item>') !== false || strpos($trimmed_line, '</channel>') !== false)
        {
            $line_after_item = $line;
            break;
        }
        else
        {
            $line = $trimmed_line . PHP_EOL;
        }
        fputs($out_file, $line);
    }
}

function add_item($out_file, $text, $link, $date)
{
    fputs($out_file, '<item>' . PHP_EOL);
    fputs($out_file, '<description>' . $text . '</description>' . PHP_EOL);
    fputs($out_file, '<link>' . $link . '</link>' . PHP_EOL);
    fputs($out_file, '<pubDate>' . $date . '</pubDate>' . PHP_EOL);
    fputs($out_file, '</item>' . PHP_EOL);
}

function copy_rest($in_file, $out_file, &$line_after_item)
{
    fputs($out_file, $line_after_item);  # Eaten up in update_channel
    while (feof($in_file)) { fwrite($out_file, fread($in_file, 8192)); }
}

function add_article($text, $link, $filename, $tmp_filename)
{
    $rss_time_format = 'D, d M Y H:i:s O';  # DATE_RSS in PHP7
    $rss_date = date($rss_time_format, time());
    $index_file = fopen($filename, 'r');
    $tmp_file = fopen($tmp_filename, 'w');
    $line_after_item = '';
    update_channel($index_file, $tmp_file, $rss_date, $line_after_item);
    add_item($tmp_file, $text, $link, $rss_date);
    copy_rest($index_file, $tmp_file, $line_after_item);
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
    $last_modified_format = 'D, d M Y H:i:s \\G\\M\\T';  # DATE_RFC7231 in PHP7
    $last_modified = date($last_modified_format, filemtime($filename));

    header('Content-Type: application/xml');
    header('Content-Length: ' . $content_length);
    header('Last-Modified: ' . $last_modified);

    if ($headers_only === true)
        return;

    $index_file = fopen($filename, 'r');
    echo fgets($index_file);  # Output declaration line
    echo $stylesheet_line;    # Output stylesheet line
    fpassthru($index_file);   # Output the rest of the index file and close it
    fclose($index_file);
}

$request_method = $_SERVER['REQUEST_METHOD'];
if ($request_method === 'HEAD')
{
    output_index('index.xml', $headers_only = true);
}
elseif ($request_method === 'GET')
{
    output_index('index.xml');
}
else
{
    http_response_code(405);
}

