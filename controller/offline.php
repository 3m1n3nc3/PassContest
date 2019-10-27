<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings; 
	$PTMPL['page_title'] = $LANG['offline'].' - '.$LANG['maintenance'];  
    
    if ($settings['mode'] == 'offline') {
        $PTMPL['offline_site'] = $LANG['offline_site'];       
    } else {
        header("Location: ".permalink($CONF['url'].'/index.php?a=featured'));
    }

	$theme = new themer('welcome/offline_site');
	return $theme->make();
}

?>