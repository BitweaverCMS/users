<h1>{tr}Change password enforced{/tr}</h1>
<form method="post" action="{$smarty.const.USERS_PKG_URL}change_password.php">
<input type="hidden" name="user_id" value="{$userInfo.user_id}" />
{if $userInfo.provpass}
  <input type="hidden" name="provpass" value="{$userInfo.provpass|escape}" />
{/if}

<table class="panel">
<tr>
  <td>{tr}User{/tr}:</td>
  <td><b>{$userInfo.login}</b></td>
</tr>

{if !$userInfo.provpass}
<tr>
  <td>{tr}Old password{/tr}:</td>
  <td><input type="password" name="oldpass" /></td>
</tr>     
{/if}

<tr>
  <td>{tr}New password{/tr}:</td>
  <td><input type="password" name="pass" /></td>
</tr>  
<tr>
  <td>{tr}Again please{/tr}:</td>
  <td><input type="password" name="pass2" /></td>
</tr>  
<tr>
  <td>&nbsp;</td>
  <td><input type="submit" name="change" value="{tr}change{/tr}" /></td>
</tr>  
</table>
</form>
