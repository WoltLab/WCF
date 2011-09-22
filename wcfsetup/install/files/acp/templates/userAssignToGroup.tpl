{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/users1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.assignToGroup{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserAssignToGroup">

	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</legend>
			
			<ul>
				{implode from=$users item=$user}<li><a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}" class="badge badgeButton">{$user}</a></li>{/implode}
			</ul>
		</fieldset>	
		
		<fieldset>
			<legend>{lang}wcf.acp.user.groups{/lang}</legend>
			
			
			<dl{if $errorField == 'groupIDs'} class="formError"{/if}>
				<dt></dt>
				<dd><!-- ToDo: Build proper definition list -->
					{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
					{if $errorField == 'groupIDs'}
						<small class="innerError">
							<span class="arrowOuter" style="display: none;"><span class="arrowInner"></span></span>
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						</small>
					{/if}
				<dd>
			</dl>
			
		</fieldset>
		
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
