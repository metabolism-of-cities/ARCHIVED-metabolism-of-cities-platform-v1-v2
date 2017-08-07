<?php
$show_breadcrumbs = true;
require_once 'functions.php';
$id = (int)$_GET['id'];
$section = 4;
$page = $id;
$page = 5;

if ($id) {


  $info = $db->record("SELECT * FROM tags_parents WHERE id = $id");
  $this_page = $info->name;
  $list = $db->query("SELECT * FROM tags WHERE parent = $id ORDER BY tag");

  foreach ($list as $row) {
    $publications[$row['id']] = $db->query("SELECT papers.id, papers.title, papers.author, papers.year 
    FROM tags_papers 
      JOIN papers ON tags_papers.paper = papers.id
    WHERE tags_papers.tag = {$row['id']} AND papers.status = 'active' ORDER BY papers.year DESC, papers.title");
  }

} else {
  $home = true;
  $list = $db->query("SELECT id, name, 
    (SELECT COUNT(*) 
    FROM papers 
      JOIN tags_papers ON papers.id = tags_papers.paper
      JOIN tags ON tags_papers.tag = tags.id WHERE tags.parent = tags_parents.id) AS total
  FROM tags_parents ORDER BY parent_order");
  foreach ($list as $row) {
    $publications[$row['id']] = $db->query("SELECT tags.tag, tags.id,
      (SELECT COUNT(*) 
        FROM papers 
        JOIN tags_papers ON papers.id = tags_papers.paper
      WHERE tags_papers.tag = tags.id AND papers.status = 'active') AS total
    FROM tags
    WHERE tags.parent = {$row['id']} ORDER BY tags.tag");
  }
}
$all_tags = $db->query("SELECT * FROM tags ORDER BY tag");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $header ?>
    <title><?php echo $info->name ? "Collection: " . $info->name : "Publication Collections" ?> | <?php echo SITENAME ?></title>
    <?php if (!$home) { ?>
    <script type="text/javascript">
    $(function(){
      $(".nav-sidebar a").click(function(event){
             event.preventDefault();
             //calculate destination place
             var dest=0;
             if($(this.hash).offset().top > $(document).height()-$(window).height()){
                  dest=$(document).height()-$(window).height();
             }else{
                  dest=$(this.hash).offset().top;
             }
             dest = dest-50;
             //go to destination
             $('html,body').animate({scrollTop:dest}, 1000,'swing');
         });     
    });
    </script>
    <?php } ?>
    <link rel="stylesheet" href="css/sidebar.css" />
    <link rel="stylesheet" href="css/select2.min.css" />
    <style type="text/css">
      h2 {
        margin-top:0;
        padding-top:30px;
      }
      .alert .right {
        float:right;
        margin-top:-7px;
      }
      .resultbox .row {
        padding:10px;
      }
      .resultbox h4 {
        padding:10px 10px 0 10px;
      }
    </style>
  </head>

  <body>

<?php require_once 'include.header.php'; ?>

<?php if ($home) { ?>

<?php require_once 'include.search.php'; ?>

</div>
<div class="bg-blue text-white py-4">
            <div class="container">
              <div class="row text-center text-lg-left">

    <h1>Publication Collections</h1>
    <p>
      This section provides an overview of the main classifications that have been used to group the different publications together.
    </p>
  </div>

</div>
</div>
</div>
<div class="container">

  <?php foreach ($list as $row) { $pubs = $publications[$row['id']]; ?>
  <h2 id="section<?php echo $row['id'] ?>"><a href="publications/collections/<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a></h2>

  <div class="alert alert-secondary">
    <strong><?php echo $row['total'] ?></strong> publications found.
    <a href="publications/collections/<?php echo $row['id'] ?>" class="btn btn-default right">View All</a>
  </div>

  <?php if (count($publications[$row['id']])) { ?>

    <ul class="multicolumn">
    <?php foreach ($publications[$row['id']] as $subrow) { ?>
      <li><a href="tags/<?php echo $subrow['id'] ?>/<?php echo flatten($subrow['tag']) ?>"><?php echo $subrow['tag'] ?></a> (<?php echo $subrow['total'] ?>)</li>
    <?php } ?>
    </ul>

  <?php } ?>

<?php } ?>

<?php } else { ?>

  <div class="row">

    <div class="col-md-3">
      <ul class="nav nav-section-menu nav-sidebar">
        <?php foreach ($list as $row) { ?>
          <li>
            <a href="publications/collections/<?php echo $id ?>#section<?php echo $row['id'] ?>" class="nav-link">
              <?php echo $row['tag'] ?>
              <i class="fa fa-angle-right"></i>
            </a>
          </li>
        <?php } ?>
      </ul>
    </div>

    <div class="col-md-9 main">

      <h1><?php echo $info->name ?></h1>

      <?php foreach ($list as $row) { $pubs = $publications[$row['id']]; ?>
      <h2 id="section<?php echo $row['id'] ?>"><a href="tags/<?php echo $row['id'] ?>/<?php echo flatten($row['tag']) ?>"><?php echo $row['tag'] ?></a></h2>

      <?php echo $row['description'] ?>

      <div class="alert alert-info">
        <strong><?php echo count($pubs) ?></strong> publications found.
      </div>

      <?php if (count($pubs)) { ?>

        <table class="table table-striped">
          <tr>
            <th>Title</th>
            <th>Author(s)</th>
            <th>Year</th>
          </tr>
        <?php foreach ($pubs as $row) { ?>
          <tr>
            <td><a href="publication/<?php echo $row['id'] ?>"><?php echo $row['title'] ?></a></td>
            <td><?php echo $row['author'] ?></td>
            <td><?php echo $row['year'] ?></td>
          </tr>
        <?php } ?>
        </table>

        <?php } ?>

      <?php } ?>

    </div>

  </div>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

  </body>
</html>
