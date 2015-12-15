<?php

$url = 'http://developer.ebay.com/devzone/finding/callref/Enums/GlobalIdList.html';

$content = file_get_contents($url);

$patt_table = '#.*<table.*class="tableEnum".*>(.*)</table>.*#Uism';
$patt = '#.*<tr.*>.*<td.*>(.*)</td>.*<td.*>.*</td>.*<td.*>.*</td>.*<td.*>(.*)</td>.*<td.*>(.*)</td>.*</tr>.*#Uism';

preg_match($patt_table, $content, $match);

preg_match_all($patt, $match[1], $matches);

print_r($matches);

$ebay_global_ids = array();

foreach($matches[3] as $_key => $_val) {
    if(strtolower($_val) == 'n/a') {
        continue;
    }

    $_val = str_replace('eBay ', '', $matches[2][$_key]);

    $ebay_global_ids[$matches[1][$_key]] = $_val;
}

print_r($ebay_global_ids);

$file_content = '<?php' . PHP_EOL . 'return ' . var_export($ebay_global_ids, true) . ';';

echo $file_content;

file_put_contents('ebay_global_ids.php', $file_content);

foreach($matches[3] as $_key => $_val) {
    if(strtolower($_val) == 'n/a') {
        continue;
    }

    $ebay_site_ids[$matches[1][$_key]] = $_val;
}

ksort($ebay_site_ids);

print_r($ebay_site_ids);

$file_content = '<?php' . PHP_EOL . 'return ' . var_export($ebay_site_ids, true) . ';';

echo $file_content;

file_put_contents('ebay_site_ids.php', $file_content);