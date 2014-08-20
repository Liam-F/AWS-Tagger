<?php
require "vendor/autoload.php"; // you can change this

require "lib/Spyc.php";
require "lib/tagatron_funcs.php";

//Get Options
$opt = getopt("c:");
if(!$opt["c"])
	die("Usage: \n php index.php -c[onfig]");
	
//Get Config file
$config = spyc_load_file($opt["c"]);
//var_dump($config);	
$ak = $config["Keys"]["Access_Key"];
$sk = $config["Keys"]["Secret_Key"];
$type = $config["Platform"];
$tagFilePath = $config["TagFile"];
$Client = null; //client to interact with AWS

function buildAndSubmit($delimitedInfo, $type, $Client){
				switch ($type){
				case "EC2": 
					$req = buildTagRequest($delimitedInfo);
					try {
						$Client->createTags($req);
					} catch (Exception $e){
						if($e->getMessage()!=="You must specify one or more tags to create") //tagatron_funcs already takes care of that
							echo "!!! ERROR:" . $e->getMessage()."\n";
					}
					break;
				case "ASG":
					$req = buildASGTagRequest($delimitedInfo); 
					try {
						$Client->createOrUpdateTags($req);
					} catch (Exception $e){
						if($e->getMessage()!=="You must specify one or more tags to create")
							echo "!!! ERROR:" . $e->getMessage()."\n";
					}
					break;
				}
}

if(file_exists($tagFilePath)){
	//Open the input file
	$tagFile = fopen($tagFilePath, "r");
	
	while(!feof($tagFile)){
		$line = fgets($tagFile);
			if(empty(trim($line))){
				echo "Empty line detected \n";
				continue;
			}
		$delimitedInfo = explode("\t",$line);

		if(!is_null($Client)){ //separated incase of an error
			if($Client->getRegion() == $delimitedInfo[0]){ //here, delimitedInfo[0] is the region if EC2/ASG
				//Build the request given that: Region is current region that the client is on, client isn't null
				buildAndSubmit($delimitedInfo, $type, $Client);
			} else if($type !== "S3") {
				//Build the engine/request given that: Region differential, client isn't Null
				$Client = initClient($type, $ak, $sk, $delimitedInfo[0]);
				buildAndSubmit($delimitedInfo, $type, $Client);
			} else if($type == "S3"){
				//client isn't null
				$req = buildS3TagRequest($delimitedInfo);
				try {
				$Client->putBucketTagging($req);
				} catch (Exception $e){
						if($e->getMessage()!=="You must specify one or more tags to create")
							echo "!!! ERROR:" . $e->getMessage()."\n";				
				}
			}
		} else if($type !== "S3"){
				//Build the engine/request given that: client is null
				$Client = initClient($type, $ak, $sk, $delimitedInfo[0]);
				buildAndSubmit($delimitedInfo, $type, $Client);
		} else if($type == "S3"){
				//client is null
				$Client = initClient($type, $ak, $sk, null);
				$req = buildS3TagRequest($delimitedInfo);
				try {
				$Client->putBucketTagging($req);
				} catch (Exception $e){
						if($e->getMessage()!=="You must specify one or more tags to create")
							echo "!!! ERROR:" . $e->getMessage()."\n";				
				}
		}
		
	}
	
	fclose($tagFile);
}
?>