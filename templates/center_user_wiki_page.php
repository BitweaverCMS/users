<?php

global $gQueryUser;

include_once( USERS_PKG_PATH.'includes/lookup_user_inc.php' );

if( $gQueryUser && $gQueryUser->isValid() ) {
	$_template->tpl_vars['gQueryUser'] = new Smarty_variable( $gQueryUser );
}
