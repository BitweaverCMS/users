{tr}Hi{/tr} {$mail_user},

{tr}Someone from the internet address {$smarty.server.REMOTE_ADDR} requested a reminder of the password for the account{/tr}:
   	{$mail_user}

{tr}You may use the following URL to reset your password for this account:{/tr}
{$linkUri}confirm.php?v={$mailUserId}:{$mailProvPass}

{tr}This link will remain active only for the next 3 days or until one of the following occurs:
* The first sucessful use of this link.
* Another password reset request is made.{/tr}
