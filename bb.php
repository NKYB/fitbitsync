<?php
require_once 'bootstrap.php';
require_once 'fitbitphp.php';

if (getVar('action') != '')
    $action = getVar('action');
else
    logMsg('No action detected');

function action($action, $json=""){
    cleanVars();
    switch($action){
        case 'LOGIN_WEB':
            $json = action('LOGIN');
            if ($json->token != ""){
                $sql = "UPDATE fitbit_tokens SET bbuser = '" .$json->bbuser. "', bbpass = '" .$json->bbpass. "', bbtoken = '" .$json->json->token. "', bbuser_id = '" .$json->bbuser_id. "' WHERE encoded_id = '".$json->encoded_id."';";
                mysql_query($sql);
                echo 'Login Success';
            } else {
                echo 'Login Failure';
            }
            break;
        case 'LOGIN':
            $bbuser = getVar('bbuser');
            $bbpass = getVar('bbpass');
            $fitbit_token = "";
            $fitbit_secret = "";
            $encoded_id = getVar('encoded_id');
            if($bbuser == ""){
                
                $sql = "SELECT * FROM fitbit_tokens WHERE encoded_id = '" . $encoded_id . "' LIMIT 1;";
                $result = mysql_query($sql);
                if($row = mysql_fetch_array($result)) {
                    if ($row['bbuser'] != ''){
                        $bbuser = $row['bbuser'];
                        $bbpass = $row['bbpass'];
                        $fitbit_token = $row['token'];
                        $fitbit_secret = $row['secret'];
                    }
                }
            }
            
            $cookie_jar = tempnam('cookie_jar/', 'cookie');
            

            $data = array(
                'username' => $bbuser,
                'password' => md5($bbpass),
                'rememberMe' => true 
            );                                                                   
            $data_string = json_encode($data);                                                                                   

            $ch = curl_init('https://api.bodybuilding.com/login');                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);   
            curl_setopt($ch, CURLOPT_COOKIEJAR, realpath($cookie_jar));
//            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data_string))                                                                       
            );                                                                                                                   
            $response = curl_exec($ch);
//            var_dump($response);
            $json = json_decode($response);
            $json->bbuser = $bbuser;
            $json->bbuser_id = $json->userId;
            $json->bbpass = $bbpass;
            $json->encoded_id = $encoded_id;
            $json->cookie_jar = $cookie_jar;
            $json->fitbit_token = $fitbit_token;
            $json->fitbit_secret = $fitbit_secret;
            curl_close($ch);
            
            $cookie = file_get_contents(realpath($cookie_jar));
            $sessionKeys = substr($cookie,strlen($cookie)-22,22);
            $json->session_keys = explode("\t",   $sessionKeys);
            $json->unit_type = action('GET_WEIGHT_UNITS', $json); 
            return $json;
            break;
        case 'GET_WEIGHT_UNITS':
            $data = array(
                'userid' => $json->bbuser_id,
                'needsuser' => getVar('needsuser'),
                $json->session_keys[0] => trim($json->session_keys[1])
            );  
            $data_string = json_encode($data); 
            $url  = "http://api.bodybuilding.com/api-proxy/stats/get?needsuser=1&userid=".$json->bbuser_id;
            $ch = curl_init($url);                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, realpath($json->cookie_jar));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data_string))                                                                       
            );                                                                                                                   

            $response = json_decode(curl_exec($ch));
            curl_close($ch);
            return $response->data->format;
            break;
        case 'SET_VALUE':
            $data = array(
                'userid' => $json->bbuser_id,
                'stat' => getVar('stat'),
                'value' => getVar('value'),
                'date' => getVar('date'),
                'unit' => getVar('unit'),
                'shareonfb' => getVar('shareonfb'),
                'caption' => getVar('caption'),
                'needsuser' => getVar('needsuser'),
                $json->session_keys[0] => trim($json->session_keys[1]),
                'lng' => '1',
                'rememberme' => 'true'
            );                               
            $data_string = json_encode($data);    

            $url  = "http://api.bodybuilding.com/api-proxy/stats/set?";
            $url .= "userid=".$json->bbuser_id."&";
            $url .= "stat=".getVar('stat')."&";
            $url .= "value=".getVar('value')."&";
            $url .= "date=".getVar('date')."&";
            $url .= "unit=".getVar('unit')."&";
            $url .= "shareonfb=".getVar('shareonfb')."&";
            $url .= "caption=".getVar('caption')."&";
            $url .= "needsuser=".getVar('needsuser')."&";

            $ch = curl_init($url);                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, realpath($json->cookie_jar));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($data_string))                                                                       
            );                                                                                                                   

            curl_exec($ch);
            curl_close($ch);
            break;
    }
}  action($action, $json);

function getVar($name){
    $found = '';
    if (isset($_POST[$name])){
        if ($name != 'json')
            $found = $_POST[$name];
        else
            $found = mysql_real_escape_string($_POST[$name]);
    } else if (isset($_GET[$name])){
        if ($name != 'json')
            $found = $_GET[$name];
        else
            $found = mysql_real_escape_string($_GET[$name]);
    }
    return $found;
}

function cleanVars(){
    foreach ($_POST as $key => &$post) {
        if ($key != 'json')
            $post = mysql_real_escape_string($post);
    }
    foreach ($_GET as &$get) {
        if ($key != 'json')
            $get = mysql_real_escape_string($get);
    }
}

function logMsg($msg=''){
//    $sql  = "INSERT INTO logs SET ";
//    $sql .= "ip = '" .  getUserIP() . "',";
//    $sql .= "referrer = '" . $_SERVER['HTTP_REFERER'] . "', ";
//    $sql .= "msg = '" . $msg . "';";
//    echo $sql;
//    mysql_query($sql);
}