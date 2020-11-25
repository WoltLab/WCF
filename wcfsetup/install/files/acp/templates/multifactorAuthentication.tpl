{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}

{include file='header' __isLogin=true}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.security.multifactor.authentication{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.user.security.multifactor.authentication.description{/lang}</p>
	</div>
</header>

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
			<dl>
				<dt>{lang}wcf.user.security.multifactor.authentication.loginAs{/lang}</dt>
				<dd>{$user->username}</dd>
			</dl>
			{@$form->getHtml()}
		</div>
	</div>
</div>

{include file='footer'}
