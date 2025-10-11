
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

$url = "https://arunjunan20.github.io/My-IPTV/"; // Your API URL
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout in 10 sec
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Max execution time 30 sec

$response = curl_exec($ch);
curl_close($ch);
$response = preg_replace(
    '/tvg-logo\s*=\s*"https:\/\/yt3\.googleusercontent\.com\/GJVGgzRXxK1FDoUpC8ztBHPu81PMnhc8inodKtEckH-rykiYLzg93HUQIoTIirwORynozMkR=s900-c-k-c0x00ffffff-no-rj"/',
    'tvg-logo="https://raw.githubusercontent.com/vijay-iptv/logos/refs/heads/main/Zee_Tamil_News.png"',
    $response
);
$response = preg_replace(
    '/https:\/\/d229kpbsb5jevy\.cloudfront\.net\/timesplay\/content\/common\/logos\/channel\/logos\/wthfwe\.jpeg/',
    'https://mediaready.videoready.tv/tatasky-epg/image/fetch/f_auto,fl_lossy,q_auto,h_250,w_250/https://ltsk-cdn.s3.eu-west-1.amazonaws.com/jumpstart/Temp_Live/cdn/HLS/Channel/imageContent-12095-j9ooixfs-v1/imageContent-12095-j9ooixfs-m1.png',
    $response
);
$response = preg_replace(
    '/https:\/\/images\.now-tv\.com\/shares\/channelPreview\/img\/en_hk\/color\/ch115_160_115/',
    'https://raw.githubusercontent.com/vijay-iptv/logos/refs/heads/main/HBO.png',
    $response
);
$response = preg_replace(
    '/https:\/\/resizer-acm\.eco\.astro\.com\.my\/tr:w-256,q:85\/https:\/\/divign0fdw3sv\.cloudfront\.net\/Images\/ChannelLogo\/contenthub\/337_144\.png/',
    'https://raw.githubusercontent.com/vijay-iptv/logos/refs/heads/main/Cinemax.png',
    $response
);
$response = preg_replace(
    '/https:\/\/d229kpbsb5jevy\.cloudfront\.net\/timesplay\/content\/common\/logos\/channel\/logos\/vunjev\.jpeg/',
    'https://raw.githubusercontent.com/vijay-iptv/logos/refs/heads/main/MNX_HD.png',
    $response
);
$response = preg_replace(
    '/https:\/\/d229kpbsb5jevy\.cloudfront\.net\/timesplay\/content\/common\/logos\/channel\/logos\/leazcc\.jpeg/',
    'https://mediaready.videoready.tv/tatasky-epg/image/fetch/f_auto,fl_lossy,q_auto,h_250,w_250/https://ltsk-cdn.s3.eu-west-1.amazonaws.com/jumpstart/Temp_Live/cdn/HLS/Channel/imageContent-826-j5m9kx5c-v1/imageContent-826-j5m9kx5c-m1.png',
    $response
);
echo $response;

$url = "https://raw.githubusercontent.com/vijay-iptv/tamil/refs/heads/main/iptv.m3u"; // Your API URL
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout in 10 sec
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Max execution time 30 sec

$response = curl_exec($ch);
curl_close($ch);
echo $response;
exit;
?>