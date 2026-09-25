<dl>
	<dt></dt>
	<dd>
		<woltlab-core-notice type="info" id="cssClassNameExample">{lang}wcf.acp.notice.example{/lang}</woltlab-core-notice>
	</dd>
</dl>

<script data-relocate="true">
	{
		const example = document.getElementById('cssClassNameExample');
		const updateExample = (cssClassName) => {
			if (cssClassName === 'custom') {
				example.hidden = true;
			}
			else {
				example.type = cssClassName;
				example.hidden = false;
			}
		};

		document.querySelectorAll('input[name=cssClassName]').forEach((element) => {
			element.addEventListener('change', () => {
				updateExample(element.value);
			});
		});

		const checked = document.querySelector('input[name=cssClassName]:checked');
		if (checked !== null) {
			updateExample(checked.value);
		}
	}
</script>
