<?php
$ranking = $gBitSystem->get_user_galleries($user, $module_rows);
$smarty->assign('modUserG', $ranking);
?>
