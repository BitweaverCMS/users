<!--
<textarea id="fooz">Hola a todos</textarea>
<input type="button" value="set" onMouseOver="return overlib('das');" onMouseOut="nd();" />
-->
<!-- onClick='return overlib(\"".$repl."\",STICKY,CAPTION,\"Spellchecker suggestions\");'-->
<div class="display customhome">
<div class="header">
<h1>{$browserTitle} Home</h1>
</div>

<div class="body">

<table class="other">
<tr>
<td style="width: 50%; vertical-align: top;">
	<div class="box">
		<h3>
			What is bitweaver?
		</h3>
		<div class="boxcontent">
			bitweaver is a powerful Content Management System easy to customize and
			configure designed to create Portals, community sites, intranets,
			and general web applications.
		</div>
	</div>
</td>
<td style="width: 50%; vertical-align: top;">
	<div class="box">
		<h3>News</h3>
		<div class="boxcontent">
			{content id=1}
		</div>
	</div>
</td>
</tr>
</table>

<div class="box">
<h3>
Download!
</h3>
<div class="boxcontent">
Our last stable release of bitweaver is 1.2 - Bonnie - you can download bitweaver from <a href="http://www.sourceforge.net/projects/bitweaver/">Sourceforge</a>
</div>
</div>

<div class="box">
<h3>
Join us!
</h3>
<div class="boxcontent">
bitweaver is a fairly young project and needs all the help we can get. 
There are many ways of contributing - the first step is to learn more. Our site is located at <a href="http://www.bitweaver.org/wiki/index.php"> bitweaver CMS Home</a> or join our projects <a href="http://sourceforge.net/mail/?group_id=64258">mailing list</a>
</div>
</div>
<div class="box">
<h3>
Some of the many features in bitweaver
</h3>
<div class="boxcontent">
<ul>
<li>A wiki system where users can edit pages using a powerful syntax</li>
<li>Articles, reviews, topics and user submissions</li>
<li>Forums</li>
<li>Weblogs</li>
<li>Image galleries</li>
<li>File galleries</li>
<li>Chatrooms</li>
<li>FAQs</li>
<li>Polls</li>
<li>Send and receive objects to/from other bitweaver sites</li>
<li>Powerful permission system with users/groups/roles for all the sections and features</li>
</ul>
</div>
</div>

<div class="box">
<h3>
Testing bitweaver
</h3>
<div class="boxcontent">
<ol>
<li>May be you want to start reading the bitweaver <a href="{$smarty.const.FAQS_PKG_URL}index.php">FAQ</a></li>
<li>Test the wiki following this <a href="{$smarty.const.WIKI_PKG_URL}index.php">link</a></li>
<li>Visit the <a href="{$smarty.const.BITFORUMS_PKG_URL}index.php">forums</a>, write a topic or reply to an existing topic if you want</li>
<li>At the <a href="{$smarty.const.IMAGEGALS_PKG_URL}index.php">images galleries</a> section you can see some images try <a href="{$smarty.const.IMAGEGALS_PKG_URL}upload_image.php?gallery_id=2">uploading images</a> to our public gallery</li>
<li>You can download some tiki-add-ons from the <a href="{$smarty.const.FILEGALS_PKG_URL}list_file_gallery.php?gallery_id=1">tiki-add-ons file gallery</a></li>
<li>Visit the <a href="{$smarty.const.CATEGORIES_PKG_URL}index.php">category browser</a> to test the tiki categorizing system</li>
<li>You can enter the <a href="{$smarty.const.CHAT_PKG_URL}index.php">chatrooms</a> to test our chatting system maybe you should invite a friend to test it</li>
<li>From the <a href="{$smarty.const.GAMES_PKG_URL}index.php">games</a> section you can play some flash games</li>
<li>If you are not logged you can <a href="{$smarty.const.USERS_PKG_URL}register.php">register</a> as a new user and test features available to registered users such as setting user preferences, user bookmarks or configuring modules</li>
<li>If you are curious visit the <a href="{$smarty.const.STATS_PKG_URL}index.php">stats</a> page</li>
<li>The <a href="{$smarty.const.ARTICLES_PKG_URL}index.php">articles</a> section will show a demo article that you can read and comment</li>
<li>If you want to test editing an article or review you can <a href="{$smarty.const.ARTICLES_PKG_URL}edit_submission.php">write a submission</a></li>
<li>From the <a href="{$smarty.const.BLOGS_PKG_URL}index.php">weblogs</a> section you can create a weblog and test writing to your weblog</li>
</ol>
</div>
</div>

</div><!-- end .body -->
</div><!-- end .customhome -->
