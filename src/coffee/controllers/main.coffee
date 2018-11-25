
app = angular.module("myApp")

app.run ($rootScope) ->
		$rootScope.base_url
		$rootScope.autoUpdate = true
		$rootScope.autoSeconds = 5

app.service "MarketFunctions", ["$http", ($http) ->
	self = this
	self.markets = {}
	self.marketsList = {}
	self.marketsBase = []

	# self.getMarkets()
	return {
		saveAll : (list) ->
			self.markets = list
			self.marketsList = list

		getAll : () ->
			return self.markets

		removeMarket : (item) ->
			self.markets = _.filter(self.markets, (el) ->
				return item.MarketName != el.MarketName
			)
			self.markets

		cleanMarkets : () ->
			_.each self.markets, (item) ->
				item.Show = false
			self.markets
			# self.markets = []

		orderColumn : (column, currentOrder) ->
			boolOrder = 0
			self.markets = _.sortBy(self.markets, (item) ->
				if column == "MarketName"
					item[column]
				else if column == "Variation"
					100-(item.Last*100)/item.PrevDay
				else if column == "Show"
					return if !item.Show || item.Show == undefined then 1 else 0
				else
					Number(item[column])
			)
			if currentOrder == column+"-Up"
				currentOrder = column+"-Down"
				self.markets = self.markets.reverse()
				boolOrder = 2
			else if currentOrder == column+"-Down"
				currentOrder = "" 
				self.markets = self.marketsList
				boolOrder = 0
			else 
				currentOrder = column+"-Up"
				boolOrder = 1
			[self.markets, currentOrder]
	}

]
app.controller "loginController", ["$scope", "$http", "$location", (obj, http, navigate) ->
	obj.usuario =
		username : ""
		password : ""
	obj.login = () =>
		http
			url: $root.base_url+"/index.php/api/login/validarUsuario"
			method: "POST"
			headers: 
				"Content-type": "application/json"
			responseType:"json"
			data: obj.usuario
		.then(
			(data) ->
				resultado = data.data.userdata
				if resultado.result
					obj.login = false
					navigate.path("home")
					# console.log "redirect to inicio"
				else
					alert("Datos erroneos pedazo de logi")
				
		)
]
app.controller "listController", 
["$scope",
"$timeout",
"$interval",
"$http",
"$rootScope",
"$mdDialog",
"MarketFunctions"
, ($scope, $timeout, $interval, $http, $root, $mdDialog, MarketFunctions) ->
	$scope.markets = {}
	$scope.marketsBase = []
	$scope.selectMarket = ""
	$scope.activeTab = ""
	$scope.expanded = false
	$scope.expandedAux = false
	$scope.currentOrder = ""
	$scope.loading = true
	$scope.customFullscreen = false;
	$scope.autoUpdate = $root.autoUpdate
	$scope.autoSeconds = $root.autoSeconds

	$scope.$watch(
		() ->
			return $root.autoUpdate*$root.autoSeconds
		() ->
			$interval.cancel($root.intervalUpdate)
			if $root.autoUpdate*$root.autoSeconds*1000 != 0 && true
				$root.intervalUpdate = $interval(() ->
					$http
						url: $root.base_url+"/index.php/api/utiles/getMarkets"
						method: "GET"
						headers: 
							"Content-type": "application/json"
						responseType:"json"
					.then(
						(data) ->
							list = data.data.result
							_.each list, (item, i) ->
								_.each $scope.markets, (selected) ->
									if selected.MarketName == item.MarketName and selected.Show
										item.Show = true
										selected.Bid = item.Bid
										selected.Ask = item.Ask
										selected.Last = item.Last
										item.Changes = 
											Bid : []
											Ask : []

										item.Changes.Bid.push(item.Bid)
										item.Changes.Ask.push(item.Ask)
										item.Changes.Bid = _.first((_.sortBy _.uniq(item.Changes.Bid), (el) ->
											return el).reverse(), 5)
										item.Changes.Ask = _.first(_.sortBy(_.uniq(item.Changes.Ask), (el) ->
											return el
										), 5)
										item.Changes = selected.Changes
										$scope.selectMarket = item.MarketName
										$scope.markets = MarketFunctions.saveAll $scope.markets
					)

				, $root.autoUpdate*$root.autoSeconds*1000)
		, true)


	$http
		url: $root.base_url+"/index.php/api/utiles/getMarkets"
		method: "GET"
		headers: 
			"Content-type": "application/json"
		responseType:"json"
	.then(
		(data) ->
			$scope.markets = data.data.result
			$scope.markets = _.sortBy($scope.markets, (item) ->
				item["MarketName"]
			)

			_.each $scope.markets, (el) =>
				nombre = (el.MarketName).split("-")
				if !_.contains($scope.marketsBase, nombre[0]) 
					($scope.marketsBase).push(nombre[0])
			$scope.activeTab = $scope.marketsBase[0]
			# $scope.markets = $scope.markets;
			$scope.markets = MarketFunctions.saveAll $scope.markets
			$scope.loading = false
	)

	$scope.clickMarket = (item, update) ->
		if !item.Show || item.Show == undefined || update
			item.Show = true
			item.loading = true
			item.expandedMarket = false

			$scope.markets = MarketFunctions.saveAll $scope.markets
			if item.Changes == undefined
				item.Changes = {
					Bid : []
					Ask : []
				}
			$http
				url: $root.base_url+"/index.php/api/utiles/getMarket/market/"+item.MarketName
				method: "GET"
				headers: 
					"Content-type": "application/json"
				responseType:"json"
			.then(
				(data) ->
					item.Changes.Bid.push(data.data.result.Bid)
					item.Changes.Ask.push(data.data.result.Ask)
					item.Changes.Bid = _.first((_.sortBy _.uniq(item.Changes.Bid), (el) ->
						return el).reverse(), 5)
					item.Changes.Ask = _.first(_.sortBy(_.uniq(item.Changes.Ask), (el) ->
						return el
					), 5)

					item.Bid = data.data.result.Bid
					item.Ask = data.data.result.Ask
					item.Last = data.data.result.Last
					$scope.selectMarket = item.MarketName
					$scope.markets = MarketFunctions.saveAll $scope.markets
					item.loading = false
			)
		else
			item.Show = false
			$scope.markets = MarketFunctions.saveAll $scope.markets

	$scope.openSettings = ($event) ->
		# console.log $root.base_url
		$mdDialog.show 
			controller: listController,
			templateUrl: $root.base_url+"assets/templates/dialogSettings.html",
			parent: angular.element(document.body),
			targetEvent: $event,
			clickOutsideToClose:true,
			fullscreen: $scope.customFullscreen
		.then(
			(answer) ->
				$scope.status = "You said the information was \"" + answer + "\"."
			, () ->
				$scope.status = "You cancelled the dialog."
		)

	$scope.clickTab = (item) ->
		$scope.activeTab = item

	$scope.removeMarket = (item) ->
		# console.log i
		item.Show = false
		$scope.markets = MarketFunctions.saveAll $scope.markets

	$scope.cleanMarkets = () ->
		$scope.markets = MarketFunctions.cleanMarkets()

	$scope.orderColumn = (column) ->
		array = MarketFunctions.orderColumn(column, $scope.currentOrder)
		$scope.markets = array[0]
		$scope.currentOrder = array[1]

	$scope.expandMarkets = () ->
		if $scope.expanded
			$scope.expandedAux = !$scope.expanded
			$timeout(() ->
				$scope.expanded = !$scope.expanded
			, 500);
		else
			$scope.expanded = !$scope.expanded
			$timeout(() ->
				$scope.expandedAux = $scope.expanded
			, 250);

	listController = ($scope, $mdDialog) ->
		$scope.autoUpdate = $root.autoUpdate
		$scope.autoSeconds = $root.autoSeconds
		$scope.hide = () ->
			$mdDialog.hide()

		$scope.cancel = () ->
			$mdDialog.cancel()

		$scope.answer = (answer) ->
			$mdDialog.hide(answer)

		$scope.saveSettings = () ->
			$root.autoUpdate = $scope.autoUpdate
			$root.autoSeconds = $scope.autoSeconds
			$mdDialog.cancel()

			
]