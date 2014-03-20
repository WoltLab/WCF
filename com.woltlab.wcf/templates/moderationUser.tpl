<article class="message messageReduced">
	<div>
		<section class="messageContent">
			<div>
				<header class="messageHeader">
					<div class="box32">
						{if $user->userID}
							<a href="{link controller='User' object=$user->getDecoratedObject()}{/link}" class="framed">{@$user->getAvatar()->getImageTag(32)}</a>
						{else}
							<span class="framed">{@$user->getAvatar()->getImageTag(32)}</span>
						{/if}

						<div class="messageHeadline">
							<h1><a href="{@$user->getLink()}">{$user->getTitle()}</a></h1>
							<p>
								<span class="username"><a href="{link controller='User' object=$user->getDecoratedObject()}{/link}">{$user->getUsername()}</a></span>
									{lang}wcf.user.membersList.registrationDate{/lang}
							</p>
						</div>
					</div>
				</header>

				<div class="messageBody">
					<div>
						<div class="messageText">
							{include file="userProfileAbout"}
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</article>