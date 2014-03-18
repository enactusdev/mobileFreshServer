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
    if( strcasecmp($_GET['method'],'getuserinfo') == 0){
        $sql = "SELECT * from userinfo ";   
        $result = mysql_query($sql) or die(mysql_error());

        $usernameArry = array();
        while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $username = $selector1;
            $usernameArry[] = $username;
        }
        $response['status'] = $api_response_code[ $response['code'] ]['Message'];
        $response['data'] = $usernameArry; 
    
    }


//Insert Food Details
    if( strcasecmp($_GET['method'],'foodDetails') == 0){

        $foodtype=$_GET['foodtype'];
        $time=$_GET['time'];
        $geocode=$_GET['geocode'];
        $status=$_GET['status'];
    

        $sql="INSERT INTO foodinfo (foodtype,time,geocode,status) VALUES ('$foodtype','$time','$geocode','$status')";
        $result = mysql_query($sql) or die(mysql_error());
        $response['message'] = $api_response_code[1]['Message'];
        
    }


//Get NodeList
    if( strcasecmp($_GET['method'],'getnodeList') == 0)
    {

        if(strcasecmp($_GET['usertype'],'admin')==0)
        {
            $sql = "SELECT * from foodinfo where status LIKE '%wating%'";   
            $result = mysql_query($sql) or die(mysql_error());

            $foodArry = array();
            while($selector1 = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $foodArry['NodeLocation'] = $selector1['geocode'];
                $foodArry['time'] = $selector1['time'];
                $foodArry['foodtype'] = $selector1['foodtype'];
            }
        
            $response['message'] ='success';
            $response['data'] = $foodArry; 
        }
        if(strcasecmp($_GET['usertype'],'admin')!=0)
        {
        
            $response['message'] ='cannot display';
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

        $sql="INSERT INTO userinfo (username,email,password,organizationname,usertype) VALUES ('$username','$email','$password','$organizationname','$usertype')";
        $result = mysql_query($sql) or die(mysql_error());
        $response['message'] = $api_response_code[1]['Message'];
        
    }


//SignIn
    if( strcasecmp($_GET['method'], 'signin')==0)
    {
        open();
        $email=$_GET['email'];
        $password=$_GET['password'];
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
            $response['message'] = "admin signed in"; 
            $response['token'] = $logintoken;
            clean($max);
        }
        if((strcasecmp($email, $usrinfo['email'])==0) && (strcasecmp($password, $usrinfo['password'])==0) && (strcasecmp($usrinfo['usertype'],'user')==0))
        {   
            $logintoken=read($email);
            $response['message'] = "user signed in";
            $response['token'] = $logintoken;
            clean($max);
        }
        if((strcasecmp($email, $usrinfo['email'])!=0) || (strcasecmp($password, $usrinfo['password'])!=0))
        {      
            $response['message'] = $api_response_code[ 4 ]['Message'];
        
        }

    }



//Update Food Status
    if( strcasecmp($_GET['method'], 'updatefoodStatus')==0)
    {
        if(strcasecmp($_GET['usertype'],'admin')==0)
        {
            $status=$_GET['status'];
            $foodtyp=$_GET['foodtype'];
            $sql="UPDATE foodinfo set status= '$status' where foodtype= '$foodtyp'";
            $result = mysql_query($sql) or die(mysql_error());
            $response['message'] = $api_response_code[1]['Message'];

        }
    }      





// --- Step 4: Deliver Response
 
// Return Response to browser
deliver_response($_GET['format'], $response);
 
?>
            