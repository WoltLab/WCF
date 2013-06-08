{if !$supportPaste|isset}{assign var=supportPaste value=false}{/if}
{foreach from=$messages item=message}
	<article class="message messageReduced marginTop jsInvalidQuoteTarget" data-link="{@$message->getLink()}" data-username="{$message->getUsername()}">
		<div>
			<section class="messageContent">
				<div>
					<header class="messageHeader">
						<div class="box32">
							{if $userProfiles[$message->getUserID()]|isset}
								<a href="{link controller='User' object=$userProfiles[$message->getUserID()]}{/link}" class="framed">{@$userProfiles[$message->getUserID()]->getAvatar()->getImageTag(32)}</a>
							{/if}
							
							<div class="messageHeadline">
								<h1><a href="{@$message->getLink()}">{$message->getTitle()}</a></h1>
								<p>
									<span class="username">{if $userProfiles[$message->getUserID()]|isset}<a href="{link controller='User' object=$userProfiles[$message->getUserID()]}{/link}">{$message->getUsername()}</a>{else}{$message->getUsername()}{/if}</span>
									{@$message->getTime()|time}
								</p>
							</div>
						</div>
					</header>
					
					<div class="messageBody">
						<div>
							<div class="messageText">
								<ul>
									{foreach from=$message key=quoteID item=quote}
										<li data-quote-id="{@$quoteID}">
											<span>
												<input type="checkbox" value="1" id="quote_{@$quoteID}" class="jsCheckbox" />
												{if $supportPaste}<span class="icon icon16 icon-plus jsTooltip jsInsertQuote" title="{lang}wcf.message.quote.insertQuote{/lang}"></span>{/if}
											</span>
											
											<div class="jsQuote">
												<label for="quote_{@$quoteID}">{@$quote}</label>
											</div>
											<div class="jsFullQuote">
												{$message->getFullQuote($quoteID)}
											</div>
										</li>
									{/foreach}
								</ul>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</article>
{/foreach}