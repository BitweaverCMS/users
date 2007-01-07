{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_online_users.tpl,v 1.5 2007/01/07 22:22:18 squareing Exp $ *}
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
