<?php
//start session and connect
session_start();
include('connection.php');

//define error messages
$missingdeparture = '<p><strong>Please enter your departure!</strong></p>';
$invaliddeparture = '<p><strong>Please enter a valid departure!</strong></p>';
$missingdestination = '<p><strong>Please enter your destination!</strong></p>';
$invaliddestination = '<p><strong>Please enter a valid destination!</strong></p>';
$missingprice = '<p><strong>Please choose a price per seat!</strong></p>';
$invalidprice = '<p><strong>Please choose a valid price per seat using numbers only!!</strong></p>';
$missingseatsavailable = '<p><strong>Please select the number of available seats!</strong></p>';
$invalidseatsavailable = '<p><strong>The number of available seats should contain digits only!</strong></p>';
$missingfrequency = '<p><strong>Please select a frequency!</strong></p>';
$missingdays = '<p><strong>Please select at least one weekday!</strong></p>';
$missingdate = '<p><strong>Please choose a date for your trip!</strong></p>';
$missingtime = '<p><strong>Please choose a time for your trip!</strong></p>';

//Get inputs:
$trip_id = $_POST["trip_id"];
$departure = $_POST["departure2"];
$destination = $_POST["destination2"];
$price = $_POST["price2"];
$seatsavailable = $_POST["seatsavailable2"];
$regular = $_POST["regular2"];
$date = $_POST["date2"];
$time = $_POST["time2"];
$monday = $_POST["monday2"];
$tuesday = $_POST["tuesday2"];
$wednesday = $_POST["wednesday2"];
$thursday = $_POST["thursday2"];
$friday = $_POST["friday2"];
$saturday = $_POST["saturday2"];
$sunday = $_POST["sunday2"];

//check coordinates
if(!isset($_POST["departureLatitude"]) or !isset($_POST["departureLongitude"])){
    $errors .= $invaliddeparture;   
}else{
    $departureLatitude = $_POST["departureLatitude"];
    $departureLongitude = $_POST["departureLongitude"];
}

if(!isset($_POST["destinationLatitude"]) or !isset($_POST["destinationLongitude"])){
    $errors .= $invaliddestination;   
}else{
    $destinationLatitude = $_POST["destinationLatitude"];
    $destinationLongitude = $_POST["destinationLongitude"];
}


//Check departure:
if(!$departure){
    $errors .= $missingdeparture;   
}else{
    $departure = filter_var($departure, FILTER_SANITIZE_STRING); 
}

//Check destination:
if(!$destination){
    $errors .= $missingdestination;   
}else{
    $destination = filter_var($destination, FILTER_SANITIZE_STRING); 
}

//Check Price
if(!$price){
    $errors .= $missingprice; 
}elseif(preg_match('/\D/', $price)  // you can use ctype_digit($price)
){
        $errors .= $invalidprice;   
}else{
    $price = filter_var($price, FILTER_SANITIZE_STRING);    
}

//Check Seats Available
if(!$seatsavailable){
    $errors .= $missingseatsavailable; 
}elseif(preg_match('/\D/', $seatsavailable)  // you can use ctype_digit($seatsavailable)
){
        $errors .= $invalidseatsavailable;   
}else{
    $seatsavailable = filter_var($seatsavailable, FILTER_SANITIZE_STRING);    
}

//Check regular
if(!$regular){
    $errors .= $missingfrequency;    
}elseif($regular == "Y"){
    if(!$monday && !$tuesday && !$wednesday && !$thursday && !$friday && !$saturday && !$sunday ){
        $errors .= $missingdays; 
    }
    if(!$time){
        $errors .= $missingtime;   
    }
}elseif($regular == "N"){
    if(!$date){
        $errors.= $missingdate;   
    }
    if(!$time){
        $errors .= $missingtime;   
    }
}

//if there is an error print error message
if($errors){
    $resultMessage = "<div class='alert alert-danger'>$errors</div>";
    echo $resultMessage;
}else{
    //no errors, prepare variables for the query
    $tbl_name = 'carsharetrips';
    $departure = mysqli_real_escape_string($link, $departure);
    $destination = mysqli_real_escape_string($link, $destination);
    if($regular == "Y"){
        //query for a regular trip
        $sql = "UPDATE $tbl_name SET `departure`= '$departure',`departureLongitude`='$departureLongitude',`departureLatitude`='$departureLatitude', `destination`='$destination',`destinationLongitude`='$destinationLongitude',`destinationLatitude`='$destinationLatitude', `price`='$price', `seatsavailable`='$seatsavailable', `regular`='$regular', `monday`='$monday', `tuesday`='$tuesday', `wednesday`='$wednesday', `thursday`='$thursday', `friday`='$friday', `saturday`='$saturday', `sunday`='$sunday', `time`='$time' WHERE `trip_id`='$trip_id' LIMIT 1";
    }else{ 
        //query for a one off trip
        $sql = "UPDATE $tbl_name SET `departure`= '$departure',`departureLongitude`='$departureLongitude',`departureLatitude`='$departureLatitude', `destination`='$destination',`destinationLongitude`='$destinationLongitude',`destinationLatitude`='$destinationLatitude', `price`='$price', `seatsavailable`='$seatsavailable', `regular`='$regular', `date`='$date', `time`='$time'  WHERE `trip_id`='$trip_id'";    
    }
    $results = mysqli_query($link, $sql);
    //check if query is successful
    if(!$results){
        echo '<div class=" alert alert-danger">There was an error! The trip could not be updated!</div>';        
    }
}

?>