(function() {
  var app;

  app = angular.module("myApp");

  app.run(function($rootScope) {
    $rootScope.base_url;
    $rootScope.autoUpdate = true;
    return $rootScope.autoSeconds = 5;
  });

  app.service("MarketFunctions", [
    "$http", function($http) {
      var self;
      self = this;
      self.markets = {};
      self.marketsList = {};
      self.marketsBase = [];
      return {
        saveAll: function(list) {
          self.markets = list;
          return self.marketsList = list;
        },
        getAll: function() {
          return self.markets;
        },
        removeMarket: function(item) {
          self.markets = _.filter(self.markets, function(el) {
            return item.MarketName !== el.MarketName;
          });
          return self.markets;
        },
        cleanMarkets: function() {
          _.each(self.markets, function(item) {
            return item.Show = false;
          });
          return self.markets;
        },
        orderColumn: function(column, currentOrder) {
          var boolOrder;
          boolOrder = 0;
          self.markets = _.sortBy(self.markets, function(item) {
            if (column === "MarketName") {
              return item[column];
            } else if (column === "Variation") {
              return 100 - (item.Last * 100) / item.PrevDay;
            } else if (column === "Show") {
              if (!item.Show || item.Show === void 0) {
                return 1;
              } else {
                return 0;
              }
            } else {
              return Number(item[column]);
            }
          });
          if (currentOrder === column + "-Up") {
            currentOrder = column + "-Down";
            self.markets = self.markets.reverse();
            boolOrder = 2;
          } else if (currentOrder === column + "-Down") {
            currentOrder = "";
            self.markets = self.marketsList;
            boolOrder = 0;
          } else {
            currentOrder = column + "-Up";
            boolOrder = 1;
          }
          return [self.markets, currentOrder];
        }
      };
    }
  ]);

  app.controller("loginController", [
    "$scope", "$http", "$location", function(obj, http, navigate) {
      obj.usuario = {
        username: "",
        password: ""
      };
      return obj.login = (function(_this) {
        return function() {
          return http({
            url: $root.base_url + "/index.php/api/login/validarUsuario",
            method: "POST",
            headers: {
              "Content-type": "application/json"
            },
            responseType: "json",
            data: obj.usuario
          }).then(function(data) {
            var resultado;
            resultado = data.data.userdata;
            if (resultado.result) {
              obj.login = false;
              return navigate.path("home");
            } else {
              return alert("Datos erroneos pedazo de logi");
            }
          });
        };
      })(this);
    }
  ]);

  app.controller("listController", [
    "$scope", "$timeout", "$interval", "$http", "$rootScope", "$mdDialog", "MarketFunctions", function($scope, $timeout, $interval, $http, $root, $mdDialog, MarketFunctions) {
      var listController;
      $scope.markets = {};
      $scope.marketsBase = [];
      $scope.selectMarket = "";
      $scope.activeTab = "";
      $scope.expanded = false;
      $scope.expandedAux = false;
      $scope.currentOrder = "";
      $scope.loading = true;
      $scope.customFullscreen = false;
      $scope.autoUpdate = $root.autoUpdate;
      $scope.autoSeconds = $root.autoSeconds;
      $scope.$watch(function() {
        return $root.autoUpdate * $root.autoSeconds;
      }, function() {
        $interval.cancel($root.intervalUpdate);
        if ($root.autoUpdate * $root.autoSeconds * 1000 !== 0 && true) {
          return $root.intervalUpdate = $interval(function() {
            return $http({
              url: $root.base_url + "/index.php/api/utiles/getMarkets",
              method: "GET",
              headers: {
                "Content-type": "application/json"
              },
              responseType: "json"
            }).then(function(data) {
              var list;
              list = data.data.result;
              return _.each(list, function(item, i) {
                return _.each($scope.markets, function(selected) {
                  if (selected.MarketName === item.MarketName && selected.Show) {
                    item.Show = true;
                    selected.Bid = item.Bid;
                    selected.Ask = item.Ask;
                    selected.Last = item.Last;
                    item.Changes = {
                      Bid: [],
                      Ask: []
                    };
                    item.Changes.Bid.push(item.Bid);
                    item.Changes.Ask.push(item.Ask);
                    item.Changes.Bid = _.first((_.sortBy(_.uniq(item.Changes.Bid), function(el) {
                      return el;
                    })).reverse(), 5);
                    item.Changes.Ask = _.first(_.sortBy(_.uniq(item.Changes.Ask), function(el) {
                      return el;
                    }), 5);
                    item.Changes = selected.Changes;
                    $scope.selectMarket = item.MarketName;
                    return $scope.markets = MarketFunctions.saveAll($scope.markets);
                  }
                });
              });
            });
          }, $root.autoUpdate * $root.autoSeconds * 1000);
        }
      }, true);
      $http({
        url: $root.base_url + "/index.php/api/utiles/getMarkets",
        method: "GET",
        headers: {
          "Content-type": "application/json"
        },
        responseType: "json"
      }).then(function(data) {
        $scope.markets = data.data.result;
        $scope.markets = _.sortBy($scope.markets, function(item) {
          return item["MarketName"];
        });
        _.each($scope.markets, (function(_this) {
          return function(el) {
            var nombre;
            nombre = el.MarketName.split("-");
            if (!_.contains($scope.marketsBase, nombre[0])) {
              return $scope.marketsBase.push(nombre[0]);
            }
          };
        })(this));
        $scope.activeTab = $scope.marketsBase[0];
        $scope.markets = MarketFunctions.saveAll($scope.markets);
        return $scope.loading = false;
      });
      $scope.clickMarket = function(item, update) {
        if (!item.Show || item.Show === void 0 || update) {
          item.Show = true;
          item.loading = true;
          item.expandedMarket = false;
          $scope.markets = MarketFunctions.saveAll($scope.markets);
          if (item.Changes === void 0) {
            item.Changes = {
              Bid: [],
              Ask: []
            };
          }
          return $http({
            url: $root.base_url + "/index.php/api/utiles/getMarket/market/" + item.MarketName,
            method: "GET",
            headers: {
              "Content-type": "application/json"
            },
            responseType: "json"
          }).then(function(data) {
            item.Changes.Bid.push(data.data.result.Bid);
            item.Changes.Ask.push(data.data.result.Ask);
            item.Changes.Bid = _.first((_.sortBy(_.uniq(item.Changes.Bid), function(el) {
              return el;
            })).reverse(), 5);
            item.Changes.Ask = _.first(_.sortBy(_.uniq(item.Changes.Ask), function(el) {
              return el;
            }), 5);
            item.Bid = data.data.result.Bid;
            item.Ask = data.data.result.Ask;
            item.Last = data.data.result.Last;
            $scope.selectMarket = item.MarketName;
            $scope.markets = MarketFunctions.saveAll($scope.markets);
            return item.loading = false;
          });
        } else {
          item.Show = false;
          return $scope.markets = MarketFunctions.saveAll($scope.markets);
        }
      };
      $scope.openSettings = function($event) {
        return $mdDialog.show({
          controller: listController,
          templateUrl: $root.base_url + "assets/templates/dialogSettings.html",
          parent: angular.element(document.body),
          targetEvent: $event,
          clickOutsideToClose: true,
          fullscreen: $scope.customFullscreen
        }).then(function(answer) {
          return $scope.status = "You said the information was \"" + answer + "\".";
        }, function() {
          return $scope.status = "You cancelled the dialog.";
        });
      };
      $scope.clickTab = function(item) {
        return $scope.activeTab = item;
      };
      $scope.removeMarket = function(item) {
        item.Show = false;
        return $scope.markets = MarketFunctions.saveAll($scope.markets);
      };
      $scope.cleanMarkets = function() {
        return $scope.markets = MarketFunctions.cleanMarkets();
      };
      $scope.orderColumn = function(column) {
        var array;
        array = MarketFunctions.orderColumn(column, $scope.currentOrder);
        $scope.markets = array[0];
        return $scope.currentOrder = array[1];
      };
      $scope.expandMarkets = function() {
        if ($scope.expanded) {
          $scope.expandedAux = !$scope.expanded;
          return $timeout(function() {
            return $scope.expanded = !$scope.expanded;
          }, 500);
        } else {
          $scope.expanded = !$scope.expanded;
          return $timeout(function() {
            return $scope.expandedAux = $scope.expanded;
          }, 250);
        }
      };
      return listController = function($scope, $mdDialog) {
        $scope.autoUpdate = $root.autoUpdate;
        $scope.autoSeconds = $root.autoSeconds;
        $scope.hide = function() {
          return $mdDialog.hide();
        };
        $scope.cancel = function() {
          return $mdDialog.cancel();
        };
        $scope.answer = function(answer) {
          return $mdDialog.hide(answer);
        };
        return $scope.saveSettings = function() {
          $root.autoUpdate = $scope.autoUpdate;
          $root.autoSeconds = $scope.autoSeconds;
          return $mdDialog.cancel();
        };
      };
    }
  ]);

}).call(this);
