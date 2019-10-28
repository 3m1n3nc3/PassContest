<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings; 
	// Whole function displays static pages
	$site_class = new siteClass;
	$bars = new barMenus;

	$PTMPL['recommended'] = recomendations();
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	$site_class->what = sprintf("link = '%s' AND status = '1'", $_GET['page']);
	$get_page = $site_class->static_pages(0, 0)[0];

	$theme = '';
	$pages = '';
	if ($get_page) {
		$theme = new themer('static/content');
		$PTMPL['page_title'] = stripslashes($get_page['title']);		
		$PTMPL['content'] = stripslashes($get_page['content']);
		$PTMPL['heading'] = stripslashes($get_page['title']);				 
	} else {
		// If the page is not found
		$theme = new themer('welcome/404');
		$PTMPL['page_title'] = 'Error';
	}	
	$PTMPL['footer_links'] = $pages; 
	$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $PTMPL['content'], $PTMPL['page_title']);
	return $theme->make(); 
}
?>
