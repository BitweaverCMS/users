{* $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_image_galleries.tpl,v 1.1 2005/06/19 05:12:23 bitweaver Exp $ *}
{if $user}
	{if $gBitSystemPrefs.feature_galleries eq 'y'}
		{bitmodule title="$moduleTitle" name="user_image_galleries"}
			<table class="module box">
				{section name=ix loop=$modUserG}
					<tr>
						{if $nonums != 'y'}
							<td valign="top">{$smarty.section.ix.index_next})</td>
						{/if}
						<td>
							<a href="{$gBitLoc.IMAGEGALS_PKG_URL}browse_gallery.php?gallery_id={$modUserG[ix].gallery_id}">{$modUserG[ix].name}</a>
						</td>
					</tr>
				{/section}
			</table>
		{/bitmodule}
	{/if}
{/if}
