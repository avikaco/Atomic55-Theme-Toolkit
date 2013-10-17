
<!-- BEGIN: <?php echo  __FILE__ ?> -->


<div id="a55-theme-customizer" ng-cloak ng-controller="A55ThemeCtrl">
	<tabset type="'pills'"> <!-- BEGIN BUILD CUSTOMIZER --> <tab
		heading="Build Customizer">

	<div class="nav-tab-content" id="a55-theme-options">
		<div class="row-fluid">
			<div class="span3">
				<h3>Predefined Section:</h3>
				<div id="a55-theme-section">
					<div
    					data-section-id="{{section.id}}"
						ng-repeat="section in data.sections | filter: {editable: false} | orderBy:'title'"
						ng-model="sections[$index]" data-drag="{{section.disabled}}"
						jqyoui-options="sectionPredefinedDragableOpt"
						jqyoui-draggable="{index: {{$index}}, placeholder: true, animate:true}">
						<div
							class="section ng-class:{off: !section.disabled}">
							<h3 class="section-title">{{section.title}}</h3>
						</div>
					</div>
				</div>

				<h3>Fields:</h3>
				<div id="a55-theme-field">
					<div class="control-item"
						ng-repeat="control in data.control_available | orderBy:'label'"
						data-drag="true" jqyoui-options="controlAvailabelDragOpt"
						jqyoui-draggable="{index: {{$index}}, placeholder: true, animate:true}">
						<h4 class="control-title">
							<span class="a55-control-icon type-{{control.type}}"></span>
							{{control.label}}
						</h4>
					</div>
				</div>
			</div>
			<div class="span9">
				<div class="row-fluid">
					<div class="span3">

						<button class="button-primary" ng-click="sectionEdit(null)">Add
							new section</button>
					</div>
					<div class="span9">
						<div class="pull-right">
							<img
								src="<?php echo Atomic55_Plugin::$url ?>images/ajax-loader.gif"
								alt="loading" ng-show="is_busy" />
							<button class="button-primary" ng-disabled="!customizer_changed"
								ng-click="customizerSave()">Save Changes</button>
							<button class="button" ng-disabled="!customizer_changed"
								ng-click="customizerReset()">Reset</button>
						</div>
					</div>
				</div>
				<div id="a55-section-builder" ui-sortable="sectionSortableOptions"
					ng-model="data.sections">

					<div class="section a55-open"
						ng-repeat="section in data.sections | filter: {disabled: false} | orderBy:'priority'">
						<div class="a55-section-toolbar">
							<span class="a55-setting setting-{{section.editable}}"
								title="settings" ng-click="sectionEdit(section)"></span> <span
								class="drag-handle"
								title="drag me to set order"></span> <span class="a55-remove"
								ng-click="sectionDelete(section)" title="Disable section"></span>
						</div>
						<h3 class="section-title"
							ng-click="togglePane($event, 'a55-open')">
							<span class="a55-arrow"></span>{{section.title}}
						</h3>
						<div class="section-content">

							<div class="form-horizontal section-data">
								<div class="control-group">
									<label class="control-label">Section ID:</label>
									<div class="control-input">
										<span class="monotype">{{section.id}}</span>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Capability:</label>
									<div class="control-input">
										<span class="monotype">{{section.capability}}</span><em
											class="a55-none" ng-hide="section.capability">not spesified</em>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Theme supports:</label>
									<div class="control-input">
										<span class="monotype">{{section.theme_supports}}</span><em
											class="a55-none" ng-hide="section.theme_supports">not
											spesified</em>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Description:</label>
									<div class="control-input">
										<em class="a55-none" ng-hide="section.description">no
											description</em>&nbsp;
									</div>
								</div>
								<div class="section-description control-group"
									ng-show="section.description">{{section.description}}</div>
							</div>

							<fieldset class="a55-fieldset">
								<legend>Theme Control(s):</legend>
								<div class="controls-wrp" ui-sortable="controlSortableOptions"
									ng-model="section.controls">
									<div
										ng-repeat="(id,control) in section.controls | orderBy:'priority'"
										class="control-item control-{{control.type}} ng-class:{disabled: control.disabled, 'control-editable': control.editable}">
										<div class="a55-item-toolbar">
											<span ng-click="controlEdit(control)"
												class="a55-item-setting a55-available-{{control.editable}}"
												title="edit control"></span> <span class="a55-item-handle"
												title="drag to set order"></span>
											<span ng-click="controlDelete(control)"
												class="a55-item-disable" title="enable/disable control"></span>
										</div>
										<h4 class="control-title"
											ng-click="togglePane($event, 'a55-control-open', 'disabled')">
											<span class="a55-control-icon type-{{control.type}}"></span>
											{{control.label}}
											<code class="badge">{{control.id}}</code>
										</h4>
										<div class="control-settings">
											<div class="form-horizontal">
												<div class="control-group">
													<label class="control-label" for="">Control ID:</label>
													<div class="control-input">
														<span class="monotype">{{control.id}}</span>
													</div>
												</div>
												<div class="control-group control-default-value">
													<label class="control-label" for="">Default Value:</label>
													<div class="control-input">
														<span ng-show="control.setting.default">{{control.setting.default}}</span>
														<em ng-show="!control.setting.default" class="a55-none">empty
															| FALSE</em> &nbsp;
													</div>
												</div>
												<div class="control-option" control-compiler="control"></div>
												<div class="control-group">
													<label class="control-label" for="">Get Value Code:</label>
													<div class="control-input">
														<code class="monotype" control-code-compiler="control"></code>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

	</tab> <!-- END BUILD CUSTOMIZER --> <!-- BEGIN CUSTOM CSS --> <tab
		heading="Custom CSS" select="codemirrorRefresh()">

	<div id="a55-css-editor">
		<div class="a55-admin-main-col">
			<textarea ui-codemirror="cmOption" ng-model="customCss"><?php echo esc_textarea(get_option('a55_customizer_css', '')) ?></textarea>
		</div>
		<div class="a55-admin-sidebar-col">
		    <div class="a55-toolbar-wrap">
				<img src="<?php echo Atomic55_Plugin::$url ?>images/ajax-loader.gif"
					alt="loading" ng-show="is_busy" />
				<button class="button-primary" ng-disabled="!css_changed"
					ng-click="codemirrorSave()" title="save">Save</button>
				<button class="button button-link" ng-disabled="!css_changed"
					ng-click="codemirrorReset()" title="reset to last save">Reset</button>
			</div>
			<div class="a55-container">
				<h3 class="title">Theme Customizer</h3>
				<p class="hint">Click on theme customizer variable name to create
					dynamic value.</p>

				<fieldset>
					<legend ng-click="togglePane($event, 'a55-pane-close')">Builtin Variable</legend>
					<ul>
						<li><a href="" ng-click="themeVar({id:'site_url', type: '', setting: {}})">
								Site URL</a></li>
						<li><a href="" ng-click="themeVar({id:'theme_url', type: '', setting: {}})">
								Theme URL</a></li>
					</ul>
				</fieldset>
				<fieldset
					ng-repeat="section in data.sections | filter: {disabled: false} | orderBy:'priority'">
					<legend ng-click="togglePane($event, 'a55-pane-close')">{{section.title}}</legend>
					<ul>
						<li
							ng-repeat="control in section.controls | filter: {disabled: false} | orderBy:'priority'"
							ng-class="{disabled: ! isVar(control.type)}"><a href=""
							ng-click="themeVar(control)"><span
								class="a55-control-icon type-{{control.type}}"></span>
								{{control.label}}</a></li>
					</ul>
				</fieldset>
			</div>
		</div>
	</div>

	</tab> <!-- END CUSTOM CSS --> </tabset>


	<div class="modal hide fade" modal="section_modal_open"
		close="modalClose()" options="modal_options">
		<div class="modal-header">
			<h3>{{section_modal_action}} Section</h3>
		</div>
		<div class="modal-body">
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label">Section Title:</label>
					<div class="control-input">
						<input type="text" ng-model="section_modal_data.title"
							ng-change="autoSlug('title','id','section_modal_data')"
							placeholder="section title" autofocus required>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Section ID:</label>
					<div class="control-input">
						<input type="text" ng-model="section_modal_data.id"
							ng-readonly="section_modal_data.prevent_delete"
							placeholder="alpha-numeric and underscore only, must unique"
							required restrict="[^a-z0-9\_\s]" separator="_">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">&nbsp;</label>
					<div class="control-input">
						<label class="checkbox"><input type="checkbox"
							ng-checked="section_modal_data.is_theme_option" /> Show in theme
							option page <code>(WP Admin &gt; Appearance &gt; Theme Options)</code></label>
					</div>
				</div>

				<fieldset class="a55-pane-close">
					<legend ng-click="togglePane($event, 'a55-pane-close')">Optional
						Details</legend>
					<div class="control-group">
						<label class="control-label" for="description">Description:</label>
						<div class="control-input">
							<textarea name="description" id="description" cols="30" rows="10"
								placeholder="section description"
								ng-model="section_modal_data.description"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="capability">Capability:</label>
						<div class="control-input">
							<input type="text" id="capability" name="capability"
								ng-model="section_modal_data.capability"
								placeholder="default: edit_theme_options">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="theme_supports">Theme Support:</label>
						<div class="control-input">
							<input type="text" id="theme_supports" name="theme_supports"
								ng-model="section_modal_data.theme_supports"
								placeholder="default: none (empty)">
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" ng-click="modalClose()" class="btn btn-link">Cancel</a> <a
				href="#" ng-click="sectionEditDone()" class="btn btn-primary">{{section_modal_action}}</a>
		</div>
	</div>



	<div class="modal hide fade" modal="control_modal_open"
		close="modalClose()" options="modal_options">
		<div class="modal-header">
			<h3>{{control_modal_action}} Control</h3>
		</div>
		<div class="modal-body {{control_modal_data.type}}">
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="label">Control label:</label>
					<div class="control-input">
						<input type="text" ng-model="control_modal_data.label"
							ng-change="autoSlug('label','id','control_modal_data')"
							placeholder="section title" autofocus required>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="id">Control ID:</label>
					<div class="control-input">
						<input type="text" ng-model="control_modal_data.id"
							ng-readonly="control_modal_data.prevent_delete"
							placeholder="alpha-numeric and underscore only, must unique"
							required restrict="[^a-z0-9\[\]\_\s]" separator="_">
					</div>
				</div>
				<div class="control-group a55-control-modal-default-value">
					<label class="control-label" for="value">Default value:</label>
					<div class="control-input">
						<textarea placeholder="default value"
							ng-model="control_modal_data.setting.default"></textarea>
					</div>
				</div>
				<div class="control-option" control-edit-compiler="control_modal_data"></div>
			</form>
		</div>
		<div class="modal-footer">
			<a href="#" ng-click="control_modal_open = false" class="btn btn-link">Cancel</a> <a
				href="#" ng-click="controlEditDone()" class="btn btn-primary">{{control_modal_action}}</a>
		</div>
	</div>
</div>
<!-- END OF CONTROLLER -->


<!-- END: <?php echo __FILE__ ?> -->