/**
 * Theme Settings
 * 
 * Dependencies: underscore, codemirror, angularjs
 * 
 * TODO need better approach for handling drag n drop, current approach still
 * using DOM query to watch drag n drop
 * 
 * @author Alfi Rizka
 * @copyright Atomic55.net
 */
/* jshint globalstrict: true */
/* global _: true, CodeMirror: true, angular: true */
"use strict";
CodeMirror.commands.autocomplete = function(cm) {
	CodeMirror.showHint(cm, CodeMirror.hint.css);
};

var app = angular.module('a55_theme_settings', [ 'ui.bootstrap', 'ui.codemirror', 'ui.sortable', 'ngDragDrop' ], function($dialogProvider, $compileProvider) {
	$dialogProvider.options({
		backdropClick : false,
		dialogFade : true
	});

	$compileProvider.directive('controlCompiler', function($compile) {
		return function(scope, element, attrs) {
			scope.$watch(function(scope) {
				return scope.$eval(attrs.controlCompiler);
			}, function(value) {
				var html = '';

				if (window.A55_Controls[value.type] && typeof window.A55_Controls[value.type].info_tpl === 'function') {
					html = window.A55_Controls[value.type].info_tpl();
				}
				element.html(html);
				if (html) {
					$compile(element.contents())(scope);
				}
			});
		};
	});
	$compileProvider.directive('controlEditCompiler', function($compile) {
		return function(scope, element, attrs) {
			scope.$watch(function(scope) {
				return scope.$eval(attrs.controlEditCompiler);
			}, function(value) {
				var html = '';

				if (window.A55_Controls[value.type] && typeof window.A55_Controls[value.type].edit_tpl === 'function') {
					html = window.A55_Controls[value.type].edit_tpl();
				}
				element.html(html);
				if (html) {
					$compile(element.contents())(scope);
				}
				if (window.A55_Controls[value.type] && typeof window.A55_Controls[value.type].onEdit === 'function') {
					html = window.A55_Controls[value.type].onEdit(value);
				}
			});
		};
	});
	$compileProvider.directive('controlCodeCompiler', function() {
		return function(scope, element, attrs) {
			scope.$watch(function(scope) {
				return scope.$eval(attrs.controlCodeCompiler);
			}, function(control) {
				var var_name = (control.id || '').replace(/[^\w]/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/, '');
				var html = '&lt;?php $' + var_name + ' = ';
				var _default = scope.format_value(control.setting['default']);

				if (control.setting.type === 'option') {
					html += 'get_option';
				} else {
					html += 'get_theme_mod';
				}

				html += "('" + control.id + "'";

				// default
				if (_default) {
					html += ', ' + _default;
				}

				html += '); ?&gt;';
				element.html(html);
			});
		};
	});
});

app.directive('restrict', function($parse) {
	return {
		restrict : 'A',
		require : 'ngModel',
		link : function(scope, iElement, iAttrs) {
			scope.$watch(iAttrs.ngModel, function(value) {
				if (value === null || value === '' || value === undefined) {
					$parse(iAttrs.ngModel).assign(scope, '');
				} else {
					// stupid way to convert into string,
					// using new String() will raise warning on jshint
					value += '';

					iAttrs.separator = iAttrs.separator || '-';
					$parse(iAttrs.ngModel).assign(scope, value.toLowerCase().replace(new RegExp(iAttrs.restrict, 'g'), '').replace(/\s+/g, iAttrs.separator));
				}
			});
		}
	};
});

app.controller('A55ThemeCtrl', function($scope, $http) {
	var original_data, codemirror, codemirror_doc, not_theme_var, edit_id = null, auto_slug = false, css_orig_val = '', $el = {}, inc_priority = 10;

	original_data = angular.copy(window.a55_theme_options);
	not_theme_var = [ 'atomic55_map', 'dropdown-pages', 'upload' ];

	$scope.data = window.a55_theme_options;
	$scope.is_busy = false;
	$scope.css_changed = false;
	$scope.customizer_changed = false;
	$scope.section_modal_open = false;
	$scope.control_modal_open = false;

	// add link to customizer
	angular.element(document).ready(
			function() {
				$el.section_builder = angular.element('#a55-section-builder');
				angular.element('.nav-pills').append(
						'<li class="pull-right"><a href="./customize.php" target="wp_customizer" title="open Wordpress theme customizer in new window">Open Customizer</a></li>');
			});

	/**
	 * find control & section index
	 */
	function control_find(control) {
		var section_index, control_index;
		for (section_index = 0; section_index < $scope.data.sections.length; section_index++) {
			if ($scope.data.sections[section_index].disabled) {
				continue;
			}

			control_index = $scope.data.sections[section_index].controls.indexOf(control);

			if (control_index !== -1) {
				break;
			}
		}

		if (control_index === -1 || section_index === $scope.data.sections.length) {
			// control not found in section
			return false;
		}

		return {
			section : section_index,
			control : control_index
		};
	}

	/**
	 * Begin modal related setting & event callback
	 */
	// modal options
	$scope.modal_options = {
		backdropFade : true,
		dialogFade : true
	};

	// modal close
	$scope.modalClose = function() {
		$scope.section_modal_open = false;
		$scope.control_modal_open = false;

		if (auto_slug === true && edit_id !== null) {
			// remove control, user decided to cancel add new control
			$scope.controlDelete($scope.data.sections[edit_id.section].controls[edit_id.control], true);
		}
	};

	/**
	 * Begin Utility method
	 */

	// format default value
	$scope.format_value = function(val) {
		var html = '', _default;

		if (val) {
			if (isNaN(val)) {
				_default = val;

				// addslashes to ' and \
				_default = _default.replace(/('|\\)/g, '\\$1');

				// html special char
				_default = _default.replace('&', '&amp;');

				// convert NEW LINE to \n
				_default = _default.replace(/\n/g, '\\n');
				html += "'" + _default + "'";
			} else if (val === true) {
				html += 'TRUE';
			} else if (val === false) {
				html += 'FALSE';
			} else { // number
				html += val;
			}
		}

		return html;
	};

	// generate auto slug
	$scope.autoSlug = function(source, target, scope) {
		if (auto_slug === true) {
			$scope[scope][target] = $scope[scope][source];
		}
	};

	// pane toogler
	$scope.togglePane = function($event, className, disableClassName) {
		var $parent = angular.element($event.currentTarget).parent();

		className = className || 'a55-pane-expanded';
		disableClassName = disableClassName || 'a55-disabled-true';

		if ($parent.hasClass(disableClassName)) {
			return true;
		}

		$parent.toggleClass(className);
	};

	/**
	 * Begin Codemirror CSS theme customizer
	 */
	// event after codemirror editor loaded
	$scope.codemirrorLoaded = function(_editor) {
		codemirror = _editor;
		codemirror_doc = codemirror.getDoc();
		codemirror.focus();
		css_orig_val = codemirror_doc.getValue();
		$scope.css_changed = false;
	};

	// event when codemirror editor changed value
	$scope.codemirrorChanged = function() {
		$scope.css_changed = true;
	};

	// codemirror options
	$scope.cmOption = {
		lineNumbers : true,
		tabMode : 'indent',
		theme : 'eclipse',
		mode : 'css',
		onLoad : $scope.codemirrorLoaded,
		onChange : $scope.codemirrorChanged
	};

	// reset codemirror value to last save
	$scope.codemirrorReset = function() {
		codemirror_doc.setValue(css_orig_val);
		$scope.css_changed = false;
	};

	// refresh codemirror UI
	$scope.codemirrorRefresh = function() {
		codemirror.refresh();
	};

	// save codemirror editor value
	$scope.codemirrorSave = function() {
		var postData = {
			action : 'atomic55_theme_customcss',
			css : codemirror_doc.getValue()
		};

		css_orig_val = postData.css;
		$scope.css_changed = false;

		// show loading
		$scope.is_busy = true;

		// post to server
		$http.post(window.ajaxurl, postData, {
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded'
			}
		}).success(function(response) {
			$scope.is_busy = false;
			if (response.error === true) {
				window.alert(response.err_msg);
			}
		}).error(function() {
			$scope.is_busy = false;
		});
	};

	// insert theme customizer variable into codemirror editor
	$scope.isVar = function(control_type) {
		return not_theme_var.indexOf(control_type) === -1;
	};

	$scope.themeVar = function(control) {
		var text_replace, _default;

		if ($scope.isVar(control.type) === false) {
			return false;
		}

		if (control.setting.type === 'option') {
			text_replace = 'option:' + control.id;
		} else {
			text_replace = control.id;
		}
		_default = $scope.format_value(control.setting['default']);
		if (_default) {
			_default = _default.replace('&amp;', '&');
			text_replace += "(" + _default + ")";
		}

		text_replace = '<%= ' + text_replace + ' %>';
		codemirror_doc.replaceSelection(text_replace);
	};

	/**
	 * Begin Manage control
	 */
	$scope.controlAvailabelDragOpt = {
		connectToSortable : '.controls-wrp',
		helper : 'clone'
	};

	$scope.controlSortableOptions = {
		connectWith : '.controls-wrp',
		handle : '.a55-item-handle',
		axis : 'y',
		distance : 15,
		placeholder : 'a55-sortable-section-ghost',
		forcePlaceholderSize : true,
		update : function(e, ui) {
			var is_new_control = ui.item.data('drag'), parent = ui.item.parent();
			var i, siblings;

			if (is_new_control === true) {
				return;
			}

			siblings = parent.children();
			for (i = 0; i < siblings.length; i++) {
				angular.element(siblings[i]).scope().control.priority = (i + 1) * inc_priority;
			}

			// mark customizer has changed.
			$scope.customizer_changed = true;
		},
		receive : function(e, ui) {
			var is_new_control = ui.item.data('drag');
			var parent, this_scope, parent_scope, droped_el, siblings;
			var i, j;

			if (is_new_control === true) {
				/* receive new control from left pane (fields available) */
				// copy scope
				this_scope = angular.copy(ui.item.scope().control);
				// find droped element
				droped_el = jQuery($el.section_builder).find('[data-drag="true"]');
				parent = droped_el.parent();
				parent_scope = parent.scope();
				siblings = parent.children();
				// set priority
				for (i = 0; i < siblings.length; i++) {
					if (angular.element(siblings[i]).data('drag')) {
						this_scope.priority = (i + 1) * inc_priority;
					} else {
						angular.element(siblings[i]).scope().control.priority = (i + 1) * inc_priority;
					}
				}

				// replace undefined with copied scope
				parent_scope.section.controls = _.without(parent_scope.section.controls, undefined);
				parent_scope.section.controls.push(this_scope);

				// open control edit
				$scope.controlEdit(this_scope);
				// remove element
				droped_el.remove();
			} else {
				/* receive control from another section */
				parent = ui.item.parent();
				this_scope = ui.item.scope();
				parent_scope = parent.scope();

				// Receive control from another section,
				// build priority on section origin
				i = parent_scope.section.controls.indexOf(this_scope);
				if (i === -1) {
					// look for which section (origin)
					for (i = 0; i < $scope.data.sections.length; i++) {
						j = $scope.data.sections[i].controls.indexOf(this_scope);
						if (j > -1) {
							// copy to control to new section
							parent_scope.section.controls.push(angular.copy(this_scope));
							// delete control from origin section
							$scope.data.sections[i].controls.splice(j, 1);
							// reorder priority in origin section

							break;
						}
					}
				}
			}
			// mark customizer has changed.
			$scope.customizer_changed = true;
		}
	};

	// control edit option
	$scope.controlEditOption = function(control) {
		if (window.A55_Controls[control.type] && typeof window.A55_Controls[control.type].edit === 'function') {
			return window.A55_Controls[control.type].edit(control);
		}
		return '';
	};

	// delete option on control type radio & select
	$scope.controlModalOptionAdd = function() {
		$scope.control_modal_data.choices.push([ '', '' ]);
	};

	// delete option on control type radio & select
	$scope.controlOptionDelete = function(control, option) {
		var index, option_index;

		if (control === null) {
			option_index = $scope.control_modal_data.choices.indexOf(option);
			if (option_index === -1) {
				return;
			}

			$scope.control_modal_data.choices.splice(option_index, 1);

			// check is default value = deleted key
			if (option[0] === $scope.control_modal_data.setting['default']) {
				if ($scope.control_modal_data.choices.length > 0) {
					$scope.control_modal_data.setting['default'] = $scope.control_modal_data.choices[0][0];
				}
			}
		} else {
			index = control_find(control);

			if (index === false) {
				// control not found in section
				return false;
			}

			option_index = $scope.data.sections[index.section].controls[index.control].choices.indexOf(option);
			if (option_index === -1) {
				return;
			}

			// delete option item from choices
			$scope.data.sections[index.section].controls[index.control].choices.splice(option_index, 1);

			// check is default value = deleted key
			if (option[0] === $scope.data.sections[index.section].controls[index.control].setting['default']) {
				if ($scope.data.sections[index.section].controls[index.control].choices.length > 0) {
					$scope.data.sections[index.section].controls[index.control].setting['default'] = $scope.data.sections[index.section].controls[index.control].choices[0][0];
				}
			}

			// enable button save & reset
			$scope.customizer_changed = true;
		}
	};

	// delete control
	$scope.controlDelete = function(control, without_confirm) {
		var index;

		if (control.disabled === false) {
			if (without_confirm !== true) {
				// current state disabled = false, user want to disable control,
				// ask to confirm
				if (window.confirm('Are you sure want to delete "' + control.label + '" ?') === false) {
					return false;
				}
			}
		}

		index = control_find(control);
		if (index === false) {
			// control not found in section
			return false;
		}

		if (control.editable === false) {
			$scope.data.sections[index.section].controls[index.control].disabled = !control.disabled;
		} else {
			$scope.data.sections[index.section].controls.splice(index.control, 1);
		}

		// enable button save & reset
		$scope.customizer_changed = true;
	};

	// edit control
	$scope.controlEdit = function(control) {
		if (control.editable === false || control.disabled === true) {
			// control is not editable or disabled
			return;
		}

		edit_id = control_find(control);
		if (edit_id === false) {
			// control not found
			return false;
		}

		if (control.id) {
			$scope.control_modal_action = 'Update';
			auto_slug = false;
		} else {
			$scope.control_modal_action = 'Create';
			auto_slug = true;
		}

		$scope.control_modal_data = angular.copy(control);

		// open modal
		$scope.control_modal_open = true;
	};

	// done editing control (save data)
	$scope.controlEditDone = function() {// show loading
		var i, j;

		// validate data
		if (!$scope.control_modal_data.label || !$scope.control_modal_data.id) {
			window.alert('Control Label and ID is required');
			return false;
		}

		// is control id was change?
		if ($scope.data.sections[edit_id.section].controls[edit_id.control].id !== $scope.control_modal_data.id) {
			// check is new id duplicate
			for (i = 0; i < $scope.data.sections.length; i++) {
				for (j = 0; j < $scope.data.sections[i].controls.length; j++) {
					if ($scope.control_modal_data.id === $scope.data.sections[i].controls[j].id) {
						window.alert('Control ID "' + $scope.control_modal_data.id + '" already exist.\nChoose other name.');
						return false;
					}
				}
			}
		}

		// update to control scope
		$scope.data.sections[edit_id.section].controls[edit_id.control] = $scope.control_modal_data;

		// reset
		edit_id = null;
		$scope.control_modal_open = false;
		$scope.customizer_changed = true;
	};

	/**
	 * Begin Manage Section
	 */
	$scope.sectionSortableOptions = {
		handle : '.drag-handle',
		axis : 'y',
		distance : 15,
		placeholder : 'a55-sortable-section-ghost',
		forcePlaceholderSize : true,
		start : function() {
			// angular.element('#a55-section-builder').addClass('section-order-start');
		},
		stop : function() {
			// angular.element('#a55-section-builder').removeClass('section-order-start');
		},
		update : function() {
			var child = $el.section_builder.children(), i;

			for (i = 0; i < child.length; i++) {
				angular.element(child[i]).scope().section.priority = (i + 1) * inc_priority;
			}

			// mark customizer has changed.
			$scope.customizer_changed = true;
		},
		receive : function() {
			var child = $el.section_builder.children(), section_id, scp, i, j, el;

			// remove undefined, this from drag event
			i = $scope.data.sections.indexOf(undefined);
			$scope.data.sections.splice(i, 1);

			// remove dropped element
			for (i = 0; i < child.length; i++) {
				el = angular.element(child[i]);
				scp = el.scope();
				section_id = el.data('section-id');

				if (section_id) {
					el.remove();

					for (j = 0; j < $scope.data.sections.length; j++) {
						if ($scope.data.sections[j].id === section_id) {
							$scope.data.sections[j].priority = (i + 1) * inc_priority;
							$scope.data.sections[j].disabled = false;
							break;
						}
					}
				} else {
					scp.section.priority = (i + 1) * inc_priority;
				}
			}

			// mark customizer has changed.
			$scope.customizer_changed = true;
		}
	};
	$scope.sectionPredefinedDragableOpt = {
		connectToSortable : '#a55-section-builder',
		helper : 'clone'
	};

	// section delete (single delete)
	$scope.sectionDelete = function(section) {
		var index = $scope.data.sections.indexOf(section);

		if (index === -1) {
			return false;
		} else if (window.confirm('Are you sure want to delete "' + $scope.data.sections[index].title + '" section?') === false) {
			return false;
		}

		if ($scope.data.sections[index].editable === false) {
			$scope.data.sections[index].disabled = true;
		} else {
			delete ($scope.data.sections[index]);
		}

		// enable button save & reset
		$scope.customizer_changed = true;
	};

	// section edit
	$scope.sectionEdit = function(section) {
		$scope.section_modal_action = 'Update';
		auto_slug = false;

		if (section === null) {
			$scope.section_modal_action = 'Create';
			section = $scope.data.defaults.section;
			auto_slug = true;
			edit_id = null;
		} else {
			edit_id = $scope.data.sections.indexOf(section);
			if (edit_id === -1) {
				return; // not found
			}
		}

		if (section.editable === false || section.disabled === true) {
			// section is not editable or disabled
			return;
		}

		$scope.section_modal_data = angular.copy(section);
		$scope.section_modal_open = true;
	};

	// done edit section
	$scope.sectionEditDone = function() {
		// validate data
		if (!$scope.section_modal_data.title || !$scope.section_modal_data.id) {
			window.alert('Section title and ID is required');
			return false;
		}

		if (edit_id === null) {
			$scope.data.sections.push($scope.section_modal_data);
		} else {
			$scope.data.sections[edit_id] = $scope.section_modal_data;
		}

		$scope.section_modal_open = false;
		$scope.customizer_changed = true;
	};

	/**
	 * Customizer save & reset
	 */
	// save customizer settings
	$scope.customizerSave = function() {
		var postData;

		// build post data
		postData = {
			action : 'atomic55_settings_theme_customizer',
			sections : $scope.data.sections
		};

		// mark as unchange
		$scope.customizer_changed = false;

		// copy data for reset perpose
		original_data = angular.copy($scope.data);

		// show loading
		$scope.is_busy = true;

		// post to server
		$http.post(window.ajaxurl, postData, {
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded'
			}
		}).success(function(response) {
			$scope.is_busy = false;
			if (response.error === true) {
				$scope.customizer_changed = true;
				window.alert(response.err_msg);
				return false;
			}
		}).error(function() {
			$scope.customizer_changed = true;
			$scope.is_busy = false;
		});
	};

	// reset customizer setting to last save data
	$scope.customizerReset = function() {
		$scope.data.sections = [];
		$scope.data.sections = angular.copy(original_data.sections);
		$scope.customizer_changed = false;
	};

	// watch data TODO uncoment code below
	// window.onbeforeunload = function() {
	// if ($scope.css_changed === true || $scope.customizer_changed === true) {
	// return "You'll lose your changes if you leave.";
	// }
	// return;
	// };

});