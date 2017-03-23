<?php

$url='http://mapit.mysociety.org/postcode/cv376dt';

//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$result=curl_exec($ch);

// Will dump a beauty json :3
$politicaldata = json_decode($result, true);
print_r($politicaldata);
// $politicaldata2 = array_values($politicaldata);
// print_r($politicaldata2);
echo '<br/><br />';
echo $politicaldata['shortcuts']['WMC'] . '<br />';
echo $politicaldata['northing']['WMC'];

$constituencyid = $politicaldata['shortcuts']['WMC'];
$constituency = $politicaldata['areas'][$constituencyid]['name'];
echo '<br />Here is some data:' . $constituency;

//$array["threads"][13/* thread id */]["title"/* thread key */]
//$array["threads"][13/* thread id */]["content"/* thread key */]["content"][23/* post id */]["message" /* content key */];

?>