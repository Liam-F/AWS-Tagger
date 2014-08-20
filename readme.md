AWS Tagger
======

The AWS Tagger works in complement with the [AWS Tag Auditor](https://github.com/kpei/AWS-Tag-Auditor), the tagger reads through a tab-delimited file and makes the appropriate tag corrections.  The usual workflow is using the tag auditor to output in `file` format, edit the tags with a productivity software such as Excel, and pass through the file again for the AWS Tagger to make the adjustments.  The Tagger currently supports EC2, AutoScaling groups and S3 buckets.  

Requirements
------
  
* PHP >= 5
* AWS SDK - Ensure that AWS SDK folder `/vendor` is present in the same folder.  This can be changed on line 2 of the index.php

Usage
------
  
Using the command line:  
` php index.php -c configFileLocation `  

**-c** - Specify the configuration file location. This is a formatted .YAML file with Amazon keys, and resource information, see section **Configuration File** for more information.  

Configuration File
------
  
The configuration fine is a .YAML format file.  This yaml format is used for easy editing as the markup is in an easy human readable format. [Read more .YAML](http://www.yaml.org/start.html)  

Below is a structure of the configuration file, note that only one tag file and platform may be passed on each run:

__Keys__:

* Secret\_Key \- Your AWS Developer Secret Key  
* Access\_Key \- Your AWS Developer Access Key  

__Platform__: The platform to tag.  Currently, the only possible values are: EC2, ASG and S3  
__TagFile__: The final tab-delimited tag file to scan through and make the changes.  In regards to region differences and EC2/ASG clients, the first column of the tag file should contain the region the instance/group is located in.

### Example YAML configuration file

    Keys:
      Secret_Key: *********************************
      Access_Key: *******************
    Platform: S3
    TagFile: S3_Dev1.txt


Creator: Kevin Pei  
Copyright 2014  
[MIT License](https://tldrlegal.com/license/mit-license#summary)