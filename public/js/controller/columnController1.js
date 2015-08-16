angular.module('utf8convert')
    .controller('columnController', ['$http', '$scope',
    function($http, $scope) {

        var pusher = new Pusher('4a692f8bd0d32221b070');
        var channel = pusher.subscribe('column');
        channel.bind('update', function(data) {
            angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
                if (value.id == data.id) {

					// Restore angular variables
					angular.forEach(value, function(value, key) {
						if (key.charAt(0) == '_') {
							data[key] = value;
						}
					});

					if (!data.comment) {
						data._showComment = false;
					}

					// data._showData = !data._showData;

                    angular.forEach($scope.dataPoint._embedded.data_point[key], function(colValue, colKey) {
						if (typeof data[colKey] != 'undefined') {
							$scope.dataPoint._embedded.data_point[key][colKey] = data[colKey];
						}
					});

					$scope.$apply();
                }
            });
        });

        $scope.columnModel =
        {
			dateUrl: '',

            init: function(url, conversion, column)
            {
                this.baseUrl = url;
                this.load(url + '/api/data-point?conversion=' + conversion + '&column=' + column);
            },

			openUrl: function(dataPoint)
			{
				$scope.columnPromise = $http({
					method: 'post',
					timeout: 180000,
					url: $scope.columnModel.baseUrl + '/api/url',
					data: {
						dataPointId: dataPoint.id
					}
				}).success(function(data) {
					window.open(data.url);
				});

				return false;
			},

			hideNewValue: function(node)
			{
				console.log(node.$id);
				console.log($('#' + node.$id));
				$('#' + node.$id).closest('.new-value').removeClass('col-md-12').addClass('col-md-6');
			},

			addEditHelperButtons: function(dataPoint)
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

						button = $('<button type="button" alt="Inflate/Deflate textarea" class="btn btn-default"><i class="glyphicon glyphicon-expand"></i></button>');
						button.on('click', function(event) {
							dataPoint._maximized = ! dataPoint._maximized;

							textarea = $(this).parent().parent().find('textarea');

							if (dataPoint._maximized) {
								textarea.attr('cols', '120');
							} else {
								textarea.attr('cols', '50');
							}

							$scope.$apply();
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
                });

                return $scope.columnPromise;
			},

			flagAll: function(status)
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

					newData[key].approved = (status == 'approved');
					newData[key].flagged = (status == 'flagged');
					newData[key].denied = (status == 'denied');
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

			copyAll: function()
			{
				if (confirm('Are you sure?')) {
					angular.forEach($scope.dataPoint._embedded.data_point, function(value, key) {
						columnModel.update(value, {'newValue': value.oldValue})
					});
				}
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
