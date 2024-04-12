{hascontent}
	<div class="authOtherOptionButtons">
		<div class="authOtherOptionButtons__separator">
			{lang}wcf.user.login.thirdPartySeparator{/lang}
		</div>

		<ul class="authOtherOptionButtons__buttonList">
			{content}
				{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
					<li id="facebookAuth" class="thirdPartyLogin">
						<a
							href="{link controller='FacebookAuth'}{/link}"
							class="button thirdPartyLoginButton facebookLoginButton"
							rel="nofollow"
						>{icon size=24 name='facebook' type='brand'} <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
					</li>
				{/if}
				
				{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
					<li id="googleAuth" class="thirdPartyLogin">
						<a
							href="{link controller='GoogleAuth'}{/link}"
							class="button thirdPartyLoginButton googleLoginButton"
							rel="nofollow"
						>{icon size=24 name='google' type='brand'} <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
					</li>
				{/if}
			
				{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
					<li id="twitterAuth" class="thirdPartyLogin">
						<a
							href="{link controller='TwitterAuth'}{/link}"
							class="button thirdPartyLoginButton twitterLoginButton"
							rel="nofollow"
						>{icon size=24 name='x-twitter' type='brand'} <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
					</li>
				{/if}
				
				{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
					<li id="githubAuth" class="thirdPartyLogin">
						<a
							href="{link controller='GithubAuth'}{/link}"
							class="button thirdPartyLoginButton githubLoginButton"
							rel="nofollow"
						>{icon size=24 name='github' type='brand'} <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
					</li>
				{/if}
				
				{event name='3rdpartyButtons'}
			{/content}
		</ul>
	</div>
{/hascontent}
