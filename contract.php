<?php
/*-------------------------------------------------------------+
| SYSTOPIA Contract Extension                                  |
| Copyright (C) 2017 SYSTOPIA                                  |
| Author: M. McAndrew (michaelmcandrew@thirdsectordesign.org)  |
|         B. Endres (endres -at- systopia.de)                  |
| http://www.systopia.de/                                      |
+--------------------------------------------------------------*/

require_once 'contract.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contract_civicrm_config(&$config) {
  _contract_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contract_civicrm_xmlMenu(&$files) {
  _contract_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contract_civicrm_install() {
  _contract_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function contract_civicrm_postInstall() {
  _contract_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contract_civicrm_uninstall() {
  _contract_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contract_civicrm_enable() {
  _contract_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contract_civicrm_disable() {
  _contract_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function contract_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contract_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contract_civicrm_managed(&$entities) {
  _contract_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contract_civicrm_caseTypes(&$caseTypes) {
  _contract_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contract_civicrm_angularModules(&$angularModules) {
  _contract_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contract_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contract_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function contract_civicrm_buildForm($formName, &$form) {


  switch ($formName) {

    // Membership form in view mode
    case 'CRM_Member_Form_MembershipView':
      $id =  CRM_Utils_Request::retrieve('id', 'Positive', $form);
      $modifyForm = new CRM_Contract_FormUtils($form, $id);
      $modifyForm->showPaymentContractDetails();
      break;

    // Membership form in add or edit mode
    case 'CRM_Member_Form_Membership':
      if(in_array($form->getAction(), array(CRM_Core_Action::UPDATE, CRM_Core_Action::ADD))){

        // Standard form
        if(!isset($form->_groupTree)){
          $formUtils = new CRM_Contract_FormUtils($form, $form->_id);
          $formUtils->removeMembershpEditDisallowedCoreFields();
        // Custom data version
        }else{
          $formUtils = new CRM_Contract_FormUtils($form, $form->_entityId);
          $formUtils->removeMembershpEditDisallowedCustomFields();
        }
      }
      break;

    //Activity form in view mode
    case 'CRM_Activity_Form_Activity':
      if($form->getAction() == CRM_Core_Action::VIEW){
        $id =  CRM_Utils_Request::retrieve('id', 'Positive', $form);
        $modifyForm = new CRM_Contract_FormUtils($form, $id, 'Activity');
        $modifyForm->showPaymentContractDetails();
      }
      break;

  }
}

function contract_civicrm_links( $op, $objectName, $objectId, &$links, &$mask, &$values ){
  switch ($objectName) {
    case 'Membership':
      $alter = new CRM_Contract_AlterMembershipLinks($objectId, $links, $mask, $values);
      $alter->removeActions(array(CRM_Core_Action::UPDATE, CRM_Core_Action::RENEW));
      $alter->addHistoryActions();
      break;
    }
}

function contract_civicrm_pre($op, $objectName, $id, &$params){
  if($objectName == 'Membership' && in_array($op, array('create', 'edit'))){
    $BAOWrapper = CRM_Contract_Modify_BAOWrapper::singleton();
    $BAOWrapper->pre($id, $params);
  }
}

function contract_civicrm_post($op, $objectName, $id, &$objectRef){
  if($objectName == 'Membership' && in_array($op, array('create', 'edit'))){
    $BAOWrapper = CRM_Contract_Modify_BAOWrapper::singleton();
    $BAOWrapper->post($id);
  }
}

function contract_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  //&apiWrappers is an array of wrappers, you can add your(s) with the hook.
  // You can use the apiRequest to decide if you want to add the wrapper (eg. only wrap api.Contact.create)
  if($apiRequest['entity'] == 'Membership' & $apiRequest['action'] == 'create'){
    $wrappers[] = CRM_Contract_Modify_APIWrapper::singleton();
  }
}
