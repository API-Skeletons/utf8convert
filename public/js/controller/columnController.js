angular.module('utf8convert')
    .controller('columnController', ['$http', '$scope',
    function($http, $scope) {

        $scope.columnModel =
        {
			dataPointUrl: '',

            init: function(url, conversion, column)
            {
                this.dataPointUrl = url;

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

            update: function(dataPoint)
            {
				url = this.dataPointUrl + '/' + dataPoint.id;

                $scope.columnUpdatePromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: url,
                    data: {
						'newValue': dataPoint.newValue,
						'flagged': dataPoint.flagged,
						'approved': dataPoint.approved
					}
                }).success(function(data) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return;
						}
					});
                });

                return $scope.columnUpdatePromise;
			}
        }
    }
]);
