
<?php
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

$jio_m3u_url = 'https://raw.githubusercontent.com/alex8875/m3u/refs/heads/main/jstar.m3u';
$zee5_m3u_url = 'https://raw.githubusercontent.com/alex8875/m3u/refs/heads/main/z5.m3u';
$json_url = 'https://raw.githubusercontent.com/vijay-iptv/JSON/refs/heads/main/jiodata.json';

// Load M3U and JSON
$jiom3u = file_get_contents($jio_m3u_url);
$zee5m3u = file_get_contents($zee5_m3u_url);
$json = json_decode(file_get_contents($json_url), true);
if (preg_match('/__hdnea__=[^"}]+/', $jiom3u, $matches)) {
    $hdnea = $matches[0];
} else {
    // 2. Fallback: try to extract from URL query string
    if (preg_match('/[?&]__hdnea__=([^&]+)/', $jiom3u, $matches)) {
        $hdnea = '__hdnea__=' . $matches[1];
    } else {
        $hdnea = '';
    }
}
$output = '#EXTM3U x-tvg-url="https://avkb.short.gy/jioepg.xml.gz"' . PHP_EOL;
foreach ($json as $item) {
    if (isset($item['channel_id'], $item['logoUrl'], $item['channelLanguageId'])) {
        $channelMap[(string)$item['channel_id']] = [
            'logo' => $item['logoUrl'],
            'language' => $item['channelLanguageId']
        ];
    }
    if (isset($item['channel_id'], $item['logoUrl'], $item['channelLanguageId'], $item['channel_name'], $item['license_key'], $item['bts'])) 
    {
        $output .= '#EXTINF:-1 tvg-id="' . $item['channel_id'] . '" group-title="JioPlus-' . $item['channelLanguageId'] . '" tvg-logo="' . $item['logoUrl'] . '",' . $item['channel_name'] . PHP_EOL;
        $output .= '#KODIPROP:inputstream.adaptive.license_type=clearkey' . PHP_EOL;
        $output .= '#KODIPROP:inputstream.adaptive.license_key=' . $item['license_key'] . PHP_EOL;
        $output .= '#EXTVLCOPT:http-user-agent=plaYtv/7.1.3 (Linux;Android 13) ygx/69.1 ExoPlayerLib/824.0' . PHP_EOL;
        $output .= '#EXTHTTP:{"cookie":"'.$hdnea.'"}'  . PHP_EOL;
        $output .= 'https://jiotvpllive.cdn.jio.com/bpk-tv/' . $item['bts'] . '/index.mpd?'.$hdnea.'&xxx=%7Ccookie='.$hdnea . PHP_EOL . PHP_EOL;
    }
}
// Process M3U lines
$combined_m3u = $zee5m3u;
$lines = explode("\n", $combined_m3u);

foreach ($lines as &$line) {
    if (strpos($line, '#EXTINF:') === 0) {
        if (preg_match('/tvg-id="([^"]+)"/', $line, $match)) {
            $id = $match[1];
            if (isset($channelMap[$id])) {
                $logo = $channelMap[$id]['logo'];
                $lang = $channelMap[$id]['language'];
                if (preg_match('/tvg-logo="[^"]*"/', $line))
                {
                    $line = preg_replace('/tvg-logo="[^"]*"/', 'tvg-logo="' . $logo . '"', $line);
                }
                else 
                {
                    $line = preg_replace('/(tvg-id="[^"]+")/', '$1 tvg-logo="' . $logo . '"', $line);
                }
                if (preg_match('/group-title="Zee5-[^"]*"/', $line) && $channelMap[$id] != '') 
                {
                    $line = preg_replace('/group-title="Zee5-[^"]*"/', 'group-title="' . $lang . '"', $line);
                }
                else
                {
                    $line = preg_replace('/group-title="[^"]*"/', 'group-title="JioStar-' . $lang . '"', $line);
                }
            }
        }
    }
}
header('Content-Type: text/plain');
echo '#EXTM3U x-tvg-url="https://live.dinesh29.com.np/epg/jiotvplus/master-epg.xml.gz \n';
echo $output . PHP_EOL . PHP_EOL;
echo implode("\n", $lines);

exit;
?>