{include file='header'}

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/users1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.user.assignToGroup{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='UserAssignToGroup'}{/link}">

	<div class="wcf-box wcf-marginTop wcf-boxPadding wcf-shadow1">
		
		<fieldset>
			<legend>{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</legend>
			
			<ul>
				{implode from=$users item=$user}<li><a href="{link controller='UserEdit' id=$user->userID}{/link}" class="wcf-badge wcf-badgeButton wcf-button">{$user}</a></li>{/implode}
			</ul>
		</fieldset>	
		
		<fieldset>
			<legend>{lang}wcf.acp.user.groups{/lang}</legend>
			
			
			<dl{if $errorField == 'groupIDs'} class="wcf-formError"{/if}>
				<dt></dt>
				<dd><!-- ToDo: Build proper definition list -->
					{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
					{if $errorField == 'groupIDs'}
						<small class="wcf-innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						</small>
					{/if}
				<dd>
			</dl>
			
		</fieldset>
		
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
