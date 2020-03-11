<?php
//start session and connect to database
session_start();
include('connection.php');

//define error messages
$missingdeparture = '<p><strong>Please enter your departure!</strong></p>';
$invaliddeparture = '<p><strong>Please enter a valid departure!</strong></p>';
$missingdestination = '<p><strong>Please enter your destination!</strong></p>';
$invaliddestination = '<p><strong>Please enter a valid destination!</strong></p>';

//Get inputs:
$departure = $_POST["departure"];
$destination = $_POST["destination"];

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

//set search radius
$searchRadius = 10;

//min max Departure Longitude
$deltaLongitudeDeparture = $searchRadius*360/(24901*cos(deg2rad($departureLatitude)));
$minLongitudeDeparture = $departureLongitude - $deltaLongitudeDeparture;
if($minLongitudeDeparture < -180){
    $minLongitudeDeparture += 360;
}
$maxLongitudeDeparture = $departureLongitude + $deltaLongitudeDeparture;
if($maxLongitudeDeparture > 180){
    $maxLongitudeDeparture -= 360;
}

//min max Destination Longitude
$deltaLongitudeDestination = $searchRadius*360/(24901*cos(deg2rad($destinationLatitude)));
$minLongitudeDestination = $destinationLongitude - $deltaLongitudeDestination;
if($minLongitudeDestination < -180){
    $minLongitudeDestination += 360;
}
$maxLongitudeDestination = $destinationLongitude + $deltaLongitudeDestination;
if($maxLongitudeDestination > 180){
    $maxLongitudeDestination -= 360;
}

//min max Departure Latitude
$deltaLatitudeDeparture = $searchRadius*180/12430;
$minLatitudeDeparture = $departureLatitude - $deltaLatitudeDeparture;
if($minLatitudeDeparture < -90){
    $minLatitudeDeparture = -90;
}
$maxLatitudeDeparture = $departureLatitude + $deltaLatitudeDeparture;
if($maxLatitudeDeparture > 90){
    $maxLatitudeDeparture = 90;
}

//min max Destination Latitude
$deltaLatitudeDestination = $searchRadius*180/12430;
$minLatitudeDestination = $destinationLatitude - $deltaLatitudeDestination;
if($minLatitudeDestination < -90){
    $minLatitudeDestination = -90;
}
$maxLatitudeDestination = $destinationLatitude + $deltaLatitudeDestination;
if($maxLatitudeDestination > 90){
    $maxLatitudeDestination = 90;
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

//if there is an error print error message
if($errors){
    $resultMessage = '<div class=" alert alert-danger">' . $errors . '</div>';
    echo $resultMessage; exit;
}

//get all available trips in the carsharetrips table
$myArray = [$minLongitudeDeparture < $maxLongitudeDeparture, $minLatitudeDeparture < $maxLatitudeDeparture, $minLongitudeDestination < $maxLongitudeDestination, $minLatitudeDestination < $maxLatitudeDestination];

$queryChoice1 = [
    " (departureLongitude BETWEEN $minLongitudeDeparture AND $maxLongitudeDeparture)",
    " AND (departureLatitude BETWEEN $minLatitudeDeparture AND $maxLatitudeDeparture)",
    " AND (destinationLongitude BETWEEN $minLongitudeDestination AND $maxLongitudeDestination)",
    " AND (destinationLatitude BETWEEN $minLatitudeDestination AND $maxLatitudeDestination)"
];

$queryChoice2 = [
    " ((departureLongitude > $minLongitudeDeparture) OR (departureLongitude < $maxLongitudeDeparture))",
    " AND (departureLatitude BETWEEN $minLatitudeDeparture AND $maxLatitudeDeparture)",
    " AND ((destinationLongitude > $minLongitudeDestination) OR (destinationLongitude < $maxLongitudeDestination))",
    " AND (destinationLatitude BETWEEN $minLatitudeDestination AND $maxLatitudeDestination)"
];

$queryChoices = [$queryChoice2, $queryChoice1];

$sql = "SELECT * FROM carsharetrips WHERE ";

for ($value=0; $value<4; $value++) {
    $index = $myArray[$value];
    $sql .= $queryChoices[$index][$value];
}

$result = mysqli_query($link, $sql);
if(!$result){
    echo "ERROR: Unable to excecute: $sql. " . mysqli_error($link); exit;   
}

if(mysqli_num_rows($result) == 0){
    echo "<div class='alert alert-info noresults'>There are no journeys matching your search!</div>"; exit;
}

echo "<div class='alert alert-info journeysummary'>From $departure to $destination.<br />Closest Journeys:</div>";            
echo '<div id="message">'; 

//cycle through trips and find close ones

//retrieve each row in $result
while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
    
    //check if the trip date is in the past
    $dateOK = 1;
    if($row['regular']=="N"){
        $source = $row['date'];
        $tripDate = DateTime::createFromFormat('D d M, Y', $source);
        $today = date("D d M, Y");
        $todayDate = DateTime::createFromFormat('D d M, Y', $today);
        $dateOK = ($tripDate > $todayDate);
    }
    
    //if date is ok
    if($dateOK){
        //print trip
        
        //get trip user id
        $person_id = $row['user_id'];
        
        //run query to get user details
        $sql2="SELECT * FROM users WHERE user_id='$person_id' LIMIT 1";
        $result2 = mysqli_query($link, $sql2);
        
        if($result2){
            
            //get user details
            $row2 = mysqli_fetch_array($result2);
            
            //Get phone number:
            if($_SESSION['user_id']){
             $phonenumber = $row2['phonenumber'];   
            }else{
             $phonenumber = "Please sign up! Only members have access to contact information.";   
            }
            
            //get picture
            $picture = $row2['profilepicture'];
            //get firstname
            $firstname = $row2['first_name'];
            
            //get gender
            $gender = $row2['gender'];
            
            //more information
            $moreInformation = $row2['moreinformation'];
            
            //get trip departure
            $tripDeparture = $row['departure'];
            
            //get trip destination
            $tripDestination = $row['destination'];
            
            //get trip price
            $tripPrice = $row['price'];
            
            //get seats available in the trip
            $seatsAvailable = $row['seatsavailable'];
            
            //Get trip frequency and time:
            if($row['regular']=="N"){
                $frequency = "One-off journey.";
                $time = $row['date']." at " .$row['time'].".";
            }else{
                $frequency = "Regular.";
                $weekdays=['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat','sunday'=>'Sun'];
                $array = [];
                foreach($weekdays  as $key => $value){
                    if($row[$key]==1){
                        array_push($array,$value);
                    }
                    $time = implode("-", $array)." at " .$row['time'].".";
                }
            }
            
            //print trip
            echo 
             "<h4 class='row'>
                <div class='col-sm-2 journey'>
                    <div class='driver'>$firstname
                    </div>
                    <div>
                        <img class='previewing' src='$picture' />
                    </div>
                </div>

                <div class='col-sm-8 journey'>
                    <div>
                        <span class='departure'>Departure:
                        </span> 
                        $tripDeparture.
                    </div>
                    <div>
                        <span class='destination'>Destination:
                        </span> 
                        $tripDestination.
                    </div>
                    <div class='time'>
                        $time
                    </div>
                    <div>
                        $frequency
                    </div>
                </div>

                <div class='col-sm-2 price journey2'>
                    <div class='price'>
                        $$tripPrice
                    </div>

                    <div class='perseat'>
                        Per Seat
                    </div>
                    <div class='seatsavailable'>
                        $seatsAvailable left
                    </div>
                </div>
            </h4>";
            
            echo 
            "<div class='moreinfo'>
                <div>
                    <div>
                        Gender: $gender
                    </div>
                    <div class='telephone'>
                        &#9742: $phonenumber
                    </div>
                </div>
                <div class='aboutme'> 
                    About me: $moreInformation
                </div>
            </div>";
        }
    }
}
echo "</div>";


























