{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

{event name='wysiwyg'}

<script data-relocate="true">
	require([
		"WoltLabSuite/Core/Component/Ckeditor",
		"/wcf/editor/dist/bundle.js",
	], (
		{ setupCkeditor },
	) => {
		{jsphrase name='wcf.editor.button.group.block'}
		{jsphrase name='wcf.editor.button.group.format'}
		{jsphrase name='wcf.editor.button.group.list'}
		{jsphrase name='wcf.editor.restoreDraft'}

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');
		if (element === null) {
			throw new Error("Unable to find the source element '{$wysiwygSelector|encodeJS}' for the editor.")
		}

		const features = {
			attachment: element.dataset.disableAttachments !== "true",
			autosave: element.dataset.autosave || "",
			html: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}true{else}false{/if},
			image: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}true{else}false{/if},
			media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
			mention: element.dataset.supportMention === "true",
			spoiler: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('spoiler')}true{else}false{/if},
			url: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}true{else}false{/if},
		};

		const bbcodes = [
			{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
				{
					icon: '{@$__bbcode->wysiwygIcon|encodeJS}',
					name: '{@$__bbcode->bbcodeTag|encodeJS}',
					label: '{@$__bbcode->getButtonLabel()|encodeJS}',
				},
			{/foreach}
		];
		if (features.media) {
			bbcodes.push({
				icon: "file-circle-plus;false",
				name: "media",
				label: '{jslang}wcf.editor.button.media{/jslang}',
			});
		}

		void setupCkeditor(element, features, bbcodes);
	});
</script>
