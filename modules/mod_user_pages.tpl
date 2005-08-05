{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_pages.tpl,v 1.1.1.1.2.2 2005/08/05 23:00:42 squareing Exp $ *}
{if $user}
	{if $gBitSystem->isFeatureActive( 'feature_wiki' )}
		{bitmodule title="$moduleTitle" name="user_pages"}
			<table class="module box">
				{section name=ix loop=$modUserPages}
					<tr>
						{if $nonums != 'y'}
							<td valign="top">{$smarty.section.ix.index_next})</td>
						{/if}
						<td>
							<a href="{$smarty.const.WIKI_PKG_URL}index.php?page={$modUserPages[ix].page_name|escape:"url"}">{$modUserPages[ix].page_name}</a>
						</td>
					</tr>
				{/section}
			</table>
		{/bitmodule}
	{/if} {* $gBitSystem->isFeatureActive( 'feature_wiki' ) *}
{/if}   {* $user *}