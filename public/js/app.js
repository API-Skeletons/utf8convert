// Angular app
angular.module('utf8convert', ["xeditable", "cgBusy", "ui.bootstrap", "diff"])
	.run(function(editableOptions) {
	  editableOptions.theme = 'bs3'; // bootstrap3 theme
	})
	;
