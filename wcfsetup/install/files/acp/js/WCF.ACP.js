/**
 * Class and function collection for WCF ACP
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * Initialize WCF.ACP namespace
 */
WCF.ACP = { };

/**
 * Namespace for ACP application management.
 */
WCF.ACP.Application = { };

/**
 * Namespace for ACP package management.
 */
WCF.ACP.Package = { };

/**
 * Worker support for ACP.
 * 
 * @param	string		dialogID
 * @param	string		className
 * @param	string		title
 * @param	object		parameters
 * @param	object		callback
 * 
 * @deprecated  3.1 - please use `WoltLabSuite/Core/Acp/Ui/Worker` instead
 */
WCF.ACP.Worker = Class.extend({
	/**
	 * Initializes a new worker instance.
	 * 
	 * @param	string		dialogID
	 * @param	string		className
	 * @param	string		title
	 * @param	object		parameters
	 * @param	object		callback
	 */
	init: function(dialogID, className, title, parameters, callback) {
		if (typeof callback === 'function') {
			throw new Error("The callback parameter is no longer supported, please migrate to 'WoltLabSuite/Core/Acp/Ui/Worker'.");
		}
		
		require(['WoltLabSuite/Core/Acp/Ui/Worker'], function(AcpUiWorker) {
			new AcpUiWorker({
				// dialog
				dialogId: dialogID,
				dialogTitle: title,
				
				// ajax
				className: className,
				parameters: parameters
			});
		});
	}
});

/**
 * Namespace for stat-related classes.
 */
WCF.ACP.Stat = { };

/**
 * Shows the daily stat chart.
 */
WCF.ACP.Stat.Chart = Class.extend({
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		$('#statRefreshButton').click($.proxy(this._refresh, this));
		
		this._refresh();
	},
	
	_refresh: function() {
		var $objectTypeIDs = [ ];
		$('input[name=objectTypeID]:checked').each(function() {
			$objectTypeIDs.push($(this).val());
		});
		
		if (!$objectTypeIDs.length) return;
		
		this._proxy.setOption('data', {
			className: 'wcf\\data\\stat\\daily\\StatDailyAction',
			actionName: 'getData',
			parameters: {
				startDate: $('#startDateDatePicker').val(),
				endDate: $('#endDateDatePicker').val(),
				value: $('input[name=value]:checked').val(),
				dateGrouping: $('input[name=dateGrouping]:checked').val(),
				objectTypeIDs: $objectTypeIDs
			}
		});
		this._proxy.sendRequest();
	},
	
	_success: function(data) {
		switch ($('input[name=dateGrouping]:checked').val()) {
			case 'yearly':
				var $minTickSize = [1, "year"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.yearly');
				break;
			case 'monthly':
				var $minTickSize = [1, "month"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.monthly');
				break;
			case 'weekly':
				var $minTickSize = [7, "day"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.weekly');
				break;
			default:
				var $minTickSize = [1, "day"];
				var $timeFormat = WCF.Language.get('wcf.acp.stat.timeFormat.daily');
		}
		
		var options = {
			series: {
				lines: {
					show: true
				},
				points: {
					show: true
				}
			},
			grid: {
				hoverable: true
			},
			xaxis: {
				mode: "time",
				minTickSize: $minTickSize,
				timeformat: $timeFormat,
				monthNames: WCF.Language.get('__monthsShort')
			},
			yaxis: {
				min: 0,
				tickDecimals: 0,
				tickFormatter: function(val) {
					return WCF.String.addThousandsSeparator(val);
				}
			}
		};
		
		var $data = [ ];
		for (var $key in data.returnValues) {
			var $row = data.returnValues[$key];
			for (var $i = 0; $i < $row.data.length; $i++) {
				$row.data[$i][0] *= 1000;
			}
			
			$data.push($row);
		}
		
		$.plot("#chart", $data, options);
		
		require(['Ui/Alignment'], function (UiAlignment) {
			var span = elCreate('span');
			span.style.setProperty('position', 'absolute', '');
			document.body.appendChild(span);
			$("#chart").on("plothover", function(event, pos, item) {
				if (item) {
					span.style.setProperty('top', item.pageY + 'px', '');
					span.style.setProperty('left', item.pageX + 'px', '');
					$("#chartTooltip").html(item.series.xaxis.tickFormatter(item.datapoint[0], item.series.xaxis) + ', ' + WCF.String.formatNumeric(item.datapoint[1]) + ' ' + item.series.label).show();
					UiAlignment.set($("#chartTooltip")[0], span, {
						verticalOffset: 5,
						horizontal: 'center',
						vertical: 'top'
					});
				}
				else {
					$("#chartTooltip").hide();
				}
			});
		});
		
		if (!$data.length) {
			$('#chart').append('<p style="position: absolute; font-size: 1.2rem; text-align: center; top: 50%; margin-top: -20px; width: 100%">' + WCF.Language.get('wcf.acp.stat.noData') + '</p>');
		}
		
		elBySel('.contentHeader > .contentTitle').scrollIntoView({ behavior: 'smooth' });
	}
});
