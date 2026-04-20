<?php

// php index.php -TO RUN THIS FILE IN TERMINAL

//data structures to be used
$short_url_to_original=[];
$click_counts=[];
$unique_users=[];

echo "URL SHORTENER\n";


//to make short code of original
// we can take first 8 characters of it to make it short

function make_url_short($orig_url)
{
    return substr(md5($orig_url), 0, 8);
}

//now how to store short to original url in hashmap

function save_url($orig_url, &$short_url_to_original)
{
    $short_url=make_url_short($orig_url);
    $short_url_to_original[$short_url]=$orig_url;
    return $short_url;
}

//which user visited which url how many times now

function visit_url($short_url, $userip, $short_url_to_original, &$click_counts, &$unique_users)
{
    if (!isset($short_url_to_original[$short_url]))
        {
            echo "Invalid URL!! \n";
            return;
        }
    
    //now if the url is valid
    //then it must be incremented in click counts since user has clicked

    if (!isset($click_counts[$short_url]))
        {
            $click_counts[$short_url]=0;
        }
    $click_counts[$short_url]++;

    //now we need to check whether the current user is unique or not
    // if unique we will add it in set of unique visitors

    if (!isset($unique_users[$short_url]))
        {
            $unique_users[$short_url]=[];
        }

    if (!isset($unique_users[$short_url][$userip])) 
        {
            $unique_users[$short_url][$userip] = true;
        }


    echo "Wait for the Redirect to " . $short_url . " which is the shortened URL for " . $short_url_to_original[$short_url] . "\n";
}


function results($short_url, $click_counts, $unique_users)
{
    $total_clicks = $click_counts[$short_url] ?? 0;
    $total_unique = isset($unique_users[$short_url]) ? count($unique_users[$short_url]) : 0;

    return 
    [
        "clicks" => $total_clicks,
        "uniques" => $total_unique
    ];
}


//DEMO INPUTS AND OUTPUTS

// Creating short URL for original one
$short_url = save_url("https://example.com/page", $short_url_to_original);
echo "Shortened URL :: $short_url\n\n";

// Example visits for hte url
visit_url($short_url, "1.1.1.1", $short_url_to_original, $click_counts, $unique_users);
visit_url($short_url, "2.2.2.2", $short_url_to_original, $click_counts, $unique_users);
visit_url($short_url, "1.1.1.1", $short_url_to_original, $click_counts, $unique_users);

// Show final results
$result= results($short_url, $click_counts, $unique_users);

echo "\nRESULTS:\n";
print_r($result);

?>