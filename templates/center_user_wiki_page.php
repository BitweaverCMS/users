<?php

global $gQueryUser, $_template;

include_once( USERS_PKG_INCLUDE_PATH.'lookup_user_inc.php' );

if( $_template && $gQueryUser && $gQueryUser->isValid() ) {
	$_template->tpl_vars['gQueryUser'] = new Smarty_variable( $gQueryUser );
}
