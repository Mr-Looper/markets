(function() {
  angular.module("myApp", ["ngRoute", "ngMaterial"]).config((function(_this) {
    return function($routeProvider) {
      return $routeProvider.when("/", {
        controller: "listController",
        templateUrl: "assets/templates/listMarkets.html"
      }).when("/home", {
        controller: "mainController",
        templateUrl: "assets/templates/seccionPrincipal.html"
      });
    };
  })(this));

}).call(this);
