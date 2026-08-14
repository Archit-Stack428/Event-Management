<?php 
include('dbconnect.php');

$eventname = trim($_GET['eventname'] ?? '');

$eventid = 0;
$minimum = 0;
$stmt1 = $conn->prepare("SELECT event_id, min_team FROM create_event WHERE event_title = ?");
$stmt1->bind_param('s', $eventname);
$stmt1->execute();
$res1 = $stmt1->get_result();
if ($row1 = $res1->fetch_assoc()) {
    $eventid = (int)$row1['event_id'];
    $minimum = (int)$row1['min_team'];
}
$stmt1->close();

$feedback = '';
$review = array();
$rating = array();

$stmt = $conn->prepare("SELECT name, feedback, stars FROM feedback WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $feedback .= '<h4>' . (int)$row['stars'] . ' <i class="fa fa-star" data-rating="2" style="font-size:20px;color:green;"></i> by <span style="font-size:14px;">' . htmlspecialchars($row['name']) . '</span></h4>
                <p>' . htmlspecialchars($row['feedback']) . '</p>
                <hr>';
    $review[] = $row['feedback'];
    $rating[] = (int)$row['stars'];
}
$stmt->close();

$average = $rating ? array_sum($rating) / count($rating) : 0;

if ($minimum == 0) {
// Fetch single-participant registrations (prepared statement)
$stmt = $conn->prepare("SELECT name, roll_no, dept_name, email, payment_status, paid_amount FROM singleevent_registration WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();




$event = ' <br>



       <div class="row">
                        <div class="col-lg-7 col-xl-8">
						<div class="form-group pull-right">
    <input type="text" class="search form-control" placeholder="What you looking for?">
</div>
<span class="counter pull-right"></span>
<table class="table table-hover table-bordered results">
                        <thead>
                            <tr>
                                  <th>S.no</th>
      <th class="col-md-5 col-xs-5">Name</th>
                                <th class="col-md-5 col-xs-5">Roll No</th>
                                <th class="col-md-5 col-xs-5">Email</th>
                                <th class="col-md-5 col-xs-5">Department</th>
                                <th class="col-md-5 col-xs-5">Payment Status</th>
                                <th class="col-md-5 col-xs-5">Amount Paid</th>
                            </tr> <tr class="warning no-result">
      <td colspan="4"><i class="fa fa-warning"></i> No result</td>
    </tr>
  </thead>
                        <tbody> ';

 
   
    
 
$i=1;

 while($row = mysqli_fetch_array($result)) {
     
     
     
     
    
    
        
         $event .=   '<tr>
         <td>'.$i++.'</td>
                <td>'.htmlspecialchars($row['name']).'</td>
                <td>'.htmlspecialchars($row['roll_no']).'</td>
                <td>'.htmlspecialchars($row['email']).'</td>
                <td>'.htmlspecialchars($row['dept_name']).'</td>
                <td>'.htmlspecialchars($row['payment_status']).'</td>
                <td>'.htmlspecialchars($row['paid_amount']).'</td>
            </tr>';
     
     
     $dept_name[] = $row['dept_name'];
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
 }
 
 $counts = array_count_values($dept_name);
  if(!$counts['Hotel Management']){
    $hotel= '0';
}
else{
    $hotel = $counts['Hotel Management'];
}

 if(!$counts['B.tech']){
    $btech= '0';
}
else{
   $btech = $counts['B.tech'];
}

if(!$counts['M.tech']){
    $mtech= '0';
}
else{
   $mtech = $counts['M.tech'];
}


if(!$counts['B.com']){
    $bcom= '0';
}
else{
    $bcom =  $counts['B.com'];
} 



if(!$counts['BDS']){
    $bds= '0';
}
else{
   $bds = $counts['BDS'];
} 
if(!$counts['MDS']){
    $mds= '0';
}
else{
    $mds =  $counts['MDS'];
}


if(!$counts['Hotel Management']){
    $hotel= '0';
}
else{
    $hotel = $counts['Hotel Management'];
}
if(!$counts['MBA']){
    $mba= '0';
}

else{
    $mba = $counts['MBA'];
}


if(!$counts['M.sc']){
    $msc= '0';
}
else{
   $msc= $counts['M.sc'];
}

if(!$counts['B.sc']){
    $bsc= '0';
}
else{
   $bsc = $counts['B.sc'];
}

if(!$counts['LLB']){
   $llb= '0';
}
else{
    $llb = $counts['LLB'];
} 

if(! $counts['BBA']){
     $bba= '0';
}
else{
     $bba = $counts['BBA'];
}


if(!$counts['MBBS']){
      $mbbs = '0';
}
else{
      $mbbs = $counts['MBBS'];
}
 
 

 
 
 
 
 
 



 $event .= '     
                        </tbody>
                   </table></div>
                   
                   <div id="piechart"></div>
                   <br>
                   
                   
<div class="col-md-4 " style="margin-left:66%; margin-top:20px;">
	<h3><b><u>Rating & Reviews</u></b></h3>
	<div class="row">
	
		<div class="col-md-6">
			<h3 align="center"><b>'.$average.'</b> <i class="fa fa-star" data-rating="2" style="font-size:20px;color:#ff9f00;"></i></h3>
			<p>'.count($rating).' ratings and '.count($review).'reviews</p>
		</div>
		
	</div>
	<div class="row">
		<div class="col-md-12">'.$feedback.'</div>
	</div>
		
	
	
				
</div>

                   
                   
                   
                   
                   
                   
                   
                   
                   
                   
                   
                   
                        
                </div>';
                
                
                
                
                
                
                
                
                
                
    	$script = "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    	
    	<script type='text/javascript'>
// Load google charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

// Draw the chart and set the chart values
function drawChart() {
  var data = google.visualization.arrayToDataTable([
  
  
  ['Task', 'Hours per Day'],
  ['hotel Management', $hotel],
  ['B.tech', $btech],
  ['B.sc', $bsc],
  
  ['B.com', $bcom],
  ['MBBS', $mbbs],
  ['MBA', $mba],
  ['M.sc', $msc],
  ['BBA', $bba],
  ['LLB', $llb],
  ['BDS', $bds],
  ['MDS', $mds],
  ['M.tec', $mtech]
  
  
  
  
 
  
  
  
  
  
  
  
  
  
  
  
  
  
]);

  // Optional; add a title and set the width and height of the chart
  var options = {'title':'Students According to Department', 'width':400, 'height':300};

  // Display the chart inside the <div> element with id='piechart'
  var chart = new google.visualization.PieChart(document.getElementById('piechart'));
  chart.draw(data, options);
}
</script>";
    	

 
 
echo $script;
 echo $event;

 
}

else{
  
// Fetch team registrations (prepared statement)
$stmt = $conn->prepare("SELECT team_name, college_name, Student_name, emails, mobile_no, paid_amount, payment_status FROM teamevent_registration WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$event = ' <br>







       <div class="row">
                        <div class="col-lg-7 col-xl-8">
						<div class="form-group pull-right">
    <input type="text" class="search form-control" placeholder="What you looking for?">
</div>
<span class="counter pull-right"></span>
<table class="table table-hover table-bordered results">
                        <thead>
                            <tr>
                                  <th>S.no</th>
      <th class="col-md-5 col-xs-5">Team Name</th>
                                <th class="col-md-5 col-xs-5">College_name</th>
                                <th class="col-md-5 col-xs-5">Student Name</th>
                                <th class="col-md-5 col-xs-5">Email</th>
                                <th class="col-md-5 col-xs-5">Mobile_no</th>
                                <th class="col-md-5 col-xs-5">Amount Paid</th>
                                <th class="col-md-5 col-xs-5">Payment Status</th>
                            </tr> <tr class="warning no-result">
      <td colspan="4"><i class="fa fa-warning"></i> No result</td>
    </tr>
  </thead>
                        <tbody> ';

 
   
    
 
$i=1;

 while($row = mysqli_fetch_array($result)) {
     
     
     
     
    
    
        
         $event .=   '<tr>
         <td>'.$i++.'</td>
                <td>'.htmlspecialchars($row['team_name']).'</td>
                <td>'.htmlspecialchars($row['college_name']).'</td>
                <td>'.htmlspecialchars($row['student_name']).'</td>
                <td>'.htmlspecialchars($row['emails']).'</td>
                <td>'.htmlspecialchars($row['mobile_no']).'</td>
                <td>'.htmlspecialchars($row['paid_amount']).'</td>
                <td>'.htmlspecialchars($row['payment_status']).'</td>
            </tr>';
     
     
    
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
 }
 

 
 
 
 
 
 



 $event .= '    
                        </tbody>
                   </table></div>
                   <br>
                      
<div class="col-md-4 ">
	<h3><b><u>Rating & Reviews</u></b></h3>
	<div class="row">
	
		<div class="col-md-6">
			<h3 align="center"><b>'.$average.'</b> <i class="fa fa-star" data-rating="2" style="font-size:20px;color:#ff9f00;"></i></h3>
			<p>'.count($rating).' ratings and '.count($review).'reviews</p>
		</div>
		
	</div>
	<div class="row">
		<div class="col-md-12">'.$feedback.'</div>
	</div>
		
	
	
				
</div>

                  
                   
                   
                        
                </div>';
                
                
                
                
                
                
                
                
                
                
    
    echo $event;
    
    
    
}
 
 ?>
 
 
 

                        
                    
 
 
             
  
  

		

 
 
                 
 
 
 
 