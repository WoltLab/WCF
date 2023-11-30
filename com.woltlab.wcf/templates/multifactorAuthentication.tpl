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

{include file='authFlowFooter'}
