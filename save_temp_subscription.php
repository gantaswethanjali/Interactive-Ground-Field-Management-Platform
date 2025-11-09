<?php
session_start();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $_SESSION['temp_subscription'] = [
        'email'=>$_POST['email'] ?? '',
        'ground'=>$_POST['ground'] ?? '',
        'plan'=>$_POST['plan'] ?? '',
        'duration'=>$_POST['duration'] ?? ''
    ];
}
?>
