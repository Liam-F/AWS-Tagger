<?php
use Aws\Ec2\Ec2Client;
use Aws\Common\Enum\Region;
use Aws\S3\S3Client;
use Aws\AutoScaling\AutoScalingClient;

//starting position for the inputted file
define("EC2_TAG_STARTING_POSITION", 3);
define("S3_TAG_STARTING_POSITION", 1);
define("ASG_TAG_STARTING_POSITION", 2);

function initClient($type, $accessKey, $secretKey, $region){
	switch ($type) {
	case "EC2":
		return 	Ec2Client::factory(array('key' => $accessKey, 'secret' => $secretKey, 'region' => $region));
		break;
	case "ASG":
		return 	AutoScalingClient::factory(array('key' => $accessKey, 'secret' => $secretKey, 'region' => $region));
		break;
	case "S3":
		return 	S3Client::factory(array('key' => $accessKey, 'secret' => $secretKey));
		break;
	}
}

function buildTagRequest($delimitedInfo){
				$request = array("DryRun" => false, //change later
								 "Resources" => array(
													$delimitedInfo[2]),
								 "Tags" => array());
				//File the tag arrays
				$j = 0; //temp iterator for the specific position of key/value
				for($i=EC2_TAG_STARTING_POSITION; $i < count($delimitedInfo); $i+=2){
					if(trim($delimitedInfo[$i+1]) !== "-"){ //better to not add than actually add
						$request["Tags"][$j]["Key"] = $delimitedInfo[$i];
						$request["Tags"][$j]["Value"] = trim($delimitedInfo[$i+1]);
						
						echo $delimitedInfo[2].": CREATE/UPDATE ".$delimitedInfo[$i]." -> ".trim($delimitedInfo[$i+1])."\n"; //logging
					} else 
						echo $delimitedInfo[2].": IGNORE ".$delimitedInfo[$i]." Due to empty field \n";
						
					$j++;
				}
				
				return $request;

}

function buildASGTagRequest($delimitedInfo){
				$request = array("Tags" =>
								 array());
				
			$j = 0; //temp iterator for the specific position of key/value
			for($i=ASG_TAG_STARTING_POSITION; $i < count($delimitedInfo); $i+=2){
				if(trim(trim($delimitedInfo[$i+1])) !== "-"){ //better to not add than actually add
					$request["Tags"][$j]["Key"] = $delimitedInfo[$i];
					$request["Tags"][$j]["Value"] = trim(trim($delimitedInfo[$i+1]));
					$request["Tags"][$j]["ResourceId"] = $delimitedInfo[1];
					$request["Tags"][$j]["ResourceType"] = "auto-scaling-group";
					$request["Tags"][$j]["PropagateAtLaunch"] = true;
					
					echo $delimitedInfo[1].": CREATE/UPDATE ".$delimitedInfo[$i]." -> ".trim(trim($delimitedInfo[$i+1]))."\n"; //logging
				} else 
					echo $delimitedInfo[1].": IGNORE ".$delimitedInfo[$i]." Due to empty field \n";
						
				$j++;
			}		
			
			return $request;
}			

function buildS3TagRequest($delimitedInfo){
			$request = array("Bucket" => $delimitedInfo[0],
							 "TagSet" => array());
							 
			$j = 0; //temp iterator for the specific position of key/value
			for($i=S3_TAG_STARTING_POSITION; $i < count($delimitedInfo); $i+=2){
				if(trim($delimitedInfo[$i+1]) !== "-"){ //better to not add than actually add
					$request["TagSet"][$j]["Key"] = $delimitedInfo[$i];
					$request["TagSet"][$j]["Value"] = trim($delimitedInfo[$i+1]);
						
					echo $delimitedInfo[0].": CREATE/UPDATE ".$delimitedInfo[$i]." -> ".trim($delimitedInfo[$i+1])."\n"; //logging
				} else 
					echo $delimitedInfo[0].": IGNORE ".$delimitedInfo[$i]." Due to empty field \n";
						
				$j++;
			}		
			return $request;
}
?>