angular.module "myApp",["ngRoute", "ngMaterial"] 
.config ($routeProvider) =>
	$routeProvider
		.when "/", {
			controller : "listController",
			templateUrl : "assets/templates/listMarkets.html"
		}
		.when "/home", {
			controller : "mainController"
			templateUrl : "assets/templates/seccionPrincipal.html"
		}