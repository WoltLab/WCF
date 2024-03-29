{if !$supportPaste|isset}{assign var=supportPaste value=false}{/if}
{foreach from=$messages item=message}
	<article class="message messageReduced jsInvalidQuoteTarget" data-link="{@$message->getLink()}" data-username="{$message->getUsername()}">
		<div class="messageContent">
			<header class="messageHeader">
				<div class="box32 messageHeaderWrapper">
					{if $userProfiles[$message->getUserID()]|isset}
						{user object=$userProfiles[$message->getUserID()] type='avatar32' ariaHidden='true' tabindex='-1'}
					{else}
						<span><img src="{@$__wcf->getPath()}images/avatars/avatar-default.svg" alt="" class="userAvatarImage" style="width: 32px; height: 32px"></span>
					{/if}
					
					<div class="messageHeaderBox">
						<h2 class="messageTitle">
							<a href="{@$message->getLink()}">{$message->getTitle()}</a>
						</h2>
						
						<ul class="messageHeaderMetaData">
							<li>{if $userProfiles[$message->getUserID()]|isset}{user object=$userProfiles[$message->getUserID()]}{else}<span class="username">{$message->getUsername()}</span>{/if}</li>
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
					<ul class="messageQuoteItemList">
						{foreach from=$message key=quoteID item=quote}
							<li data-quote-id="{@$quoteID}" data-is-full-quote="{if $message->isFullQuote($quoteID)}true{else}false{/if}">
								<span>
									<input type="checkbox" value="1" id="quote_{@$quoteID}" class="jsCheckbox">
									{if $supportPaste}
										<button type="button" class="jsTooltip jsInsertQuote" title="{lang}wcf.message.quote.insertQuote{/lang}">
											{icon name='plus'}
										</button>
									{/if}
								</span>
								
								<div class="jsQuote">
									<label for="quote_{@$quoteID}">
										{if $message->isFullQuote($quoteID)}
											{@$quote}
										{else}
											{$quote}
										{/if}
									</label>
								</div>
								<div class="jsFullQuote">
									{$message->getFullQuote($quoteID)}
								</div>
							</li>
						{/foreach}
					</ul>
				</div>
				
				{event name='afterMessageText'}
			</div>
		</div>
	</article>
{/foreach}
