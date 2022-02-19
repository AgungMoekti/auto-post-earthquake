<?php 

$cyan="\033[1;36m";
$red="\033[1;31m";
$green="\033[1;32m";
$yellow="\033[1;33m";

// To Get Json
function curlGet($url){
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);      
    return $output;
}

// To Send Json
function curlPost(string $url, $data){
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);      
    return $output;
}
// Url api bmkg
$url_bmkg = "https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json";
// Url api facebook
$url_fb = "https://graph.facebook.com/v13.0/me/photos/";
// Url api Shakemap
$url_bmkg_pict = "https://data.bmkg.go.id/DataMKG/TEWS/";
$raw = json_decode(curlGet($url_bmkg),true) or Die("{$red}[Error] {$green}No Internet Connection". PHP_EOL);
// Lastest Earthquake
if(isset($argv[1])){
    $last_earthquake_datetime = $argv[1];
}else{
    $last_earthquake = $raw['Infogempa']['gempa']['Tanggal'];
    $last_earthquake_datetime = $raw['Infogempa']['gempa']['DateTime'];
}

// Alert
echo "{$red}[Alert] {$cyan}Script Started!" . PHP_EOL;

echo "{$yellow}[Lastest] {$cyan}$last_earthquake" . PHP_EOL;
echo "{$green}[Wait] {$cyan}Wait a new Earthquake" . PHP_EOL;
// Infinty Loops
while (True) {

    // To Check are bad connection or no
    if($r = curlGet($url_bmkg)){
        $raw = json_decode($r,true);
    }else{
        echo "{$red}[Error] {$cyan}Bad Internet connection" . PHP_EOL;
        echo "{$red}[Error] {$cyan}Trying to reconnect" . PHP_EOL;
        sleep(5);
        continue;
    }

    // To Submit New Earthquake
    if($raw['Infogempa']['gempa']['Tanggal'] != $last_earthquake || $raw['Infogempa']['gempa']['DateTime'] != $last_earthquake_datetime ){
        // Make Sure The Message is empty
        $msg = "";

        // Set new lastest erathquake Date
        $last_earthquake = $raw['Infogempa']['gempa']['Tanggal'];
        echo "{$green}[New] {$cyan}Detected New Earthquake" . PHP_EOL;
        echo "{$green}[Lastest] {$cyan}$last_earthquake" . PHP_EOL;

        // Loop
        foreach ($raw['Infogempa']['gempa'] as $key => $value) {
            echo "{$yellow}$key : $value" . PHP_EOL;
            if($key == "Shakemap"){
                $pict = $value;
                continue;
            }
            $msg .= "$key : $value";
            $msg .= PHP_EOL;
        }
        
        // Send To Facebook page
        $final = curlPost($url_fb,[
            'message' => $msg,
            'url' => $url_bmkg_pict . $pict,
            'access_token' => 'none']);
        echo "{$red}[Result] {$green}$final" . PHP_EOL;
        echo "{$green}[Wait] {$cyan}Wait a new Earthquake" . PHP_EOL;
    }
    // echo "{$green}[Wait] {$cyan}Wait a new Earthquake" . PHP_EOL;
    sleep(60);
}