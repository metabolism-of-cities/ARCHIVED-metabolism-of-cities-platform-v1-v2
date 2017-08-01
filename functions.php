<?php

if (!file_exists(__DIR__ . '/config.php')) {
  die("Config file not found! Read the readme file to create your configuration file first");
}
require_once 'config.php';
require_once 'class.db.php';

$db = new db("mysql:host=" . SERVER . ";dbname=" . DATABASE, USER, PASSWORD);
if ($db) {
  $db->setErrorCallbackFunction("kill");
} else {
  die("Could not connect to the database");
}

$css = filesize(__DIR__."/css/styles.css");

$css_files = array(
  1 => 'colour-orange.min.css',
  2 => 'colour-blue-dark.min.css',
);

$get_css = $_GET['css'] ?: $_COOKIE['css'];
if ($_GET['css'] || $_COOKIE['css']) {
  $css_files[ID] = 'colour-'.$get_css.'.min.css';
}
  

$header = '
    <meta charset="utf-8" />
    <base href="' . URL . '" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="apple-touch-icon" sizes="57x57" href="'.URL.'img/favicon/apple-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="'.URL.'img/favicon/apple-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="'.URL.'img/favicon/apple-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="'.URL.'img/favicon/apple-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="'.URL.'img/favicon/apple-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="'.URL.'img/favicon/apple-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="'.URL.'img/favicon/apple-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="'.URL.'img/favicon/apple-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="'.URL.'img/favicon/apple-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="192x192"  href="'.URL.'img/favicon/android-icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="'.URL.'img/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="96x96" href="'.URL.'img/favicon/favicon-96x96.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="'.URL.'img/favicon/favicon-16x16.png" />
    <link rel="manifest" href="'.URL.'img/favicon/manifest.json" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="'.URL.'img/favicon/ms-icon-144x144.png" />
    <meta name="theme-color" content="#ffffff" />';

$header .= PRODUCTION ? 

    '<!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
    ' :

    '<link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    ';

    $size_custom = filesize("css/custom-".ID.".css");
    $header .='
    <link href="assets/css/theme-style.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/'.$css_files[ID].'" />
    <link href="css/font-awesome.4.2.0.css" rel="stylesheet" />
    <link href="css/styles.css?reload='.$css.'" rel="stylesheet" />
    <link href="css/custom-'.ID.'.css?s='.$size_custom.'" rel="stylesheet" />

    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,700,300" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=Rambla" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=Calligraffitti" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=Roboto+Slab:400,700" rel="stylesheet" type="text/css">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->';

$header .= PRODUCTION ? 
  '
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
  ' :
  '
    <script type="text/javascript" src="assets/js/jquery-3.1.1.slim.min.js"></script>
    <script type="text/javascript" src="assets/js/tether.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
  ';

$header .= '<script src="js/bootstrap.min.js"></script>';

if ($add_to_header) {
  $header .= $add_to_header;
}

$tag_parents = $db->query("SELECT SQL_CACHE id, name FROM tags_parents ORDER BY parent_order");

$menu = array(
  2 => array(
    'label' => 'About', 
    'url' => 'page/about', 
    'menu' => array(
      1 => array('label' => 'About Us', 'url' => 'page/about'),
      3 => array('label' => 'Team', 'url' => 'page/team'),
      4 => array('label' => 'Wish List', 'url' => 'page/wishlist'),
      5 => array('label' => 'Contact Us', 'url' => 'page/contact'),
      7 => array('label' => 'Version History', 'url' => 'page/version'),
    ),
  ),
  6 => array(
    'label' => 'OMAT', 
    'url' => 'omat/about',
    'menu' => array(
      1 => array('label' => 'How It Works', 'url' => 'omat/about'),
      2 => array('label' => 'Create a Project', 'url' => 'omat/add'),
      4 => array('label' => 'Dashboard', 'url' => 'page/login'),
      5 => array('label' => 'Documentation', 'url' => 'omat/documentation'),
      3 => array('label' => 'List of Projects', 'url' => 'omat/list'),
    ),
  ),
  4 => array(
    'label' => 'Research &amp; Publications', 
    'url' => 'publications',
    'menu' => array(
      1 => array('label' => 'Introduction', 'url' => 'publications'),
      2 => array('label' => 'Current Research', 'url' => 'research/list'),
      3 => array('label' => 'Add Your Project', 'url' => 'research/add'),
      4 => array('label' => 'Publications Database', 'url' => 'publications/list'),
      5 => array('label' => 'Publications Collections', 'url' => 'publications/collections'),
      6 => array('label' => 'Search', 'url' => 'publications/search'),
      7 => array('label' => 'Add Publication', 'url' => 'publications/add'),
      8 => array('label' => 'Authors', 'url' => 'people'),
      9 => array('label' => 'Journals', 'url' => 'journals'),
    ),
  ),
  5 => array(
    'label' => 'Data', 
    'url' => 'data',
    'menu' => array(
      1 => array('label' => 'Introduction', 'url' => 'data'),
      2 => array('label' => 'Data Overview', 'url' => 'page/casestudies'),
//      3 => array('label' => 'Download', 'url' => 'data/add'),
      4 => array('label' => 'Add Data', 'url' => 'data/add'),
      5 => array('label' => 'Publications Map', 'url' => 'page/map'),
    ),
  ),
  7 => array(
    'label' => 'Stakeholders Initiative', 
    'url' => 'stakeholders',
    'menu' => array(
      1 => array('label' => 'Introduction', 'url' => 'stakeholders'),
      5 => array('label' => 'Global Urban Metabolism Dataset', 'url' => 'stakeholders/data'),
      4 => array('label' => 'UM Masterclasses', 'url' => 'stakeholders/masterclasses'),
      2 => array('label' => 'Data Visualization', 'url' => 'datavisualization'),
      3 => array('label' => 'Subscribe', 'url' => 'stakeholders/subscribe'),
    ),
  ),  
  8 => array(
    'label' => 'More', 
    'url' => 'blog',
    'menu' => array(
      1 => array('label' => 'Blog', 'url' => 'blog'),
      2 => array('label' => 'Newsletter', 'url' => 'page/mailinglist'),
      3 => array('label' => 'Videos', 'url' => 'videos'),
      //3 => array('label' => 'Links', 'url' => 'page/links'),
      //4 => array('label' => 'Social Media', 'url' => 'page/socialmedia'),
    ),
  ),
);
foreach ($tag_parents as $row) {
  $menu[4]['menu'][5][$row['id']] = array('label' => $row['name'], 'url' => 'publications/collections/'.$row['id']);
}

if (ID == 2) {
  unset($menu[6]);
  $menu[2]['label'] = "EPR Menu 1";
  $menu[5]['label'] = "EPR Menu 3";
  unset($menu[7]);
  $menu[8]['label'] = "EPR Menu 4";
  unset($menu[4]['menu'][1]);
  unset($menu[4]['menu'][2]);
  unset($menu[4]['menu'][3]);
}

$google_translate = LOCAL ? '' : '
  <script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: \'en\', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, \'google_translate_element\');
  }
  </script>
  <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>';


function mailadmins($message, $subject, $from = false, $html = false, $webmaster = false) {
  $to = $webmaster ? WEBMASTER_MAIL : EMAIL;
  $message = utf8_decode($message);
  $from = $from ? $from : "noreply@metabolismofcities.org";
  $headers = 'From: Metabolism of Cities<noreply@metabolismofcities.org>' . "\r\n" .
      'Reply-To: ' . $from . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
  if ($html) {
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
  }
  mail($to, $subject, $message, $headers);
  if (LOCAL) {
    //echo '<pre>' . $message . '</pre>';
    //die();
  }
}

function kill($message, $report = true) {
    if (PRODUCTION) {
      if ($report) {
        mailadmins($message . "<br /><br /><pre>" . getinfo() . "</pre>", "MySQL Error - Metabolism of Cities", false, true, true);
      }
      header("HTTP/1.0 404 Not Found");
      header("Location: " . URL . "404");
      exit();
    } else {
      echo $message;
      die();
    }
}

function format_date($format,$date){
	
	if($date == "0000-00-00" || $date == "0000-00-00 00:00:00" || $date == "1969-12-31"){
	  return "";  
	}

	$return = date($format,strtotime($date));
	return $return;
}

function seems_utf8($str)
{
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

function remove_accents($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	if (seems_utf8($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
		chr(195).chr(191) => 'y',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '');

		$string = strtr($string, $chars);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

function flatten($string) {
	$string = utf8_decode($string);
	$string = remove_accents($string);
	$array_rewrite = array("amp-amp" => "and", "-s-" => "s-", "-amp-oacute-" => "o", "amp-" => "");
	$string = strtolower($string);
	$string = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml);/", "$1", $string);
	$string = preg_replace("/([^a-z0-9]+)/", "-", $string);
	$string = trim($string, "-");
	$string = strtr($string, $array_rewrite);
	
	return $string;
}

function encrypt($string)
{
	if (!defined("SALT") || SALT == "") {
		kill("SALT not defined", "critical");
		return false;
	} else {
		return hash("sha512", $string.SALT);
	}
}

function br2nl($string){
	return str_replace("<br />","",$string);
}

function html($string,$clean=true) {
  if (!mb_check_encoding($string, "UTF-8")) {
    $string = utf8_decode($string);
  }
  $convert = array('’' => "'", '“' => "'", '”' => "'", '–' => "-");
  $string = strtr($string, $convert);
  $string = nl2br(htmlspecialchars($string, ENT_QUOTES, "UTF-8"));
	return($string);
}

function mysql_clean($string, $type=false) {
  $string = (get_magic_quotes_gpc()) ? stripslashes($string) : $string;
	$string = mysqli_real_escape_string(mysqli_connect(SERVER,USER,PASSWORD,DATABASE),$string);
	
	if ($type == "wildcard")
	{
		return addcslashes($string, "%_"); 
	}
	else
	{
		return "{$string}";
	}
}

function mail_clean($string, $type=NULL, $msafe="no") // check body elements of message
{
	$bad_strings = array("content-","mime-","multipart/mixed","bcc:","acc:","@yourdomain.com", ".txt");
	$spam_strings = array("a href", "[url]", "viagra");
	
	foreach($bad_strings as $bad_string)
	{
			if(stristr($string, $bad_string))
					kill ("bsafe injection", "mail injection");
	}
	
	foreach($spam_strings as $bad_string)
	{
			if(stristr($string, $bad_string))
					kill ("This is a spam message", "spam");
	}
	
	if ($type != "box") // if the input is a large box, then new lines are accepted
	{
			if(preg_match("/(%0A|%0D|\n+|\r+)/i", $string))
					kill ("bsafe injection newlines", "mail injection");
	
	}
	
	return $string;
}

function getinfo($option='all'){ 
$remoteinfo="================= DETAILS ================ 
Time => ".date("r")."
IP => ". $_SERVER['REMOTE_ADDR']."
Browser => " . $_SERVER['HTTP_USER_AGENT'] . "
File => " . $_SERVER['PHP_SELF'] . "
Server => " . $_SERVER['SERVER_NAME'] . "
Method => " . $_SERVER['REQUEST_METHOD'] . "
Query => " . $_SERVER['QUERY_STRING'] . "
HTTP Language => " . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "
Referer => " . $_SERVER['HTTP_REFERER'] . "
URL => " . $_SERVER['REQUEST_URI']."\n\n";

$post = "============== POST VARIABLES ============ \n".
(($_POST) ? print_r($_POST, true) . "\n\n" : $post="POST IS EMPTY\n\n");

$files = "============== FILES VARIABLES ============ \n".
(($_FILES) ? print_r($_FILES, true) . "\n\n" : $files="FILES IS EMPTY\n\n");

$cookies = "============ COOKIE VARIABLES =========== \n".
(($_COOKIE) ? print_r($_COOKIE, true) . "\n\n": $cookies ="NO COOKIES\n\n");

$backtrace="================== PHP DEBUG ================ \n".print_r(debug_backtrace(),true); 

$lasterror="============== PHP LAST ERROR ============ \n".print_r(error_get_last(),true);
	
		switch($option){
				case 'url':
						$info = "URL: {$_SERVER['REQUEST_URI']}";
						break;
						
				case 'post':
						$info = $post;
						break;
		
				case 'cookies':
						$info = $cookies;
						break;
						
				case 'remote':
						$info = $remoteinfo;
						break;
						
				case 'php':
						$info = $lasterror.$backtrace;
						break;
						
				case 'sql':
						$info = $sql;
						break;

				case 'files':
						$info = $files;
						break;
						
				case 'all':
				default:
						$info = $url.$post.$remoteinfo.$cookies.$files;
						break;
		}
		return $info;
} 

function formatTime($time, $minhour = false)
{
	$hours = floor($time/60);
	if ($hours > 0 || $minhour) {
		$minutes = (int)$time-($hours*60);
		if ($minutes < 10) { $minutes = "0$minutes"; }
		$return = "$hours:$minutes";
		$return .= !$minhour ? " h" : '';
	}
	elseif ($time == 0) { $return = "0:00"; } 
	elseif ($time < 10) { $return = (int)$time . " min"; }
	else { $return = (int)$time . " min"; }
	return $return;
}

function truncate($string,$length=100,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}


function nameScraper($string, $insert = true) {
  // This function attempts to extract a name or list of names
  // from a string, and look these names up in the list with authors.
  // Optionally the name(s) will be inserted if they don't exist

  global $db;

  // Some last names are very common, so for those names we want
  // to enforce a check of the first name as well. For other names,
  // we will accept the lastname as the single parameter to validate
  // that this is a unique person

  $force_firstname_check = array(
    "Li" => true,
    "Lee" => true,
    "Marques" => true,
    "Chang" => true,
    "Chen" => true,
    "Yu" => true,
    "Huang" => true,
    "Ji" => true,
    "Lin" => true,
    "Wu" => true,
    "Wang" => true,
  );

  $array = array("&" => "and", ";" => " and ");
  $string = strtr($string, $array);

  $explode = explode(" and ", $string);
  if (count($explode) == 1) {
    $explode_commas = explode(",", $string);
    if (count($explode_commas) > 2) {
      $explode = $explode_commas;
    }
    if (count($explode) == 1) {
      $explode_semicolon = explode(";", $string);
      if (count($explode_semicolon) > 1) {
        $explode = $explode_semicolon;
      }
    }
  }
  foreach ($explode as $name) {
    $firstname = false;
    $lastname = false;
    $id = false;
    $comma_explode = explode(",", $name);
    if (count($comma_explode) > 1) {
      // Normally the name is in the format of Lastname, Firstname so this
      // should yield two items in the array
      $firstname = trim($comma_explode[1]);
      $lastname = trim($comma_explode[0]);
    } else {
      // If this has no commas then we assume it is "Firstname Lastname"
      // This is error prone as names could consist of several firstnames or lastnames
      // But this will give a good-enough first result, and manual checking is
      // required anyway. 
      $name = trim($name);
      $explode_space = explode(" ", $name);
      $firstname = trim($explode_space[0]);
      $lastname = trim($explode_space[1]);
      if ($explode_space[3]) {
        // If there are four names, we expect this to be firstname firstname lastname lastname
        $firstname .= " " . trim($explode_space[2]);
        $lastname .= " " . trim($explode_space[3]);
      } elseif ($explode_space[2]) {
        // If thre are three names we expect two last names
        $lastname .= " " . trim($explode_space[2]);
      }
    }
    if ($lastname) {
      $set_lastname = addslashes($lastname);
      $info = $db->query("SELECT * FROM people WHERE lastname = '$set_lastname' AND firstname = '$firstname' AND active = 1");
      if (count($info) == 1) {
        $id = $info[0]['id'];
      } elseif (count($info) > 1) {
        // If we have more than one record (same last names?) we try using the first name as well
        $info_with_firstname = $db->query("SELECT * FROM people WHERE lastname = '$set_lastname' AND firstname LIKE '%{$firstname}% AND active = 1'");
        if (count($info_with_firstname) == 1) {
          // If this returns one record, great, let's go for it:
          $id = $info_with_firstname[0]['id'];
        }
      } elseif (!count($info) && !$force_firstname_check[$lastname]) {
        $info = $db->query("SELECT * FROM people WHERE lastname = '$set_lastname' AND active = 1");
        if (count($info) == 1) {
          $id = $info[0]['id'];
          $firstname_found = $info[0]['firstname'];
          if (strlen($firstname_found) <= 5 && strlen($firstname) > strlen($firstname_found)) {
            // The present firstname is less than 4 characters (so likely only the initials)
            // so if we now have a larger firstname, let's update this.
           $post = array(
              'firstname' => mysql_clean($firstname),
            );
            $db->update("people",$post,"id = " . $info[0]['id']);
          }
        }
      }
      if (!$id) {
        $post = array(
          'firstname' => $firstname,
          'lastname' => $lastname,
        );
        $db->insert("people",$post);
        $info = $db->record("SELECT id FROM people 
          WHERE firstname = '$firstname' AND lastname='$set_lastname' 
          ORDER BY id DESC LIMIT 1");
        $id = $info->id;
        if (!$id) {
        die("SELECT id FROM people 
          WHERE firstname = '$firstname' AND lastname='$set_lastname' 
          ORDER BY id DESC LIMIT 1");
        }
      }
      $return[] = $id;
    }
  }
  return $return;
}

function authorlist($id, $type = 'html') {
  global $db;
  $authors = $db->query("SELECT people.* 
  FROM 
    people_papers 
  JOIN people ON people_papers.people = people.id
  WHERE people_papers.paper = $id AND people.active IS TRUE");
  foreach ($authors as $row) {
    $name = $row['firstname'] . " " . $row['lastname'];
    if ($type == 'html') {
      $return .= '<li><a href="people/'.$row['id'].'-'.flatten($name).'">'.$name.'</li>';
    } elseif ($type == 'plain') {
      $return .= $row['lastname'] . ', ' . $row['firstname'] . ' and ';
    } elseif ($type == 'array') {
      $return[$row['id']] = $row['firstname'] . ' ' . $row['lastname'];
    }
  }
  if ($type == 'plain') {
    $return = substr($return, 0, -5);
  } elseif ($type == 'html') {
    $return = "<ul>$return</ul>";
  }
  return $return;
}

function bbcode($string, $clean=false) {
	
	// Add a line break to make things easier... we'll remove this later
	$string .= "\n"; 

	// Convert to html entities
	$string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');

	// *Bold*
	$string = preg_replace('/\*([^\n\']+)\*/','<strong>${1}</strong>',$string);

	// _Italic_
	$string = preg_replace('/\_([^\n\']+)\_/','<em>${1}</em>',$string);

	// ## Headers
	for ($i=7;$i>0;$i--) {
		$string = preg_replace('/[#]{'.$i.'}(.*)/', '<h'.$i.'>${1}</h'.$i.'>',	$string);
	}

	// * Lists
  // We need to add an extra white line; if not, the first item in the list will not be recognized, if this 
  // is the first line of the stirng. However, in case that this string contains no lists, then we need to remove
  // this extra \n
  $temp_string = "\n" . $string;
	$temp_string = preg_replace('/(\n[ ]*[^#* ][^\n]*)\n(([ ]*[*]([^\n]*)\n)+)/', '${1}<ul>'."\n".'${2}'.'</ul>'."\n", $temp_string);
	$temp_string = preg_replace('/\n[ ]*[\*#]+([^\n]*)/','<li>${1}</li>',$temp_string);

  $string = $temp_string == "\n" . $string ? $string : $temp_string;

	// <p> Paragraphs and <br /> line breaks
	$string = '<p>'.preg_replace('#(<br\s*?/?>\s*?){2,}#', '</p>'."\n".'<p>', nl2br($string, true)).'</p>';

	// Remove \r\n that are wondering about
	$remove = array(
		"\n" => '', 
		"\r" => '', 
	);
	$string = strtr($string,$remove);

	// Convert incorrect closing tags after p and br conversion
	$convert = array(
		'<br /></h1></p>' => '</h1>',
		'<br /></h2></p>' => '</h2>',
		'<br /></h3></p>' => '</h3>',
		'<br /></h4></p>' => '</h4>',
		'<br /></h5></p>' => '</h5>',
		'<br /></h6></p>' => '</h6>',
		'<br /><ul>' => '</p><ul>',
		'</ul><br />' => '</ul><p>',
		'<br /><h' => '</p><h',
		'<br /></h' => '</h',
		'<p><h' => '<h',
		'<br /></li>' => '</li>',
		'<p></p>' => '',
		'<p><ul>' => '<ul>',
		'<br /></ul></p>' => '</ul>',
		'<p><br />' => '<p>',
		'</li><br /></ul>' => '</li></ul>',
		'<p><li>' => '<ul><li>',
		'</li><br /></p>' => '</li></ul>',
		'</li></p>' => '</li></ul>',
	);
	$string = strtr($string,$convert);

	$array = array(
		'<p></p>' => '',
		'</li></p>' => '</li></ul>',
	);
	// Removing these extra empty p's
	$string = strtr($string, $array);

  // Regular links
	$string = preg_replace("/\[(.*?) (.*?)\]/", "<a href=\"\$1\">\$2</a>", $string);

  $end = substr($string, strlen($string)-5, 5);

  if ($end != "</ul>") {
	  $string = substr($string, 0, -4);
	  $string .= "</p>";
  }

	if ($clean) { $string = mysql_clean($string); }
	return $string;
}

function check_mail($email)
{        
  return (filter_var($email,FILTER_VALIDATE_EMAIL)) ? true : false;
}


function peoplelog($action) {
  global $db;

  $post = array(
    'url' => mysql_clean($_SERVER['REQUEST_URI']),
    'ip' => mysql_clean($_SERVER["REMOTE_ADDR"]),
    'action' => mysql_clean($action),
    'info' => mysql_clean(getinfo()),
    'people' => (int)$_COOKIE['id'],
  );

  $db->insert("people_log",$post);

  return true;
}

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*/
 
class SimpleImage {
   
   var $image;
   var $image_type;
 
   function load($filename) {
	  $image_info = getimagesize($filename);
	  $this->image_type = $image_info[2];
	  if( $this->image_type == IMAGETYPE_JPEG ) {
		 $this->image = imagecreatefromjpeg($filename);
	  } elseif( $this->image_type == IMAGETYPE_GIF ) {
		 $this->image = imagecreatefromgif($filename);
	  } elseif( $this->image_type == IMAGETYPE_PNG ) {
		 $this->image = imagecreatefrompng($filename);
	  }
   }
   
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=85, $permissions=null) {
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image,$filename,$compression);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
		 imagegif($this->image,$filename);         
	  } elseif( $image_type == IMAGETYPE_PNG ) {
		 imagepng($this->image,$filename);
	  }   
	  if( $permissions != null) {
		 chmod($filename,$permissions);
	  }
   }
   function output($image_type=IMAGETYPE_JPEG) {
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
		 imagegif($this->image);         
	  } elseif( $image_type == IMAGETYPE_PNG ) {
		 imagepng($this->image);
	  }   
   }
   function getWidth() {
	  return imagesx($this->image);
   }
   function getHeight() {
	  return imagesy($this->image);
   }
   function resizeToHeight($height) {
	  $ratio = $height / $this->getHeight();
	  $width = $this->getWidth() * $ratio;
	  if ($height < $this->getHeight()) {
		  $this->resize($width,$height);
	  }
   }
   function resizeToWidth($width) {
	  $ratio = $width / $this->getWidth();
	  $height = $this->getheight() * $ratio;
	  if ($width < $this->getWidth()) {
		   $this->resize($width,$height);
	  }
   }
   function scale($scale) {
	  $width = $this->getWidth() * $scale/100;
	  $height = $this->getheight() * $scale/100; 
	  $this->resize($width,$height);
   }
   function resize($width,$height) {
	  $new_image = imagecreatetruecolor($width, $height);
	  imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	  $this->image = $new_image;   
   }

   function rotate($angle) {
		$new_image = imagerotate($this->image,$angle,imageColorAllocateAlpha($this->image, 250, 250, 250,0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		$this->image = $new_image;
   }
}

function smartcut($string, $length,$suffix="...") {
  if (!$string) {
	return $string;
  }
  $string_length = strlen($string);
  if ($string_length <= $length) {
	return $string;
  }
  $pos = strpos($string, ' ', $length);
  if ($string_length > $length && !$pos) {
	$result = substr($string, 0, $length);
  } else {
	$result = substr($string,0,$pos);
  }
  if ($result != $string) {
	$result .= $suffix;
  }
  return $result;
}

$languages = array('English', 'Chinese', 'Spanish', 'French', 'German', 'Other');

$update = $db->record("SELECT SQL_CACHE date_added FROM papers ORDER BY id DESC LIMIT 1");

$topic = ID == 2 ? "EPR" : "Urban Metabolism";

$version = '1.5 beta';

// Run to get the total lines for the newsletter: git ls-files *php | xargs wc -l
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") && PRODUCTION && !$skip_ssl) {
  $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  header("Location: $redirect");
  exit();
}
?>
