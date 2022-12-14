<?php
$password = '';

function echo_reply($contents, $content_type)
{
    header('Content-Type: ' . $content_type);
    header('Content-Length: ' . strlen($contents));
    echo $contents;
}

function parameter_present_and_not_empty($p)
{
    return array_key_exists($p, $_GET) && !empty($_GET[$p]);
}

function requested_add_new_article()
{
    $has_password = parameter_present_and_not_empty('p');
    $has_text = parameter_present_and_not_empty('t');
    $has_link = parameter_present_and_not_empty('l');
    return $has_password && $has_text && $has_link;
}

function update_channel($in_file, $out_file, &$date, &$line_after_item)
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

function add_item($out_file, &$date)
{
    $text = $_GET['t'];
    $link = $_GET['l'];
    fputs($out_file, '<item>' . PHP_EOL);
    fputs($out_file, '<title>' . htmlspecialchars($text, ENT_XML1, 'utf-8') . '</title>' . PHP_EOL);
    $encoded_link = htmlentities($link, ENT_XML1);
    fputs($out_file, '<link>' . $encoded_link . '</link>' . PHP_EOL);
    fputs($out_file, '<guid>' . $encoded_link . '</guid>' . PHP_EOL);
    fputs($out_file, '<pubDate>' . $date . '</pubDate>' . PHP_EOL);
    fputs($out_file, '</item>' . PHP_EOL);
}

function copy_rest($in_file, $out_file, &$line_after_item)
{
    fputs($out_file, $line_after_item);  # Eaten up in update_channel
    while (!feof($in_file)) { fwrite($out_file, fread($in_file, 8192)); }
}

function add_article($filename, $tmp_filename)
{
    global $password;
    if ($password === '')
    {
        echo_reply('You did not set the password on server!', 'text/plain');
        exit;
    }
    if ($password !== $_GET['p'])
    {
        echo_reply('Wrong password!', 'text/plain');
        exit;
    }

    $rss_time_format = 'D, d M Y H:i:s O';  # DATE_RSS in PHP7
    $rss_date = date($rss_time_format, time());
    $index_file = fopen($filename, 'r');
    $tmp_file = fopen($tmp_filename, 'w');
    if ($tmp_file === false)
    {
        echo_reply('Cannot write on server, possibly wrong permissions!', 'text/plain');
        exit;
    }
    $line_after_item = '';
    update_channel($index_file, $tmp_file, $rss_date, $line_after_item);
    add_item($tmp_file, $rss_date);
    copy_rest($index_file, $tmp_file, $line_after_item);
    fclose($index_file);
    fclose($tmp_file);
    rename($tmp_filename, $filename);
    $reply_ok = <<<EOT
<!doctype html>
<title>:-)</title>
<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; font-family: sans-serif; font-size: 10ex">:-)</div>
<script>setTimeout(function() { window.history.back(); }, 1000);</script>
EOT;
    echo_reply($reply_ok, 'text/html');
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
    if (requested_add_new_article())
        add_article('index.xml', 'indextmp.xml');
    else
        output_index('index.xml');
}
else
{
    http_response_code(405);
}

