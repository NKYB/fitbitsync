<?php
require_once 'bootstrap.php';
require_once 'fitbitphp.php';
$fitbit = new FitBitPHP(FITBIT_KEY, FITBIT_SECRET);
$fitbit->setMetric(0);

$status = 0;
if (isset($_GET['status'])){
    $status = intval($_GET[status]);
} else if ($fitbit->sessionStatus() == 2){
    $status = 2;
} else if ($fitbit->sessionStatus() == 1){
    $status = 3;
}

?>
<!--
// *****************************************************************************
// *
// *
// *          THIS IS THE SOURCE CODE, PLEASE DON'T HACK THIS SITE :)
// *
// *
// *****************************************************************************
-->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sync FITBIT Weight to BodyBuilding.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.1.4/semantic.min.css">
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.1.4/semantic.min.js"></script>
    
    <script>
        if(typeof(console) === 'undefined') {
            var console = {}
            console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = function() {};
        }
        $(document).ready(function(){
            $('#bbSubmit').click(function(){
                var bbuser = $('#bbuser').val();
                var bbpass = $('#bbpass').val();
                var encoded_id = $('#encoded_id').val();
                $.ajax({
                    method: "POST",
                    url: "bb.php",
                    data: {action:'LOGIN_WEB',bbuser:bbuser ,bbpass:bbpass,encoded_id:encoded_id},
                    success: function(response){
                        if (response == 'Login Success'){
                            location.href='index.php?status=5'
                        } else {
                            $('#bbLoginError').text('Your login credentials could not login to bodybuilding.com, please try again.');
                            // error msg for login
                        }
                        console.log(response);
                    }
                });
            });
        });
    </script>
    <style>
        .bb_container{
            font:14px "ProximaNovaRegular",Arial,Helvetica,sans-serif;
            color: #626161;
            background: #ebebeb url(page_background.jpg) repeat-x;
            margin: 0;
        }
    </style>
</head>
<?php 
//print_r($status);
if($status == 4){   
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
    $xml = $fitbit->getProfile();
    if($xml->user->encodedId){
?>
    <body class="bb_container">
        <div style="background-image:url(bbauth.jpg);position: absolute;top:200px;left:35%;height:567px;width:452px;"></div>
        <div style="position: absolute;top:410px;left:35%;height:567px;width:400px;padding:40px;padding-left:80px;">
            <p>Please enter your bodybuilder.com account</p>     
            <div id="bbLoginError" ></div>
            <div class="ui one column stackable center aligned page grid">

                <div class="column">
                <div class="ui form">
                  <div class="field">
                    <label style=""></label>
                    <input type="text" id="bbuser"  name="bbuser" placeholder="Username">
                  </div>
                  <div class="field">
                    <label style=""></label>
                    <input id="bbpass" name="bbpass" placeholder="Password" type="password">
                  </div>
                  <input type="hidden" name="encoded_id" id="encoded_id" value="<?php echo $xml->user->encodedId; ?>" />
                  <button id="bbSubmit" class="ui button" type="submit">Submit</button>
                </div>
                </div>
            </div>
        </div>
<?php 
    } else {
        header('Location: index.php');
    }
} else {
?>
<body style="background-color: #1b1c1d;">
<div class="container">
  <div class="ui inverted vertical masthead center aligned segment" style="padding:200px;">
  <h1>Synchronize your FITBIT data with your BODYBUILDING.com account.</h1><br><br>
<?php 
//print_r($status);
if($status == 0){   
?>
      <button class="ui primary button" onclick="javascript:location.href='index.php?status=1';" style="font-size:24px;">GET Started</button>
      <br><br>
      (Takes less than 1 minute)
<?php 
} else if($status == 1){ 
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
} else if($status == 3){ 
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
    $encoded_id = $fitbit->getProfile()->user->encodedId;
    $token = $_SESSION['fitbit_Token'];
    $secret = $_SESSION['fitbit_Secret'];
    $sql = "INSERT INTO fitbit_tokens (encoded_id,token,secret) VALUES ('" . $encoded_id . "','" . $token . "','" . $secret . "') ON DUPLICATE KEY UPDATE token='" . $token . "', secret='" . $secret . "';";
//        var_dump($sql);
    mysql_query($sql);
    header('Location: index.php');
} else if($status == 2){    
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
    $xml = $fitbit->getProfile();

    // lookup sql 
    $sql = "SELECT * FROM fitbit_tokens WHERE encoded_id = '" . $xml->user->encodedId . "' LIMIT 1;";
    $result = mysql_query($sql);
    if($row = mysql_fetch_array($result)) {
        if ($row['bbuser'] == ''){
            header('Location: index.php?status=4');
        } else {
            header('Location: index.php?status=5');
        }
    }
} else if($status == 5){ 
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
    $xml = $fitbit->getProfile();
//    var_dump($fitbit->getTimeSeries('weight','today','1d'));
?>
    <input type="hidden" name="encoded_id" id="encoded_id" value="<?php echo $xml->user->encodedId; ?>" />

    <h3>All your accounts are hooked up.<br>Everything is ready to sync.<br>What are you waiting for!</h3>
    <br>
    <button class="ui primary button" onclick="javascript:location.href='index.php?status=6';" style="font-size:24px;">Sync Weight and % Body Fat</button>
    <div id="bbSetResult" ></div>
<?php
} else if($status == 6){ 
?>
<?php
    require_once 'bb.php';
    $fitbit->initSession('http://localhost/fitbitsync/index.php');
    $xml = $fitbit->getProfile();
    
    $_POST['encoded_id'] = $xml->user->encodedId;;
    $json = action('LOGIN');

    if ($json->cookie_jar != ""){


        $weights = $fitbit->getTimeSeries('weight','today','max');
        $bodyfats = $fitbit->getTimeSeries('fat','today','max');
//        var_dump($bodyfats);
        echo "<h3>Attempting to sync " . count($weights) . " records.</h3>";
        set_time_limit ( 6000 );
        foreach ($weights as $key => $weight){
            echo "<p>Updating:  " . $weight->dateTime . " to " . round($weight->value * 2.20462,1) . " lbs and % body fat to". round($bodyfats[$key]->value,1)."</p>";
            flush();
            $_POST['needsuser'] = '1';
            $_POST['userid'] = $json->bbuser_id;
            $_POST['stat'] = 'weight';
            $_POST['value'] = round($weight->value * 2.20462,1);
            $_POST['date'] = strtotime($weight->dateTime);
            $_POST['unit'] = 'imperial';
            $_POST['shareonfb'] = 'false';
            $_POST['caption'] = '';
            action('SET_VALUE',$json);
            
            $_POST['stat'] = 'bodyfat';
            $_POST['value'] = round($bodyfats[$key]->value,1);
            action('SET_VALUE',$json);
        }
        echo "<h3>Done</h3>";
    }
?>
    
    
        
<?php
} 
?>
  </div>
</div>
<?php
} 
?>

</body>
</html>
