<?php
$show_breadcrumbs = true;
require_once 'functions.php';
$section = 2;
$page = 1;
$papers = $db->record("SELECT SQL_CACHE COUNT(*) AS total FROM papers WHERE status = 'active'");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $header ?>
    <title>About the Metabolism of Cities website | <?php echo SITENAME ?></title>
  </head>

  <body>

<?php require_once 'include.header.php'; ?>

<div class="jumbotron">

  <h1>Metabolism of Cities</h1>

<p>
Metabolism of Cities was originally set up (then called MFA-tools.net) to share information and results from a research project on the City of Cape Town. 
</p>
<p>
The team slowly expanded working on adding more publications on our publication database and creating our global urban metabolism database. The first face-to-face meeting happened in Surrey during the ISIE 2015 conference. Already there was some interest from the research community to the open access approach we had implemented.
</p>
<p>
Short after, the team grew even further. The online platform was now called Metabolism of Cities to better reflect our core interest but also as a reference to the pioneering article of Abel Wolman that in some way helped to shape the urban metabolism field.
</p>
<p>
Thanks to the Stakeholders Initiative we initiated, we had a great response from young students and established professionals alike and received continuous contributions from other people in the community. 
</p>
<p>
Eventually, Metabolism of Cities members started carrying out research that fueled the community platform. As such, Metabolism of Cities became a digital research lab with members in a 5 universities and countries. 
</p>
<p>
In mid-2017 we became a member of the GI-REC (Global Initiative for Resource Efficient Cities).
</p>

</div>

<h2>Website Content</h2>
  <p>
    The Metabolism of Cities website collects information useful to researchers involved in Urban Metabolism (UM) research.
    There are four primary sections:
  </p>

<div class="list-group">
  <a href="research/list" class="list-group-item">
    <h4 class="list-group-item-heading">Publications &amp; Research</h4>
    <p class="list-group-item-text">
      A list of current, ongoing research. Urban metabolism is currently undertaken throughout the world
      on many different levels (urban, national, regional), and with very different scopes (economy-wide, particular
      substances, etc.). This section aims to list currently ongoing research with the goal to encourage cooperation
      and to facilitate communication between different groups of researchers. Are you undertaking research? Add
      your project now! <br />
      You will furthermore find information on a variety of UM-related publications. These publications
      include methodology research, case studies, handbooks, and more. The database currently 
      includes <strong><?php echo $papers->total ?></strong> publications, and we encourage
      our visitors to add references to missing publications. 
    </p>
  </a>
  <a href="data" class="list-group-item">
    <h4 class="list-group-item-heading">Data</h4>
    <p class="list-group-item-text">
      At the open source Metabolism of Cities website we want to create a
      database with urban metabolism data and indicators. That is, we plan to
      examine a variety of research studies that have calculated particular
      values (material extraction, emissions, construction material use,
      imports, exports, etc.) for an urban/provincial region. The metabolism
      indicators will also take into account energy, water, air pollution as
      well as urban characteristics indicators. By creating one large
      masterlist of these values it is much easier for other researchers to see
      what values are out there and to compare their own data to other studies.
      We aim to do this at different spatial scales as well: region, city,
      municipalities, ... With this big masterlist it will therefore also
      become possible to identify indicators for resource use and pollution
      emission.
    </p>
  </a>
  <a href="stakeholders" class="list-group-item">
    <h4 class="list-group-item-heading">Stakeholders Initiative</h4>
    <p class="list-group-item-text">
      At the open source Metabolism of Cities website we want to create a
      database with urban metabolism data and indicators. That is, we plan to
      examine a variety of research studies that have calculated particular
      values (material extraction, emissions, construction material use,
      imports, exports, etc.) for an urban/provincial region. The metabolism
      indicators will also take into account energy, water, air pollution as
      well as urban characteristics indicators. By creating one large
      masterlist of these values it is much easier for other researchers to see
      what values are out there and to compare their own data to other studies.
      We aim to do this at different spatial scales as well: region, city,
      municipalities, ... With this big masterlist it will therefore also
      become possible to identify indicators for resource use and pollution
      emission.
    </p>
  </a>
  <a href="omat/about" class="list-group-item">
    <h4 class="list-group-item-heading">Online Material Flow Analysis Tool (OMAT)</h4>
    <p class="list-group-item-text">
      The <strong>O</strong>nline <strong>M</strong>aterial Flow <strong>A</strong>nalysis <strong>T</strong>ool (OMAT)
      is a free, open source tool that assists researchers in undertaking an MFA. It allows for data to be loaded into 
      an online database, with easy options for collaboration and data management. Data sets can be kept private or 
      can be publicly shared. 
    </p>
  </a>
</div>

<?php require_once 'include.footer.php'; ?>

  </body>
</html>
