{* $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_bookmarks.tpl,v 1.5 2006/09/03 20:14:58 squareing Exp $ *}
{if $gBitSystem->isFeatureActive( 'user_bookmarks' ) and $gBitUser->isRegistered() and $gBitUser->hasPermission( 'p_tidbits_create_bookmarks' )}
	{bitmodule title="$moduleTitle" name="user_bookmarks"}
		<table class="module box">
			{section name=ix loop=$modb_folders}
				<tr><td valign="bottom">
					<a href="{$ownurl}{$modb_sep}bookmarks_parent={$modb_folders[ix].folder_id}">{biticon ipackage="icons" iname="folder" iexplain="folder"}</a>{$modb_folders[ix].name}
				</td></tr>
			{/section}
			{section name=ix loop=$modb_urls}
				<tr><td>
					<a href="{$modb_urls[ix].url}">{$modb_urls[ix].name}</a>
					{if $gBitUser->hasPermission( 'p_tidbits_cache_bookmarks' ) and $urls[ix].datalen > 0}
						(<a href="{$smarty.const.USERS_PKG_URL}cached_bookmark.php?urlid={$modb_urls[ix].url_id}">{tr}cache{/tr}</a>)
					{/if}
					<a href="{$ownurl}{$modb_sep}bookmark_removeurl={$modb_urls[ix].url_id}">{biticon ipackage="icons" iname="edit-delete" iexplain="remove"}</a>
				</td></tr>
			{/section}
		</table><br />
		{form action=$ownurl}
			<input type="submit" name="bookmark_mark" value="{tr}mark{/tr}" />
		{/form}
	{/bitmodule}
{/if}
