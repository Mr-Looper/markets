(function() {
  var app;

  app = angular.module('myApp');

  app.filter('customFilter', function() {
    return function(input, search) {
      var expected, result;
      if (!input) {
        return input;
      }
      if (!search) {
        return input;
      }
      expected = ('' + search).toLowerCase();
      result = {};
      angular.forEach(input, function(value, key) {
        var actual;
        actual = ('' + value.MarketName).toLowerCase();
        if (actual.indexOf(expected) !== -1) {
          return result[key] = value;
        }
      });
      return result;
    };
  });

  app.filter("customFormat", function() {
    return function(input, maxDecimal) {
      var valor;
      valor = 100 - (input.Last * 100) / input.PrevDay;
      input.variation = valor > 0 ? 'positive' : valor < 0 ? 'negative' : 'zero';
      return "" + Math.round(valor * 100) / 100;
    };
  });

  app.filter("customShowMarket", function() {
    return function(input) {
      var selected;
      selected = [];
      _.each(input, function(m) {
        if (m.Show) {
          return selected.push(m);
        }
      });
      return selected;
    };
  });

}).call(this);
