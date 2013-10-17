/**
 * Admin customizer builder
 * 
 * @author Alfi Rizka <alfi@dedicated-it.com>
 * @opyright Atomic55.net
 */

(function(w) {
	"use strict";

	w.A55_Controls = {};
	w.A55_Controls._default = {
		/**
		 * will fire on control modal
		 */
		edit : function(data) {
			return data;
		},

		/**
		 * Will fire after control modal initialize, - event binding
		 */
		editCallback : function() {
		},

		/**
		 * Will fire if user save control - Validating data
		 */
		editDone : function(data) {
			return data;
		},

		/**
		 * Will fire on admin control information
		 */
		info : function() {
			var html = '';

			return html;
		}
	};

	/**
	 * BEGIN Range control
	 */
	w.A55_Controls.atomic55_range = {};
	w.A55_Controls.atomic55_range.info_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Minimum:</label><div class="control-input">{{control.min}}&nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Maximum:</label><div class="control-input">{{control.max}}&nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Steps:</label><div class="control-input">{{control.step}}&nbsp;</div></div>';

		return html;
	};
	w.A55_Controls.atomic55_range.edit_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Minimum:</label><div class="control-input"><input type="text" required restrict="[^0-9]" separator="" ng-model="control_modal_data.min" /></div></div>';
		html += '<div class="control-group"><label class="control-label">Maximum:</label><div class="control-input"><input type="text" required restrict="[^0-9]" separator="" ng-model="control_modal_data.max" /></div></div>';
		html += '<div class="control-group"><label class="control-label">Steps:</label><div class="control-input"><input type="text" required restrict="[^0-9]" separator="" ng-model="control_modal_data.step" /></div></div>';
		html += '<div class="control-group"><label class="control-label">Default:</label><div class="control-input"><input type="range"  class="a55-control-range-input" min="{{control_modal_data.min}}" max="{{control_modal_data.max}}" step="{{control_modal_data.step}}" ng-model="control_modal_data.setting.default" />{{control_modal_data.setting.default}}</div></div>';

		return html;
	};

	/**
	 * BEGIN Map control
	 */
	w.A55_Controls.atomic55_map = {};
	w.A55_Controls.atomic55_map.info_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Lat &amp; Lng:</label><div class="control-input">{{lat}}, {{lng}} &nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Map Type:</label><div class="control-input">{{map_type}}&nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Dimension:</label><div class="control-input">{{width}} &times; {{height}} &nbsp;</div></div>';
		html += '<div class="control-map-preview" ng-show="lat">Map Preview Here</div>';

		return html;
	};
	w.A55_Controls.atomic55_map.edit_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Lat &amp; Lng:</label><div class="control-input">{{lat}}, {{lng}} &nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Map Type:</label><div class="control-input">{{map_type}}&nbsp;</div></div>';
		html += '<div class="control-group"><label class="control-label">Dimension:</label><div class="control-input">{{width}} &times; {{height}} &nbsp;</div></div>';
		html += '<div class="control-map-preview" ng-show="lat">Map Preview Here</div>';

		return html;
	};

	/**
	 * BEGIN Color control
	 */
	w.A55_Controls.color = {};
	w.A55_Controls.color.info_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Default Value:</label><div class="control-input"><span class="color-preview" ng-show="control.setting.default" style="background-color:{{control.setting.default}}">{{control.setting.default}}</span>&nbsp;</div></div>';

		return html;
	};
	w.A55_Controls.color.edit_tpl = function() {
		var html = '';
		html += '<div class="control-group"><label class="control-label">Default Value:</label><div class="control-input"><input type="hidden" id="a55-modal-control-color-picker" ng-model="control_modal_data.setting.default" /></div></div>';

		return html;
	};
	w.A55_Controls.color.onEdit = function(control) {
		jQuery('#a55-modal-control-color-picker').wpColorPicker({
			color : control.setting['default'],
			defaultColor : control.setting['default'],
			change : function(event, ui) {
				control.setting['default'] = ui.color.toString();
			}
		}).wpColorPicker('color', control.setting['default'] || '#ef8000');

	};

	/**
	 * BEGIN Image control
	 */
	w.A55_Controls.image = {};
	w.A55_Controls.image.info_tpl = function() {
		var html = '';
		html += '<div class="control-group" ng-show="control.setting.default"><label class="control-label">Image Preview:</label><div class="control-input">&nbsp;</div></div><img class="control-image-preview" ng-show="control.setting.default" ng-src="{{control.setting.default}}" />';

		return html;
	};

	/**
	 * BEGIN radio control
	 */
	w.A55_Controls.radio = {};
	w.A55_Controls.radio.info_tpl = function() {
		var html = '<div class="control-group"><label class="control-label">Choices:</label><div class="control-input"><em class="a55-none" ng-show="control.choices.length==0">No choice</em>&nbsp;</div></div>';
		html += '<ul ng-hide="control.choices.length==0" class="control-choices">';
		html += '<li ng-repeat="choice in control.choices"><a title="delete option" class="a55-remove" ng-click="controlOptionDelete(control, choice)" ng-show="control.editable"></a> <span class="label" ng-show="!!choice[0]">{{choice[0]}}</span> {{choice[1]}}</li></ul>';

		return html;
	};
	w.A55_Controls.radio.edit_tpl = function() {
		var html = '<div class="control-group"><label class="control-label">Choices:</label><div class="control-input"><a ng-click="controlModalOptionAdd()">Add Option</a></div></div>';
		html += '<div class="modal-control-choice-item" ng-repeat="choice in control_modal_data.choices">';
		html += '<input type="radio" ng-model="control_modal_data.setting.default" value="{{choice[0] || choice[1]}}" name="choice" /><input type="text" placeholder="option label" class="modal-control-choice-label" ng-model="choice[1]" /><input class="modal-control-choice-value" placeholder="option value (optional)" type="text" ng-model="choice[0]" /><a class="a55-remove" ng-click="controlOptionDelete(null, choice)" title="delete option">&times;</a></div>';
		return html;
	};
	w.A55_Controls.select = w.A55_Controls.radio;
})(window);