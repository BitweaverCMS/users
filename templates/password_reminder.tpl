{tr}Hi{/tr} {$mail_user},

{tr}Someone from the internet address{/tr} {$smarty.server.REMOTE_ADDR} {tr}requested a reminder of the password for the account{/tr}: 
{literal}   {/literal}	{$mail_user}

{tr}You may use the following URL to reset your password for this account:{/tr} 

{$mail_machine}?v={$mailUserId}:{$mailProvPass}

{tr}This link will remain active only for the next 3 days or until one of the following occurs:{/tr}
* {tr}The first sucessful use of this link.{/tr}
* {tr}Another password reset request is made.{/tr}
