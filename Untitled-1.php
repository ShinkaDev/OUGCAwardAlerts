<?php 

$plugins->add_hook('global_start', 'award_alert_register_formatter');

shinka_award_alert_install() {
  shinka_award_alert_register_alert();  
}

shinka_award_alert_uninstall() {
  shinka_award_alert_register_alert();  
}

function shinka_award_alert_register_alert() {
  if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

	if (!$alertTypeManager) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
	}

	$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
	$alertType->setCode('my_alert_type_code'); // The codename for your alert type. Can be any unique string.
	$alertType->setEnabled(true);
	$alertType->setCanBeUserDisabled(true);

	$alertTypeManager->add($alertType);
}
}

function shinka_award_alert_unregister_alert() {
  if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
  
    if (!$alertTypeManager) {
      $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
    }
  
    $alertTypeManager->deleteByCode('my_alert_type_code');
  }
}

function award_alert_register_formatter() {
  if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
    $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
  
    if (!$formatterManager) {
      $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
    }
  
    $formatterManager->registerFormatter(
          new MyCustomAlertFormmatter($mybb, $lang, 'my_alert_type_code')
      );
  }
}