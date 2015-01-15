angular.module('utf8convert')
    .controller('columnController', ['$http', '$scope',
    function($http, $scope) {

        $scope.conversion = 0;
        $scope.column = 0;

        $scope.setConversion = function(conversion)
        {
            $scope.conversion = conversion;
        }

        $scope.getConversion = function()
        {
            return $scope.conversion;
        }

        $scope.setColumn = function(column)
        {
            $scope.column = column;
        }

        $scope.getColumn = function()
        {
            return $scope.column;
        }

        $scope.setDataPointUrl = function(url)
        {
            $scope.dataPointUrl = url;
        }

        $scope.getDataPointUrl = function()
        {
            return $scope.dataPointUrl;
        }

        $scope.columnModel =
        {
            init: function(url, conversion, column)
            {
                $scope.setConversion(conversion);
                $scope.setColumn(column);
                $scope.setDataPointUrl(url);

                this.load(url + '?conversion=' + conversion + '&column=' + column);
            },

            load: function(url)
            {
                $scope.columnPromise = $http({
                    method: 'get',
                    timeout: 180000,
                    url: url
                }).success(function(data) {
                    $scope.dataPoint = data;

                    console.log(data);
                });

                return $scope.columnPromise;
            },

            updateNewValue: function(dataPoint, newValue)
            {
				url = $scope.getDataPointUrl() + '/' + dataPoint.id;

                $scope.columnUpdateNewValuePromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: url,
                    data: {
						'newValue': newValue,
					}
                }).success(function(data) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return;
						}
					});
                });

                return $scope.columnUpdateNewValuePromise;
			},

            toggleFlagged: function(dataPoint)
            {
				url = $scope.getDataPointUrl() + '/' + dataPoint.id;

                $scope.columnToggleFlaggedPromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: url,
                    data: {
						'flagged': !dataPoint.flagged,
						'approved': false
					}
                }).success(function(data) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return;
						}
					});
                });

                return $scope.columnToggleFlaggedPromise;
			},

            toggleApproved: function(dataPoint)
            {
				url = $scope.getDataPointUrl() + '/' + dataPoint.id;

                $scope.columnToggleFlaggedPromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: url,
                    data: {
						'approved': !dataPoint.approved,
						'flagged': false
					}
                }).success(function(data) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return;
						}
					});
                });

                return $scope.columnToggleFlaggedPromise;
			}
        }
    }
]);
