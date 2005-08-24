{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_online_users.tpl,v 1.3 2005/08/24 20:59:13 squareing Exp $ *}
{strip}
{bitmodule title="$moduleTitle" name="online_users"}
	{if $logged_users > 1}
  		{tr}We have {$logged_users} online users{/tr}
	{elseif $logged_users == 1}
		{tr}We have {$logged_users} online user{/tr}
	{/if}
	<ol>
		{section name=ix loop=$online_users}
			{if $online_users[ix].user_information ne 'public'}{assign var=nolink value=1}{else}{assign var=nolink value=0}{/if}
			<li>{displayname hash=$online_users[ix] nolink=$nolink}</li>
		{/section}
	</ol>
{/bitmodule}
{/strip}
