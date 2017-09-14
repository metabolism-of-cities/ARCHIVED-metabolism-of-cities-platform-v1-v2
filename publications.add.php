<?php
$show_breadcrumbs = true;
$skip_login = true;
require_once 'functions.php';
require_once 'functions.omat.php';
$section = 4;
$page = 7;

if ($_GET['profile']) {
  require_once 'functions.profile.php';
  $sub_page = 3;
}

$journals = $db->query("SELECT * FROM sources ORDER BY name");

function bibtexclean($string) {
  $array = array(
    "{\'e}" => "é", 
    "{\'E}" => "É",
    "{\`e}" => "è",
    "{\`E}" => "È",
    "{\'o}" => "ö",
    "{\'a}" => "á",
    "{\~n}" => "ñ",
    "{\'\i}" => "í",
  );
  return strtr($string, $array);
}

if ($_POST['title']) {
  if ($_POST['source'] == "unlisted") {
    $post = array(
      'name' => html($_POST['newsource']),
    );
    $db->insert("sources",$post);
    $_POST['source'] = $db->lastInsertId();
  }

  if (!$_POST['source']) {
    die("Please select a journal/source/publisher for this publication. You can add a new one if yours doesn't appear in the list");
  }

  if ($_POST['language'] != 'English') {
    $original = $_POST['title'];
    $_POST['title'] = $_POST['title_english'];
    $_POST['title_native'] = $original;
    $original = $_POST['abstract'];
    $_POST['abstract'] = $_POST['abstract_english'];
    $_POST['abstract_native'] = $original;
  }

  $post = array(
    'title' => html($_POST['title']),
    'title_native' => html($_POST['title_native']),
    'author' => html($_POST['author']),
    'volume' => (int)$_POST['volume'],
    'issue' => (int)$_POST['issue'],
    'pages' => html($_POST['pages']),
    'type' => (int)$_POST['type'],
    'length' => html($_POST['length']),
    'year' => (int)$_POST['year'],
    'doi' => html($_POST['doi']),
    'abstract' => html($_POST['abstract']),
    'abstract_native' => html($_POST['abstract_native']),
    'language' => html($_POST['language']),
    'link' => html($_POST['link']),
    'source' => (int)$_POST['source'],
    'status' => mysql_clean('pending'),
    'date_added' => mysql_clean(date("Y-m-d H:i:s")),
  );
  $id = $db->insert("papers",$post);
  $id = $db->lastInsertId();

  $hash = encrypt($id . html($_POST['title'], false));

  $message = 
"Name: " . mail_clean($_POST['yourname']) . "
E-mail: " . mail_clean($_POST['youremail']) . "
Date: " . date("r") . "
IP: " . $_SERVER["REMOTE_ADDR"] . "

-----------------------------------------
          PUBLICATION ADDED
-----------------------------------------
Title: " . mail_clean($_POST['title']) . "
Author: " . mail_clean($_POST['author']) . "
Paper ID: $id
Link: " . URL . "publication/$id
Review: " . URL . "publication.view.php?id=$id&hash=$hash

-----------------------------------------
                MESSAGE
-----------------------------------------
" . mail_clean($_POST['comments'], "box") . "


-----------------------------------------
            TECH DETAILS
-----------------------------------------
" . getinfo();

  mailadmins($message, "New publication at the Metabolism of Cities website", $_POST['youremail']);
  if ($_GET['profile']) {
    peoplelog("User added a new publication");
  }


} elseif ($_POST['bibtex']) {
  $info = $db->record("SELECT '' AS title");
  // Get an empty object so we can load new values

  $bibtex = bibtexclean($_POST['bibtex']);
  $explode = explode("\n", $bibtex);
  $clean = array('\&' => '&');
  foreach ($explode as $key => $value) {
    $line = $value;
    $sub = explode("=", $value);
    $name = trim($sub[0]);
    $value = $sub[1];
    preg_match('/{(.*)}/', $value, $match);
    $string = strtr($match[1], $clean);
    if ($name == "title") {
      $info->title = strip_tags($string);
    } elseif ($name == "author") {
      $info->author = strip_tags($string);
    } elseif ($name == "journal") {
      $info->journal_name = $string;
    } elseif ($name == "year") {
      $info->year = strip_tags($string);
    } elseif ($name == "volume") {
      $info->volume = strip_tags($string);
    } elseif ($name == "pages") {
      $info->pages = strip_tags($string);
    } elseif ($name == "number") {
      $info->issue = strip_tags($string);
    } elseif ($name == "issue") {
      $info->issue = strip_tags($string);
    } elseif ($name == "abstract") {
      $info->abstract = strip_tags($string);
    } elseif ($name == "doi") {
      $info->doi = strip_tags($string);
    } elseif ($name == "isbn") {
      $info->doi = strip_tags($string);
    }
  }
}

$types = $db->query("SELECT * FROM paper_types ORDER BY name");

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $header ?>
    <title>Add Publication | <?php echo SITENAME ?></title>
    <script type="text/javascript" src="js/autosize.js"></script>
    <style type="text/css">
    textarea.form-control[name='abstract']{height:300px}
    textarea.form-control[name='abstract_english']{height:300px}
    .newsource{display:none}
    textarea[name='bibtex']{
      height:34px;
    }
    .bibtexsubmit{display:none}
    .foreignlanguage{display:none}
    </style>
    <script type="text/javascript">
    $(function(){
      $("input[name='doi']").keydown(function(){
        if ($(this).val() == "") {
          $(".publink").slideDown('slow');
        } else {
          $(".publink").slideUp('slow');
        }
      });
      $("select[name='source']").change(function(){
        if ($(this).val() == "unlisted") {
          $(".newsource").show('fast');
        } else {
          $(".newsource").hide('fast');
        }
      });
      $("textarea[name='bibtex']").focus(function(){
        $(".bibtexsubmit").show();
      });
      $("textarea[name='bibtex']").autosize();
      $("select[name='language']").change(function(){
        if ($(this).val() == "English") {
          $(".foreignlanguage").hide();
        } else {
          $(".foreignlanguage").show();
        }
      });
    });
    </script>
  </head>

  <body>

<?php require_once 'include.header.php'; ?>

<h1>Add Publication</h1>

<p>Do you know of a missing publication? Please add the details here so people
can find this! The publication should be related to urban metabolism research.</p>

<?php if ($id) { ?>

  <div class="alert alert-success">

  <p>Thanks, the paper has been submitted! This entry will be reviewed and most likely soon be added to the database.
  You will receive an e-mail confirmation when this paper has been added.</p>

  <?php if ($_GET['profile']) { ?>
    <p>
      <a href="profile/<?php echo $profile_id ?>/publication" class="btn btn-primary btn-large">Add another publication</a>
      <a href="profile/<?php echo $profile_id ?>/dashboard" class="btn btn-primary btn-large">Back to your dashboard</a>
    </p>
  <?php } else { ?>
    <p>
      <a href="publications/add" class="btn btn-primary btn-large">Add another publication</a>
      <a href="./" class="btn btn-primary btn-large">Back to the homepage</a>
    </p>
  <?php } ?>

  <?php if (defined("ADMIN")) { ?>
    <h2>Admin Tools</h2>
    <p>
      <a href="publication/<?php echo $id ?>" class="btn btn-large btn-warning">
        <i class="fa fa-gear"></i> View/edit/activate publication
      </a>
    </p>
  <?php } ?>

  </div>

<?php } else { ?>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Bibtex code</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="bibtex" required placeholder="If you have the Bibtex code available, paste it here, and the fields will be automatically filled. Example:
      
@article{author2015,
  title={Here goes the article title},
  author={Arduino, Elizabeth and McArthur, Bobby},
  etc.
}"></textarea>
    </div>
  </div>

  <div class="form-group bibtexsubmit">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Load Bibtex code</button>
    </div>
  </div>

</form>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Language</label>
    <div class="col-sm-10">
      <select name="language" class="form-control">
        <?php foreach ($languages as $value) { ?>
          <option value="<?php echo $value ?>"<?php if ($row['language'] == 'English') { echo ' selected'; } ?>><?php echo $value ?></option>
        <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Title</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="title" value="<?php echo $info->title ?>" required />
    </div>
  </div>

  <div class="form-group foreignlanguage">
    <label class="col-sm-2 control-label">Title in English</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="title_english" value="<?php echo $info->title ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Author(s)</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="author" value="<?php echo $info->author ?>" required />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Type</label>
    <div class="col-sm-10">
      <select name="type" class="form-control">
        <?php foreach ($types as $row) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->type || $row['id'] == 16 && !$id) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Journal / source / publisher</label>
    <div class="col-sm-10">
      <select name="source" class="form-control" required>
        <option value=""></option>
      <?php foreach ($journals as $row) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->source || $row['name'] == $info->journal_name) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
        <option value="unlisted">UNLISTED - add new option</option>
      </select>
    </div>
  </div>

  <div class="form-group newsource">
    <label class="col-sm-2 control-label">Other source</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="newsource" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Year of publication</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="year" value="<?php echo $info->year ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">DOI / ISBN</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="doi" value="<?php echo $info->doi ?>" placeholder="If provided, no link is necessary" />
    </div>
  </div>

  <div class="form-group publink">
    <label class="col-sm-2 control-label">Link for more information</label>
    <div class="col-sm-10">
      <input class="form-control" type="url" name="link" value="<?php echo $info->link ?>" placeholder="http://..." />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Abstract</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="abstract"><?php echo $info->abstract ?></textarea>
    </div>
  </div>

  <div class="form-group foreignlanguage">
    <label class="col-sm-2 control-label">Abstract in English</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="abstract_english"><?php echo $info->abstract ?></textarea>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Your name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="yourname" value="<?php echo $profile_info->firstname ?> <?php echo $profile_info->lastname ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Your e-mail</label>
    <div class="col-sm-10">
      <input class="form-control" type="email" name="youremail" value="<?php echo $profile_info->email ?>" />
    </div>
  </div>

  <fieldset>
    <legend>Optional Information</legend>
    
    <div class="form-group">
      <label class="col-sm-2 control-label">Volume</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="volume" value="<?php echo $info->volume ?>" placeholder="E.g. 15" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Issue</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="issue" value="<?php echo $info->issue ?>" placeholder="E.g. 4" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Pages</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="pages" value="<?php echo $info->pages ?>" placeholder="E.g. 150-173" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Comments</label>
      <div class="col-sm-10">
        <textarea class="form-control" name="comments"><?php echo $info->comments ?></textarea>
      </div>
    </div>

  </fieldset>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Add</button>
    </div>
  </div>

</form>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

  </body>
</html>
