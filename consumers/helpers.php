<?php
	/**
     * HTTP request using PHP CURL functions
     * Requires curl library installed and configured for PHP
     * 
     * @param Array $get_variables
     * @param Array $post_variables
     * @param Array $headers
     */
    function curlRequest($url, $get_variables = null, $post_variables = null, $headers = null)
    {
        $ch = curl_init();
        
        if (is_array($get_variables)) {
            $get_variables = '?' . str_replace('&amp;', '&', urldecode(http_build_query($get_variables)));
        } else {
            $get_variables = null;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url . $get_variables);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //CURL doesn't like google's cert
        
        if (!empty($post_variables)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_variables);
        }
        
        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return array(
            'body' => $response,
            'code' => $code
        );
    }


    /**
     * HTTP request using PHP CURL functions
     * 
     * @param String $content
     */
	function logdata($content)
	{
		$content = $content.PHP_EOL;
		//$LOG_DIR = "/var/www/html/ip/logs/";
        $LOG_DIR = "logs/";
		#$ip = $_SERVER['REMOTE_ADDR'];
		
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$agent = $_SERVER['HTTP_USER_AGENT'];
		else 
			$agent = 'Client';
			
		$date = date("Ymd");
		$date2 = date("Y m d H:i");
		$file = $LOG_DIR."log.txt";//"log-$date.txt";
		file_put_contents($file,"<br>$date2 $content<br>",FILE_APPEND);
	}

    //Function to get Data string
    function getDataString($inputstr,$datatag){
        $datastartpos = strpos($inputstr, $datatag);
        $dataendpos = strrpos($inputstr, $datatag);
        $data=substr($inputstr,$datastartpos - 1,$dataendpos + strlen($datatag)+2 - $datastartpos);
        return $data;
    }
    
    //Function to get Signature string
    function getSignatureString($inputstr,$sigtag){
        $sigstartpos = strpos($inputstr, $sigtag);
        $sigendpos = strrpos($inputstr, $sigtag);
        $signature=substr($inputstr,$sigstartpos + strlen($sigtag)+1,$sigendpos - $sigstartpos -strlen($sigtag)-3);
        return $signature;
    }
