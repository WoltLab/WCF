<article class="message messageReduced">
	<div>
		<section class="messageContent">
			<div>
				<header class="messageHeader">
					<div class="box32">
						<a href="{link controller='User' object=$message->getUserProfile()->getDecoratedObject()}{/link}" class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(32)}</a>
						
						<div class="messageHeadline">
							<h1><a href="{@$message->getLink()}">{$message->getTitle()}</a></h1>
							<p>
								<span class="username"><a href="{link controller='User' object=$message->getUserProfile()->getDecoratedObject()}{/link}">{$message->getUsername()}</a></span>
								{@$message->getTime()|time}
							</p>
						</div>
					</div>
				</header>
				
				<div class="messageBody">
					<div>
						<div class="messageText">
							{@$message->getFormattedMessage()}
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</article>