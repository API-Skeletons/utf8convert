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

			addConvertButton: function()
			{
				$('span.editable-buttons').each(function(index, node) {
					if ( ! $(node).find('button.convert').length) {
						button = $('<button type="button" class="btn btn-danger convert"><i class="glyphicon glyphicon-flash"></i></button>');
						button.on('click', function(event) {
							textarea = $(this).parent().parent().find('textarea');

							$scope.columnPromise = $http({
								method: 'post',
								timeout: 180000,
								url: $scope.columnModel.baseUrl + '/api/convert',
								data: {
									value: textarea.val()
								}
							}).success(function(data) {
								textarea.val(data.value);
								textarea.trigger('change');
							});
						});
						$(node).append(button);
					}
				});
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
							delete data._links;
							$scope.dataPoint._embedded.data_point[key].data = data;
							return;
						}
					});
				});
			},

			create: function(fromDataPoint, column, oldValue, newValue)
			{
				$scope.columnPromise = $http({
					method: 'post',
					timeout: 180000,
					url: this.baseUrl + '/api/data-point',
					data: {
						fromDataPointId: fromDataPoint.id,
						column: column,
						oldValue: oldValue,
						newValue: newValue
					}
				}).success(function(data) {
					fromDataPoint._showData = false;
					$scope.columnModel.data(fromDataPoint);
				});

				return $scope.columnPromise;
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

					data._showData = !data._showData;

					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						if (value.id == data.id) {
							$scope.dataPoint._embedded.data_point[key] = data;
							return $scope.columnModel.data($scope.dataPoint._embedded.data_point[key]);
						}
					});
                });

                return $scope.columnPromise;
			},

			approveAll: function()
			{
				var newData = angular.copy($scope.dataPoint._embedded.data_point);

				angular.forEach(newData, function(value, key) {
					delete newData[key].dataPointPrimaryKey;
					delete newData[key].convertWorker;
					delete newData[key].dataPointIteration;
					delete newData[key].user;
					delete newData[key].columnDef;
					delete newData[key].conversion;
					delete newData[key].oldValue;
					delete newData[key].newValue;

					angular.forEach(value, function(fieldValue, fieldKey) {
						if (fieldKey.charAt(0) == '_') {
							delete newData[key][fieldKey];
						}
					});

					newData[key].approved = true;
					newData[key].flagged = false;
					newData[key].denied = false;
				});

                $scope.columnPromise = $http({
                    method: 'patch',
                    timeout: 180000,
                    url: this.baseUrl + '/api/data-point',
                    data: newData
                }).success(function(data) {
					$scope.dataPoint._embedded.data_point = data._embedded.data_point;
				});
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
