{* $Header$ *}
{strip}
{if $modLastPages}
	{bitmodule title="$moduleTitle" name="last_changes"}
		<ol>
			{section name=ix loop=$modLastPages}
				<li>
					{if !$contentType}
						<strong>{tr}{$gLibertySystem->getContentTypeName($modLastPages[ix].content_type_guid)}{/tr}: </strong>
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
