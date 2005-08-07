<div class="admin box">
	<div class="boxtitle">{tr}User files{/tr}</div>
	<div class="boxcontent">
		<form action="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=userfiles" method="post">
		<table class="panel">
		<tr><td>{tr}Quota (Mb){/tr}</td><td>
		<input type="text" name="userfiles_quota" value="{$userfiles_quota|escape}" size="5" />
		</td></tr>
		<tr><td>{tr}Use database to store userfiles{/tr}:</td><td><input type="radio" name="uf_use_db" value="y" {if $uf_use_db eq 'y'}checked="checked"{/if}/></td></tr>
		<tr><td>{tr}Use a directory to store userfiles{/tr}:</td><td><input type="radio" name="uf_use_db" value="n" {if $uf_use_db eq 'n'}checked="checked"{/if}/></td></tr>
		<tr><td align="right">{tr}Path{/tr}:</td><td><input type="text" name="uf_use_dir" value="{$uf_use_dir|escape}" size="50" /> </td></tr>
		<tr class="panelsubmitrow"><td colspan="2"><input type="submit" name="userfilesprefs" value="{tr}Change preferences{/tr}" /></td></tr>
		</table>
		</form>
	</div>
</div>
