<?php

declare(strict_types=1);

require_once('config.php');


//ONLY HAVE TO READ THE CURRENT FEATURE FLAG THAT IS TO BE USED
function getFeatureFlag():bool
{
    //first give some variable the location of the file it will take feature flag info i.e. featureFlag.json

    $featureFlagFilePath=__DIR__ . '/featureFlag.json';  /////??????CONCATENATION USING . !!!!!!!!!

    //now what if this file not even exist- check first girl

    if (!file_exists($featureFlagFilePath))
        {
            return QUESTION_ORDER_RANDOM_ENABLED_DEFAULT;
        } //hehe return default only

    //now what if it exists which it will ofc since I already made the file hehe
    //so yes it exists now
    //now find what is in it
    //so how will u find it
    //by getting the content ofc
    //bring its content

    $featureFlagFileContent=file_get_contents($featureFlagFilePath);


    //hurray brought content nice
    //now lets move to next step

    //so now check whether there is any content here or not //which it will be but still CHECK IT OUT

    if ($featureFlagFileContent===false)
        {
            return QUESTION_ORDER_RANDOM_ENABLED_DEFAULT;
        }

    //now if not false then
    //what it might be
    //to read the value of the special enabled field from that file right
    //so now access that field and brings its contents!!!!


    $featureFlag=json_decode($featureFlagFileContent, true);

    //now decoded now find
    //now it might also be empty so again check
    
    if (!is_array($featureFlag))
        {
            return QUESTION_ORDER_RANDOM_ENABLED_DEFAULT;
        }


    $featureFlagName='questionOrdering';
    //now finally u can return hehe
    //the actual field specific value
    return (bool) $featureFlag[$featureFlagName]['enabled'];

}


?>