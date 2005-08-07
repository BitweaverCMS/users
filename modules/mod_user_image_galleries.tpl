{* $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_image_galleries.tpl,v 1.3 2005/08/07 17:46:48 squareing Exp $ *}
{if $user}
	{if $gBitSystem->isFeatureActive( 'feature_galleries' )}
		{bitmodule title="$moduleTitle" name="user_image_galleries"}
			<table class="module box">
				{section name=ix loop=$modUserG}
					<tr>
						{if $nonums != 'y'}
							<td valign="top">{$smarty.section.ix.index_next})</td>
						{/if}
						<td>
							<a href="{$smarty.const.IMAGEGALS_PKG_URL}browse_gallery.php?gallery_id={$modUserG[ix].gallery_id}">{$modUserG[ix].name}</a>
						</td>
					</tr>
				{/section}
			</table>
		{/bitmodule}
	{/if}
{/if}
