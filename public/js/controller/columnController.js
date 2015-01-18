angular.module('utf8convert')
    .controller('columnController', ['$http', '$scope',
    function($http, $scope) {

        $scope.columnModel =
        {
			dateUrl: '',

            init: function(url, conversion, column)
            {
                this.baseUrl = url;
                this.load(url + '/api/data-point?conversion=' + conversion + '&column=' + column);
            },

            load: function(url)
            {
                $scope.columnPromise = $http({
                    method: 'get',
                    timeout: 180000,
                    url: url
                }).success(function(data) {
                    $scope.dataPoint = data;
                });

                return $scope.columnPromise;
            },

			// Show the raw table data
			data: function(dataPoint)
			{
				if (dataPoint._showData) {
					dataPoint._showData = false;
					return;
				}

				dataPoint._showData = true;

				$scope.columnPromise = $http({
					method: 'get',
					timeout: 180000,
					url: this.baseUrl + '/api/data-point-data/' + dataPoint.id
				}).success(function(data) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == dataPoint.id) {
							$scope.dataPoint._embedded.data_point[key].data = data;
							return;
						}
					});
				});
			},

            update: function(dataPoint, data)
            {
                $scope.columnPromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: this.baseUrl + '/api/data-point/' + dataPoint.id,
                    data: data
                }).success(function(data) {
					// Restore angular variables
					angular.forEach(dataPoint, function(value, key) {
						if (key.charAt(0) == '_') {
							data[key] = value;
						}
					});

					if (!data.comment) {
						data._showComment = false;
					}

					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return;
						}
					});
                });

                return $scope.columnPromise;
			},

			showAllComments: function()
			{
				angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
					if (value.comment) {
						value._showComment = true;
					}
				});
			},

			hideAllComments: function()
			{
				angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
					value._showComment = false;
				});
			}
        }
    }
]);
