<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2004 by Tikipro - cfowler, btodoroff, et al
// +----------------------------------------------------------------------
global $gQueryUser, $gBitUser, $smarty;
if( !empty( $gQueryUser->mInfo ) ) {
	$smarty->assign_by_ref('userInfo', $gQueryUser->mInfo );
} elseif( !empty( $gBitUser->mInfo ) ) {
	$smarty->assign_by_ref('userInfo', $gBitUser->mInfo );
}
?>
