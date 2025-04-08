<?php
$path=$_SERVER['DOCUMENT_ROOT'];
require_once $path."/schoolpro/database/database.php";

class attendanceDetails
{
     public function saveAttendance($dbo,$session,$course,$fac,$student,$ondate,$status)
     {
        $rv=[-1];
        $c="insert into attendance_details
        (session_id,course_id,faculty_id,student_id,on_date,status)
        values
        (:session_id,:course_id,:faculty_id,:student_id,:on_date,:status)";
        $s=$dbo->conn->prepare($c);
        try{
              $s->execute([":session_id"=>$session,":course_id"=>$course,":faculty_id"=>$fac,":student_id"=>$student,":on_date"=>$ondate,":status"=>$status]);
              $s->execute();
              $rv=[1];
        }
        catch(Exception $e)
        {
             //$rv=[$e->getMessage()];
             //it might happen that the entry is there, we just have to set reset the status
             $c="update attendance_details set status=:status
                where 
                session_id=:session_id and course_id=:course_id and faculty_id=:faculty_id
                and student_id=:student_id and on_date=:on_date";
                $s=$dbo->conn->prepare($c);
                try{
                    $s->execute([":session_id"=>$session,":course_id"=>$course,":faculty_id"=>$fac,":student_id"=>$student,":on_date"=>$ondate,":status"=>$status]);
                    $s->execute();
                    $rv=[1];
                }
                catch(Exception $ee)
                {
                    $rv=[$e->getMessage()];
                }

        }
        return $rv;
     }
     public function getPresentListOfAClassByAFacOnADate($dbo,$session,$course,$fac,$ondate)
     {
        $rv=[];
        $c="select student_id from attendance_details 
        where session_id=:session_id and course_id=:course_id
        and faculty_id=:faculty_id and on_date=:on_date
        and status='YES'";
        $s=$dbo->conn->prepare($c);
        try
        {
            $s->execute([":session_id"=>$session,":course_id"=>$course,":faculty_id"=>$fac,":on_date"=>$ondate]);
            $rv=$s->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $e)
        {

        }
        return $rv;
     }

     public function getAttenDanceReport($dbo,$session,$course,$fac)
     {
        $report=[];
        $sessionName='';
        $facname='';
        $courseName='';
        //get session fac and course names
        $c="select * from session_details where id=:id";
        $s=$dbo->conn->prepare($c);
        try{
         $s->execute([":id"=>$session]);
         $sd=$s->fetchAll(PDO::FETCH_ASSOC)[0];
         $sessionName=$sd['year']." ".$sd['term'];
        }
        catch(Exception $e)
        {

        }

        $c="select * from faculty_details where id=:id";
        $s=$dbo->conn->prepare($c);
        try{
         $s->execute([":id"=>$fac]);
         $sd=$s->fetchAll(PDO::FETCH_ASSOC)[0];
         $facname=$sd['name'];
        }
        catch(Exception $e)
        {

        }


        $c="select * from course_details where id=:id";
        $s=$dbo->conn->prepare($c);
        try{
         $s->execute([":id"=>$course]);
         $sd=$s->fetchAll(PDO::FETCH_ASSOC)[0];
         $courseName=$sd['code']."-".$sd['title'];
        }
        catch(Exception $e)
        {

        }

        array_push($report,["Session:",$sessionName]);
        array_push($report,["Course:",$courseName]);
        array_push($report,["Faculty:",$facname]);

        //first get the total number of classses by the fac
        $total=0;
        $start='';
        $end='';
        $c="select distinct on_date from attendance_details where 
        session_id=:session_id and course_id=:course_id and faculty_id=:faculty_id
        order by on_date";
        $s=$dbo->conn->prepare($c);
        try{
          $s->execute([":session_id"=>$session,":course_id"=>$course,":faculty_id"=>$fac]);
          $rv=$s->fetchAll(PDO::FETCH_ASSOC);
          $total=count($rv);
          if($total>0)
          {
            $start=$rv[0]['on_date'];
            $end=$rv[$total-1]['on_date'];
          }

        }
        catch(Exception $ee)
        {

        }
        
        array_push($report,["total",$total]);
        array_push($report,["start",$start]);
        array_push($report,["end",$end]);
        //get the number of attended classes for each registered student
        /*
        
        */
        $rv=[];
        $c="select rsd.id,rsd.roll_no,rsd.name,count(ad.on_date) as attended from 
        (
           select sd.id,sd.roll_no,sd.name,crd.session_id,
           crd.course_id from student_details as sd,course_registration as crd
           where sd.id=crd.student_id and crd.session_id=:session_id and 
           crd.course_id=:course_id
        ) as rsd left join attendance_details as ad 
        on rsd.id=ad.student_id AND
        rsd.session_id=ad.session_id and 
        rsd.course_id =ad.course_id
        and status='YES'
        and 
        ad.faculty_id=:faculty_id
        group by rsd.id;";
        $s=$dbo->conn->prepare($c);
        try{
          $s->execute([":session_id"=>$session,":course_id"=>$course,":faculty_id"=>$fac]);
          $rv=$s->fetchAll(PDO::FETCH_ASSOC);
          
        }
        catch(Exception $ee)
        {

        }
         //compute the precent
        for($i=0;$i<count($rv);$i++)
        {
         $rv[$i]['percent']=0.00;
         if($total>0)
         {
            $rv[$i]['percent']=round($rv[$i]['attended']/$total*100.0,2);
         }
        }
        array_push($report,["slno","rollno","name","attended","percent"]);
        $report=array_merge($report,$rv);
        

        //return the result
        return $report;
     }
}
?>