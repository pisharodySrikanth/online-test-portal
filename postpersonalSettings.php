<?php

require 'required.php';
include 'remove.php';
include 'updatetppr.php';

if(isset($_POST['save']))	{
	/*login*/
	$name = htmlentities(mysql_real_escape_string($_POST['name']));
	$dob = strtotime($_POST['dob']);
	$contact = $_POST['contact'];
	$email = htmlentities(mysql_real_escape_string($_POST['email']));
	$gender = $_POST['gender'];
	$security = $_POST['security'];
	$answer = htmlentities(mysql_real_escape_string($_POST['answer']));
	//student
	if(isset($_POST['college']))
		$college_id = $_POST['college'];
	else $college_id = 0;
	if(isset($_POST['course']))	{
		$course = $_POST['course'];
		$sem = $_POST['sem'];
	} else	$course = $sem = 0;
	if(isset($_POST['qualification']))
		$qualification = $_POST['qualification'];
	else $qualification = '';
	
	//updating the login table
	$query = "update `login` set `name` = '$name', `contactNo` = '$contact',
	`dob` = '$dob', `email` = '$email', `gender` = '$gender', `answer` = '$answer', `securityquestion` = '$security'
	where `L_id` = '$l_id'";
	if(!(mysql_query($query)))
		echo mysql_error();
	
	//taking the current course and sem
	$query = 'select Course_id, sem from student where L_id = '.$l_id;
	if($query_run = mysql_query($query))	{
		$c_num = mysql_num_rows($query_run);
		if($c_num == 1)	{
			$c_course = mysql_result($query_run, 0, 'Course_id');
			$c_sem = mysql_result($query_run, 0, 'sem');
		}
	} else echo mysql_error();
		
	//checking if the course and sem are unchanged
	if($c_num == 1 && ($c_course != $course || $c_sem != $sem))	{
		if($c_sem == 1 || $c_sem == 2)
			$c_course = 0;
		//taking the subjects of current course and sem
		$query = 'select Subj_id from subj_course_mapping where Course_id = '.$c_course.' and sem = '.$c_sem;
		if($query_run = mysql_query($query))	{
			if(mysql_num_rows($query_run))	{
				while($array = mysql_fetch_assoc($query_run))	{
					$subjid = $array['Subj_id'];
					/*deleting records from papers appeared table*/
					$queryd = 'delete pa from papersappeared pa 
								join paper p on p.Paper_id = pa.Paper_id 
								where pa.L_id = '.$l_id.' and p.Subj_id = '.$subjid;
					if(!(mysql_query($queryd)))
						die(mysql_error());

					/*deleting records from mcqs appeared table*/
					$querym = 'delete ma from mcqsappeared ma 
								join mcq m on m.Mcq_id = ma.Mcq_id
								join chapter c on c.Chp_id = m.Chp_id
								where ma.L_id = '.$l_id.' and c.Subj_id = '.$subjid;
					if(!(mysql_query($querym)))
						die(mysql_error());
					
					//deleting the toppers entry of the user
					delete_topper($subjid);
				}
			}
		} else echo mysql_error();
	}
	
	//updating the student table
	$query = "update `student` set `College_id` = '$college_id', `Course_id` = '$course', `sem` = '$sem', 
	`qualification` = '$qualification'
	where `L_id` = '$l_id'";
	if(!mysql_query($query))
		echo mysql_error();
	
	//checking if there subjects of the updated course in interests of the user and if yes then removing them from interests table
	if($sem == 1 || $sem == 2)
		$qcourse = 0;
	else $qcourse = $course;
	
	$query = 'delete i from interests i 
				join subj_course_mapping sc on i.Subj_id = sc.Subj_id
				where i.L_id = '.$l_id.' and sc.Course_id = '.$qcourse.' and sc.sem = '.$sem;
	if(!mysql_query($query))
		echo '1'.mysql_error();
	
	echo '<script type=text/javascript> window.location.href="personalSettings.php?message=2" </script>';
}


?>