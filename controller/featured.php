<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings; 
	$PTMPL['page_title'] = $LANG['featured'];  

	$bars = new barMenus;
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	$side_bar = new sidebarClass;
	$PTMPL['shared_menu'] = $side_bar->user_navigation();
	$PTMPL['recommended'] = recomendations();

	if (isset($_POST['limit'])) {
		$PTMPL['limit'] = $_POST['limit'];
	} else {
		$PTMPL['limit'] = $settings['per_featured']; 
	} 

	$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $PTMPL['page_title'], $PTMPL['page_title']);
	$theme = new themer('featured/content');
	return $theme->make();
}
?>