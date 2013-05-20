<div class="container marginTop">
	<ul class="containerList messageSearchResultList">
		{foreach from=$objects item=message}
			<li>
				<div class="box48">
					<a href="{link controller='User' object=$message->getUserProfile()}{/link}" title="{$message->getUserProfile()->username}" class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(48)}</a>
					
					<div>
						<div class="containerHeadline">
							<h3><a href="{$message->getLink($query)}">{$message->getSubject()}</a></h3>
							<p>
								<a href="{link controller='User' object=$message->getUserProfile()}{/link}" class="userLink" data-user-id="{@$message->getUserProfile()->userID}">{$message->getUserProfile()->username}</a>
								<small>- {@$message->getTime()|time}</small>
								{if $message->getContainerTitle()}<small>- <a href="{$message->getContainerLink()}">{$message->getContainerTitle()}</a></small>{/if}
							</p> 
							<small class="containerContentType">{lang}wcf.search.object.{@$message->getObjectTypeName()}{/lang}</small>
						</div>
						
						<p>{@$message->getFormattedMessage()}</p>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
</div>