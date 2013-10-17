<?php
$option_name = 'a55_theme_branding';
$a55_theme_branding_options = get_option($option_name);
?>
<div id="a55-theme-others" ng-cloak ng-controller="A55ThemeCtrl">
	<tabset type="'pills'"> <tab heading="Branding">
	<form action="" class="form-horizontal">
		<div class="control-group">
			<label class="control-label">Login Logo:</label>
			<div class="control-input">
				<div ng-show="data.login" class="a55-img-preview">
					<img ng-src="{{data.login}}" />
				</div>
				<input type="button" class="button" value="Select Image"
					ng-click="selectImage('login')" /> <input ng-show="data.login"
					type="button" ng-click="removeImage('login')" class="button-danger button" value="Remove" /> <span class="description">max
					width = 320px</span>
			</div>
		</div>
		<br />
		<div class="control-group">
			<label class="control-label">Site Favicon:</label>
			<div class="control-input">
				<div ng-show="data.favicon" class="a55-img-preview">
					<img ng-src="{{data.favicon}}" />
				</div>
				<input type="button" class="button" value="Select Image"
					ng-click="selectImage('favicon')" /> <input ng-show="data.favicon"
					type="button" ng-click="removeImage('favicon')" class="button-danger button" value="Remove" /> <span
					class="description">recomended size: 16px &times; 16px</span>
			</div>
		</div>
		<br />
		<div class="control-group">
			Admin header <span class="description">(HTML syntax)</span>:
			<div class="a55-toolbar">
				<a ng-click="preview('admin_header')">Preview</a> | <a
					ng-click="formReset('admin_header')">Reset</a>
			</div>
		</div>
		<div class="control-group codemirror-wrp">
			<textarea ui-codemirror="cmOption" ng-model="data.admin_header"
				name="admin_header"><?php echo (isset($a55_theme_branding_options['admin_header']) ? esc_textarea($a55_theme_branding_options['admin_header']) : '') ?></textarea>
		</div>
		<br />
		<div class="control-group">
			Admin footer <span class="description">(HTML syntax)</span>:
			<div class="a55-toolbar">
				<a ng-click="preview('admin_footer')">Preview</a> | <a
					ng-click="formReset('admin_footer')">Reset</a>
			</div>
		</div>
		<div class="control-group codemirror-wrp">
			<textarea ui-codemirror="cmOption" ng-model="data.admin_footer"
				name="admin_footer"><?php echo (isset($a55_theme_branding_options['admin_footer']) ? esc_textarea($a55_theme_branding_options['admin_footer']) : '') ?></textarea>
		</div>
		<br /> <input class="button-primary" type="submit" value="Save"
			ng-disabled="!data_changed" ng-click="brandingSave()" /> <input
			class="button" type="reset" value="Reset" ng-click="formReset(null)"
			ng-disabled="!data_changed" />
	</form>
	</tab> <!-- 
	    <tab heading="Export Settings"> 
	        asdasd 
	    </tab>
	    --> <tab heading="Reset Settings">
	<div class="alert alert-error">
		<p>
			<strong>Reset Plugin Settings</strong>
		</p>
		<p>Click on "Reset" button your theme setting will be delete.</p>
		<button class="btn" type="button" ng-disabled="reset_btn_state.customizer"
			ng-click="resetSettings('customizer')">Reset Theme Customizer</button>
		<button class="btn" type="button" ng-disabled="reset_btn_state['theme support']"
			ng-click="resetSettings('theme support')">Reset Theme Support</button>
		<button class="btn" type="button" ng-disabled="reset_btn_state['theme branding']"
			ng-click="resetSettings('theme branding')">Reset Theme Branding</button>
		<button class="btn btn-danger" type="button" ng-disabled="reset_btn_state.customizer && reset_btn_state['theme support'] && reset_btn_state['theme branding']"
			ng-click="resetSettings('all')">Reset All</button>
	</div>
	</tab> </tabset>
</div>