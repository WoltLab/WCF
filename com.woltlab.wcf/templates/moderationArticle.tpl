<article class="message messageReduced">
	<section class="messageContent">
		<header class="messageHeader">
			<div class="box32 messageHeaderWrapper">
				{if $article->getUserProfile()->userID}
					{user object=$article->getUserProfile() type='avatar32' ariaHidden='true'}
				{else}
					<span>{@$article->getUserProfile()->getAvatar()->getImageTag(32)}</span>
				{/if}
				
				<div class="messageHeaderBox">
					<h2 class="messageTitle">
						<a href="{@$article->getLink()}">{$article->getTitle()}</a>
					</h2>
					
					<ul class="messageHeaderMetaData">
						<li>
							{if $article->getUserProfile()->userID}
								{user object=$article->getUserProfile() class='username'}
							{else}
								{$article->getUserProfile()->username}
							{/if}
						</li>
						<li><span class="messagePublicationTime">{@$article->time|time}</span></li>
						
						{event name='messageHeaderMetaData'}
					</ul>
				</div>
			</div>
			
			{event name='messageHeader'}
		</header>
		
		<div class="messageBody">
			{event name='beforeMessageText'}
			
			<div class="messageText">
				{@$article->getFormattedContent()}
			</div>
			
			{event name='afterMessageText'}
		</div>
	</section>
</article>
