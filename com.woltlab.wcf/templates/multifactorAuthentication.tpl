{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}

{capture assign='sidebarLeft'}
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
{/capture}

{include file='header' __disableAds=true __disableLoginLink=true __sidebarLeftHasMenu=true}

{$user->username}

{@$form->getHtml()}

{include file='footer' __disableAds=true}
