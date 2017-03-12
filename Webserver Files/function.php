<?php
require("db.php");

function output($username, $table) {

	global $stuff;
	global $mysqli;
      
	
	$username = mysqli_real_escape_string($mysqli, $username);
	$query = "SELECT * FROM $table WHERE username = '$username' LIMIT 1";

	if ($username == 'All') {
            $query = "SELECT SUM(runtime) as runtime, SUM(var1) as var1, SUM(var2) as var2, SUM(var3) as var3, SUM(var4) as var4 	    FROM $table";
	}
	
	$result = mysqli_query($mysqli, $query);
                      

	
	if(mysqli_num_rows($result)==1) {
	
		// Vars to write
		$row = mysqli_fetch_assoc($result);
		$human_seconds = convert_seconds($row["runtime"]);
		
		
		// Put the name of the table in your SQL table it is going to access
		// Then include the background image on your server that you want it to use
                   if ($table == ""){
     
                      $image = imageCreateFromPNG("Image.png") or die("Cannot select the correct background image. Please 				contact the script writer."); 
                    }
                    if ($table == ""){
                     $image = imageCreateFromPNG("Image.png") or die("Cannot select the correct background image. Please 			contact the script writer."); 
                    }
                     if ($table == ""){
                    
                      $image = imageCreateFromPNG("Image.png") or die("Cannot select the correct background image. Please 			contact the script writer."); 
                    }
		
		// All coords/data which is going to be written on the image.
		
                    $loc = array(
		
			//    string to write,      font size,      x coord,       text_area_width,   y coord
			array($username,            25,             185,             150,                 90),
			array($human_seconds,       25,             240,             150,                 150),
			array(adjust($row["var1"]), 25,             700,             150,                 90),
			array(adjust($row["var2"]), 25,             700,             150,                 150),
			array(adjust($row["var3"]), 12,               0,               0,                   0),
			array(adjust($row["var4"]), 12,               0,               0,                   0)
			
			// leave the text_area_width at 0  if you do not want the text to be centered.
		);
		
		// Path to font you wish to use
		$font = 'arial.ttf';

		// Text color in RGB values (currently white)
		$color = ImageColorAllocate($image, 255, 255, 255);
		
		// PNG Transparency settings
		imagecolortransparent($image, $color);
		imagesavealpha($image, true);
		
		foreach ($loc as $lineToWrite) {
			// Size of the text we're going to write
			$BoundingBox = imagettfbbox($lineToWrite[1], 0, $font, $lineToWrite[0]);
			
			if ($lineToWrite[3] > 0) {
				$text_width = $BoundingBox[4] - $BoundingBox[6]; // Top right 'x' minus Top left 'x' 
				$x = $lineToWrite[2] + (($lineToWrite[3] - $text_width) / 2 );
			} else
				$x = $lineToWrite[2];
			
			imagettftext($image, $lineToWrite[1], 0, $x, $lineToWrite[4], $color, $font, $lineToWrite[0]);
		}
		
		// now save a copy of the image to the /users/ folder for caching.
		imagepng($image, "users/".$table."---" . $username. ".png"); 
		
		// send the image to the browser
		header("Content-type: image/png");
		
 		imagepng($image);
		imagedestroy($image);
	} else {
		echo "Username not found";
	}
}

function input($scriptTable,$username, $runtime, $var1, $var2, $var3, $var4) {
      
	
	global $mysqli;
	
	$username = mysqli_real_escape_string($mysqli, $username);
	$runtime = mysqli_real_escape_string($mysqli, $runtime);
	$var1 = mysqli_real_escape_string($mysqli, $var1);
	$var2 = mysqli_real_escape_string($mysqli, $var2);
	$var3 = mysqli_real_escape_string($mysqli, $var3);
	$var4 = mysqli_real_escape_string($mysqli, $var4);
	
	$query = "SELECT * FROM $scriptTable WHERE username = '$username'";
	
	$result = mysqli_query($mysqli, $query);
	
	if(mysqli_num_rows($result)==1) {
	
		if(empty($username) || empty($runtime)) {
			echo "You are missing some parameters.";
		} else {
			$row = mysqli_fetch_assoc($result);
			$runtime = $runtime + $row["runtime"];
			$var1 = $var1 + $row["var1"];
			$var2 = $var2 + $row["var2"];
			$var3 = $var3 + $row["var3"];
			$var4 = $var4 + $row["var4"];
			$update_query = "UPDATE $scriptTable SET runtime='$runtime', var1='$var1', var2='$var2', var3='$var3', var4='$var4' WHERE username='$username'";
			$update_result = mysqli_query($mysqli, $update_query);
			if(!$update_result) {
				echo "Error performing query.";
			} else {
			
				// delete the cached image since the data has been updated now
				if (file_exists("users/" .$scriptTable . $username.".png")) {
        			unlink("users/" .$scriptTable . $username . ".png");
				}
				
				// delete the 'all users' image as well since a user has updated.
				if (file_exists("users/All Users.png")) {
                    unlink("users/" .$scriptTable ."&All.png");
                }
			}
		}
	}
	else if(mysqli_num_rows($result)==0) {
		if(empty($username) || empty($runtime)) {
			echo "You are missing some parameters.";
		} else {
			$insert_query = "INSERT INTO $scriptTable (username, runtime, var1, var2, var3, var4) VALUES ('$username', '$runtime', '$var1', '$var2', '$var3', '$var4')";
			$insert_result = mysqli_query($mysqli, $insert_query);
			if(!$insert_result) {
				echo "Error performing query.";
			}
		}
	}
	else {
		echo "Error performing query.";
	}
}

function convert_seconds($d)
{
    $periods = array( 'D'    => 86400,
                      'H'   => 3600,
                      'M' => 60,
                      'S' => 1 );
    $parts = array();
    foreach ( $periods as $name => $dur )
    {
        $div = floor( $d / $dur );
         if ( $div == 0 )
                continue;
         else if ( $div == 1 )
                $parts[] = $div . "" . $name;
         else
                $parts[] = $div . "" . $name;
         $d %= $dur;
    }
    $last = array_pop( $parts );
    if ( empty( $parts ) )
        return $last;
    else
        return join( ',', $parts ) . "," . $last;
}

function adjust($int) {
	if ($int > 1000000) {
		return ((int) ($int / 1000000))."M";
	}
	//un-comment this section if you want it to display K when numbers are in the thousands
	//if ($int > 1000) {
	//	return ((int) ($int / 1000))."K";
	//}
	
	return $int;
}
?>
