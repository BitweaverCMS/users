{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_pages.tpl,v 1.1 2005/06/19 05:12:23 bitweaver Exp $ *}
{if $user}
	{if $gBitSystemPrefs.feature_wiki eq 'y'}
		{bitmodule title="$moduleTitle" name="user_pages"}
			<table class="module box">
				{section name=ix loop=$modUserPages}
					<tr>
						{if $nonums != 'y'}
							<td valign="top">{$smarty.section.ix.index_next})</td>
						{/if}
						<td>
							<a href="{$gBitLoc.WIKI_PKG_URL}index.php?page={$modUserPages[ix].page_name|escape:"url"}">{$modUserPages[ix].page_name}</a>
						</td>
					</tr>
				{/section}
			</table>
		{/bitmodule}
	{/if} {* $gBitSystemPrefs.feature_wiki eq 'y' *}
{/if}   {* $user *}