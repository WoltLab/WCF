<article class="message">
	<section class="messageContent">
		<header class="messageHeader">
			<div class="box32 messageHeaderWrapper">
				<a href="{link controller='User' object=$message->getUserProfile()->getDecoratedObject()}{/link}">{@$message->getUserProfile()->getAvatar()->getImageTag(32)}</a>
				
				<div class="messageHeaderBox">
					<h2 class="messageTitle">
						<a href="{@$message->getLink()}">{$message->getTitle()}</a>
					</h2>
					
					<ul class="messageHeaderMetaData">
						<li><a href="{link controller='User' object=$message->getUserProfile()->getDecoratedObject()}{/link}" class="username">{$message->getUsername()}</a></li>
						<li><span class="messagePublicationTime">{@$message->getTime()|time}</span></li>
						
						{event name='messageHeaderMetaData'}
					</ul>
				</div>
			</div>
			
			{event name='messageHeader'}
		</header>
		
		<div class="messageBody">
			{event name='beforeMessageText'}
			
			<div class="messageText">
				{@$message->getFormattedMessage()}
			</div>
			
			{event name='afterMessageText'}
		</div>
	</section>
</article>
