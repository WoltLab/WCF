<div class="contentHeaderTitle">
	<h1 class="contentTitle" itemprop="name headline">{$articleContent->title}</h1>
	<ul class="inlineList contentHeaderMetaData articleMetaData">
		<li itemprop="author" itemscope itemtype="http://schema.org/Person">
			{icon name='user'}
			{if $article->userID}
				<a href="{$article->getUserProfile()->getLink()}" class="userLink" data-object-id="{$article->userID}" itemprop="url">
					<span itemprop="name">{unsafe:$article->getUserProfile()->getFormattedUsername()}</span>
				</a>
			{else}
				<span itemprop="name">{$article->username}</span>
			{/if}
		</li>
		
		<li>
			{icon name='clock'}
			<a href="{$article->getLink()}">{time time=$article->time}</a>
			<meta itemprop="datePublished" content="{$article->time|date:'c'}">
		</li>

		{if $article->hasLabels}
			<li>
				{icon name='tags'}
				<ul class="labelList">
					{foreach from=$article->getLabels() item=label}
						<li>{unsafe:$label->render()}</li>
					{/foreach}
				</ul>
			</li>
		{/if}
		
		<li>
			{icon name='eye'}
			{lang}wcf.article.articleViews{/lang}
		</li>
		
		{if $article->getDiscussionProvider()->getDiscussionCountPhrase()}
			<li itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
				{icon name='comments'}
				{if $article->getDiscussionProvider()->getDiscussionLink()}<a href="{$article->getDiscussionProvider()->getDiscussionLink()}">{else}<span>{/if}
				{$article->getDiscussionProvider()->getDiscussionCountPhrase()}
				{if $article->getDiscussionProvider()->getDiscussionLink()}</a>{else}</span>{/if}
				<meta itemprop="interactionType" content="http://schema.org/CommentAction">
				<meta itemprop="userInteractionCount" content="{$article->getDiscussionProvider()->getDiscussionCount()}">
			</li>
		{/if}
		
		{hascontent}
			<li>
				{icon name='flag'}
				{content}
					{if $article->isDeleted}
						<span class="badge red">{lang}wcf.message.status.deleted{/lang}</span>
					{/if}
					{if !$article->isPublished()}
						<span class="badge green">{lang}wcf.message.status.disabled{/lang}</span>
					{/if}
					{event name='contentHeaderMetaDataFlag'}
				{/content}
			</li>
		{/hascontent}
		
		{event name='contentHeaderMetaData'}
	</ul>
	
	<div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
		<meta itemprop="name" content="{PAGE_TITLE|phrase}">
		<div itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">
			<meta itemprop="url" content="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
		</div>
	</div>
</div>
