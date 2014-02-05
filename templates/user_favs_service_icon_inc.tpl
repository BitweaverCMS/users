{if $gContent && $gContent->isValid() && $gBitUser->isRegistered() && $gContent->hasService($smarty.const.CONTENT_SERVICE_USERS_FAVS) && $gBitThemes->isJavascriptEnabled()}
{strip}
{if $fav.content_id eq $gContent->mContentId && $gBitUser->getFavorites($gContent->mContentId)}
	{assign var=isFavorite value='true'}
{else}
	{assign var=isFavorite value='false'}
{/if}
<a title="{if $isFavorite eq 'true'}{tr}Remove from your favorites{/tr}{else}{tr}Add to your favorites{/tr}{/if}" onclick="BitUser.toggleFavorite({$gContent->mContentId});" href="javascript:void(0); {* {$smarty.const.USERS_PKG_URL}bookmark.php?content_id={$gContent->mContentId} *}" >
	{if $isFavorite eq 'true'}
		{booticon iname="icon-heart-empty" ipackage="icons" iexplain="Remove Favorite"}
	{else}
		{booticon iname="icon-heart" ipackage="icons" iexplain="Favorite"}
	{/if}
</a>
	<script type="text/javascript">/* <![CDATA[ */
		if( typeof( BitUser ) == 'undefined' ){ldelim} BitUser = {ldelim}{rdelim} {rdelim};
		BitUser.bookmarkUrl = "{$smarty.const.USERS_PKG_URL}bookmark.php";
		BitUser.isFavorite = {$isFavorite}; 
	{literal}
		BitUser.toggleFavorite = function( contentId ){
			var ajax = new BitBase.SimpleAjax();
			var query = 'content_id='+contentId+'&action='+(BitUser.isFavorite?'remove':'add');
			ajax.connect( BitUser.bookmarkUrl, query, BitUser.postFavorite, "GET" );
		};
		BitUser.postFavorite = function( rslt ){
			var obj = eval( "(" + rslt.responseText + ")" );
			switch( obj.Status.code ){
			case 205:
				BitUser.isFavorite = obj.Result.bookmark_state;
			case 400:
			case 401:
			default:
				break;
			}
			alert( obj.Status.message );
		};
	{/literal} /* ]]> */</script>
{*

     * var fnWhenDone = function ( pResponse ) {
     *       alert( pResponse.responseText );
     *     };
     *     var ajax = new BitBase.SimpleAjax();
     *     ajax.connect("mypage.php", "POST", "foo=bar&baz=qux", fnWhenDone);
     * };
	 *}
{/strip}
{/if}
