/**
 * Theme Settings
 * 
 * Dependencies: underscore, angularjs
 * 
 * @author Alfi Rizka
 * @copyright Atomic55.net
 */

/* global _: true, angular: true */

var app = angular.module('a55_theme_support', [ 'ui.bootstrap' ], function($dialogProvider) {
	$dialogProvider.options({
		backdropClick : false,
		dialogFade : true
	});
});
app.directive('restrict', function($parse) {
	return {
		restrict : 'A',
		require : 'ngModel',
		link : function(scope, iElement, iAttrs) {
			scope.$watch(iAttrs.ngModel, function(value) {
				if (!value) {
					return;
				}
				$parse(iAttrs.ngModel).assign(scope, value.toLowerCase().replace(new RegExp(iAttrs.restrict, 'g'), '').replace(/\s+/g, '-'));
			});
		}
	};
});
app.controller('A55ThemeCtrl', function($scope, $http) {
	'use strict';

	var orig_id = null;

	$scope.data = window.a55_theme_options;
	$scope.is_busy = false;
	$scope.opts = {
		backdropFade : true,
		dialogFade : true
	};
	$scope.editorOptions = {
		lineWrapping : true,
		lineNumbers : true,
		readOnly : 'nocursor',
		mode : 'php'
	};

	// fix data type, only when empty array, all data must be object, object not
	// have .length
	if ($scope.data.menus.length === 0) {
		$scope.data.menus = {};
	}
	if ($scope.data.sidebars.length === 0) {
		$scope.data.sidebars = {};
	}
	if ($scope.data.imageSizes.length === 0) {
		$scope.data.imageSizes = {};
	}

	$scope.modalClose = function() {
		$scope.menuModalShouldOpen = false;
		$scope.sidebarModalShouldOpen = false;
		$scope.imageModalShouldOpen = false;
	};
	$scope.autoSlug = function(source, target) {
		if (orig_id === null) {
			$scope[target] = $scope[source];
		}
	};

	/**
	 * convert camelCase, dashed & underscore to human text
	 */
	$scope.humanize = function(str) {
		var temp, i;

		if (str === null || str === undefined) {
			return '';
		}

		str = str.replace(/([a-z\d])([A-Z]+)/g, '$1 $2').replace(/([_\s]|[-\s])+/g, ' ').trim().toLowerCase();
		temp = str.split(' ');
		for (i = 0; i < temp.length; i++) {
			temp[i] = temp[i].substr(0, 1).toUpperCase() + temp[i].substring(1);
		}
		return temp.join(' ');
	};

	function save() {
		var postData;

		postData = angular.copy($scope.data);
		postData.action = 'atomic55_settings_theme';
		// remove default args,
		delete (postData.default_args);

		// show loading
		$scope.is_busy = true;

		// post to server
		$http.post(window.ajaxurl, postData, {
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded'
			}
		}).success(function() {
			$scope.is_busy = false;
		}).error(function() {
			$scope.is_busy = false;
		});
	}

	$scope.togglePane = function($event) {
		var $parent = angular.element($event.currentTarget).parent();
		if ($parent.hasClass('a55-disabled-true')) {
			return true;
		}

		$parent.toggleClass('a55-pane-expanded');
	};

	// ---- MENU ---- //
	$scope.menuCount = {
		0 : 'No Menu',
		one : '{} Menu',
		other : '{} Menus'
	};

	$scope.menuGetTotal = function() {
		return _.size($scope.data.menus);
	};

	$scope.menuDelete = function(menuId) {
		if (!$scope.data.menus[menuId]) {
			return false;
		}

		if ($scope.data.menus[menuId].disabled === true) {
			// re-enable menu
			$scope.data.menus[menuId].disabled = false;
		} else {
			// disable or delete
			if (window.confirm('Are you sure want to disable this menu?') === false) {
				return false;
			}

			if ($scope.data.menus[menuId].builtin === true) {
				$scope.data.menus[menuId].disabled = true;
			} else {
				delete ($scope.data.menus[menuId]);
			}
		}

		// save data to server
		save();
	};

	$scope.menuUpdate = function(menuId) {
		if ($scope.data.menus[menuId]) {
			orig_id = menuId;
			$scope.menu_modal_title = 'Update Menu';
			$scope.menu_modal_id = menuId;
			$scope.menu_modal_label = $scope.data.menus[menuId].label;
			$scope.menu_modal_readonly = $scope.data.menus[menuId].disabled;
		} else {
			orig_id = null;
			$scope.menu_modal_id = '';
			$scope.menu_modal_label = '';
			$scope.menu_modal_readonly = false;
			$scope.menu_modal_title = 'New Menu';
		}

		$scope.menuModalShouldOpen = true;
	};

	$scope.menuSave = function() {
		var id_exists = false, temp;

		if ($scope.menu_modal_id === '' || $scope.menu_modal_label === '') {
			window.alert('Menu name and ID is required');
			return false;
		}

		if (orig_id !== $scope.menu_modal_id) {
			angular.forEach($scope.data.menus, function(menu) {
				if (menu.id === $scope.menu_modal_id) {
					id_exists = true;
					return false;
				}
			});

			if (id_exists === true) {
				window.alert('Menu ID "' + $scope.menu_modal_id + '" already exist');
				return false;
			}
		}

		temp = {
			id : $scope.menu_modal_id,
			label : $scope.menu_modal_label,
			builtin : false,
			disabled : false
		};

		if ($scope.data.menus[orig_id]) {
			temp.builtin = $scope.data.menus[orig_id].builtin;
			temp.disabled = $scope.data.menus[orig_id].disabled;

			if (orig_id !== $scope.menu_modal_id) {
				// remove old data
				delete ($scope.data.menus[orig_id]);
			}
		}

		// assign to model
		$scope.data.menus[$scope.menu_modal_id] = temp;

		// save data to server
		save();

		// close modal
		$scope.modalClose();
	};

	// ----- SIDEBAR ----- //
	$scope.sidebarCount = {
		0 : 'No Sidebar',
		one : '{} Sidebar',
		other : '{} Sidebars'
	};

	$scope.sidebarGetTotal = function() {
		return _.size($scope.data.sidebars);
	};

	$scope.sidebarDelete = function(sidebarId) {
		if (!$scope.data.sidebars[sidebarId]) {
			return false;
		}

		if ($scope.data.sidebars[sidebarId].disabled === true) {
			// re-enable sidebar
			$scope.data.sidebars[sidebarId].disabled = false;
		} else {
			// disable or delete
			if (window.confirm('Are you sure want to disable this sidebar?') === false) {
				return false;
			}

			if ($scope.data.sidebars[sidebarId].builtin === true) {
				$scope.data.sidebars[sidebarId].disabled = true;
			} else {
				delete ($scope.data.sidebars[sidebarId]);
			}
		}

		// save data to server
		save();
	};

	$scope.sidebarUpdate = function(sidebarId) {
		if ($scope.data.sidebars[sidebarId]) {
			orig_id = sidebarId;
			$scope.sidebar_modal_title = 'Update Sidebar';
			$scope.sidebar_modal_readonly = $scope.data.sidebars[sidebarId].disabled;
			$scope.sidebar_modal_id = sidebarId;
			$scope.sidebar_modal_name = $scope.data.sidebars[sidebarId].name;
			$scope.sidebar_modal_description = $scope.data.sidebars[sidebarId].description;
			$scope.sidebar_modal_class = $scope.data.sidebars[sidebarId]['class'];
			$scope.sidebar_modal_before_title = $scope.data.sidebars[sidebarId].before_title;
			$scope.sidebar_modal_after_title = $scope.data.sidebars[sidebarId].after_title;
			$scope.sidebar_modal_before_widget = $scope.data.sidebars[sidebarId].before_widget;
			$scope.sidebar_modal_after_widget = $scope.data.sidebars[sidebarId].after_widget;
		} else {
			orig_id = null;
			$scope.sidebar_modal_title = 'New Sidebar';
			$scope.sidebar_modal_readonly = false;
			$scope.sidebar_modal_id = '';
			$scope.sidebar_modal_name = '';
			$scope.sidebar_modal_description = '';
			$scope.sidebar_modal_class = '';
			$scope.sidebar_modal_before_title = '';
			$scope.sidebar_modal_after_title = '';
			$scope.sidebar_modal_before_widget = '';
			$scope.sidebar_modal_after_widget = '';
		}

		$scope.sidebarModalShouldOpen = true;
	};

	$scope.sidebarSave = function() {
		var id_exists = false, temp;

		if ($scope.sidebar_modal_id === '' || $scope.sidebar_modal_name === '') {
			window.alert('Menu name and ID is required');
			return false;
		}

		if (orig_id !== $scope.sidebar_modal_id) {
			angular.forEach($scope.data.sidebars, function(sidebar) {
				if (sidebar.id === $scope.sidebar_modal_id) {
					id_exists = true;
					return false;
				}
			});

			if (id_exists === true) {
				window.alert('Sidebar ID "' + $scope.sidebar_modal_id + '" already exist');
				return false;
			}
		}

		temp = {
			id : $scope.sidebar_modal_id,
			name : $scope.sidebar_modal_name,
			description : $scope.sidebar_modal_description,
			'class' : $scope.sidebar_modal_class,
			before_title : $scope.sidebar_modal_before_title,
			after_title : $scope.sidebar_modal_after_title,
			before_widget : $scope.sidebar_modal_before_widget,
			after_widget : $scope.sidebar_modal_after_widget,
			builtin : false,
			disabled : false
		};

		if ($scope.data.sidebars[orig_id]) {
			temp.builtin = $scope.data.sidebars[orig_id].builtin;
			temp.disabled = $scope.data.sidebars[orig_id].disabled;

			// user has updated sidebar id
			if (orig_id !== $scope.sidebar_modal_id) {
				// remove old data
				delete ($scope.data.sidebars[orig_id]);
			}
		}

		// register new sidebar
		$scope.data.sidebars[$scope.sidebar_modal_id] = temp;

		// save data to server
		save();

		// close modal
		$scope.modalClose();
	};

	// ----- IMAGE SIZES ----- //
	$scope.imageSizeCount = {
		0 : 'No Image Size',
		one : '{} Image Size',
		other : '{} Image Sizes'
	};
	$scope.imageSizeGetTotal = function() {
		return _.size($scope.data.imageSizes);
	};

	$scope.imageSizeDelete = function(imageSizeId) {
		if (!$scope.data.imageSizes[imageSizeId]) {
			// data not exist
			return false;
		} else if ($scope.data.imageSizes[imageSizeId].protected === true) {
			return false;
		}

		if ($scope.data.imageSizes[imageSizeId].disabled === true) {
			// re-enable imageSize
			$scope.data.imageSizes[imageSizeId].disabled = false;
		} else {
			// disable or delete
			if (window.confirm('Are you sure want to disable this imageSize?') === false) {
				return false;
			}

			if ($scope.data.imageSizes[imageSizeId].builtin === true) {
				$scope.data.imageSizes[imageSizeId].disabled = true;
			} else {
				delete ($scope.data.imageSizes[imageSizeId]);
			}
		}

		// save data to server
		save();
	};

	$scope.imageSizeUpdate = function(imageSizeId) {
		if ($scope.data.imageSizes[imageSizeId]) {
			orig_id = imageSizeId;
			$scope.imageSize_modal_title = 'Update Image Size';
			$scope.imageSize_modal_readonly = $scope.data.imageSizes[imageSizeId].builtin;
			$scope.imageSize_modal_id = imageSizeId;
			$scope.imageSize_modal_name = $scope.data.imageSizes[imageSizeId].name;
			$scope.imageSize_modal_width = $scope.data.imageSizes[imageSizeId].width;
			$scope.imageSize_modal_height = $scope.data.imageSizes[imageSizeId].height;
			$scope.imageSize_modal_crop = $scope.data.imageSizes[imageSizeId].crop;
		} else {
			orig_id = null;
			$scope.imageSize_modal_title = 'New Image Size';
			$scope.imageSize_modal_readonly = false;
			$scope.imageSize_modal_id = '';
			$scope.imageSize_modal_name = '';
			$scope.imageSize_modal_width = '';
			$scope.imageSize_modal_height = '';
			$scope.imageSize_modal_crop = false;
		}

		$scope.imageModalShouldOpen = true;
	};

	$scope.imageSizeSave = function() {
		var id_exists = false, temp;

		if ($scope.imageSize_modal_id === '' || $scope.imageSize_modal_width === '' || $scope.imageSize_modal_height === '') {
			window.alert('Image ID, width & height is required');
			return false;
		}

		if (orig_id !== $scope.imageSize_modal_id) {
			angular.forEach($scope.data.imageSizes, function(imageSize) {
				if (imageSize.id === $scope.imageSize_modal_id) {
					id_exists = true;
					return false;
				}
			});

			if (id_exists === true) {
				window.alert('Image size ID "' + $scope.imageSize_modal_id + '" already exist');
				return false;
			}
		}

		temp = {
			id : $scope.imageSize_modal_id,
			name : $scope.imageSize_modal_name,
			width : $scope.imageSize_modal_width,
			height : $scope.imageSize_modal_height,
			crop : $scope.imageSize_modal_crop,
			builtin : false,
			disabled : false,
			protected : false
		};

		if ($scope.data.imageSizes[orig_id]) {
			// update mode
			temp.builtin = $scope.data.imageSizes[orig_id].builtin;
			temp.disabled = $scope.data.imageSizes[orig_id].disabled;
			temp.protected = $scope.data.imageSizes[orig_id].protected;

			// user change ID
			if (orig_id !== $scope.imageSize_modal_id) {
				// remove old data
				delete ($scope.data.imageSizes[orig_id]);
			}
		}

		// register new imageSize
		$scope.data.imageSizes[$scope.imageSize_modal_id] = temp;

		// save data to server
		save();

		// close modal
		$scope.modalClose();
	};
});