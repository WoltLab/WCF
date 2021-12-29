{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentDescription'}{lang}wcf.user.security.multifactor.authentication.description{/lang}{/capture}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

<div class="section tabMenuContainer staticTabMenuContainer">
	<nav class="tabMenu">
		<ul>
			{foreach from=$setups item='_setup'}
				<li{if $setup->getId() == $_setup->getId()} class="active"{/if}>
					<a href="{link controller='MultifactorAuthentication' object=$_setup url=$redirectUrl}{/link}"><span class="boxMenuLinkTitle">{lang}wcf.user.security.multifactor.{$_setup->getObjectType()->objectType}{/lang}</span></a>
				</li>
			{/foreach}
		</ul>
	</nav>

	<div class="tabMenuContent">
		<div class="section">
			{@$form->getHtml()}
		</div>
	</div>
</div>

{include file='footer' __disableAds=true}
