<?php
/**
 * OUGC Award Alert
 * 
 * Integrates OUGC Awards with MyAlerts 2.0.
 *
 * @package OUGC Award Alert
 * @author  Shinka <shinka@shinka.dev>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 0.1
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

// All the available alerts should be placed here    
$GLOBALS['alertslist'] = array(
    "give_award",
    "revoke_award"
);

function shinka_awardalert_info()
{
    global $lang;

    $installed = false;
    if (function_exists('myalerts_is_installed')) {
        $installed = myalerts_is_installed();
    }
    
    $myalerts_notice = '';
    if (!$installed) {
        $myalerts_notice = '<br /><span style="color:#f00">' . $lang->shinka_awardalert_warning . '</span>.';
    }
    
    $info = array(
        'name' => $lang->shinka_awardalert,
        'description' => $lang->shinka_awardalert_desc . $myalerts_notice,
        'website' => 'https://github.com/shinka',
        'author' => 'Shinka',
        'version' => '0.1',
        'compatibility' => '18*'
    );
    
    return $info;
}

function shinka_awardalert_is_installed()
{
    global $cache;
    
    $info      = shinka_awardalert_info();
    $installed = $cache->read("shinka_plugins");
    if ($installed[$info['name']]) {
        return true;
    }
}

function shinka_awardalert_install()
{
    global $db, $PL, $lang, $mybb, $cache, $alertslist;
    
    if (!$lang->shinka_awardalert) {
        $lang->load('shinka_awardalert');
    }
    
    // MyAlerts installation check
    if (!$db->table_exists('alerts')) {
        flash_message($lang->shinka_awardalert_error_myalertsnotinstalled, "error");
        admin_redirect("index.php?module=config-plugins");
    }
    
    $info                         = shinka_awardalert_info();
    $shinkaPlugins                = $cache->read('shinka_plugins');
    $shinkaPlugins[$info['name']] = array(
        'title' => $info['name'],
        'version' => $info['version']
    );
    $cache->update('shinka_plugins', $shinkaPlugins);
    
    // Register our alerts!
    $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
    
    $alertTypesToAdd = array();
    foreach ($alertslist as $type) {
        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode($type);
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);
        
        $alertTypesToAdd[] = $alertType;
    }
    
    $alertTypeManager->addTypes($alertTypesToAdd);
}

function shinka_awardalert_uninstall()
{
    global $db, $PL, $cache, $alertslist, $lang;
    
    if (!$lang->shinka_awardalert) {
        $lang->load("shinka_awardalert");
    }
    
    // Delete alerts
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
        
        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }
        
        foreach ($alertslist as $type) {
            $alertTypeManager->deleteByCode($type);
        }
    }
    
    // Delete the plugin from cache
    $info          = shinka_awardalert_info();
    $shinkaPlugins = $cache->read('shinka_plugins');
    unset($shinkaPlugins[$info['name']]);
    $cache->update('shinka_plugins', $shinkaPlugins);
}

$plugins->add_hook('global_start', 'shinka_awardalert_register_formatters');
function shinka_awardalert_register_formatters()
{
    global $mybb, $lang;
    
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        
        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        
        $formatterManager->registerFormatter(new Shinka_AwardAlert_MyAlerts_Formatter_AwardFormatter($mybb, $lang, 'award'));
    }
}

// Generate the alerts
$plugins->add_hook('ougc_awards_give_award', 'shinka_awardalert_addAlert_give_award');
function shinka_awardalert_addAlert_give_award(&$args)
{
    $user   = $args['user'];
    $award  = $args['award'];
    $reason = $args['reason'];
    
    $code = 'give_award';
    
    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode($code);
    
    if ($alertType != null and $alertType->getEnabled()) {
        $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $user['uid'], $alertType, 0);
        
        $extra_details = array(
            'award' => $award,
            'reason' => $args['reason']
        );
        
        $alert->setExtraDetails($extra_details);
        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
    }
} 