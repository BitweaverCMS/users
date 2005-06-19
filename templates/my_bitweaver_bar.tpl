{*
<table>
<tr>

{if $user}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}index.php?home={$user}" title="{tr}My homepage{/tr}">
{biticon ipackage="users" iname="my_homepage" iexplain="My homepage"}</a></td>
{/if}

<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}my.php" title="{tr}MyTiki{/tr}">
{biticon ipackage="users" iname="my_bitweaver" iexplain="MyTiki"}</a></td>

{if $gBitSystemPrefs.feature_userPreferences eq 'y'}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}preferences.php" title="{tr}Preferences{/tr}">
{biticon ipackage="users" iname="my_prefs" iexplain="Preferences"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_messages eq 'y' and $gBitUser->hasPermission( 'bit_p_messages' )}
<td valign="top" align="center"><a href="{$gBitLoc.MESSU_PKG_URL}message_box.php" title="{tr}Messages{/tr}">
{biticon ipackage="users" iname="my_messages" iexplain="Messages"}<div align="center"><small>{$unreadMsgs}</small></div></a></td>
{/if}

{if $gBitSystemPrefs.feature_tasks eq 'y' and $gBitUser->hasPermission( 'bit_p_tasks' )}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}tasks.php" title="{tr}Tasks{/tr}">
{biticon ipackage="users" iname="my_tasks" iexplain="Tasks"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_user_bookmarks eq 'y' and $gBitUser->hasPermission( 'bit_p_create_bookmarks' )}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}bookmarks.php" title="{tr}Bookmarks{/tr}">
{biticon ipackage="users" iname="my_bookmarks" iexplain="Bookmarks"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_user_layout eq 'y' and $gBitUser->hasPermission( 'bit_p_configure_modules' )}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}assigned_modules.php" title="{tr}Modules{/tr}">
{biticon ipackage="users" iname="my_modules" iexplain="Modules"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_newsreader eq 'y' and $gBitUser->hasPermission( 'bit_p_newsreader' )}
<td valign="top" align="center"><a href="{$gBitLoc.NEWSREADER_PKG_URL}index.php" title="{tr}Newsreader{/tr}">
{biticon ipackage="users" iname="my_news" iexplain="Newsreader"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_webmail eq 'y' and $gBitUser->hasPermission( 'bit_p_use_webmail' )}
<td valign="top" align="center"><a href="{$gBitLoc.WEBMAIL_PKG_URL}index.php" title="{tr}Webmail{/tr}">
{biticon ipackage="users" iname="my_webmail" iexplain="Webmail"}</a></td>
{/if}

{if $gBitSystemPrefs.package_notepad eq 'y' and $gBitUser->hasPermission( 'bit_p_notepad' )}
<td valign="top" align="center"><a href="{$gBitLoc.NOTEPAD_PKG_URL}index.php" title="{tr}Notepad{/tr}">
{biticon ipackage="users" iname="my_notes" iexplain="Notepad"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_userfiles eq 'y' and $gBitUser->hasPermission( 'bit_p_userfiles' )}
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}files.php" title="{tr}MyFiles{/tr}">
{biticon ipackage="users" iname="my_files" iexplain="My Files"}</a></td>
{/if}

{if $gBitSystemPrefs.feature_minical eq 'y'}
<td valign="top" align="center"><a href="{$gBitLoc.MINICAL_PKG_URL}index.php" title="{tr}Mini Calendar{/tr}">
{biticon ipackage="users" iname="my_minical" iexplain="Mini Calendar"}</a></td>
{/if}

{if $gBitSystem->isFeatureActive('feature_user_watches') }
<td valign="top" align="center"><a href="{$gBitLoc.USERS_PKG_URL}watches.php" title="{tr}My watches{/tr}">
{biticon ipackage="users" iname="my_watches" iexplain="My watches"}</a></td>
{/if}

</tr>
</table>
*}
