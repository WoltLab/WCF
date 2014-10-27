<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.like.likes.more': '{lang}wcf.like.likes.more{/lang}',
			'wcf.like.likes.noMoreEntries': '{lang}wcf.like.likes.noMoreEntries{/lang}',
			'wcf.like.likesReceived': '{lang}wcf.like.likesReceived{/lang}',
			'wcf.like.likesGiven': '{lang}wcf.like.likesGiven{/lang}',
			'wcf.like.dislikesReceived': '{lang}wcf.like.dislikesReceived{/lang}',
			'wcf.like.dislikesGiven': '{lang}wcf.like.dislikesGiven{/lang}'
		});
		
		new WCF.User.LikeLoader({@$userID});
	});
	//]]>
</script>

<ul id="likeList" class="containerList recentActivityList likeList" data-last-like-time="{@$lastLikeTime}">
	<li>
		<ul class="buttonGroup" id="likeType">
			<li><a class="button small active" data-like-type="received">{lang}wcf.like.likesReceived{/lang}</a></li>
			<li><a class="button small" data-like-type="given">{lang}wcf.like.likesGiven{/lang}</a></li>
		</ul>
		
		{if LIKE_ENABLE_DISLIKE}
			<ul class="buttonGroup" id="likeValue">
				<li><a class="button small active" data-like-value="1">{lang}wcf.like.details.like{/lang}</a></li>
				<li><a class="button small" data-like-value="-1">{lang}wcf.like.details.dislike{/lang}</a></li>
			</ul>
		{/if}
	</li>
	
	{include file='userProfileLikeItem'}
</ul>