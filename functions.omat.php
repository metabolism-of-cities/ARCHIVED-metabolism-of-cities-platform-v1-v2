<?php
// This file is to be included on all OMAT pages. 
// It makes sure the user has access to the project that
// is being managed. 
// By default, we assume that $_GET['id'] contains the project ID.
// If that is not the case, we should define $project BEFORE including
// this file. 

/**
 * A simple, clean and secure PHP Login Script / MINIMAL VERSION
 * For more versions (one-file, advanced, framework-like) visit http://www.php-login.net
 *
 * Uses PHP SESSIONS, modern password-hashing and salting and gives the basic functions a proper login system needs.
 *
 * @author Panique
 * @link https://github.com/panique/php-login-minimal/
 * @license http://opensource.org/licenses/MIT MIT License
 */

// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.5', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.5 !");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    // if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
    // (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
    require_once("login/libraries/password_compatibility_library.php");
}

// include the configs / constants for the database connection
require_once("login/config/db.php");

// load the login class
require_once("login/classes/Login.php");

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();

$omat_link = "omat";
if ($public_login) {
  $omat_link = "omat-public";
}

if ($login->isUserLoggedIn() == true) {
  $user_id = (int)$_SESSION['user_id'];
  $permissions = $db->query("SELECT * FROM users_permissions WHERE user = $user_id");
  foreach ($permissions as $permissionrow) {
    $authorized .= $permissionrow['dataset'] . ",";
  }
  $authorized = substr($authorized, 0, -1);
} elseif (!$skip_login && !$public_login) {
  header("Location: " . URL . "page/login");
  exit();
}

if (!$skip_login && !$no_project_selected) {
  if (!$project) {
    $project = $_GET['project'] ? (int)$_GET['project'] : (int)$_GET['id'];
  }
  if (!$project) {
    die("No project defined");
  }

  if ($public_login) {
    $check = $db->record("SELECT * FROM mfa_dataset WHERE id = $project AND access != 'private'");
  } else {
    $check = $db->record("SELECT * FROM mfa_dataset WHERE id = $project AND id IN ($authorized)");
  }
  if (!$check->id) {
    kill("Invalid dataset opened");
  }
  $omat_sidebar = !$disable_sidebar ? true : false;
}

if ($omat_sidebar) {
  $header .= '<link rel="stylesheet" href="css/sidebar.css" />';
}

$omat_menu = array(
  1 => array(
    'label' => 'Data', 
    'url' => "omat/$project/manage", 
    'menu' => array(
      1 => array('label' => 'Manage Data', 'url' => "omat/$project/manage", 'icon' => 'pencil'),
      2 => array('label' => 'Manage Resources', 'url' => "omat/$project/contacts", 'icon' => 'list'),
      5 => array('label' => 'Filter Contacts', 'url' => "omat/$project/filters/contacts", 'icon' => 'user'),
      3 => array('label' => 'Filter Sources', 'url' => "omat/$project/filters/sources", 'icon' => 'link'),
      4 => array('label' => 'Files', 'url' => "omat/$project/files", 'icon' => 'file-pdf-o'),
      6 => array('label' => 'Industries', 'url' => "omat/$project/industries", 'icon' => 'cogs'),
    ),
  ),
  2 => array(
    'label' => 'Maintenance', 
    'url' => "omat/$project/dashboard", 
    'menu' => array(
      1 => array('label' => 'Data Quality Indicators', 'url' => "omat/$project/maintenance-dqi", 'icon' => 'star'),
      2 => array('label' => 'Types of Sources', 'url' => "omat/$project/maintenance-sources", 'icon' => 'link'),
      3 => array('label' => 'Types of Contacts', 'url' => "omat/$project/maintenance-contacts", 'icon' => 'user'),
      4 => array('label' => 'Types of Activities', 'url' => "omat/$project/maintenance-activities", 'icon' => 'comment'),
      5 => array('label' => 'Types of Scales', 'url' => "omat/$project/maintenance-scales", 'icon' => 'dot-circle-o'),
      6 => array('label' => 'Types of Tags', 'url' => "omat/$project/maintenance-tags", 'icon' => 'tag'),
      7 => array('label' => 'Industry Sizes', 'url' => "omat/$project/maintenance-industrysizes", 'icon' => 'cogs'),
    ),
  ),
  3 => array(
    'label' => 'Reports', 
    'url' => "omat/$project/reports", 
    'menu' => array(
      1 => array('label' => 'Data Overview', 'url' => "omat/$project/reports-dataoverview"),
      2 => array('label' => 'Indicators', 'url' => "omat/$project/reports-indicators"),
      6 => array('label' => 'Graphs', 'url' => "omat/$project/reports-graphs"),
      3 => array('label' => 'Data Tables', 'url' => "omat/$project/reports-tables"),
      4 => array('label' => 'Activity Log', 'url' => "omat/$project/reports-activities"),
      5 => array('label' => 'Travel Log', 'url' => "omat/$project/reports-travel"),
    ),
  ),
);

if (!LOCAL) {
  unset($omat_menu[1]['menu'][6]);
}

if (!$check->multiscale) {
  unset($omat_menu[2]['menu'][5]);
}

if (!$check->contact_management) {
  unset($omat_menu[2]['menu'][2]);
  unset($omat_menu[2]['menu'][3]);
  unset($omat_menu[2]['menu'][6]);
  unset($omat_menu[1]['menu'][2]);
  unset($omat_menu[1]['menu'][3]);
  unset($omat_menu[1]['menu'][4]);
}

if (!$check->time_log) {
  unset($omat_menu[2]['menu'][4]);
  unset($omat_menu[3]['menu'][4]);
  unset($omat_menu[3]['menu'][5]);
}

if (!$check->dqi) {
  unset($omat_menu[2]['menu'][1]);
}

if ($public_login) {
  unset($omat_menu[3]['menu'][1]);
}

function hierarchyTree($id) {
  global $db, $ancestors;
  $info = $db->record("SELECT id, name, belongs_to FROM mfa_contacts WHERE id = $id");
  $ancestors[] = array($info->id, $info->name);
  if ($info->belongs_to) {
    hierarchyTree($info->belongs_to);
  } else {
    return $ancestors;
  }
}

function timeContact($id) {
  // This function will figure out how much time was spent on the children of
  // this contact
  global $db;
  $time = $db->record("SELECT SUM(time) AS time FROM mfa_activities_log WHERE contact = $id");
  $time_sources = $db->record("SELECT SUM(time) AS time FROM mfa_activities_log 
    WHERE source IN (SELECT id FROM mfa_sources WHERE belongs_to = $id)");
  return $time->time + $time_sources->time;
}

?>
