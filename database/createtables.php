<?php

$path=$_SERVER['DOCUMENT_ROOT'];
require_once $path."/attendanceapp/database/database.php";

function clearTable($dbo,$tabName)
{
    $c="delete from :tabname";
    $s=$dbo->conn->prepare($c);
    try{
        $s->execute([":tabname"=>$tabName]);
    }
    catch(PDOException $oo)
    {

    }
}

$dbo=new Database();

$c="create table student_details
(
    id int auto_increment primary key,
    roll_no varchar(20) unique,
    name varchar(50)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>student_details created");
}
catch(PDOException $o)
{
    echo("<br>student_details not created");
}

$c="create table course_details
(
    id int auto_increment primary key,
    code varchar(20) unique,
    title varchar(50),
    credit int
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>course_details created");
}
catch(PDOException $o)
{
    echo("<br>course_details not created");
}

$c="create table faculty_details
(
    id int auto_increment primary key,
    user_name varchar(20) unique,
    name varchar(100),
    password varchar(50)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>faculty_details created");
}
catch(PDOException $o)
{
    echo("<br>faculty_details not created");
}

$c="create table session_details
(
    id int auto_increment primary key,
    year int,
    term varchar(50),
    unique (year,term)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>session_details created");
}
catch(PDOException $o)
{
    echo("<br>session_details not created");
}

$c="create table course_registration
(
    student_id int,
    course_id int,
    session_id int,
    primary key (student_id,course_id,session_id)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>course_registration created");
}
catch(PDOException $o)
{
    echo("<br>course_registration not created");
}

$c="create table course_allotment
(
    faculty_id int,
    course_id int,
    session_id int,
    primary key (faculty_id,course_id,session_id)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>course_allotment created");
}
catch(PDOException $o)
{
    echo("<br>course_allotment not created");
}

$c="create table attendance_details
(
    faculty_id int,
    course_id int,
    session_id int,
    student_id int,
    on_date date,
    status varchar(10),
    primary key (faculty_id,course_id,session_id,student_id,on_date)
)";
$s=$dbo->conn->prepare($c);
try{
    $s->execute();
    echo("<br>attendance_details created");
}
catch(PDOException $o)
{
    echo("<br>attendance_details not created");
}

?>
