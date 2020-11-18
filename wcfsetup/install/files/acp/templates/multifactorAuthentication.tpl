{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}

{include file='header' __isLogin=true}

<section class="box">
	<h2 class="boxTitle">{lang}wcf.user.security.multifactor.methods{/lang}</h2>
	
	<div class="boxContent">
		<nav>
			<ol class="boxMenu">
				{foreach from=$setups item='_setup'}
					<li{if $setup->getId() == $_setup->getId()} class="active"{/if}>
						<a class="boxMenuLink" href="{link controller='MultifactorAuthentication' object=$_setup url=$redirectUrl}{/link}"><span class="boxMenuLinkTitle">{lang}wcf.user.security.multifactor.{$_setup->getObjectType()->objectType}{/lang}</span></a>
					</li>
				{/foreach}
			</ol>
		</nav>
	</div>
</section>

<div class="section box48">
	{@$userProfile->getAvatar()->getImageTag(48)}
	
	<div>
		<div class="containerHeadline">
			<h3>
				{lang}wcf.user.security.multifactor.authentication.user.headline{/lang}
			</h3>
		</div>
		<div class="containerContent">
			{lang}wcf.user.security.multifactor.authentication.user.content{/lang}
		</div>
		
		<form action="{link controller='MultifactorAuthenticationAbort'}{/link}" method="post">
			<button type="submit">{lang}wcf.user.security.multifactor.authentication.logout{/lang}</button>
			{csrfToken}
		</form>
	</div>
</div>

{@$form->getHtml()}

{include file='footer'}
