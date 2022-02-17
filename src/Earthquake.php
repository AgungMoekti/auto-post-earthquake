<?php 

class Earthquake{

    // Url api BMKG
    const URL_BMKG = "https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json";
    // Url api Facebook
    const URL_FB = "https://graph.facebook.com/v13.0/me/photos/";
    // Url api Shakemap
    const URL_BMKG_PICT = "https://data.bmkg.go.id/DataMKG/TEWS/"; 

    // Access token facebook api
    public ?string $access_token = null;

    public function __construct()
    {
        $this->start();
    }

    private function curlGet():string|bool
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, self::URL_BMKG); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch);      
        return $output;
    }

    private function curlPost(array $data):string|bool
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, self::URL_FB);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch);      
        return $output;
    }

    public function start():void
    {
        $pict = null;

        $cyan="\033[1;36m";
        $red="\033[1;31m";
        $green="\033[1;32m";
        $yellow="\033[1;33m";

        if( isset( $this->access_token )){
            Die("{$red}[Error] {$green}Set the access token first". PHP_EOL);
        }
        
        if( !$r = $this->curlGet() ){
            Die("{$red}[Error] {$green}No Internet Connection". PHP_EOL);
        }else{
            $last_earthquake = json_decode($r,true)['Infogempa']['gempa']['Tanggal'];
        }

        while (True){

            // To Check are bad connection or no
            if( is_string($r = $this->curlGet()) ){
                $raw = json_decode($r,true);
            }else{
                echo "{$red}[Error] {$cyan}Bad Internet connection" . PHP_EOL;
                sleep(5);
                continue;
            }

            // To Submit New Earthquake
            if(isset($raw['Infogempa']['gempa']['Tanggal']) && $raw['Infogempa']['gempa']['Tanggal'] != $last_earthquake){
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
                $final = $this->curlPost([
                    'message' => $msg,
                    'url' => self::URL_BMKG_PICT . $pict,
                    'access_token' => $this->access_token]);
                echo "{$red}[Result] {$green}$final" . PHP_EOL;
            }

            echo "{$green}[Wait] {$cyan}Wait a new Earthquake" . PHP_EOL;
            sleep(10);   


        }
    }
}