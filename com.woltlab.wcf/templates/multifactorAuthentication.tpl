{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}
{capture assign='contentDescription'}{lang}wcf.user.security.multifactor.authentication.description{/lang}{/capture}

{include file='authFlowHeader'}

<div class="section">
	{@$form->getHtml()}

	<div class="authOtherOptionButtons">
		<div class="authOtherOptionButtons__separator">
			{lang}wcf.user.security.multifactor.otherOptions{/lang}
		</div>

		<ul class="authOtherOptionButtons__buttonList">
			{foreach from=$setups item='method'}
				{if $setup->getId() != $method->getId()}
					<li>
						<a
							href="{link controller='MultifactorAuthentication' object=$method url=$redirectUrl}{/link}"
							class="button authOtherOptionButtons__button"
						>
							{if $method->getObjectType()->icon}
								{icon size=24 name=$method->getObjectType()->icon}
							{else}
								{icon size=24 name='lock'}
							{/if}
							<span>{lang}wcf.user.security.multifactor.{$method->getObjectType()->objectType}{/lang}</span>
						</a>
					</li>
				{/if}
			{/foreach}
		</ul>
	</div>
</div>

{*
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
			
		</div>
	</div>
</div>
*}
{include file='authFlowFooter'}
