<div id="a55-theme-support" ng-cloak ng-controller="A55ThemeCtrl">
  <tabset type="'pills'">
    <!-- BEGIN NAVIGATION -->
    <tab heading="Navigation">
        
        <div modal="menuModalShouldOpen" close="modalClose()" options="opts">
            <div class="modal-header">
                <h3>{{menu_modal_title}}</h3>
            </div>
            <div class="modal-body">
                <form ng-submit="menuSave()" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="label">Menu Name:</label>
                        <div class="control-input">
                          <input type="text" name="label" placeholder="Menu name" ng-change="autoSlug('menu_modal_label','menu_modal_id')" ng-model="menu_modal_label" autofocus required tabindex="1">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="id">Menu ID:</label>
                        <div class="control-input">
                          <input type="text" name="id" tabindex="2" restrict="[^a-z0-9\_\s]" ng-readonly="menu_modal_readonly" placeholder="alphanumeric and underscore only" ng-model="menu_modal_id" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-link cancel" ng-click="modalClose()" tabindex="4">Cancel</button>
                <button class="btn btn-primary" ng-click="menuSave()" tabindex="3">Save</button>
            </div>
        </div>
            
        <div class="alert alert-success">
            <strong>Tips:</strong> Read more about Wordpress navigation <a href="http://codex.wordpress.org/Function_Reference/wp_nav_menu" target="wordpress_docs" title="open wordpress documentation">here</a>.
        </div>
        
        <div class="row-fluid navtab-header">
            <div class="span6">
                <h3><ng-pluralize count=menuGetTotal() when="menuCount"></ng-pluralize> Registered</h3>
            </div>
            <div class="span6">
                <input type="button" value="Register Menu" class="button-primary pull-right" ng-click="menuUpdate(null)" />
                <span class="a55-busy {{is_busy}}"><img src="<?php echo Atomic55_Plugin::$url?>images/ajax-loader.gif" alt="loading" /> saving data...</span>
            </div>
        </div>
        
        <div class="a55-pane a55-disabled-{{menu.disabled}}" ng-repeat="menu in data.menus | orderBy:'label'">
            <div class="a55-pane-toolbar">
                <span class="a55-icon-setting" title="edit menu" ng-click="menuUpdate(menu.id)"></span>
                <span class="a55-icon-disable" title="enable/disable menu" ng-click="menuDelete(menu.id)"></span>
            </div>
            <h4 class="a55-pane-title" ng-click="togglePane($event)">{{menu.label}} <code class="badge">{{menu.id}}</code></h4>
            <div class="a55-pane-container">
                <strong>Code:</strong><br>
                <code class="a55-code">&lt;?php wp_nav_menu( array( 'theme_location' =&gt; '{{menu.id}}' ) ); ?&gt;</code>
            </div>
        </div>
        
    </tab>
     <!-- END NAVIGATION -->
     
    <!-- BEGIN SIDEBAR -->
    <tab heading="Sidebar">
        <div modal="sidebarModalShouldOpen" close="modalClose()" options="opts">
            <div class="modal-header">
                <h3>{{sidebar_modal_title}}</h3>
            </div>
            <div class="modal-body">
                <form ng-submit="sidebarSave()" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="name">Sidebar Name:</label>
                        <div class="control-input">
                          <input type="text" name="name" placeholder="sidebar name" ng-model="sidebar_modal_name" ng-change="autoSlug('sidebar_modal_name','sidebar_modal_id')" autofocus required tabindex="1">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="id">Sidebar ID:</label>
                        <div class="control-input">
                          <input type="text" name="id" tabindex="2" restrict="[^a-z0-9\_\s]" ng-readonly="sidebar_modal_readonly" placeholder="alphanumeric and underscore only" ng-model="sidebar_modal_id" required>
                        </div>
                    </div>
                    <h3>Optional Details</h3>
                    <div class="control-group">
                        <label class="control-label" for="desc">Description:</label>
                        <div class="control-input">
                          <textarea name="desc" cols="30" rows="3" tabindex="2" ng-model="sidebar_modal_description"></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="class">CSS class name:</label>
                        <div class="control-input">
                          <input type="text" name="class" placeholder="widget element class, default: {{data.default_args.sidebars.class}} (empty)" ng-model="sidebar_modal_class" tabindex="3">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="before_widget">Before Widget:</label>
                        <div class="control-input">
                          <input type="text" name="before_widget" placeholder="default: {{data.default_args.sidebars.before_widget}}" ng-model="sidebar_modal_before_widget" tabindex="4">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="after_widget">After Widget:</label>
                        <div class="control-input">
                          <input type="text" name="after_widget" placeholder="default: {{data.default_args.sidebars.after_widget}}" ng-model="sidebar_modal_after_widget" tabindex="5">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="before_title">Before Title:</label>
                        <div class="control-input">
                          <input type="text" name="before_title" placeholder="default: {{data.default_args.sidebars.before_title}}" ng-model="sidebar_modal_before_title" tabindex="7">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="after_title">After Title:</label>
                        <div class="control-input">
                          <input type="text" name="after_title" placeholder="default: {{data.default_args.sidebars.after_title}}" ng-model="sidebar_modal_after_title" tabindex="8">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-link cancel" ng-click="modalClose()" tabindex="10">Cancel</button>
                <button class="btn btn-primary" ng-click="sidebarSave()" tabindex="9">Save</button>
            </div>
        </div>
            
        <div class="alert alert-success">
            <strong>Tips:</strong> Read more about Wordpress Sidebar <a href="http://codex.wordpress.org/Sidebars" target="wordpress_docs" title="open wordpress documentation">here</a>.
        </div>
        
        <div class="row-fluid navtab-header">
            <div class="span6">
                <h3><ng-pluralize count=sidebarGetTotal() when="sidebarCount"></ng-pluralize> Registered</h3>
            </div>
            <div class="span6">
                <input type="button" value="Register Sidebar" class="button-primary pull-right" ng-click="sidebarUpdate(null)" />
                <span class="a55-busy {{is_busy}}"><img src="<?php echo Atomic55_Plugin::$url?>images/ajax-loader.gif" alt="loading" /> saving data...</span>
            </div>
        </div>
        <div class="a55-pane a55-disabled-{{sidebar.disabled}}" ng-repeat="sidebar in data.sidebars | orderBy:'name'" ng-order-by="data.sidebars">
            <div class="a55-pane-toolbar">
                <span class="a55-icon-setting" title="edit sidebar" ng-click="sidebarUpdate(sidebar.id)"></span>
                <span class="a55-icon-disable" title="enable/disable sidebar" ng-click="sidebarDelete(sidebar.id)"></span>
            </div>
            <h4 class="a55-pane-title" ng-click="togglePane($event)">{{sidebar.name}} <code class="badge">{{sidebar.id}}</code></h4>
            <div class="a55-pane-container">
                <div class="a55-sidebar-description">{{sidebar.description}}</div>
                <strong>Code:</strong><br>
                    <code class="a55-code">&lt;?php if ( is_active_sidebar( '{{sidebar.id}}' ) ) : ?&gt;
    &lt;?php dynamic_sidebar( '{{sidebar.id}}' ); ?&gt;
&lt;?php else : ?&gt;
    &lt;!-- Create some custom HTML or call the_widget().  It's up to you. --&gt;
&lt;?php endif; ?&gt;
                </code>
            </div>
        </div>
    </tab>
    <!-- END SIDEBAR -->
     
    <!-- BEGIN IMAGE SIZE -->
    <tab heading="Image Size">
        <div modal="imageModalShouldOpen" close="modalClose()" options="opts">
            <div class="modal-header">
                <h3>{{imageSize_modal_title}}</h3>
            </div>
            <div class="modal-body">
                <form ng-submit="imageSizesSave()" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label" for="name">Size Name:</label>
                        <div class="control-input">
                          <input type="text" name="name" placeholder="image size name" ng-model="imageSize_modal_name" ng-change="autoSlug('imageSize_modal_name','imageSize_modal_id')" autofocus required tabindex="1">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="id">Image Sizes ID:</label>
                        <div class="control-input">
                          <input type="text" name="id" tabindex="2" restrict="[^a-zA-Z0-9\_\-\s]" ng-readonly="imageSize_modal_readonly" placeholder="alphanumeric, dash and underscore only" ng-model="imageSize_modal_id" required>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="">Dimension:</label>
                        <div class="control-input">
                          <input type="text" name="width" class="a55-input-mini" placeholder="width (px)" restrict="[^0-9]" ng-model="imageSize_modal_width" tabindex="3" required> &times; 
                          <input type="text" class="a55-input-mini" name="height" restrict="[^0-9]" placeholder="height (px)" ng-model="imageSize_modal_height" tabindex="4" required> pixel
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">&nbsp;</label>
                        <div class="control-input">
                          <label><input type="checkbox" name="crop" ng-model="imageSize_modal_crop" tabindex="5"> Crop the image if larger than {{imageSize_modal_width || 0}}px &times; {{imageSize_modal_height || 0}}px</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-link cancel" ng-click="modalClose()" tabindex="7">Cancel</button>
                <button class="btn btn-primary" ng-click="imageSizeSave()" tabindex="6">Save</button>
            </div>
        </div>
            
        <div class="alert alert-success">
            <strong>Tips:</strong> Read more about Wordpress Image Sizes <a href="http://codex.wordpress.org/Post_Thumbnails" target="wordpress_docs" title="open wordpress documentation">here</a>.
        </div>
        
        <div class="row-fluid navtab-header">
            <div class="span6">
                <h3><ng-pluralize count="imageSizeGetTotal()" when="imageSizeCount"></ng-pluralize> Registered</h3>
            </div>
            <div class="span6">
                <input type="button" value="Register Image Size" class="button-primary pull-right" ng-click="imageSizeUpdate(null)" />
                <span class="a55-busy" ng-show="is_busy"><img src="<?php echo Atomic55_Plugin::$url?>images/ajax-loader.gif" alt="loading" /> saving data...</span>
            </div>
        </div>
        <div class="a55-pane a55-disabled-{{imageSize.disabled}} a55-protected-{{imageSize.protected}}" ng-repeat="imageSize in data.imageSizes | orderBy:'name'" ng-order-by="data.imageSizes">
            <div class="a55-pane-toolbar">
                <span class="a55-icon-setting" title="edit image size" ng-click="imageSizeUpdate(imageSize.id)"></span>
                <span class="a55-icon-disable" title="enable/disable image size" ng-click="imageSizeDelete(imageSize.id)"></span>
            </div>
            <h4 class="a55-pane-title" ng-click="togglePane($event)"><span ng-bind="imageSize.name || humanize(imageSize.id)"></span> <code class="badge">{{imageSize.id}}</code></h4>
            <div class="a55-pane-container">
                <div class="a55-sidebar-description">Width: <code>{{imageSize.width}}</code>, Height: <code>{{imageSize.height}}</code>, Crop: <code>{{imageSize.crop}}</code></div>
                <strong>Code:</strong><br>
                    <code class="a55-code">&lt;?php 
if ( has_post_thumbnail() ) {
  the_post_thumbnail('{{imageSize.id}}');
} 
?&gt;
                </code>
            </div>
        </div>
    </tab>
    <!-- END IMAGE SIZE -->
  </tabset>
</div>
