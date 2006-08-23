{if $mail_provpass}
{tr}Hi{/tr} {$mail_user},

{tr}Someone from the internet address{/tr} {$smarty.server.REMOTE_ADDR} {tr}requested a reminder of the password for the account{/tr}: 
{literal}   {/literal}	{$mail_user}

{tr}You may use the following URL to reset your password for this account:{/tr} 

{$mail_machine}?user={$mail_user}&pass={$mail_provpass}

{tr}This link will remain active only for the next 3 days or until one of the following occurs:{/tr}
{literal}   {/literal}	{tr}The first sucessful use of this link.{/tr}
{literal}   {/literal}	{tr}Another password reset request is made.{/tr}

{else}
{tr}Hi{/tr} {$mail_user},

{tr}Someone from the internet address{/tr} {$smarty.server.REMOTE_ADDR} {tr}requested a reminder of the password for the account{/tr}: {$mail_user}

{tr}Since this is your registered email address we inform that the password for this account is:{/tr} {$mail_pass}
{/if}
