{* $Header$ *}
{strip}
{bitmodule title="$moduleTitle" name="online_users"}
	{tr}We have {$logged_users} online user(s){/tr}
	<ol>
		{section name=ix loop=$online_users}
			{if $online_users[ix].users_information ne 'public'}{assign var=nolink value=1}{else}{assign var=nolink value=0}{/if}
			<li>{displayname hash=$online_users[ix] nolink=$nolink}</li>
		{/section}
	</ol>
{/bitmodule}
{/strip}
