<?php
$nvi_info = $gBitSystem->get_news_from_last_visit($user);
$smarty->assign('nvi_info', $nvi_info);
?>