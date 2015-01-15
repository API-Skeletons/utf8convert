// Angular app
angular.module('utf8convert', ["xeditable", "cgBusy"])
	.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'
	});
