<?php 
require('./app/init.php');
if (!authenticate()) {
    header("Location: ".baseUri()."/login ");
    exit; // Important to exit to prevent further execution
}
?>
<!doctype html>
<html lang="en" data-bs-theme="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aaytiin Property Management System</title>
  <!--favicon-->
  <link rel="icon" href="<?=baseUri();?>/public/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
  <link href="<?=baseUri();?>/public/css/font-awesome/css/all.min.css" rel="stylesheet">
	<link href="<?=baseUri();?>/public/css/pace.min.css" rel="stylesheet">
	<script src="<?=baseUri();?>/public/js/pace.min.js"></script>
  <link href="<?=baseUri();?>/public/css/utilities.css" rel="stylesheet">
  <!--plugins-->
  <link href="<?=baseUri();?>/public/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?=baseUri();?>/public/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="<?=baseUri();?>/public/plugins/metismenu/mm-vertical.css">
  <link rel="stylesheet" type="text/css" href="<?=baseUri();?>/public/plugins/simplebar/css/simplebar.css">
  <link rel="stylesheet" type="text/css" href="<?=baseUri();?>/public/plugins/pikaday/css/pikaday.css">
  <!--bootstrap css-->
  <link href="<?=baseUri();?>/public/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="<?=baseUri();?>/public/css/sumo_select.css" rel="stylesheet">
  <!--main css-->
  <link href="<?=baseUri();?>/public/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/css/bootstrap-extended.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/css/bootstrap.bundle.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/main.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/dark-theme.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/blue-theme.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/semi-dark.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/bordered-theme.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/sass/responsive.css" rel="stylesheet">
  <link href="<?=baseUri();?>/public/css/styles.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
  <script type="text/javascript">
    let settings = JSON.parse(localStorage.getItem('settings'));
    // console.log(settings)
    if (settings) {
        document.documentElement.removeAttribute('data-bs-theme');
        document.documentElement.setAttribute('data-bs-theme', settings.theme);

        

    }
  </script>
</head>



<body>