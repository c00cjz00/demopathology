<?php
include("php/base.php");
$pass=0;
if (isset($_GET['op']) && ($_GET['op']=="logout")) {
 $_SESSION = array();
 session_destroy();
 header("Location: myLogin.php");
 die();	
}

if(!isset($_SESSION['LoggedIn']) || $_SESSION['LoggedIn']!=1){
 if(isset($_POST['username']) && isset($_POST['password'])){
  $connection=mysqli_connect($dbhost,$dbuser,$dbpass,$dblogin) or die("ERROR: Can't connect to MySQL DB: " . mysql_error());
  $username = mysqli_real_escape_string($connection,$_POST['username']);
  $password = md5(mysqli_real_escape_string($connection,$_POST['password']));
  $query="SELECT * FROM ".$dblogin.".Users WHERE Username = '".$username."' AND Password = '".$password."'";
  $checklogin = mysqli_query($connection,$query);
  if(mysqli_num_rows($checklogin) == 1){
   $row = mysqli_fetch_array($checklogin);
   $email = $row['EmailAddress'];
   $_SESSION['Username'] = $username;
   $_SESSION['EmailAddress'] = $email;
   $_SESSION['LoggedIn'] = 1;
   $pass=2;
  }
 }
}else{
 $pass=1;	
}	
if ($pass==0){
 header("Location: myLogin.php");
 die();
}	
if (!isset($_GET['source'])) {
 header("Location: index.php?source=jpgCancer.json");
 die();
} 

?>
<html>

<head>
	<title>DEMO</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,user-scalable=no" />
	<link rel="stylesheet" href="css/microdraw.css" type="text/css" />
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />	
	<style>
	.circle0{
		/*padding: px;
		border-radius: 2px;
		border: double 2px rgba(255,255,255,0);
		background: rgba(0,0,0,0.7);
		background-clip: padding-box;*/
		border: double 0px rgba(255,255,255,0);	
	}	
	.circle{
		/*padding: px;
		border-radius: 2px;
		border: double 2px rgba(255,255,255,0);
		background: rgba(0,0,0,0.7);
		background-clip: padding-box;*/
		border: double 4px rgba(255,255,255,0);	
	}	
	#myBtn{
	 width:70px;   
	 font-size:small 
	}

	#myBtn option{
	 width:200px;   
	}
	#slice-name{
	 width:30px;   
	 font-size:small 
	}	
	#myBtn2{
	 font-size:small 
	}	
	</style>

</head>

<body>

<!-- Toolbar -->
<div id="menuBar" class="table">
	<div class="row">
		<div id="myNavigator"></div>
	</div>
    <p></p>
	<div align="center" class="circle0">		
	<img class="button" id="prev"    title="Previous slice"         src="img/myprev.svg" />
	<input id="slice-name" list="slice-names"/>		
	<img class="button" id="next"    title="Next slice"             src="img/mynext.svg" />
	</div>	
	<div align="center" class="circle"></div>	
	<div align="center" class="circle">			
    <img class="button" id="home"           title="Home"                   src="img/myhome.svg" />
	<a href="index.php?op=logout"><img src="img/mylogout.svg" title="登出"/></a>
	</div>	
	<div align="center" class="circle">		
	<img class="button" id="save"        title="Save annotations"       src="img/mysave.svg" />	
	<img class="button" id="delete"      title="Delete region"          src="img/mytrash.svg" >
	</div>	
	<div align="center" class="circle">				
	<img class="button" id="zoom"        title="Navigator"              src="img/mynavigate.svg" />
	<img class="button" id="select"       title="Select"                 src="img/myselect.svg" />
	</div>
	<div align="center" class="circle">				
	<img class="button" id="draw"         title="Draw"                   src="img/mydraw.svg" />
	<img class="button" id="draw-polygon" title="Draw polygon"           src="img/mydraw-polygon.svg" />
	</div>
	<div align="center" class="circle">				
	<img class="button" id="copy"           title="Copy path"              src="img/mycopy.svg" />
	<img class="button" id="paste"          title="Paste path"             src="img/mypaste.svg" />	
	</div>
	<div align="center" class="circle">				
	<img class="button" id="undo"           title="undo"              src="img/myundo.svg" />
	<img class="button" id="redo"          title="redo"             src="img/myredo.svg" />	
	</div>
	<div align="center" class="circle">					
	<select id="myBtn" onChange="myFunction()">
	<option value="Phase1">Phase1</option>
	<option value="Phase2">Phase2</option>
	<option value="Phase3">Phase3</option>
	<option value="Normal">Normal</option>
	<option value="Bubble" selected="selected">Bubble</option>
	</select>	
	</div>	
	<!--div class="row">
		<div class="cell"-->
			<div id="regionList" class="myBtn2"></div>
		<!--/div>
	</div-->
</div>

<div id="colorSelector">
	stroke color
	<select id="selectStrokeColor" onChange="onSelectStrokeColor();">
		<option value="0">black</option>
		<option value="1">white</option>
		<option value="2">red</option>
		<option value="3">green</option>
		<option value="4">blue</option>
		<option value="5">yellow</option>
	</select>
    <br>
    <br>
    stroke width
    <input type="button" id="strokeWidthDec" value="-" onClick="onStrokeWidthDec();"><input type="button" id="strokeWidthInc" value="+" onClick="onStrokeWidthInc();">
	<br>
	<br>
	fill color
	<input type="color" id="fillColorPicker" value="#ff0000" onChange="onFillColorPicker(this.value);" >
	<br>
	<br>
	&alpha;<input type="range" min="0" max="100" id="alphaSlider" onInput="onAlphaSlider(this.value);" ><input id="alphaFill" onInput="onAlphaInput(this.value);" >
	<br>
	<br>
	<input type="button" id="okStrokeColor" value="ok" onClick="setRegionColor();">
</div>

<!-- Region Picker -->
<div id="regionPicker">
</div>

<!-- OpenSeadragon viewer -->
<div id="openseadragon1" style="width:100%">
</div>

<!-- alert/info box after saving -->
<div id="saveDialog"></div>


<!-- Load javascript -->
<script>
 var myName= "Bubble";
 function myFunction() {
  var x = document.getElementById("myBtn").value;
  myName = x;
 }
</script>

<script src="lib/paper-full-1.0.2.js"></script>
<!--script src="lib/paper-full-0.9.25.min.js"></script-->
<script src="lib/openseadragon/openseadragon.js"></script>
<script src="lib/openseadragon-viewerinputhook.min.js"></script>
<script src="lib/OpenSeadragonScalebar/openseadragon-scalebar.js"></script>
<script src="lib/jquery-1.11.0.min.js"></script>
<script src="lib/mylogin/login.js"></script>
<script src="neurolex-ontology.js"></script>
<script src="base.js"></script>
<script src="microdraw.js"></script>
</body>

</html>


