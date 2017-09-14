<?php
$skip_login = true;
require_once 'functions.php';
require_once 'functions.omat.php';
$section = 4;
$page = 4;

$id = (int)$_GET['id'];
$hash = $_GET['hash'];

if (!$_GET['hash'] && !defined("ADMIN")) {
  $sql = "AND papers.status = 'active'";
}

$info = $db->record("SELECT papers.*, sources.name, paper_types.name AS type
FROM papers 
  JOIN sources ON papers.source = sources.id
  JOIN paper_types ON papers.type = paper_types.id
WHERE papers.id = $id $sql");

$this_page = "Publication #$id";

if (!count($info)) {
  header("HTTP/1.0 404 Not Found");
  $info = $db->record("SELECT 'Publication not found' AS title");
  $notfound = true;
}

$gethash = encrypt($info->id . $info->title);

if ((defined("ADMIN") && !$_GET['test_mode']) || ($gethash == $_GET['hash'])) {
  $admin_mode = true;
  $hash = $gethash;
} elseif ($_GET['hash']) {
  // There is a hash but not valid; don't show data if not active
  $info = $db->record("SELECT papers.*, sources.name
  FROM papers 
    JOIN sources ON papers.source = sources.id
  WHERE papers.id = $id AND papers.status = 'active'");
}

if ($admin_mode && $_GET['tweet']) {
  require_once 'apis/functions.twitter.php';
  $print = tweet("New publication added: " . $info->title, URL . "publication/$id");
}

if ($_GET['statuschange']) {
  $print = "Status was changed";
}

if ($admin_mode) {
  if ($_POST['tag']) {
    $tag = html($_POST['tag']);
    $check = $db->query("SELECT * FROM tags WHERE tag = '$tag'");
    if (count($check)) {
      $error = "Tag already exists! Please make sure the name is unique.";
    } else {
      $post = array(
        'tag' => html($_POST['tag']),
        'parent' => (int)$_POST['parent'],
      );
      $db->insert("tags",$post);
      $get_id = $db->record("SELECT id FROM tags WHERE tag = '" . html($_POST['tag']) . "'");
      $new_tag_id = $get_id->id;
      $print = "New tag has been created";
      if ($_POST['parent'] == 4) {
        $print .= "<br /><strong>NOTE! </strong> New cities will only appear on the map if you enter the GPS coordinates.
        Please go to the <a href='tags/$new_tag_id/newtag'>Tag detail page</a> to set the coordinates.";
      }
    }
  }
  if ($_GET['status']) {
    $status = mysql_clean($_GET['status']);
    $db->query("UPDATE papers SET status = '$status' WHERE id = $id");
    header("Location: " . URL . "publication.view.php?id=$id&hash=$hash&statuschange=true");
    exit();
  }
  // In admin mode we show ALL the tags and allow the admin to activate/deactivate
  // them. In regular user mode, we only show the active tags.
  $tags = $db->query("SELECT tags.*, tags_parents.name AS parentname 
  FROM tags
    JOIN tags_parents ON tags.parent = tags_parents.id
  ORDER BY tags.parent, tags.tag");

  $papertags = $db->query("SELECT tag FROM tags_papers WHERE paper = $id");
  foreach ($papertags as $row) {
    $activetag[$row['tag']] = true;
  }
  $parents = $db->query("SELECT * FROM tags_parents ORDER BY name");
} else { 
  $tags = $db->query("SELECT DISTINCT tags.*, tags_parents.name AS parentname 
  FROM tags
    JOIN tags_papers ON tags.id = tags_papers.tag
    JOIN tags_parents ON tags.parent = tags_parents.id
  WHERE tags_papers.paper = $id
  ORDER BY tags.parent, tags.tag");
}

if ($info->doi) {
  $type_of_link = strpos($info->doi, "http") > -1 && !strpos($info->doi, "dx.doi.org") ? "web" : "doi";
  if ($type_of_link == "doi") {
    $type_of_link = strpos($info->doi, "/") > -1 ? "doi" : "isbn";
  }
}
$remove_dashes = array("-" => "");

$authors = authorlist($id, 'array');

if ($admin_mode && $_GET['authorscrape']) {
  $db->query("DELETE FROM people_papers WHERE paper = $id");
  $authors = nameScraper($info->author);
  if (is_array($authors)) {
    foreach ($authors as $author_id) {
      $post = array(
        'people' => $author_id,
        'paper' => $id,
      );
      $db->insert("people_papers",$post);
    }
  } else {
    die("No authors found! These were searched: " . $info->author);
  }
  header("Location: " . URL . "publication/$id");
  exit();
}
$dataviz = $db->query("SELECT * FROM datavisualizations WHERE paper = $id");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $header ?>
    <title><?php echo $info->title ?> <?php if (!$notfound) { ?>by <?php echo $info->author ?> (<?php echo $info->year ?>) <?php } ?> | <?php echo SITENAME ?></title>

    <style type="text/css">
    .panel{display:inline-block}
    dt,dd{padding:5px 0}
    ul#tags, ul#tags ul{list-style:none;margin-left:0;padding-left:0}
    ul#tags ul{margin-bottom:20px}
    ul#tags ul li{display:inline-block}
    ul#tags>li{font-weight:700}
    .right{float:right;margin-left:5px}
    .alert-warning{margin-top:40px}
    .status-deleted{opacity:0.5}
    <?php if (!$admin_mode) { ?>
      ul#tags li{display:inline-block;margin:0 5px 5px 0}
    <?php } ?>
    </style>
    <?php if ($admin_mode) { ?>
    <script type="text/javascript">
    $(function(){
      $("#tags a").click(function(e){
        var action = $(this).data("action");
        var getObject = $(this);
        $.post("ajax.php",{
          id: $(this).data("id"),
          paper: <?php echo $id ?>,
          action: action,
          dataType: "json"
        }, function(data) {
          if (action == "add") {
            getObject.removeClass("btn-default");
            getObject.addClass("btn-primary");
          } else {
            getObject.removeClass("btn-primary");
            getObject.addClass("btn-default");
          }
        },'json')
        .error(function(){
          $("body").html("Could not send data.");
        });
        e.preventDefault();
      });
    });
    </script>
  <?php } ?>
  </head>

  <body>

<?php require_once 'include.header.php'; ?>

<?php if ($admin_mode) { ?>
<div class="well">
  <a class="btn btn-default" style="margin-right:30px" href="cms/index">
  <i class="fa fa-lock"></i> Admin Panel
  </a>
  <a href="publication.view.php?id=<?php echo $id ?>&amp;test_mode=1" class="btn btn-primary">View as user</a>
  <a href="casestudy.edit.php?new=<?php echo $id ?>" class="btn btn-primary">Add to case study database</a>
  <a href="publication.edit.php?id=<?php echo $id ?>&amp;hash=<?php echo $hash ?>" class="btn btn-primary">Edit</a>
  <?php if ($info->status == 'active') { ?>
    <a href="publication.view.php?id=<?php echo $id ?>&amp;hash=<?php echo $hash ?>&amp;status=deleted" class="btn btn-danger right">Delete</a>
  <?php } else { ?>
    <a href="publication.view.php?id=<?php echo $id ?>&amp;hash=<?php echo $hash ?>&amp;status=active" class="btn btn-primary">Activate</a>
  <?php } ?>
  <a class="btn" href="publication.view.php?id=<?php echo $id ?>&amp;tweet=true" onclick="javascript:return confirm('This will send a tweet to our Twitter account. Are you sure?')"><i class="fa fa-twitter"></i></a>
</div>
<?php } ?>

<?php if ($notfound) { ?>

  <div class="alert alert-warning">
    <p>
      Sorry, the publication you requested was not found. Perhaps it was removed, or you opened a broken link. 
      Please <a href="page/contact">do contact us</a> if you came here from within our website. You can use our
      <a href="publications/list">list of publications</a> or <a href="publications/collections">view our collections</a>
      to find publications.
    </p>
    <p>
      <a href="./" class="btn btn-primary btn-large">Back to our homepage</a>
    </p>

  </div>

<?php } else { ?>

<h1><?php echo $info->title ?></h1>

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
  <?php if ($error) { echo "<div class=\"alert alert-danger\">$error</div>"; } ?>

  <?php if ($info->language != "English") { ?>
    <div class="alert alert-info">
      <strong>Note:</strong> this publication is published in <strong><?php echo $info->language ?></strong>.
      We aim to provide a translation of the title and the abstract in English for publications that are originally
      published in another language. Please note that these translations are not always perfect, but our goal is to 
      give an idea of the content of this publication. Some of the translations are provided by the authors; others
      are translated by us or others.
    </div>
  <?php } ?>

<dl class="dl dl-horizontal status-<?php echo $info->status ?>">

  <dt>Title</dt>
  <dd><?php echo $info->title_native ?: $info->title; ?></dd>

  <dt>Author(s)</dt>
  <?php if ($authors) { ?>
    <?php foreach ($authors as $key => $value) { ?>
      <dd><a href="people/<?php echo $key ?>-<?php echo flatten($value) ?>"><?php echo $value ?></a></dd>
    <?php } ?>
    <?php if ($admin_mode) { ?>
      <dd><a class="btn btn-info" href="publication.view.php?id=<?php echo $id ?>&amp;authorscrape=true">Re-classify authors</a></dd>
    <?php } ?>
  <?php } else { ?>
    <dd><?php echo $info->author ?: '<em>Author information unavailable</em>'; ?></dd>
    <?php if ($admin_mode) { ?>
      <dd><a class="btn btn-info" href="publication.view.php?id=<?php echo $id ?>&amp;authorscrape=true">Classify authors</a></dd>
    <?php } ?>
  <?php } ?>

  <dt>Year</dt>
  <dd><?php echo $info->year ?></dd>

  <dt>Type</dt>
  <dd><?php echo $info->type ?></dd>
  <dt>Source</dt>
  <dd>
    <a href="source/<?php echo $info->source ?>">
    <?php echo $info->name ?></a><?php if ($info->volume) { ?>, Volume <?php echo $info->volume ?><?php } if ($info->issue) { ?>, Issue <?php echo $info->issue ?><?php } if ($info->pages) { ?>, Pages <?php echo $info->pages ?><?php } ?></dd>

  <?php if ($info->doi && $type_of_link == "doi") { ?>
    <dt>DOI</dt>
    <dd><?php echo $info->doi ?></dd>
  <?php } ?>

  <?php if ($info->doi && $type_of_link == "isbn") { ?>
    <dt>ISBN</dt>
    <dd><?php echo $info->doi ?></dd>
  <?php } ?>

  <?php if ($info->open_access || $info->abstract_status == "author_approved" ||   $info->abstract_status == "journal_approved" || $info->abstract_status == "open_access" || $info->abstract_status == "toc_only" || $admin_mode || true) { ?>
    <dt>Abstract</dt>
    <dd><?php echo $info->abstract ?: '<em>Abstract unavailable</em>'; ?></dd>
    <?php if ($info->abstract_native) { ?>
      <dt>Original Abstract</dt>
      <dd><?php echo $info->abstract ?></dd>
    <?php } ?>
  <?php } ?>

  <?php if ($info->editor_comments) { ?>
    <dt>Our comments</dt>
    <dd><?php echo $info->editor_comments ?></dd>
  <?php } ?>

  <?php if ($info->open_access) { ?>
    <dt>Access</dt>
    <dd><i class="fa fa-unlock"></i> Open Access</dd>
  <?php } elseif ($info->open_access === "0") { ?>
    <dt>Access</dt>
    <dd><i class="fa fa-lock"></i> Paid / private access</dd>
  <?php } ?>

  <?php if ($info->doi || $info->link) { ?>
    <dt>More Information</dt>
    <dd>
      <?php if (!$info->link) { ?>
        <?php if ($type_of_link == "doi") { ?>
          <a href="<?php if (!strpos($info->doi, "dx.doi.org")) { ?>http://dx.doi.org/<?php } ?><?php echo $info->doi ?>"><?php if (!strpos($info->doi, "dx.doi.org")) { ?>http://dx.doi.org/<?php } ?><?php echo $info->doi ?></a>
        <?php } else { ?>
          <a href="http://www.google.com/search?tbo=p&tbm=bks&q=isbn:<?php echo strtr($info->doi, $remove_dashes) ?>">http://www.google.com/search?tbo=p&tbm=bks&q=isbn:<?php echo strtr($info->doi, $remove_dashes) ?></a>
        <?php } ?>
      <?php } else { ?>
        <a href="<?php echo $info->link ?>"><?php echo $info->link ?></a>
      <?php } ?>
    </dd>
  <?php } ?>

  <?php if (count($dataviz)) { ?>
    <dt>Data Visualizations</dt>
    <?php foreach ($dataviz as $row) { ?>
      <dd>

        <div class="panel panel-default">
          <div class="panel-heading"><?php echo $row['title'] ?></div>
          <div class="panel-body">
            <span>
              <a href="datavisualizations/<?php echo $row['id'] ?>-<?php echo flatten($row['title']) ?>">
                <img src="media/dataviz/<?php echo $row['id'] ?>.thumb.jpg" alt="" />
              </a>
            </span>
            <br />
              <a href="datavisualizations/<?php echo $row['id'] ?>-<?php echo flatten($row['title']) ?>">
                View visualization &raquo;
              </a>
          </div>
        </div>

        </dd>
    <?php } ?>
  <?php } ?>
    

</dl>

<h2>Tags</h2>

<?php if ($admin_mode || PRODUCTION) { ?>

<ul id="tags">
<?php foreach ($tags as $row) { ?>
  <?php if ($row['parentname'] != $parent && $admin_mode) { ?>
  <?php if ($parent) { ?>
      </ul>
  <?php } ?>
    <li><?php echo $row['parentname'] ?>
      <ul>
  <?php } $parent = $row['parentname']; ?>
  <li>
    <a 
      data-id="<?php echo $row['id'] ?>" 
      data-action="<?php echo $activetag[$row['id']] ? 'delete' : 'add'; ?>"
      href="tags/<?php echo $row['id'] ?>/<?php echo flatten($row['tag']) ?>" 
      class="btn btn-<?php echo $activetag[$row['id']] || !$admin_mode ? 'primary' : 'default'; ?>">
        <?php echo $row['tag'] ?>
    </a>
  </li>
<?php } ?>
  </ul>
</ul>

<?php } else { ?>

<?php foreach ($tags as $row) { ?>
<ul id="tags">
  <?php if ($row['parentname'] != $parent && $admin_mode) { ?>
  <?php if ($parent) { ?>
      </ul>
  <?php } ?>
    <li><?php echo $row['parentname'] ?>
      <ul>
  <?php } $parent = $row['parentname']; ?>
  <li>
    <a href="http://">
      <?php echo $parent ?>
    </a>
    &raquo;
  </li>
  <li>
    <a 
      data-id="<?php echo $row['id'] ?>" 
      data-action="<?php echo $activetag[$row['id']] ? 'delete' : 'add'; ?>"
      href="tags/<?php echo $row['id'] ?>/<?php echo flatten($row['tag']) ?>" 
      class="">
        <?php echo $row['tag'] ?>
    <span class="badge"><?php echo rand(33,99) ?></span>
    </a>
  </li>
  </ul>
<?php } ?>
</ul>
<?php } ?>

<?php if ($admin_mode) { ?>
  <h3>Add New Tag</h3>
  <form method="post">
    <p>
      <select name="parent" required>
        <option value=""></option>
      <?php foreach ($parents as $row) { ?>
        <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
      <input type="text" name="tag" required />
      <button type="submit" class="btn btn-primary">Add Tag</button>
    </p>
  </form>
<?php } ?>

<p>
  <a href="javascript:history.back()" class="btn btn-info">Back</a>
  <a href="publication/<?php echo $id ?>/flag" class="btn btn-warning pull-right"><i class="fa fa-flag"></i> Incorrect or incomplete information? Click here to report this.</a>
</p>

<div class="alert alert-warning">
  This website provides meta data on papers and other publications, with links
  to the original publications. These papers may be copyrighted or otherwise
  protected by the publishing journal or author. Some journals provide open
  access to their publications.  When possible we will try to include abstracts
  and more details for open access publications. For more details, follow the
  link to the original document and/or contact the publisher/author. 
</div>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

  </body>
</html>
