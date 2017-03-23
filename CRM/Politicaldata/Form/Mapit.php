<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Politicaldata_Form_Mapit extends CRM_Core_Form {
  public function buildQuickForm() {
  	


    // add form elements
    $this->add(
      'text', // field type
      'mapit_api_key', // field name
      'Mapit API Key', // field label
      array('size' => 60,),
      TRUE // is required
    );
    
    //custom field numbers
    // $this->add(
    //  'text', // field type
    //  'constituency_custom', // field name
    //  'Custom Field Number: Constituency', // field label
    //  array('size' => 4)
    //);   
    
    
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

	  //set page title
	  CRM_Utils_System::setTitle(ts('Mapit Settings'));
	  	
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

	function setDefaultValues() {

  	//get existing api key, if there is one
  	$result = civicrm_api3('Setting', 'get', array(
		  'sequential' => 1,
		  'return' => array("mapitkey"),
		));
				
		$api_key = $result['values'][0]['mapitkey'];
		$defaults['mapit_api_key'] = $api_key;
				
    return $defaults;

	}

  public function postProcess() {
    $values = $this->exportValues();
    
    $apikey = $values['mapit_api_key'];

      
 		if ($apikey) {
 			
 			$params = array(
        'mapitkey' => $apikey,
        'sequential' => 1,
      );
 			
	    try {
	      $result = civicrm_api3('Setting', 'create', $params);
	    }
	    
	    catch (CiviCRM_API3_Exception $e) {
	      
	      $errorMessage = $e->getMessage();
	      $errorCode = $e->getErrorCode();
	      $errorData = $e->getExtraParams();
	      
	      CRM_Core_Session::setStatus(ts('Something went wrong when adding settings: "%1"', array(
	      	1 => $errorMessage
	    	)));
	      
	    }
	    
  	  CRM_Core_Session::setStatus(ts('API key saved'));
	  
  	}
  	
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
