<?php
$password = '';

function jsonp_reply($source_code)
{
    header('Content-Type: text/javascript');
    header('Content-Length: ' . strlen($source_code));
    echo $source_code;
}

function jsonp_cleanup()
{
    $script_element_id = $_GET['s'];
    $timeout_id = $_GET['o'];
    $javascript = <<<EOT
(function() {
    var scriptElement = document.getElementById('$script_element_id');
    scriptElement.parentElement.removeChild(scriptElement);
    clearTimeout($timeout_id);
})();
EOT;
    jsonp_reply($javascript);
}

function jsonp_retry($error)
{
    jsonp_cleanup();
    $https = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'];
    $server_port = $https && $_SERVER['SERVER_PORT'] != '443' || !$https && $_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '';
    $url = ($https ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $server_port . $_SERVER['REQUEST_URI'];
    $password = $_GET['p'];
    $text = $_GET['t'];
    $link = $_GET['l'];
    $javascript = <<<EOT
(function() {
    var url = '$url';
    var password = '$password';
    function connectionError(link, text) {
        var retry = confirm('Connection error! Retry?');
        if (retry) {
            addArticle(link, text);
        }
    }
    function addArticle(link, text) {
        // Collect parameters
        var scriptElementId = 'script-' + Date.now();
        var timeoutId = setTimeout(connectionError, 5000, link, text);
        // Add script element
        var script = document.createElement('script');
        script.id = scriptElementId;
        script.src = url + '?p=' + encodeURIComponent(password) + '&l=' + encodeURIComponent(link) + '&t=' + encodeURIComponent(text) + '&s=' + encodeURIComponent(scriptElementId) + '&o=' + encodeURIComponent(timeoutId);
        document.body.appendChild(script);
    }
    var retry = confirm('$error Retry?');
    if (retry) {
        addArticle('$link', '$text');
    }
})();
EOT;
    echo $javascript;
}

function jsonp_ok()
{
    jsonp_cleanup();
    $javascript = <<<EOT
(function() {
    var element = document.createElement('div');
    element.textContent = ':-)';
    element.style.position = 'fixed';
    element.style.top = 0;
    element.style.left = 0;
    element.style.width = '100%';
    element.style.backgroundColor = 'lightgray';
    element.style.zIndex = 50000;
    element.style.opacity = 0.6;
    element.style.textAlign = 'center';
    element.style.fontFamily = 'sans-serif';
    element.style.fontSize = '5ex';
    document.body.appendChild(element);
    setTimeout(function() {
        element.parentElement.removeChild(element);
    }, 2000);
})();
EOT;
    echo $javascript;
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
    $has_script_element_id = parameter_present_and_not_empty('s');
    $has_timeout_id = parameter_present_and_not_empty('o');
    return $has_password && $has_text && $has_link && $has_script_element_id && $has_timeout_id;
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
    fputs($out_file, '<link>' . htmlspecialchars($link, ENT_XML1, 'utf-8') . '</link>' . PHP_EOL);
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
        jsonp_retry('You did not set the password on server!');
        exit;
    }
    if ($password !== $_GET['p'])
    {
        jsonp_retry('Wrong password!');
        exit;
    }

    $rss_time_format = 'D, d M Y H:i:s O';  # DATE_RSS in PHP7
    $rss_date = date($rss_time_format, time());
    $index_file = fopen($filename, 'r');
    $tmp_file = fopen($tmp_filename, 'w');
    if ($tmp_file === false)
    {
        jsonp_retry('Cannot write on server, possibly wrong permissions!');
        exit;
    }
    $line_after_item = '';
    update_channel($index_file, $tmp_file, $rss_date, $line_after_item);
    add_item($tmp_file, $rss_date);
    copy_rest($index_file, $tmp_file, $line_after_item);
    fclose($index_file);
    fclose($tmp_file);
    rename($tmp_filename, $filename);
    jsonp_ok();
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

