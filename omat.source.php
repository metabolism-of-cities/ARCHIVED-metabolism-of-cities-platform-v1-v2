<?php
require_once 'functions.php';
require_once 'functions.omat.php';
$section = 6;
$load_menu = 1;
$sub_page = 2;

$id = (int)$_GET['id'];
$project = (int)$_GET['project'];

$types = $db->query("SELECT * FROM mfa_sources_types WHERE dataset = $project ORDER BY name");

if ($id) {
  $info = $db->record("SELECT * FROM mfa_sources WHERE id = $id AND dataset = $project");
  if (!count($info)) {
    die("Record not found");
  }
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'type' => $_POST['type'] ? (int)$_POST['type'] : NULL,
    'belongs_to' => $_POST['belongs_to'] ? (int)$_POST['belongs_to'] : NULL,
    'details' => html($_POST['details']),
    'dataset' => $project,
    'url' => html($_POST['url']),
  );
  if ($id) {
    $db->update("mfa_sources",$post,"id = $id");
  } else {
    $db->insert("mfa_sources",$post);
    $id = $db->lastInsertId();
  }
  header("Location: " . URL . "omat/$project/viewsource/$id");
  exit();
}

$organizations = $db->query("SELECT id,name FROM mfa_contacts WHERE dataset = $project ORDER BY name");

$belongs_to = $info->belongs_to ?: $_GET[0];

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $header ?>
    <title>Sources | <?php echo SITENAME ?></title>
    <script type="text/javascript" src="js/autosize.js"></script>
    <script type="text/javascript">
    $(function(){
      $("textarea").autosize();
      $("input[name='organization']").change(function(){
        if ($("input[name='organization']").is(":checked")) {
          $("#employer").slideUp('fast');
        } else {
          $("#employer").slideDown('fast');
        }
      });
      $("input[name='organization']").change();
    });
    </script>
  </head>

  <body class="omat">

<?php require_once 'include.header.php'; ?>

  <h1>Sources</h1>

  <ol class="breadcrumb">
    <li><a href="omat/<?php echo $project ?>/dashboard">Dashboard</a></li>
    <li><a href="omat/<?php echo $project ?>/sources">Sources</a></li>
    <?php if ($id) { ?>
      <li><a href="omat/<?php echo $project ?>/viewsource/<?php echo $id ?>"><?php echo $info->name ?></a></li>
    <?php } ?>
    <li class="active"><?php echo $id ? "Edit" : "Add" ?> Source</li>
  </ol>

  <form method="post" class="form form-horizontal">

    <div class="form-group">
      <label class="col-sm-2 control-label">Name</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="name" value="<?php echo $info->name ?>" />
      </div>
    </div>

    <?php if (count($organizations)) { ?>

      <div class="form-group">
        <label class="col-sm-2 control-label">Belongs to</label>
        <div class="col-sm-10">
          <select name="belongs_to" class="form-control">
              <option value=""></option>
            <?php foreach ($organizations as $row) { ?>
              <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $belongs_to) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

    <?php } ?>


    <?php if (count($types)) { ?>

      <div class="form-group">
        <label class="col-sm-2 control-label">Classification</label>
        <div class="col-sm-10">
          <select name="type" class="form-control">
              <option value=""></option>
            <?php foreach ($types as $row) { ?>
              <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->type) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

    <?php } ?>

    <div class="form-group">
      <label class="col-sm-2 control-label">URL</label>
      <div class="col-sm-10">
        <input class="form-control" type="url" name="url" value="<?php echo $info->url ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Notes</label>
      <div class="col-sm-10">
        <textarea class="form-control" name="details"><?php echo br2nl($info->details) ?></textarea>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  
  </form>


<?php require_once 'include.footer.php'; ?>

  </body>
</html>
