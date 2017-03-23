<?php

require_once 'politicaldata.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function politicaldata_civicrm_config(&$config) {
  _politicaldata_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function politicaldata_civicrm_xmlMenu(&$files) {
  _politicaldata_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function politicaldata_civicrm_install() {
  _politicaldata_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function politicaldata_civicrm_uninstall() {
  _politicaldata_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function politicaldata_civicrm_enable() {
  _politicaldata_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function politicaldata_civicrm_disable() {
  _politicaldata_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function politicaldata_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _politicaldata_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function politicaldata_civicrm_managed(&$entities) {
  _politicaldata_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function politicaldata_civicrm_caseTypes(&$caseTypes) {
  _politicaldata_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function politicaldata_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _politicaldata_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function politicaldata_civicrm_post($op, $objectName, $id, &$objectref){
	if ($objectName == 'Address' and ($op == 'create' or $op == 'edit' or op == 'restore')) {
			
		$contact_id = $objectref->contact_id;
		$postcode = $objectref->postal_code;
		$country = $objectref->country_id;
		
		//bail if no postcode
  	if (!isset($postcode) or empty($postcode)) { 
  		return;
  	}
  	
  	//bail if the country explicitly isn't the UK. If there isn't a country try anyway
	 	if (isset($country) AND $country != 1226) { 
	 		return; 
	 	} 

			
		//bail if there's no contact ID (creating locations on the Manage Event page seems to do this)
		if (!$contact_id) {
			return;
		}

		//bail if it's not their primary addresses	
		if ($objectref->is_primary != 1){ 
			return;
		}

		//get API key from db (set via civicrm/mapit/settings)
  	$result = civicrm_api3('Setting', 'get', array(
		  'sequential' => 1,
		  'return' => array("mapitkey"),
		));
		
		
		$apikey = $result['values'][0]['mapitkey'];
				
		//check whether we're using lat/long or postcode
		$usingLatLong = false;


		//BHA: check if it's a local group
		$contactType = civicrm_api3('Contact', 'get', array(
		  'sequential' => 1,
		  'return' => "contact_sub_type",
		  'id' => $contact_id,
		));
		$contactType = $contactType[values][0]['contact_sub_type'][0];
		if ($contactType == 'LocalGroup') {
			$usingLatLong = true;
		}
		
		if ($usingLatLong){
			
			$lat = $objectref->geo_code_1;
			$long = $objectref->geo_code_2;
			$url='https://mapit.mysociety.org/point/4326/' . $long . ',' . $lat;
			
			//if api key, use it. Otherwise this'll default to the no-api-key 50/day version	
			if ($apikey) {
				$url .= '?api_key=' . $apikey; 
			}
			
		} else { //postcodes
			
			//tidy up postcode
			$postcode=str_replace(' ','',$postcode);
			
			$url='https://mapit.mysociety.org/postcode/' . $postcode;
			if ($apikey) {
				$url .= '?api_key=' . $apikey; 
			}
			
		}
		
		
		//fetch data from mapit

		//Initiate curl
		$ch = curl_init();
		//Return the response as a string (if false it prints the response)
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//set 4 seconds max wait time
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,4);
		//set the url
		curl_setopt($ch, CURLOPT_URL,$url);
		//execute
		$result=curl_exec($ch);
		//convert result json to array
		$politicaldata = json_decode($result, true);
		
		//bail if no data
		if(!$politicaldata) {
			error_log('mapit returning no data');
			
			if(curl_errno($ch)){
		   error_log('Curl error: ' . curl_error($ch));
			}
			
			$foo = print_r(curl_getinfo($ch),true);
			error_log($foo);
			
			return;
		}
			
		//get appropriate ward and council, depending on whether it's a unitary authority
		//TODO: this doesn't work for lat/long lookups
		$isward = 1;
		if (politicaldata_array_key_exists_r('county',$politicaldata)) {
			$isward = 0;
			$wardcountyid = $politicaldata['shortcuts']['ward']['county'];
			$warddistrictid = $politicaldata['shortcuts']['ward']['district'];
		} else {
			$wardid = $politicaldata['shortcuts']['ward'];
		}
		
		$iscouncil = 1;
		if (politicaldata_array_key_exists_r('county',$politicaldata)) {
			$iscouncil = 0;
			$councilcountyid = $politicaldata['shortcuts']['council']['county'];
			$councildistrictid = $politicaldata['shortcuts']['council']['district'];
		} else {
			$councilid = $politicaldata['shortcuts']['council'];
		}
		
		//Parliamentary Constituency
		$constituencyid = $politicaldata['shortcuts']['WMC'];

		
		//assign values for Council and Local Authority
		$constituency = $politicaldata['areas'][$constituencyid]['name'];
		
		if ($iscouncil == 1) {
			$highestla = $politicaldata['areas'][$councilid]['name'];
		} else {
			$highestla = $politicaldata['areas'][$councilcountyid]['name'];
		}
		
		if ($iscouncil == 1) {
			$secondhighestla = '';
		} else {
			$secondhighestla = $politicaldata['areas'][$councildistrictid]['name'];
		}
		
		if ($isward == 1) {
			$ward = $politicaldata['areas'][$wardid]['name'];
		} else {
			$ward = $politicaldata['areas'][$warddistrictid]['name'];
		}
				
				
		//for all others it's identical (afaik), so can search directly
		//starts working for latlong at this point, as latlong lookups only have the 'areas' array
		if($politicaldata['areas']) {
			$areas = $politicaldata['areas'];	
		} else {
			$areas = $politicaldata;
		}
		
		//European Region
		$eurvalue = politicaldata_searchForType('EUR',$areas);
		$regionalauthority = $areas[$eurvalue]['name'];
		$ukcountry = $areas[$eurvalue]['country_name'];
		
		//Welsh Assembly Region
		$waevalue = politicaldata_searchForType('WAE',$areas);
		$welshassemblyregion = $areas[$waevalue]['name'];
		
		//Welsh Assembly Constituency
		$wacvalue = politicaldata_searchForType('WAC',$areas);
		$welshassemblycon = $areas[$wacvalue]['name'];
		
		//Scottish Parliament Constituency
		$spcvalue = politicaldata_searchForType('SPC',$areas);
		$scottishparlcon = $areas[$spcvalue]['name'];

		//Scottish Parliament Region
		$spevalue = politicaldata_searchForType('SPE',$areas);
		$scottishparlreg = $areas[$spevalue]['name'];
		
		//London Assembly Constituency
		$lacvalue = politicaldata_searchForType('LAC',$areas);
		$londonassembly = $areas[$lacvalue]['name'];
		
		 // assign to the custom fields
		 $customParams = array(
			 "id" => $contact_id,
			 "sequential" => 1,
			 "custom_308" => $constituency, //Parliamentary Constituency
			 "custom_305" => $highestla, //Highest Local Authority
			 "custom_306" => $secondhighestla, //Second Highest Local Authority
			 "custom_304" => $ward, //Ward
			 "custom_307" => $regionalauthority, //Regional Authority
			 "custom_310" => $welshassemblycon, //Welsh Assembly Constituency
	 		 "custom_311" => $welshassemblyregion, //Welsh Assembly Region
	 		 "custom_312" => $scottishparlcon, //Scottish Parliamentary Contituency
	 		 "custom_313" => $scottishparlreg, //Scottish Parliament Region
	 		 "custom_314" => $londonassembly, //London Assembly Constituency
	 		 "custom_734" => $ukcountry, //UK Country
		 );
              
    //actually save to DB   		
		$result = civicrm_api3('Contact', 'create', $customParams);
	}
}

function politicaldata_array_key_exists_r($needle, $haystack) {
    $result = array_key_exists($needle, $haystack);
    if ($result) return $result;
    foreach ($haystack as $v) {
        if (is_array($v)) {
            $result = politicaldata_array_key_exists_r($needle, $v);
        }
        if ($result) return $result;
    }
    return $result;
}

function politicaldata_searchForType($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['type'] === $id) {
           return $key;
       }
   }
   return null;
}