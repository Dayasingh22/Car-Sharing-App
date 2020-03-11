<?php
session_start();
include('connection.php');

$user_id = $_SESSION['user_id'];

function changeProfilePicture($id, $file, $ext, $con){
    $permanentdestination = 'profilepicture/' . md5(time()) . ".$ext";
    if(move_uploaded_file($file, $permanentdestination)){
        $sql = "UPDATE users SET profilepicture='$permanentdestination' WHERE user_id='$id'";
        $result = mysqli_query($con, $sql);
        if(!$result){
            $resultMessage = '<div class="alert alert-danger">Unable to update profile picture. Please try again later.</div>';
            echo $resultMessage;
        }
 }else{
    $resultMessage = '<div class="alert alert-warning"Unable to upload file. Please try again later.></div>'; 
     echo $resultMessage;
 }; 
}

//error messages to display
$noFiletoUpload = "<p><strong>Please upload a file!</strong></p>";
//$fileAlreadyExists = "<p><strong>This file already exists!</strong></p>";
$wrontFormat = "<p><strong>Sorry, you can only upload pdf and text files!</strong></p>";
$fileTooLarge = "<p><strong>You can only upload files smaller than 3Mo!</strong></p>";



//file details
$extension = pathinfo($name, PATHINFO_EXTENSION);
$name = $_FILES["picture"]["name"];
$type = $_FILES["picture"]["type"];
$size = $_FILES["picture"]["size"];
$fileerror = $_FILES["picture"]["error"];
$tmp_name = $_FILES["picture"]["tmp_name"];

//allowed formats to upload
$allowedFormats = array("jpeg"=>"image/jpeg", "jpg"=>"image/jpg", "png"=>"image/png");


//check for errors
if($fileerror == 4){
    $errors .=$noFiletoUpload;   
}else{
//    if(file_exists($permanentdestination)){
//        $errors .= $fileAlreadyExists;   
//    }
    if(!in_array($type, $allowedFormats)){
        $errors .= $wrontFormat;   
    }elseif($size > 3*1024*1024){
        $errors .= $fileTooLarge;   
    }  
}



if($errors){
    $resultMessage = '<div class="alert alert-danger">' . $errors .'</div>'; 
    echo $resultMessage;
}else{
    changeProfilePicture($user_id, $tmp_name, $extension, $link);
} 

//print_r($_FILES);
if($_FILES["picture"]["error"]>0){
    $errors = '<p>There was an error: '. $_FILES["picture"]["error"] .'</p>';
    $resultMessage = '<div class="alert alert-danger">' . $errors .'</div>'; 
    echo $resultMessage;
}else{
//    echo "<p>File: ".  $_FILES["picture"]["name"] ."</p>";   
//    echo "<p>File type: ".  $_FILES["picture"]["type"] ."</p>";   
//    echo "<p>Temporary location: ".  $_FILES["picture"]["tmp_name"] ."</p>";   
//    echo "<p>File size: ".  $_FILES["picture"]["size"] ."</p>";   
}


?>