<?php 

require 'vendor/autoload.php'; // include Composer goodies
require_once 'db.php';

$db = new db();
$db->connect();  

//Get page number
$current_page = filter_input(INPUT_POST, 'current_page');
$show_love = filter_input(INPUT_POST, 'show_love');
$country = filter_input(INPUT_POST, 'country');

//check variable
if($current_page == '') $current_page = 1;


//Set the page size
$pageSize = 60;


if($show_love == "1") {
	$filter = ['love' => 'love'];
} else {
	$filter = ['capture_result' => 'Success'];
	$show_love = "";
}

if(is_null($country) or ($country == 'ALL')) {
	$country = 'ALL';
	$collection = 'capture_list_all';
} else if($country == 'HK') {
	$collection = 'capture_list_hk'; 
} else if($country == 'SG') {
	$collection = 'capture_list_sg'; 
}

if($current_page > 1) {
	$options = ['skip' => ($pageSize * ($current_page-1)),'limit' => $pageSize,'sort'=> ['ip' => 1]];    
} else {
	$options = ['limit' => $pageSize,'sort'=> ['ip' => 1]];       
}
   
$rows = $db->query($filter, $options , $collection);   
$photo_grid = '';    
$ind = 1;
foreach($rows as $row) { 
	if($row->ip > 2147483648) {
		$ip = long2ip(-(4294967296-$row->ip));
	} else {
		$ip = long2ip($row->ip);
	}
	

	//$datetime = $row->capture_timestamp->toDateTime();     
	//$datetime->setTimezone(new DateTimeZone('Asia/Hong_Kong'));  
	//$datetime = $datetime->format('Y:m:d');	
	$datetime = substr($row->capture_timestamp,0,10);
	$love = '<button id="btn_'.$row->ip.'" class="w3-right" onclick="love(\''.$row->ip.'\')">Love</button>';
	if(isset($row->love)) {
		if($row->love == "love") {
			$love = '<button id="btn_'.$row->ip.'" class="w3-right" onclick="love(\''.$row->ip.'\')">Un-Love</button>';
		}
		
	}
	if($ind == 1){ $photo_grid = $photo_grid.'<div class="w3-row-padding">'; }
	$class = '';
	if($ind != 4) {$class = 'w3-margin-bottom';}
	$photo_grid = $photo_grid.'
	<div class="w3-quarter w3-container '.$class.'">
	<img src="pic/'.strtolower($country).'/'.$row->token.'/'.$ip.'.jpeg" alt="Norway" style="width:100%" class="w3-hover-opacity" onclick="showPic(this)">
	<div class="w3-container w3-white">'.
	'<p style="font-size:xx-small">'.
	$datetime.		  
	' <input type="text" id="'.$row->ip.'" value="'.$row->link.'" size="1">'.
	'<button class="" onclick="copy(\''.$row->ip.'\')">Copy</button>'.
	$love.
	'</p>
	</div>
	</div>	
	';
	if($ind == 4){ $photo_grid = $photo_grid.'</div>'; }
	if($ind == 4) { $ind = 1;} else { $ind += 1; }
} 
   
   
//Get Page
$count = $db->count($filter, $collection);   
$max_page = ceil($count/$pageSize);
$page_but = '';
if($current_page > 1) {
	$page_but = '<li><a class="w3-hover-black" href="#" onclick="submitPage('.($current_page - 1).')"> < </a></li>';
}
if($current_page <= 6) {
	for($i = 1; $i <=11 ; $i++) {
		if($i <= $max_page) {
			if($i == $current_page) {$btn = 'w3-black';} else {$btn = 'w3-hover-black';}
			$page_but = $page_but . '<li><a class="'.$btn.'" href="#" onclick="submitPage('.$i.')"> '.$i.' </a></li>';
		}
	}   
} else {
	for($i = ($current_page - 5); $i <= ($current_page + 5); $i++){
		if($i <= $max_page) {
			if($i == $current_page) {$btn = 'w3-black';} else {$btn = 'w3-hover-black';}
			$page_but = $page_but . '<li><a class="'.$btn.'" href="#" onclick="submitPage('.$i.')"> '.$i.' </a></li>';
		}
	}            
}
if($current_page != $max_page) {
	$page_but = $page_but . '<li><a class="w3-hover-black" href="#" onclick="submitPage('.($current_page + 1).')"> > </a></li>';           
}    



   
?>

<!DOCTYPE html>
<html>
<title>Photo</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Raleway", sans-serif}
.w3-sidenav a,.w3-sidenav h4 {font-weight:bold}
input.hidden {visibility:collapse;}
</style>
<body class="w3-light-grey w3-content" style="max-width:1600px">

<!-- Sidenav/menu -->
<nav class="w3-sidenav w3-collapse w3-white w3-animate-left" style="z-index:3;width:200px;" id="mySidenav"><br>
  <div class="w3-container">
    <a href="#" onclick="w3_close()" class="w3-hide-large w3-right w3-jumbo w3-padding" title="close menu">
      <i class="fa fa-remove"></i>
    </a>
    <img src="w3images/avatar_g2.jpg" style="width:45%;" class="w3-round"><br><br>
    <h4 class="w3-padding-0"><b>PHOTO</b></h4>
  </div>
  <a href="#portfolio" onclick="w3_close()" class="w3-padding w3-text-teal"><i class="fa fa-th-large fa-fw w3-margin-right"></i>PORTFOLIO</a> 
  <a href="#about" onclick="w3_close()" class="w3-padding"><i class="fa fa-user fa-fw w3-margin-right"></i>ABOUT</a> 
  <a href="#contact" onclick="w3_close()" class="w3-padding"><i class="fa fa-envelope fa-fw w3-margin-right"></i>CONTACT</a>
   
  <!--
  <div class="w3-section w3-padding-top w3-large">
    <a href="#" class="w3-hover-white w3-hover-text-indigo w3-show-inline-block"><i class="fa fa-facebook-official"></i></a>
    <a href="#" class="w3-hover-white w3-hover-text-purple w3-show-inline-block"><i class="fa fa-instagram"></i></a>
    <a href="#" class="w3-hover-white w3-hover-text-yellow w3-show-inline-block"><i class="fa fa-snapchat"></i></a>
    <a href="#" class="w3-hover-white w3-hover-text-red w3-show-inline-block"><i class="fa fa-pinterest-p"></i></a>
    <a href="#" class="w3-hover-white w3-hover-text-light-blue w3-show-inline-block"><i class="fa fa-twitter"></i></a>
    <a href="#" class="w3-hover-white w3-hover-text-indigo w3-show-inline-block"><i class="fa fa-linkedin"></i></a>
  </div>
  -->
</nav>

<!-- Overlay effect when opening sidenav on small screens -->
<div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- The Modal -->
<div id="modal01" class="w3-modal" onclick="this.style.display='none'">
  <img class="w3-modal-content" id="img01" style="width:100%">
</div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:200px">

  <!-- Header -->
  <header class="w3-container" id="portfolio">
    <a href="#"><img src="w3images/avatar_g2.jpg" style="width:65px;" class="w3-circle w3-right w3-margin w3-hide-large w3-hover-opacity"></a>
    <span class="w3-opennav w3-hide-large w3-xxlarge w3-hover-text-grey" onclick="w3_open()"><i class="fa fa-bars"></i></span>
    <h1><b>PHOTO</b></h1>
    <div class="w3-section w3-bottombar w3-padding-16">
      <span class="w3-margin-right">Filter:</span> 
      <button class="w3-btn" onclick="showALL()">ALL</button>
      <button class="w3-btn" onclick="showHK()">HK</button>
      <button class="w3-btn" onclick="showSG()">SG</button>
      <button class="w3-btn w3-white" onclick="showLoveALL()"><i class="fa fa-diamond w3-margin-right"></i>Love ALL</button>
      <button class="w3-btn w3-white" onclick="showLoveHK()"><i class="fa fa-diamond w3-margin-right"></i>Love HK</button>
      <button class="w3-btn w3-white" onclick="showLoveSG()"><i class="fa fa-diamond w3-margin-right"></i>Love SG</button>
    </div>
  </header>
  
  <!-- First Photo Grid-->
  <?php echo $photo_grid; ?>

  <!-- Pagination -->
  <div class="w3-center w3-padding-32">
  <form id="form" method="post" action="index.php">
	<input type="hidden" name="current_page" id="current_page" value="" />  
	<input type="hidden" name="show_love" id="show_love" value="<?php echo $show_love; ?>" /> 
	<input type="hidden" name="country" id="country" value="<?php echo $country; ?>" /> 
    <ul class="w3-pagination">
		<?php echo $page_but; ?>
		<input type="text" name="input_page" id="input_page" size="4" value="<?php echo $current_page ?>"></input>
    </ul>
  </form>
  </div>


  </footer>
  

<!-- End page content -->
</div>

<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script>
// Script to open and close sidenav
function w3_open() {
    document.getElementById("mySidenav").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
function w3_close() {
    document.getElementById("mySidenav").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}
function submitPage(current_page) {
    $('#current_page').val(current_page);
    $('#form').submit();
}
function showPic(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
}
function reload(ip) {
	var form_data = new FormData();
    form_data.append('ip',ip);
	$.ajax({
		url: 'reload.php', // point to server-side PHP script 
		dataType: 'text',  // what to expect back from the PHP script, if anything
		cache: false,
		contentType: false,
		processData: false,
		data: form_data,                         
		type: 'post',
		success: function(data){  
			console.log(data);
		}
	});  	
}
function copy(ip) {
	/*
	var urlField = document.querySelector('#'+ip);
	urlField.select();
	console.log(urlField);   
	document.execCommand('copy'); // or 'cut'  
	*/
	document.getElementById(ip).focus();
	document.getElementById(ip).select(); 
	//console.log(document.getElementById(ip).value);
	//document.execCommand('SelectAll');
	document.execCommand("Copy");    
}
function love(ip) {
	var action = $('#btn_'+ip).text();
	var country = $('#country').val();
	$.post("love.php",{
			ip:ip,
			action:action,
			country:country
		},function(data, status){
		console.log(data);
    	if(status === "success") {
    		$("#btn_"+ip).text(data);
    	}
    });	
}
function showLoveALL() {
	$("#show_love").val("1");
	$('#country').val("ALL");
	$('#form').submit();		
}
function showLoveHK() {
	$("#show_love").val("1");
	$('#country').val("HK");
	$('#form').submit();		
}
function showLoveSG() {
	$("#show_love").val("1");
	$('#country').val("SG");
	$('#form').submit();		
}
function showALL() {
	$("#show_love").val("");
	$('#country').val("ALL");
	$('#form').submit();
}
function showHK() {
	$("#show_love").val("");
	$('#country').val("HK");
	$('#form').submit();
}
function showSG() {
	$("#show_love").val("");
	$('#country').val("SG");
	$('#form').submit();
}
$(document).ready(function(){
    $('#input_page').keypress(function (e) {
  		if (e.which == 13) {
    		submitPage($('#input_page').val());
  		}  		
	});
});
</script>

</body>
</html>
