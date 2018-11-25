app = angular.module('myApp')

app.filter('customFilter', () ->
	return (input, search) ->
		if (!input)
			return input
		if (!search)
			return input
		expected = ('' + search).toLowerCase()
		result = {}
		angular.forEach(input, (value, key) -> 
			actual = ('' + value.MarketName).toLowerCase()
			if (actual.indexOf(expected) != -1) 
				result[key] = value
		)
		result
)
app.filter("customFormat", () ->
	return (input, maxDecimal) ->
		valor = 100-(input.Last*100)/input.PrevDay
		input.variation = if valor > 0 then 'positive' else if valor < 0 then 'negative' else 'zero'
		"" + Math.round(valor*100)/100

)

app.filter("customShowMarket", () ->
	return (input) ->
		selected = []
		_.each input, (m) ->
			if m.Show
				selected.push(m)
		selected
)