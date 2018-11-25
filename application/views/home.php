<!DOCTYPE html>
<html lang="es" ng-app="myApp">
  <head>


    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Markets</title>
    <!-- <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
    <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fonts.googleapis.css" media="screen"/>
    <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css" media="screen"/>
    <!-- <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/dataTables.min.css" media="screen"/> -->
    <!-- <link rel="stylesheet" href="https://ui.lumapps.com/lumx.css"> -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.8/angular-material.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/jquery-3.3.1.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/jquery-ui.min.js"></script>
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular.js"></script> -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/underscore-min.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-animate.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-aria.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-messages.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-route.min.js"></script>
  <!-- Angular Material Library -->
  <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.8/angular-material.min.js"></script>
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular-route.js"></script> -->
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular-animate.js"></script> -->
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular-material.js"></script> -->
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular-aria.js"></script> -->
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/angular-messages.js"></script> -->
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/lib/lumx/core/js/lumx.js"></script> -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/app.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/directives/directives.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/controllers/main.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/filters/filters.js"></script>
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
      <script src="assets/js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body style="background-color: #353942">
    <div ng-view></div>

  </body>
  <script type="">
    var app = angular.module('myApp').run(function($rootScope) {
        $rootScope.base_url = "<?php echo base_url(); ?>";
    })
  </script>
  <!--   <div id="header" ng-controller="headerController">
      <md-nav-bar md-selected-nav-item="currentNavItem">
        <md-nav-item  ng-repeat="item in menu" md-nav-href="#{{item.ID_MENU}}" name="{{item.ID_MENU}}">{{item.NOMBRE}}</md-nav-item>
      </md-nav-bar>
    </div>
    <div id="body" ng-controller="mainController" ng-view>
    </div>
    <div id="footer"></div>
    <div class="row" id="modalPrincipal" style="margin-bottom: 0">
      <div id="modal" class="modal" style="margin: auto"></div>

    </div>
    <div style="position: fixed; bottom: 10px; right: 10px; font-weight: bold; z-index: 999999"><span style="font-size: 11px; ">v<?php echo (isset($userdata["version"]) ? $userdata["version"] : "") ?></span></div> -->
</html>