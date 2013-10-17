<?php
/**
 * Admin settings page
 * 
 * @author Alfi Rizka
 * @copyright Atomic55.net
 */
$page_link = admin_url('options-general.php') . '?page=' . $_GET['page'];
if (isset($_GET['tab']) === FALSE) {
    $active_tab = 'customizer';
    $page_tpl = 'admin.themecustomizer.php';
} elseif ($_GET['tab'] === 'settings') {
    $active_tab = 'settings';
    $page_tpl = 'admin.themesettings.php';
} elseif ($_GET['tab'] === 'theme-support') {
    $active_tab = 'theme-support';
    $page_tpl = 'admin.themesupport.php';
} else {
    $active_tab = 'customizer';
    $page_tpl = 'admin.themecustomizer.php';
}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>
	<h2>Atomic55 &mdash; Theme Toolkit</h2>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo $page_link ?>&tab=customizer"
			class="nav-tab <?php echo ($active_tab === 'customizer' ? 'nav-tab-active' : '') ?>">Theme
			Customizer</a>
		<a href="<?php echo $page_link ?>&tab=theme-support"
			class="nav-tab <?php echo ($active_tab === 'theme-support' ? 'nav-tab-active' : '') ?>">Theme
			Support</a> <a href="<?php echo $page_link ?>&tab=settings"
			class="nav-tab <?php echo ($active_tab === 'settings' ? 'nav-tab-active' : '') ?>">Others</a>
	</h2>
	
	<?php require_once Atomic55_Plugin::$path . 'page/' . $page_tpl; ?>
</div>
