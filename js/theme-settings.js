/**
 * Theme Settings
 * 
 * Dependencies: underscore, angularjs
 * 
 * @author Alfi Rizka
 * @copyright Atomic55.net
 */

/* global angular: true, CodeMirror: true, alert: true, confirm: true */

CodeMirror.commands.autocomplete = function(cm) {
	CodeMirror.showHint(cm, CodeMirror.hint.html);
};

var app = angular.module('a55_theme_settings', [ 'ui.bootstrap', 'ui.codemirror' ], function($dialogProvider) {
	$dialogProvider.options({
		backdropClick : false,
		dialogFade : true
	});
});

app.controller('A55ThemeCtrl', function($scope, $http, $timeout) {
	"use strict";

	var codemirror = {}, wp_media = {};

	$scope.data = angular.copy(window.a55_theme_options);
	$scope.data_changed = false;
	$scope.reset_btn_state = {
		customizer : false,
		'theme support' : false,
		'theme branding' : false,
		all : false
	};

	// codemirror options
	$scope.cmOption = {
		lineNumbers : true,
		tabMode : 'indent',
		theme : 'eclipse',
		mode : 'text/html',
		htmlMode : true,
		onLoad : function(_editor) {
			codemirror[_editor.getTextArea().name] = _editor;
			$scope.data_changed = false;
		},
		onChange : function() {
			$scope.data_changed = true;
		}
	};

	$scope.removeImage = function(name) {
		if (confirm('Are you sure want to delete ' + name + ' image?') === false) {
			return false;
		}

		$scope.data[name] = '';
		$scope.data_changed = true;
	};

	$scope.selectImage = function(name) {
		if (wp_media[name]) {
			wp_media[name].open();
			return;
		}

		// Create the media frame.
		wp_media[name] = window.wp.media({
			title : 'Select Image',
			library : {
				type : 'image'
			},
			button : {
				text : 'Choose',
				close : false
			}
		});

		// When an image is selected, run a callback.
		wp_media[name].on('select', function() {
			// Grab the selected attachment.
			var attachment = wp_media[name].state().get('selection').first();

			$timeout(function() {
				$scope.data[name] = attachment.attributes.url;
				$scope.data_changed = true;
			}, 100);

			// close media modal
			wp_media[name].close();
		});

		// Finally, open the modal.
		wp_media[name].open();
		return false;
	};

	$scope.resetSettings = function(reset_var) {
		var post_data;

		if (confirm('Are you sure want to reset ' + reset_var + ' settings?') === false) {
			return false;
		}

		post_data = {
			action : 'atomic55_theme_reset',
			reset : reset_var
		};

		if (reset_var === 'theme branding' || reset_var === 'all') {
			$scope.data_changed = false;

			codemirror.admin_header.getDoc().setValue('');
			codemirror.admin_footer.getDoc().setValue('');
			$scope.preview('admin_header');
			$scope.preview('admin_footer');
		}

		if (reset_var === 'all') {
			$scope.reset_btn_state['theme branding'] = true;
			$scope.reset_btn_state['theme support'] = true;
			$scope.reset_btn_state.customizer = true;
		} else {
			$scope.reset_btn_state[reset_var] = true;
		}

		// show loading
		$scope.is_busy = true;

		// post to server
		$http.post(window.ajaxurl, post_data, {
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded'
			}
		}).success(function(response) {
			if (response.error === true) {
				alert(response.err_msg);
			}
		});
	};

	$scope.brandingSave = function() {
		var post_data = angular.copy($scope.data);

		post_data.action = 'atomic55_theme_branding';

		$scope.data_changed = false;
		$scope.preview('admin_header');
		$scope.preview('admin_footer');

		// show loading
		$scope.is_busy = true;

		$scope.reset_btn_state['theme branding'] = false;

		// post to server
		$http.post(window.ajaxurl, post_data, {
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded'
			}
		}).success(function(response) {
			$scope.is_busy = false;
			if (response.error === true) {
				alert(response.err_msg);
			}
		}).error(function() {
			$scope.data_changed = true;
			$scope.is_busy = false;
		});
	};

	$scope.preview = function(name) {
		var html, target_el;

		if (!codemirror[name]) {
			return;
		}

		html = codemirror[name].getDoc().getValue();
		target_el = angular.element('#a55-theme-' + name).html(html);
	};

	$scope.formReset = function(name) {
		if (!name) {
			$scope.data = angular.copy(window.a55_theme_options);
		} else if ($scope.data[name] !== undefined) {
			$scope.data[name] = window.a55_theme_options[name];

			if (name === 'admin_header' || name === 'admin_footer') {
				codemirror[name].getDoc().setValue($scope.data[name]);
				$scope.preview(name);
			}
		}
	};

	window.onbeforeunload = function() {
		if ($scope.data_changed === true || $scope.customizer_changed === true) {
			return "You'll lose your changes if you leave.";
		}
		return;
	};
});