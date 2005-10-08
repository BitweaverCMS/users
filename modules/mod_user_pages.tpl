{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_pages.tpl,v 1.1.1.1.2.3 2005/10/08 16:46:59 squareing Exp $ *}
{strip}
{if $modLastPages}
	{bitmodule title="$moduleTitle" name="last_changes"}
		<ol>
			{section name=ix loop=$modLastPages}
				<li>
					{if !$userContentType}
						<strong>{tr}{$modLastPages[ix].content_description}{/tr}: </strong>
					{/if}
					{$modLastPages[ix].display_link}
					{if $userShowDate}
						<br/><span class="date">{$modLastPages[ix].last_modified|bit_long_date}</span>
					{/if}
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ol>
		<a href="{$smarty.const.LIBERTY_PKG_URL}list_content.php?user_id={$gQueryUserId}&amp;sort_mode=last_modified_desc{if $contentType}&content_type_guid={$contentType}{/if}">{tr}View more{/tr}&hellip;</a>
	{/bitmodule}
{/if}
{/strip}
