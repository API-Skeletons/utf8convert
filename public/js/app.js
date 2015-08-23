// Angular app
angular.module('utf8convert', ["xeditable", "cgBusy", "ui.bootstrap", "diff"])
	.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme
	})
	.filter('hasCorruptedData', [ function() {
			return function(data) {
				return (data.indexOf(String.fromCharCode(160)) >= 0);
			}
	    } ]
    )
    ;
