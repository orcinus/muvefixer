<?
error_reporting(E_ALL);
session_start();

if(!isset($_SESSION["id"]))
  $_SESSION["id"] = uniqid();

if($_FILES)
  if($_FILES["input"]["error"] > 0) {
    echo "Error!";
  } else {
    if(!file_exists('upload'))
      mkdir('upload', 0777, TRUE);
    if(!file_exists('upload/'.$_SESSION["id"]))
      mkdir('upload/'.$_SESSION["id"], 0777, TRUE);
    move_uploaded_file($_FILES["input"]["tmp_name"], "upload/" . $_SESSION["id"] . "/" . $_FILES["input"]["name"]);
    $_SESSION["upload_file"] = $_FILES["input"]["name"];
    $_SESSION["upload_timestamp"] = time();
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="ico/favicon.png">

    <title>mUVe 3D G-code Pre-Processor</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/muve.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="../../assets/js/html5shiv.js"></script>
      <script src="../../assets/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <div class="container">
    <div class="navbar navbar-inverse navbar-default">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">G-code Pre-Processor</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Process</a></li>
            <li><a href="#">Configure</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">About</a></li>
          </ul>
        </div><!--/.nav-collapse -->
    </div>

    <div class="jumbotron">
      <img src="img/mUVe-logo.png">
      <hr>
      <div id="step1" class="enabled">
        <h4><span class="numberCircle">1</span> Upload Slic3r File</h4>
        <div class="step-button">
          <button id="upload" type="button" class="btn btn-default" disabled>Upload</button>
        </div>
        <div class="upload-button">
          <form enctype="multipart/form-data" name="uploadform" action="index.php" method="post">
            <input name="input" type="file" class="filestyle" data-classButton="btn btn-primary" data-input="true" data-icon="false" data-buttonText="Choose file" data-classInput="input-small">
          </form>
        </div>
      </div>
      <hr>
      <div id="step2" class="disabled">
        <h4><span class="numberCircle">2</span> Pre-check</h4>
        <div class="step-button">
          <button id="check" type="button" class="btn btn-default" disabled>Check</button>
        </div>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            <span class="sr-only">0% Complete</span>
          </div>
        </div>
      </div>
      <hr>
      <div id="step3" class="disabled">
        <h4><span class="numberCircle">3</span> Process &amp; Save</h4>
        <div class="step-button">
          <button id="process" type="button" class="btn btn-default" disabled>Process</button>
        </div>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            <span class="sr-only">0% Complete</span>
          </div>
        </div>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Bootstrap upload button -->
    <script src="js/bootstrap-filestyle.min.js"></script>

    <script>
      $(':file').change(function() {
        if($(':file').filestyle('input')) {
          $('#upload').removeClass('btn-default')
                      .addClass('btn-primary')
                      .removeProp('disabled');
        } else {
          $('#upload').removeClass('btn-primary')
                      .addClass('btn-default')
                      .addProp('disabled');
        }
      });

      $('#upload').click(function() {
        if($(':file').filestyle('input')) {
          $('[name=uploadform]').submit();
        }
      });

      <? if(isset($_SESSION["upload_file"])): ?>
      $('#step2').removeClass('disabled');
      $('#check').removeClass('btn-default')
                 .addClass('btn-primary')
                 .removeProp('disabled')
                 .click(function() {
                    alert("foo");
                 });
      <? endif ?>
    </script>
  </body>
</html>