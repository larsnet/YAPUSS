<?php 
/*
# YAPUSS - Yet Another Passerelle(bridge) Universelle Surveillance Station
## Version History :
```txt
V6   by sebcbien (18/10/17)
V6.1 by Jojo (19/10/2017)	:	server IP adress generated automatically
V6.2 by Sebcbien:				added ptz placeholder
V6.3 by Jojo (19/10/2017)	:	remove hardcoding of file name & location
V6.4 by Jojo (20/10/2017)	:	links are opened in a new tab
V7   by Sebcbien (21/10/17)	:	Added enable/disable action
								Changed file Name (SSS_Get.php)
V8   by Jojo (25/10/2017)	:	add start/stop recording action
								& global review for alignments, comments, ...
V9   by Jojo (20/11/2017)	:	takes screenshots and send them per e-mail as attachement
								& actions for all cameras
								& update menu
V10   by seb (22/11/2017)	:	Added PTZ function, small bug fixes & rearrange code for speed optimisation
V10.1 by seb (25/11/2017)	:	correction bug actions.
v10.2 by Jojo (25/11/2017)	:	correction bug list PTZ ids & code optimization (use function)
v11   by Jojo (23/12/2017)	:	get configuration from external file
v11.1 by Jojo (22/06/2018)	:	add TimeStamps for display in logs
v12   by Jojo (14/09/2018)	:	add possibility to personnalize subject of the e-amil
v13   by seb (16/09/2018)	:	add elapsed time counter for debug purposes
								& solve bug ini file not parsed when YAPUSS script is not in the root folder
v14   by seb (18/09/2018)	:	add method to re-use the SID between API calls
								& add method to reset SID (action=ResetSID)
v15   by seb (19/09/2018)	:	add method to write all available snapshots to disk (list=AllSnapshots)
								& resolve bug about snapshot quality not working (see more info in .ini file)
								& Cosmetic Work
v16   by seb (21/09/2018)	:	keep the same SID for all sessions. Script is now WAYYYY Faster and les load on Surveillance Station
V16.1 by seb (22/09/2018)	:	add auto creation of SessionFile.txt if not present
								& WRITE Permission IS NEEDED by the web server to write session file.
								& CURL must be enabled in php option
								& check if getsnapshot failed, and then reset SID to get a new one
								& cleaned up the code
v16.2 by Jojo (23/09/2018)	:	rename SessionFile.txt to SSS_Get.session
v16.3 by Seb (23/09/2018)	:	clear bug not showing snapshots when in debug mode. cleaning the code.
v17.0b by Jojo (23/09/2018)	:	work in progress to add save snapshots with history
v17.1b by Seb (24/09/2018)	:	Add status images sent to client when no image is available on SS
v18 by Jojo (04/10/2018)	:	review/optimize code
								& review/standadize actions
								& review documentation
								& add parameter debug=1
								& add autorefresh (via .ini file ( v >= 3.0) or via parameter)
v18.1 by Jojo (21/02/2019)	:	correct bug with PTZ
v19.0 by Jojo (xx/03/2019)	:	add zoom in/out
								& add patrol
# ToDo:
 - accept array of cameras form url arguments
 - find a quicker test to check if api access is ok (retreiving json of cameras takes 0,5 second)
 - Clean up code with faster check with PTZ at the beginning
 - Force refresh of snapshot on demo page
```
## Requirements
```txt
PHP 7.0, altrough a previous version may work for some functionalities
cURL extension (optional, for downloading snapshots and saving them to the web server)
WRITE Permission IS NEEDED by the web server to write session file.
User defined in the .ini file must be director of the cameras.
```
## Installation instructions :
```txt
Install php 7.0 on the Web server.
Save this file with extension .php (example : SSS_Get.php)
In the same folder, create the .ini file with the SAME name (except the extension) as this scirpt file (example : SSS_Get.ini)

If you use Synology to send mails. you need to configure the notification in the control panel.
I share with you some strange behaviors.
	1) When using GMAIL, even if the test mail notification was ok, this php mail was not send. => Solution is to re-do the autentication for Gmail in the notification Panel of the Synology. But after few hours / days it does not work anymore ...
	2) So I tried Yahoo! as SMTP provider, but the delivery of mails tooks a long time (several seconds/minutes)
	3) I use now the SMTP of my mail provider : this is as fast as whith Gmail.

```
## Thread here :
```txt
https://www.domotique-fibaro.fr/topic/11097-yapuss-passerelle-universelle-surveillance-station/
Thanks to all open sources examples grabbed all along the web and specially filliboy who made this script possible.

```
## Syntax :
```txt
	- Snapshot quality: snapQual=0: High Quality | 1: Medium Quality | 2: Low Quality (if available) default is set in .ini: profileType

	- action=
		enable 		- enable camera
		disable 	- disable camera
		start 		- start recodring camera
		stop 		- stop recording camera
		snapshot 	- save camera snapchot (Snapshot-Cam-#.jpg in the script running folder)
		archive 	- save camera snapshot to /Snapshots sub-folder with timestamp (Snapshot_#_<cam name>_yyyymmdd_hhmmss.jpg)
		mail 		- send per e-mail a camera snapshot
		ResetSID 	- force regeneration of a new sid. Should not be needed, an SID stays untill a reboot of the synology

	  if camera=# provided : restrict the action to the specified cameera
	  if camera=0|All or no camera specified : perform action for All cameras

	  for action=start & action=mail, adding the parameter '&enable=1' enable the disabled camera before the action.

	- display=# : Send one image (of camera #) to the client & display

	- Other functions :
		Get Snapshot :	http://xxxxxx/SSS_Get.php?stream=jpeg&camera=#&snapQual=q   - returns snapshot of camera #, Quality q
		Get Mjpeg :		http://xxxxxx/SSS_Get.php?stream=mjpeg&camera=#             - returns mjpeg stream of camera #
		Debug :			http://xxxxxx/SSS_Get.php?debug=1							- run the script in debug mode
		Refresh :		http://xxxxxx/SSS_Get.php?refresh=#							- refresh de home page every # sec/9999 to stop
		Move Camera :	http://xxxxxx/SSS_Get.php?ptz=<position>&camera=#		    - move camera # to PTZ position <position>
		Patrol Camera :	http://xxxxxx/SSS_Get.php?patrol=<patrol>&camera=#			- patrol camera # according to patrol def <patrol>
		Zoom Camera :	http://xxxxxx/SSS_Get.php?zoom=in|out|inTot|outTot&camera=# - zoom camera # in|out|inTot|outTot

```
## Some example :
```txt
	http://xxxxxx/SSS_Get.php?action=snapshot&camera=19&snapQual=0 
		Save snapshot of camera Nr 19 on disk (High Quality)
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=0
		Save all available snapshots to disk (High Quality)
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=0&display=1 
		Save all available snapshots to disk (High Quality) and return one snapshot of camera Nr 1.
		(Typical use: ask this urls with one display . It will then act as scheduler. Then grab the writen image on disk with the other displays).
	http://xxxxxx/SSS_Get.php?action=snapshot&snapQual=1&display=2.
		Save all available snapshots to disk (Medium Quality) and return one snapshot of camera Nr 2.
	http://xxxxxx/SSS_Get.php?action=enable&camera=14              - enable camera 14
	http://xxxxxx/SSS_Get.php?action=enable&camera=0               - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=enable&camera=0All            - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=enable                        - enable ALL cameras
	http://xxxxxx/SSS_Get.php?action=mail&camera=14                - send per mail snapshot of camera 14
	http://xxxxxx/SSS_Get.php?action=mail&subject=non default      - send per mail snapshot of ALL cameras with the non default subject on the mail

Help function:
	http://xxxxxx/SSS_Get.php                                      - Returns the list of all cameras with a snapshot, status, urls etc.
	http://xxxxxx/SSS_Get.php?list=json                            - Returns a json with all cameras
	http://xxxxxx/SSS_Get.php?list=camera                          - Returns the list of all cameras with a snapshot, status, urls etc.

PTZ function
	http://xxxxxx/SSS_Get.php?cameraPtz=5&camera=19                - moves camera Nr 19 to PTZ position id 5

```
## License
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
*/

$CodeVersion = "v19.0";

// from .ini file (.ini file mut have the same name as the running script)
$ini_array = parse_ini_file(substr(basename($_SERVER['SCRIPT_NAME']).PHP_EOL, 0, -4)."ini");
$user = $ini_array[user];
$pass = $ini_array[pass];
$ip_ss = $ini_array[ip_ss];
$port = $ini_array[port];
$http = $ini_array[http];
$vCamera = $ini_array[vCamera];
$vAuth = $ini_array[vAuth];
$ptzCapTest = $ini_array[ptzCapTest];
$profileType = $ini_array[profileType];
$debug = $ini_array[debug];
$refresh = $ini_array[refresh];
// e-mail configuration
$from_mail = $ini_array[from_mail];
$from_name = $ini_array[from_name];
$reply_mail = $ini_array[reply_mail];
$reply_name = $ini_array[reply_name];
$to_mail = $ini_array[to_mail];
$to_name = $ini_array[to_name];
$subject = $ini_array[subject];
$body = $ini_array[body];
// end from .ini file

// auto configuration
$ip = $_SERVER['SERVER_ADDR']; 					// IP-Adress of your Web server hosting this script
$file = $_SERVER['PHP_SELF'];  					// path & file name of this running php script
$dirname = pathinfo($file, PATHINFO_DIRNAME);	// relative path
	if ($dirname == "/") {$dirname = "";}
$dirnamefull = getcwd();						// full path : expl /volume1/web/...
//$SessionFile = substr(basename($_SERVER['SCRIPT_NAME']).PHP_EOL, 0, -4)."session";  Modify subfolder with write permissions
$SessionFile = "/volume1/web/SSS_session/SSS_Get.session";
//$SessionSave = (object)array();

// URL parameters
$stream = $_GET['stream'];
$cameraID = $_GET['camera'];
$displayID = $_GET['display'];
$cameraPtz = $_GET["ptz"];
$cameraPatrol = $_GET["patrol"];
$cameraZoom = $_GET["zoom"];
$action = $_GET["action"];
$enable = $_GET["enable"];
$list = $_GET["list"];
// if parameter specified, use the specified one
if ($_GET["debug"] != NULL) {$debug = 1;}
if ($_GET["refresh"] != NULL) {$refresh = $_GET["refresh"];}
if ($_GET["subject"] != NULL) {$subject = $_GET["subject"];}
if ($_GET["snapQual"] != NULL) {$profileType = $_GET["snapQual"];}

// Default values
if ($stream == NULL && $cameraID == NULL && $cameraPtz == NULL && $action == NULL && $list == NULL) { 
    $list = "camera";
}
if ($cameraID == NULL) {
	$cameraID = 0;
}
if ($stream == NULL) {
	$stream = "jpeg";
}
$SnapDir = "Snapshots";

// Debug Alert
if ($debug) {echo ("YAPUSS php code version: ".$CodeVersion."<br>");}
if ($debug) {echo "<hr>DEBUG ENABLED, TURN OFF BY SETTING VAR debug TO false IN THE CODE<br>!!!!REMOVE debug = true WHEN CODE IS IN PRODUCTION !!!!<hr>";}

// SID
SessionRead();
if ($debug) {echo time_elapsed("End of initialisation :");}
if ($debug) {echo "<br>Status of variables BEFORE EXIT: <b>path:</b> ".$CamPath." <b>sid:</b> ".$sid." <b>auth:</b> ".$AuthPath."<br>";}

if ($sid != "") {
	if ($debug) {echo("Var session existantes:<br> CamPath: ".$CamPath."<br> AuthPath: ".$AuthPath."<br> SID: ".$sid."<br>");}
	//TESTER l'accès avec camera list json
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	$obj = json_decode($json);
		//Check if auth pas ok 
		if($obj->success != "true"){
			SessionSave("","",""); // reset session file and exit
			exit();
		}
	} else { //Get a new SID ------------------------------------------------------------------
	//Get SYNO.API.Auth Path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.API.Auth');
	$obj = json_decode($json);
	$AuthPath = $obj->data->{'SYNO.API.Auth'}->path;
	if ($debug) {echo time_elapsed("Received Auth Path :");}
	//Get SYNO.SurveillanceStation.Camera path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.Camera');
	$obj = json_decode($json);
	$CamPath = $obj->data->{'SYNO.SurveillanceStation.Camera'}->path;
	if ($debug) {echo time_elapsed("Received Camera Path. Asking Syno API for a new SID.");}
	//Get SID
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$AuthPath.'?api=SYNO.API.Auth&method=Login&version=6&account='.$user.'&passwd='.$pass.'&session=SurveillanceStation&format=sid'); 
	if ($debug) {echo time_elapsed("Json received: ".$json);}
	$obj = json_decode($json); 
	//Check if auth ok 
	if($obj->success != "true"){
		if ($debug) {echo "<br>";}
		if ($debug) {echo time_elapsed("Error not a valid SID. Clearing the SID, please rety ! ");}
			SessionSave("","","");
		exit();
	} else {
		//authentification successful
		$sid = $obj->data->sid;
		if ($debug) {echo time_elapsed("New SID generated storing in .session file. Value: ".$sid);}
		SessionSave($CamPath,$sid,$AuthPath);
		SessionRead();
		if ($debug) {echo time_elapsed("Generated and Stored new SID. Test: ".$sid);}
		exit();
	}
}
if ($debug) {echo time_elapsed("All Session Variables :");}
if ($debug) {echo time_elapsed("<br> CamPath: ".$CamPath."<br> AuthPath: ".$AuthPath."<br> SID: ".$sid);}
//Re-Check if auth OK ----------------------------------------------------------------------------
if ($sid == ""){
	echo "Error: Sid Not SET. Clearing file session and Exiting";
	SessionSave("","","");
	// on ferme la page qui vient d'être générée
	exit();
}
// -----------------------------------------------------------------------------------
// OK, SID Exists
// -----------------------------------------------------------------------------------

// create full information page --------------------------------------------------------------
if ($list == "camera") {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
	$obj = json_decode($json);
	$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
	//list & status of known cams 
	$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
	$nbrCam = $obj->data->total;
	echo("<meta http-equiv='refresh' content='".$refresh."'>"); //Refresh by HTTP META

	//
	echo "<u>Informations :</u><hr>";
	if ($nbrCam == 0) {
		echo "No camera defined.";
		exit();
	} else {
		echo "Camera count : <b>".$nbrCam."</b><br>";
	}
	echo "Actual <b>SID</b> : ".$sid."<br>";
	echo "Force SID Renew ? <a href=http://".$ip.$file."?action=ResetSID target='_blank'>http://".$ip.$file."?action=ResetSID </a><br><br>";
	echo "Saved Snapshots location : <i>".$dirnamefull."</i><br>";
	echo "Archived Snapshots location : <i>".$dirnamefull."/".$SnapDir."</i><br>";
	if ($refresh == 9999) {   // if 9999 s = no refresh
		echo "Activate defualt refresh time : <a href=http://".$ip.$file."?refresh=".$ini_array[refresh]." target='_blank'>".$ini_array[refresh]." sec</a>";
	} else {
		echo "Current tefresh time : ".$refresh." sec. -> <a href=http://".$ip.$file."?refresh=9999 target='_blank'> Stop</a>";		
	}
	//
	echo "<br><hr><u>Functions for ALL cameras :</u><hr>";
	echo "<B>Save</b> all available snapshots to disk : <a href=http://".$ip.$file."?action=snapshot&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=snapshot&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=snapshot&snapQual=2 target='_blank'>LowQuality</a><br>";
	echo "<b>Archive</b> all available snapshots to disk : <a href=http://".$ip.$file."?action=archive&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=archive&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=archive&snapQual=2 target='_blank'>LowQuality</a><br>";
	echo "Get all cameras <b>JSON</b> : <a href=http://".$ip.$file."?list=json target='_blank'>http://".$ip.$file."?list=json </a><br>";
	echo "<b>Enable</b> all cameras : <a href=http://".$ip.$file."?action=enable target='_blank'>http://".$ip.$file."?action=enable </a><br>";
	echo "<b>Disable</b> all cameras : <a href=http://".$ip.$file."?action=disable target='_blank'>http://".$ip.$file."?action=disable </a><br>";
	echo "<b>Start recording</b> all cameras : <a href=http://".$ip.$file."?action=start target='_blank'>http://".$ip.$file."?action=start </a><br>";
	echo "<b>Stop recording</b> all cameras : <a href=http://".$ip.$file."?action=stop target='_blank'>http://".$ip.$file."?action=stop </a><br>";
	echo "Send screenshots per <b>e-mail</b> for all cameras : <a href=http://".$ip.$file."?action=mail target='_blank'>http://".$ip.$file."?action=mail </a><br>";
	//
	echo "<br><hr><u>Actions for each individual cameras :</u><hr>";
	//list of known cams 
	foreach($obj->data->cameras as $cam){
		$id_cam = $cam->id;
		$nomCam = $cam->name;
		$vendor = $cam->vendor;
		$model = $cam->model;
		$ptzCap = $cam->ptzCap;
		echo "Camera Name : <b>".$nomCam." (".$id_cam.") ";
		if ($cam->enabled) {
			echo "Is Enabled</b> --> <a href=http://".$ip.$file."?action=disable&camera=".$id_cam." target='_blank'>Disable ?</a><br>";
		} else {
			echo "Is disabled</b> --> <a href=http://".$ip.$file."?action=enable&camera=".$id_cam." target='_blank'><b>Enable ?</b></a><br>";
		}
		echo "Vendor : <b>".$vendor."</b> - Model : <b>".$model."</b><br>";
		
		if ($ptzCap > $ptzCapTest) {
			echo "<i>List of PTZ presets : </i>";
			$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=ListPreset&version=1&cameraId='.$id_cam.'&_sid='.$sid);
			$listPtz = json_decode($json);
			if ($listPtz->data->total == 0) {
				echo "No PTZ defined.<br>";
			} else {
				echo "<b>".$listPtz->data->total."</b> PTZ.<br>";
				foreach($listPtz->data->presets as $ptzId){
					echo "id: ".$ptzId->id." Name: ".$ptzId->name."  --  URL: "."<a href=http://".$ip.$file."?ptz=".$ptzId->id."&camera=".$id_cam." target='_blank'>http://".$ip.$file."?ptz=".$ptzId->id."&camera=".$id_cam."</a><br>";
					}
			}

			echo "<i>List of Patrol defined : </i>";
			$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=ListPatrol&version=1&cameraId='.$id_cam.'&_sid='.$sid);
			$listPatrol = json_decode($json);
			if ($listPatrol->data->total == 0) {
				echo "No Patrol defined.<br>";
			} else {
				echo "<b>".$listPatrol->data->total."</b> Patrols.<br>";
				foreach($listPatrol->data->patrols as $patrolId){
					echo "id: ".$patrolId->id." Name: ".$patrolId->name."  --  URL: "."<a href=http://".$ip.$file."?patrol=".$patrolId->id."&camera=".$id_cam." target='_blank'>http://".$ip.$file."?patrol=".$patrolId->id."&camera=".$id_cam."</a><br>";
					}
			}
			
			echo "<i>Zoom : </i>";
			echo "IN : "."<a href=http://".$ip.$file."?zoom=in&camera=".$id_cam." target='_blank'>step by step</a>";
			echo " - "."<a href=http://".$ip.$file."?zoom=inMax&camera=".$id_cam." target='_blank'>total</a>";
			echo " -- OUT : "."<a href=http://".$ip.$file."?zoom=out&camera=".$id_cam." target='_blank'>step by step</a>";
			echo " - "."<a href=http://".$ip.$file."?zoom=outMax&camera=".$id_cam." target='_blank'>total</a><br>";

		}
		//check if cam is connected
		if (!$cam->status) {
			//check if cam is activated
			if($cam->enabled) {
				echo "<b>Display</b> snapshot : <a href=http://".$ip.$file."?stream=jpeg&camera=".$id_cam."&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?stream=jpeg&camera=".$id_cam."&snapQual=1 target='_blank'>Medium Quality ?</a> - <a href=http://".$ip.$file."?stream=jpeg&camera=".$id_cam."&snapQual=2 target='_blank'>Low Quality ?</a><br>";
				echo "<b>Stream MJPEG : </b> <a href=http://".$ip.$file."?stream=mjpeg&camera=".$id_cam." target='_blank'>http://".$ip.$file."?stream=mjpeg&camera=".$id_cam."</a><br>";
				echo "<b>Save</b> snapshot to disk : <a href=http://".$ip.$file."?action=snapshot&camera=".$id_cam."&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=snapshot&camera=".$id_cam."&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=snapshot&camera=".$id_cam."&snapQual=2 target='_blank'>LowQuality</a><br>";		
				echo "<b>Archive</b> snapshot to disk : <a href=http://".$ip.$file."?action=archive&camera=".$id_cam."&snapQual=0 target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=archive&camera=".$id_cam."&snapQual=1 target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=archive&camera=".$id_cam."&snapQual=2 target='_blank'>LowQuality</a><br>"; 
				echo "<b>Save</b> ALL snapshots to disk and </b>display</b> snapshot of that CAM : <a href=http://".$ip.$file."?action=snapshot&snapQual=0&display=".$id_cam." target='_blank'>High Quality</a> - <a href=http://".$ip.$file."?action=snapshot&snapQual=1&display=".$id_cam." target='_blank'>Medium Quality</a> - <a href=http://".$ip.$file."?action=snapshot&snapQual=2&display=".$id_cam." target='_blank'>LowQuality</a><br>";
				echo "Send screenshot per <b>e-mail</b> : <a href=http://".$ip.$file."?action=mail&camera=".$id_cam." target='_blank'>http://".$ip.$file."?action=mail&camera=".$id_cam."</a><br>";
				//check if cam is recording
				echo 'Recording status : ';
				if ($cam->recStatus) {
					echo "<b>recording...</b> --> <a href=http://".$ip.$file."?action=stop&camera=".$id_cam." target='_blank'>stop ?</a><br>";
				} else {
					echo "<b>NOT recording...</b> --> <a href=http://".$ip.$file."?action=start&camera=".$id_cam." target='_blank'>start ?</a><br>";
				}
				echo "<img src='".$http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid."' alt='image JPG' width='480'><br>";
			}
		}
		echo time_elapsed("Elapsed Processing time : ");
		echo "<hr>";
	}
	exit();
}
// Get JSON Camera List ---------------------------------------------------------------------------
if ($list == "json") {
	echo "Json camera list viewer. Copy the Json below and paste it in this viewer :<br>";
	echo "<a href=https://codebeautify.org/jsonviewer target='_blank'>https://codebeautify.org/jsonviewer</a><br><br>";
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	echo $json;
	exit();
}



// Actions
if ($action != NULL) {
	//list & status of known cams 
	$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
	$nbrCam = $obj->data->total;
	if ($nbrCam == 0) {
		echo "No camera defined.";
		exit();
	}

	// specific preparations for actions
	if ($action == "archive") {
		// create directory for snapshots
		if (!file_exists($SnapDir)) {
			mkdir($SnapDir);
		}
	} else if ($action == "ResetSID") {
		SessionSave("","","");
		if ($debug) {echo "Status of variables after ResetSID execution : Path : ".$CamPath." SID : ".$sid." Auth : ".$AuthPath;}
		exit();
	} else if ($action == "mail") {
		// e-mail preparation
		$mail_to = "\"$to_name\"<".$to_mail.">";
		$typepiecejointe = "image/jpeg";
		// Passage à la ligne : On filtre les serveurs qui rencontrent des bogues.		
			if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail_to)) { 
				$passage_ligne = "\r\n";
			} else {
				$passage_ligne = "\n";
			}
		//Génération du séparateur
			$boundary = md5(uniqid(time()));
		// Entête mail
			$entete = "From: \"$from_name\"<".$from_mail.">".$passage_ligne;
			$entete .= "Reply-to: \"$from_name\"<".$from_mail.">".$passage_ligne;
			$entete .= "MIME-Version: 1.0 ".$passage_ligne;
			$entete .= "Content-Type: multipart/mixed; boundary=\"$boundary\" ".$passage_ligne;
			$entete .= $passage_ligne;
		// Message : entête
			$message  = "--".$boundary.$passage_ligne;
			$message .= "Content-Type: text/html; charset=\"ISO-8859-1\" ".$passage_ligne;
			$message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
			$message .= $passage_ligne;
		// Message : texte
			$message .= $body;
			$message .= $passage_ligne;
	}
	// perform the requested actions for the selected cameras
	foreach($obj->data->cameras as $cam){
		$id_cam = $cam->id;
		$nomCam = $cam->detailInfo->camName;
		$enabled_cam = $cam->enabled;
		// check if ALL Cameras
		if ($cameraID == 0 or $camera=="All") {
			$camID = $id_cam;
		} else {
			$camID = $cameraID;
		}
		if ($action == "disable" && $id_cam == $camID) {
			echo time_elapsed("Elapsed Processing time: ");
			//if cam already Disabled
			if(!$enabled_cam) {
			// if(!$cam->enabled) {
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera already Disabled <br>';
			} else {
			//Deactivate cam
			$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Disable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
			echo 'Camera SS id : '.$id_cam.'<br>';
			echo 'Camera SS Name : '.$nomCam.'<br>';
			echo 'Status : Camera Disabled <br>';
			}
		} else if ($action == "enable" && $id_cam == $camID) {
			echo time_elapsed("Elapsed Processing time: ");
			//if cam already Enabled
			if($enabled_cam) {
				echo 'Camera SS id : '.$id_cam.'<br>';
				echo 'Camera SS Name : '.$nomCam.'<br>';
				echo 'Status : Camera already Enabled <br>';
			} else {
			//Activate cam
			$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
			echo 'Camera SS id : '.$id_cam.'<br>';
			echo 'Camera SS Name : '.$nomCam.'<br>';
			echo 'Status : Camera Enabled <br>';
			}
		} else if (($action == "start" or $action == "stop") && $id_cam == $camID) {
			echo time_elapsed("Elapsed Processing time: ");
			echo 'Camera SS id : '.$id_cam.'<br>';
			echo 'Camera SS Name : '.$nomCam.'<br>';
			//if cam Disabled
			if (!$enabled_cam and $action == "start" and $enable == 1) {
				echo 'Status : Camera is Disabled => Enabeling <br>';
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
				sleep (5);  // wait 5 sec to finalyse the enabeling process
				$enabled_cam = true;
				echo 'Status : Camera Enabled <br>';
			}
			if ($enabled_cam) {
				// start or stop recording cam
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=1&action='.$action.'&cameraId='.$camID.'&_sid='.$sid);
				echo 'Status : '.$action.' recording <br>';
			} else {
				echo 'Status : Camera is Disabled => no recording action <br>';
			}
		} else if ($action == "mail" && $id_cam == $camID) {
			echo time_elapsed("Elapsed Processing time: ");
			//if cam Disabled
			if (!$enabled_cam and $enable == 1) {
				echo 'Status : Camera '.$id_cam.' - '.$nomCam.' is Disabled => Enabeling <br>';
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
				sleep (5);  // wait 5 sec to finalyse the enabeling process
				$enabled_cam = true;
				echo 'Status : Camera '.$id_cam.' - '.$nomCam.' Enabled <br>';
			}
			if ($enabled_cam) {
				$file_displayName = "Camera_".$nomCam."_".strftime("%Y%m%d_%H%M%S");
				$url = $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?camStm='.$cameraStream.'&version='.$vCamera.'&cameraId='.$camID.'&api=SYNO.SurveillanceStation.Camera&method=GetSnapshot&_sid='.$sid; 
				$data = chunk_split (base64_encode(file_get_contents($url)));
				$message .= "--".$boundary.$passage_ligne;
				$message .= "Content-Type: $typepiecejointe; name=\"$file_displayName\" ".$passage_ligne;
				$message .= "Content-Transfer-Encoding: base64 ".$passage_ligne;
				$message .= "Content-Disposition: attachment; filename=\"$file_displayName\" ".$passage_ligne;
				$message .= $passage_ligne;
				$message .= $data.$passage_ligne;
				$message .= $passage_ligne;
				echo "mail préparé pour caméraID: ".$id_cam."<br>";
			}
		} else if ($action == "archive" && $id_cam == $camID) {
			echo time_elapsed("Elapsed Processing time: ");
			chdir (getcwd()."/".$SnapDir);
			//if cam Disabled
			if (!$enabled_cam and $enable == 1) {
				echo 'Status : Camera '.$id_cam.' - '.$nomCam.' is Disabled => Enabeling <br>';
				$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&method=Enable&version='.$vCamera.'&cameraIds='.$camID.'&_sid='.$sid);
				sleep (5);  // wait 5 sec to finalyse the enabeling process
				$enabled_cam = true;
				echo 'Status : Camera '.$id_cam.' - '.$nomCam.' Enabled <br>';
			}
			if ($enabled_cam) {
				$FileName = "Snapshot_".$id_cam."_".$nomCam."_".strftime("%Y%m%d_%H%M%S").".jpg";
				SaveSnapshot ($http, $ip_ss, $port, $CamPath, $profileType, $sid, $id_cam, $FileName);
		}
			chdir (getcwd());
		} else if ($action == "snapshot" && $id_cam == $camID) { 
			$FileName = "Snapshot-Cam-".$id_cam.".jpg";
			SaveSnapshot ($http, $ip_ss, $port, $CamPath, $profileType, $sid, $id_cam, $FileName);
		}
		echo "<hr><br>";
	}
	ClosePage();
}
// SEND MAIL ---------------------------------------------------------------------------------
if ($action == "mail") {
	// Fin du mail
	$message .= "--".$boundary."--";
	// correction bug send mail
		// shell_exec('chmod +r /usr/syno/etc/synosmtp.conf');
	// send mail
	mail($mail_to, $subject, $message, $entete);
	echo "<br><b>Sujet : </b>" . $subject . "<br>";
	echo "<b>Mail envoyé. </b><br>";
}

//Display
//Send one image to client who requested to write all snapshots to disk
if ($displayID != 0){
	ob_clean();
	header('Content-Type: image/jpeg'); 
	// Read the contents of the snapshot and output it directly without putting it in memory first 
	readfile($http.'://'.$ip_ss.$dirname.'/Snapshot-Cam-'.$displayID.'.jpg');
}
if ($action != NULL) {exit();}

// Move camera to PTZ position ---------------------------------------------------------------
if ($cameraPtz != NULL && $cameraID != 0) {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
	$obj = json_decode($json);
	$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
	//echo $PtzPath.'<br>';
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=GoPreset&version=1&cameraId='.$cameraID.'&presetId='.$cameraPtz.'&_sid='.$sid);
	echo $json;
	ClosePage();
	exit();
}

// Patrol the camera ---------------------------------------------------------------
if ($cameraPatrol != NULL && $cameraID != 0) {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
	$obj = json_decode($json);
	$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
	//echo $PtzPath.'<br>';
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=RunPatrol&version=2&cameraId='.$cameraID.'&patrolId='.$cameraPatrol.'&_sid='.$sid);
	echo $json;
	ClosePage();
	exit();
}

// Zoom in|out the camera ---------------------------------------------------------------
if ($cameraZoom != NULL && $cameraID != 0) {
	//Get SYNO.SurveillanceStation.Ptz path (recommended by Synology for further update)
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=1&query=SYNO.SurveillanceStation.PTZ');
	$obj = json_decode($json);
	$PtzPath = $obj->data->{'SYNO.SurveillanceStation.PTZ'}->path;
	//echo $PtzPath.'<br>';
	if ($cameraZoom == "in" or $cameraZoom == "out") {
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=Zoom&version=1&cameraId='.$cameraID.'&control='.$cameraZoom.'&_sid='.$sid);
	} elseif ($cameraZoom == "inTot") {
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=Zoom&version=3&cameraId='.$cameraID.'&control=in&moveType=Start&_sid='.$sid);
	} elseif ($cameraZoom = "outTot") {
		$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$PtzPath.'?api=SYNO.SurveillanceStation.PTZ&method=Zoom&version=3&cameraId='.$cameraID.'&control=out&moveType=Start&_sid='.$sid);
	}
	echo $json;
	ClosePage();
	exit();
}


// Stream
if ($stream != NULL && $cameraID != 0) {
	if ($stream == "jpeg") {
		// stream = jpeg - Get Snapshot -------------------------------------------------------------------------------
		// Read the contents of the snapshot and output it directly without putting it in memory first
		ob_start();
		$length = readfile($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$cameraID.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid);
		// remove the lengt from the output
		$content = ob_get_clean();
		// Check if length is not too low, else check if cam enabled, regenerate a new SID
		if ($length <=50 ) {
			echo "File length: ".$length."<BR>";
			if ($debug) {echo time_elapsed("Error, NO SID : ");}
			echo "File empty, probable SID problem, Checking if camera enabled<br>";
			$obj = ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid);
			//$cam = $obj->data->cameras->$cameraID
				foreach($obj->data->cameras as $cam){
					$id_cam2 = $cam->id;
					if ($id_cam2 == $cameraID) {
						$nomCam = $cam->name;
						$status = $cam->status;
						//camStatus 0 … 4 Indicating the camera status • 0: Normal • 1: Disconnected • 2: Disabled • 3: Deleted • 4: Others
							if ($status == 0 && $cam->enabled) {
							echo "Camera ".$nomCam." IS enabled but not returning an image regenerating a new one<br><hr>";
							echo "Returned value: ".$content."<br>";
							echo "You may check if this images can be served by surveillance station.<br> Example: stream 3 (low quality) is not always available<br>";
							echo "</b>BE CAREFULL, YAPUSS IS NOW REGENERATING A NEW SID .... THIS IS SLOWING DOWN THE SCRIPT</b><br>";
							SessionSave("","","");
							} else {
							echo "camera status: ".$status."<br> if camera status <> 0, no image is ok.<br>";
							echo "0: Normal <br> 2: Disconnected <br> 3: Deleted <br> 4: Others <br> 5: Disabled <br> 6: Disconnected";
							ob_clean();
							header('Content-Type: image/jpeg'); 
							readfile($http.'://'.$ip_ss.$dirname.'/Camera-Status-'.$status.'.png');
							}
					}
				}
		} else {
			// Setting the correct header so the PHP file will be recognised as a JPEG file
			ob_clean();
			header('Content-Type: image/jpeg'); 
			echo $content;
		}
	} else if ($stream == "mjpeg") {
		// stream = mjpeg - Get MJPEG ---------------------------------------------------------------------------------
		$link_stream = 'http://' . $ip_ss . ':' . $port . '/webapi/SurveillanceStation/videoStreaming.cgi?api=SYNO.SurveillanceStation.VideoStream&version=1&method=Stream&cameraId=' . $cameraID . '&format=mjpeg&_sid=' . $sid;
		set_time_limit ( 60 ); 
		$r = ""; 
		$i = 0; 
		$boundary = "\n--myboundary"; 
		$new_boundary = "newboundary"; 
		$f = fopen ( $link_stream, "r" ); 
		if (! $f) { 
			// **** cannot open 
			echo "error <br>"; 
		} else { 
			// **** URL OK 
			header ( "Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0" ); 
			header ( "Cache-Control: private" ); 
			header ( "Pragma: no-cache" ); 
			header ( "Expires: -1" ); 
			header ( "Content-type: multipart/x-mixed-replace;boundary={$new_boundary}" ); 
			while ( true ) { 
				while ( substr_count ( $r, "Content-Length:" ) != 2 ) { 
					$r .= fread ( $f, 32 ); 
				} 
				$pattern = "/Content-Length\:\s([0-9]+)\s\n(.+)/i"; 
				preg_match ( $pattern, $r, $matches, PREG_OFFSET_CAPTURE ); 
				$start = $matches [2] [1]; 
				$len = $matches [1] [0]; 
				$end = strpos ( $r, $boundary, $start ) - 1; 
				$frame = substr ( "$r", $start + 2, $len ); 
				print "--{$new_boundary}\n"; 
				print "Content-type: image/jpeg\n"; 
				print "Content-Length: ${len}\n\n"; 
				print $frame; 
				usleep ( 40 * 1000 ); 
				$r = substr ( "$r", $start + 2 + $len ); 
			}
		}
		fclose ( $f ); 
	}
	exit();
}

// Functions
	// Read Session File -------------------------------------------------------------------------
function SessionRead() {
	global $SessionFile, $CamPath, $sid, $AuthPath;
	//Read the $SessionFile file to get the properties
	if (file_exists($SessionFile)) {
	$objData = file_get_contents($SessionFile);
	$SessionSave = unserialize($objData);
		if (!empty($SessionSave)) {
			$CamPath = $SessionSave->CamPath;
			$sid = $SessionSave->sid;
			$AuthPath = $SessionSave->AuthPath;
		}
	}
}
	// Write Session File to disk and create if not present --------------------------------------
function SessionSave($FCamPath, $Fsid, $FAuthPath) {
	global $SessionFile;
	if (file_exists($SessionFile)) {
		if ($debug) {echo time_elapsed("Saving new SID To .session :");}
		$SessionSave = new stdClass();
		$SessionSave->CamPath = new stdClass();
		$SessionSave->sid = new stdClass();
		$SessionSave->AuthPath = new stdClass();
		//Write Variables to text file $SessionFile
		$SessionSave->CamPath = $FCamPath;
		$SessionSave->sid = $Fsid;
		$SessionSave->AuthPath = $FAuthPath;
		$objData = serialize($SessionSave);
		if (is_writable($SessionFile)) {
			$fp = fopen($SessionFile, "w"); 
			fwrite($fp, $objData); 
			fclose($fp);
		}
	} else {
		touch($SessionFile);
		$SessionSave = new stdClass();
		$SessionSave->CamPath = new stdClass();
		$SessionSave->sid = new stdClass();
		$SessionSave->AuthPath = new stdClass();
		//Write Variables to text file $SessionFile
		$SessionSave->CamPath = $FCamPath;
		$SessionSave->sid = $Fsid;
		$SessionSave->AuthPath = $FAuthPath;
		$objData = serialize($SessionSave);
		if (is_writable($SessionFile)) {
			$fp = fopen($SessionFile, "w"); 
			fwrite($fp, $objData); 
			fclose($fp);
		}
	}
	ClosePage();
}

	//list & status of known cams ----------------------------------------------------------------
function ListCams($http, $ip_ss, $port, $CamPath, $vCamera, $sid) {
	$json = file_get_contents($http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?api=SYNO.SurveillanceStation.Camera&version='.$vCamera.'&method=List&_sid='.$sid);
	//echo $json;
	$obj = json_decode($json);
	return $obj;
}
	// To trace php execution time ---------------------------------------------------------------
function time_elapsed($affich) {
	
	$executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
return "$affich "." $executionTime"." Seconds <br>";
}
	// Save snapshot
function SaveSnapshot ($http, $ip_ss, $port, $CamPath, $profileType, $sid, $id_cam, $FileName) {
	// Setting the correct header so the PHP file will be recognised as a JPEG file 
	$SnapshotUrl = $http.'://'.$ip_ss.':'.$port.'/webapi/'.$CamPath.'?&version=9&id='.$id_cam.'&api=SYNO.SurveillanceStation.Camera&method="GetSnapshot"&profileType='.$profileType.'&_sid='.$sid;
	$ch = curl_init($SnapshotUrl);
	$fp = fopen($FileName, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}
	// on ferme la page qui vient d'être génrée
function ClosePage() {
	echo "<script>window.close();</script>";
}
?>
