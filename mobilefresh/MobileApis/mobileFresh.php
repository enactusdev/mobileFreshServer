<?php


// --- Step 1: Initialize variables and functions
 
/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
include ("../dbconectar.php");
//session_set_save_handler('_open',
//                         '_close',
//                         '_read',
//                         '_write',
//                         '_destroy',
//                         '_clean');
session_start();

function open()
{
    global $_sess_db;
 
    if ($_sess_db = conectar()) {
        return mysql_select_db('sessions', $_sess_db);
    }
 
    return FALSE;
}
 
function close()
{
    global $_sess_db;
 
    return mysql_close($_sess_db);
}

function read($email)
{
    global $_sess_db;
 
    $email = mysql_real_escape_string($email);
 
    $sql = "SELECT data
            FROM   sessions
            WHERE  email = '$email'";
 
    if ($result = mysql_query($sql, $_sess_db)) {
        if (mysql_num_rows($result)) {
            $record = mysql_fetch_assoc($result);
 
            return $record['data'];
        }
    }
 
    return '';
}

function write($email, $token)
{
    global $_sess_db;
 
    $access = time();
 
    $email = mysql_real_escape_string($email);
    $access = mysql_real_escape_string($access);
    $token = mysql_real_escape_string($token);
 
    $sql = "REPLACE
            INTO    sessions(email,access,data)
            VALUES  ('$email', '$access', '$token')";
 
    return mysql_query($sql, $_sess_db);

}

function destroy($email)
{
    global $_sess_db;
 
    $email = mysql_real_escape_string($email);
 
    $sql = "DELETE
            FROM   sessions
            WHERE  email = '$email'";
 
    return mysql_query($sql, $_sess_db);
}

function clean($max)
{
    global $_sess_db;
 
    $old = $max;
    $old = mysql_real_escape_string($old);
 
    $sql = "DELETE
            FROM   sessions
            WHERE  access < '$old'";
 
    return mysql_query($sql, $_sess_db);
}

$konectar = conectar();

function deliver_response($format, $api_response){
 
    // Define HTTP responses
    $http_response_code = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found'
    );
 
    // Set HTTP Response
    header('HTTP/1.1 '.$api_response['message']);
    // Process different content types
    if( strcasecmp($format,'json') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');
 
        // Format data into a JSON response
        $json_response = json_encode($api_response);
 
        // Deliver formatted data
        echo $json_response;
 
    }elseif( strcasecmp($format,'xml') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');
 
        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<response>'."\n".
            "\t".'<code>'.$api_response['code'].'</code>'."\n".
            "\t".'<data>'.$api_response['data'].'</data>'."\n".
            '</response>';
 
        // Deliver formatted data
        echo $xml_response;
 
    }else{
 
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        echo $api_response['data'];
 
    }
 
    // End script process
    exit;
 
}

 
// Define API response codes and their related HTTP response
$api_response_code = array(
    0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
    1 => array('HTTP Response' => 200, 'Message' => 'Success'),
    2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
    3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
    4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
    5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
    6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
);
 
// Set default HTTP response of 'ok'
//$response['code'] = 0;
//$response['status'] = 404;
$response['message'] = NULL;
 

 

//Get User Information
function getuser($email)
    //if( strcasecmp($_GET['method'],'getuserinfo') == 0)
    {
        $sql = "SELECT * from userinfo where email='$email' ";   
        $result = mysql_query($sql) or die(mysql_error());

        $usernameArry = array();
        while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $username = $selector1;
            $usernameArry['username'] = $selector1['username'];
        }
        //$response['status'] = $api_response_code[ $response['code'] ]['Message'];
        return $usernameArry; 
    
    }


//Insert Food Details
    if( strcasecmp($_GET['method'],'foodDetails') == 0){

        $foodtype=$_GET['foodtype'];
        $time=$_GET['time'];
        $geocode=$_GET['geocode'];
        $status=$_GET['status'];
        $address=$_GET['address'];
        $addressDictionary=$_GET['addressDictionary'];
        $sql="INSERT INTO foodinfo (foodtype,time,geocode,status,address,addressDictionary) VALUES ('$foodtype','$time','$geocode','$status','$address','$addressDictionary')";
        $result = mysql_query($sql) or die(mysql_error());
        $response['message'] = $api_response_code[1]['Message'];
        
    }


//Get NodeList
    if( strcasecmp($_GET['method'],'getnodeList') == 0)
    {
        $foodArry = array();

        if(strcasecmp($_GET['usertype'],'admin')==0)
        {

            $sql = "SELECT * from foodinfo where status='wating' ";   
            
            $result = mysql_query($sql) or die(mysql_error());

            
            while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC)) {

                 $foodArry[] = $selector1;
       
            }
         
      
            for($i=0;$i<sizeof($foodArry)-1;$i++)
            {
                $foodAry[$i]['NodeId']=$foodArry[$i]['id'];
                $foodAry[$i]['title']= $foodArry[$i]['address'];
                $foodArry['NodeLocation']= explode(",",$foodArry[$i]['geocode']);
                $foodAry[$i]['NodeLocation']['Latitude']= $foodArry['NodeLocation'][0];
                $foodAry[$i]['NodeLocation']['Longitude']= $foodArry['NodeLocation'][1];
                $foodAry[$i]['time'] = $foodArry[$i]['time'];
                $foodAry[$i]['foodtype'] = $foodArry[$i]['foodtype'];
                $foodAry[$i]['addressDictionary'] = $foodArry[$i]['addressDictionary'];
            }
            
            if(sizeof($foodArry)==1)
            {
               
                    $foodAry[0]['NodeId']=$foodArry[0]['id'];
                    $foodAry[0]['title']= $foodArry[0]['address'];
                    $foodArry['NodeLocation']= explode(",",$foodArry[0]['geocode']);
                    $foodAry[0]['NodeLocation']['Latitude']= $foodArry['NodeLocation'][0];
                    $foodAry[0]['NodeLocation']['Longitude']= $foodArry['NodeLocation'][1];
                    $foodAry[0]['time'] = $foodArry[0]['time'];
                    $foodAry[0]['foodtype'] = $foodArry[0]['foodtype'];
                    $foodAry[0]['addressDictionary'] = $foodArry[0]['addressDictionary'];
                 
            }
            
        
            $response['message'] ='success';
            $response['data'] = $foodAry; 
        }
        if(strcasecmp($_GET['usertype'],'admin')!=0)
        {
            $usertype=$_GET['usertype'];
            $response['message'] ="cannot display for $usertype";
        }
    }


//SignUp
    if( strcasecmp($_GET['method'],'signup') == 0)
    {

        $username=$_GET['username'];
        $email=$_GET['email'];
        $password=$_GET['password'];
        $organizationname=$_GET['organizationname'];
        $usertype=$_GET['usertype'];
        $sql="SELECT * FROM userinfo";
          $result = mysql_query($sql) or die(mysql_error());
        while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC))
        {
                     $emailArry[] = $selector1['email'];
        }
        if (in_array($email, $emailArry))
        {
            $response['message'] ="Email Exist";
        }
        else{

        if(strcasecmp($usertype, 'admin')==0)
        {
            $pin=$_GET['pin'];
            $sql="SELECT pin FROM pinTable";
            $result = mysql_query($sql) or die(mysql_error());
            while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC))
            {
                     $pinArry[] = $selector1['pin'];
            }
            if (in_array($pin, $pinArry))
            {
                $sql="INSERT INTO userinfo (username,email,password,organizationname,usertype,pin) VALUES ('$username','$email','$password','$organizationname','$usertype','$pin')";
                $result = mysql_query($sql) or die(mysql_error());
                $response['message'] = $api_response_code[1]['Message'];
            }
            else
            {
                $response['message'] = "$pin not found";
            
            }
        }
        if(strcasecmp($usertype, 'donator')==0)
        {
            $sql="INSERT INTO userinfo (username,email,password,organizationname,usertype) VALUES ('$username','$email','$password','$organizationname','$usertype')";
            $result = mysql_query($sql) or die(mysql_error());
            $response['message'] = $api_response_code[1]['Message'];
        }
    }
    }


//SignIn
    if( strcasecmp($_GET['method'], 'signin')==0)
    {
        open();
        $email=$_GET['email'];
        $password=$_GET['password'];
        $pin=$_GET['pin'];
        $token=(rand(1000000000,9999999999).$email);
        $max=date("d/m/y")+7;
        write($email, $token);
        $sql="SELECT * FROM userinfo WHERE email LIKE '%$email%'";
        $result = mysql_query($sql) or die(mysql_error());
        while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $usrinfo = $selector1;
        }
        if((strcasecmp($email, $usrinfo['email'])==0) && (strcasecmp($password, $usrinfo['password'])==0) && (strcasecmp($usrinfo['usertype'],'admin')==0))
        {
            $logintoken=read($email);
           $da=getuser($email);
           $response['status'] = 'Success';
            $response['message'] = "admin signed in"; 
            $response['token'] = $logintoken;
            $response['username'] = $da['username']; 
            clean($max);
        }
        if((strcasecmp($email, $usrinfo['email'])==0) && (strcasecmp($password, $usrinfo['password'])==0) && (strcasecmp($usrinfo['usertype'],'donator')==0))
        {   
            $logintoken=read($email);
            $da=getuser($email);
           $response['status'] = 'Success';
            $response['message'] = "donater signed in";
            $response['token'] = $logintoken;
            $response['username'] = $da['username']; 
            clean($max);
        }
        if((strcasecmp($email, $usrinfo['email'])!=0) || (strcasecmp($password, $usrinfo['password'])!=0))
        {      
            $response['message'] = $api_response_code[ 4 ]['Message'];
        
        }

    }



//Update Food Status
    if( strcasecmp($_GET['method'], 'foodStatus')==0)
    {
        if(strcasecmp($_GET['usertype'],'admin')==0)
        {
            $status=$_GET['status'];
            $Nodeid=$_GET['nodeid'];
            $sql="UPDATE foodinfo set status='$status' where id='$Nodeid'";
            $result = mysql_query($sql) or die(mysql_error());
            
            $response['message'] = "Success";
        }
        $response['message'] = 'Success';
    }   


//wellcome
if( strcasecmp($_GET['method'], 'wellcome')==0)
    {
        
        $response['message'] ='hi good morning';
    }      



// --- Step 4: Deliver Response
 
// Return Response to browser
deliver_response($_GET['format'], $response);
 
?>
            