<h2>{tr}User_versions_for{/tr}: {$ruser}</h2>
{if $preview}
<h2>{tr}Version{/tr}: {$version}</h2>
<div class="wikibody">{$preview.data}</div>
<br /> 
{/if}
<br />
<div align="center">
<table>
<tr>
<th>{tr}Date{/tr}</th>
<th>{tr}Page{/tr}</th>
<th>{tr}Version{/tr}</th>
<th>{tr}Ip{/tr}</th>
<th>{tr}Comment{/tr}</th>
<th>{tr}Action{/tr}</th>
</tr>
{cycle values="even,odd" print=false}
{section name=hist loop=$history}
<tr class="{cycle}">
<td>{$history[hist].last_modified|bit_long_datetime}</td>
<td><a href="{$gBitLoc.WIKI_PKG_URL}index.php?page={$history[hist].page_name|escape:"url"}">{$history[hist].page_name}</a></td>
<td>{$history[hist].version}</td>
<td>{$history[hist].ip}</td>
<td>{$history[hist].comment}</td>
<td><a href="{$gBitLoc.USERS_PKG_URL}versions.php?ruser={$ruser}&amp;page={$history[hist].page_name|escape:"url"}&amp;preview=1&amp;version={$history[hist].version}">{tr}view{/tr}</a></td>
</tr>
{sectionelse}
<tr class="norecords"><td colspan="6">
{tr}No records found{/tr}
</td></tr>
{/section}
</table>
</div>
