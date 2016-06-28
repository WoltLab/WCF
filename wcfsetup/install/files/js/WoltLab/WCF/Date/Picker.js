/**
 * Date picker with time support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Date/Picker
 */
define(['DateUtil', 'Language', 'ObjectMap', 'Dom/ChangeListener', 'Ui/Alignment', 'WoltLab/WCF/Ui/CloseOverlay'], function(DateUtil, Language, ObjectMap, DomChangeListener, UiAlignment, UiCloseOverlay) {
	"use strict";
	
	var _didInit = false;
	var _firstDayOfWeek = 0;
	
	var _data = new ObjectMap();
	var _input = null;
	var _maxDate = 0;
	var _minDate = 0;
	
	var _dateCells = [];
	var _dateGrid = null;
	var _dateHour = null;
	var _dateMinute = null;
	var _dateMonth = null;
	var _dateMonthNext = null;
	var _dateMonthPrevious = null;
	var _dateTime = null;
	var _dateYear = null;
	var _datePicker = null;
	
	var _callbackOpen = null;
	
	/**
	 * @exports	WoltLab/WCF/Date/Picker
	 */
	var DatePicker = {
		/**
		 * Initializes all date and datetime input fields.
		 */
		init: function() {
			this._setup();
			
			var elements = elBySelAll('input[type="date"]:not(.inputDatePicker), input[type="datetime"]:not(.inputDatePicker)');
			var now = new Date();
			for (var i = 0, length = elements.length; i < length; i++) {
				var element = elements[i];
				element.classList.add('inputDatePicker');
				element.readOnly = true;
				
				var isDateTime = (elAttr(element, 'type') === 'datetime');
				
				elData(element, 'is-date-time', isDateTime);
				
				// convert value
				var date = null, value = elAttr(element, 'value');
				if (elAttr(element, 'value')) {
					date = new Date(value);
					elData(element, 'value', date.getTime());
					value = DateUtil['formatDate' + (isDateTime ? 'Time' : '')](date);
				}
				
				var isEmpty = (value.length === 0);
				
				// handle birthday input
				if (element.classList.contains('birthday')) {
					elData(element, 'min-date', '100');
					elData(element, 'max-date', 'now');
				}
				
				this._initDateRange(element, now, true);
				this._initDateRange(element, now, false);
				
				if (elData(element, 'min-date') === elData(element, 'max-date')) {
					throw new Error("Minimum and maximum date cannot be the same (element id '" + element.id + "').");
				}
				
				// change type to prevent browser's datepicker to trigger
				element.type = 'text';
				element.value = value;
				elData(element, 'empty', isEmpty);
				
				if (elData(element, 'placeholder')) {
					elAttr(element, 'placeholder', elData(element, 'placeholder'));
				}
				
				// add a hidden element to hold the actual date
				var shadowElement = elCreate('input');
				shadowElement.id = element.id + 'DatePicker';
				shadowElement.name = element.name;
				shadowElement.type = 'hidden';
				
				if (date !== null) {
					shadowElement.value = DateUtil.format(date, (isDateTime) ? 'c' : 'Y-m-d');
				}
				
				element.parentNode.insertBefore(shadowElement, element);
				element.removeAttribute('name');
				
				element.addEventListener(WCF_CLICK_EVENT, _callbackOpen);
				
				// create input addon
				var container = elCreate('div');
				container.className = 'inputAddon';
				
				var button = elCreate('a');
				button.className = 'inputSuffix button';
				button.addEventListener(WCF_CLICK_EVENT, _callbackOpen);
				container.appendChild(button);
				
				var icon = elCreate('span');
				icon.className = 'icon icon16 fa-calendar';
				button.appendChild(icon);
				
				element.parentNode.insertBefore(container, element);
				container.insertBefore(element, button);
				
				// check if the date input has one of the following classes set otherwise default to 'short'
				var hasClass = false, knownClasses = ['tiny', 'short', 'medium', 'long'];
				for (var j = 0; j < 4; j++) {
					if (element.classList.contains(knownClasses[j])) {
						hasClass = true;
					}
				}
				
				if (!hasClass) {
					element.classList.add('short')
				}
				
				_data.set(element, {
					shadow: shadowElement,
					
					isDateTime: isDateTime,
					isEmpty: isEmpty,
					
					onClose: null
				});
			}
		},
		
		/**
		 * Initializes the minimum/maximum date range.
		 * 
		 * @param	{Element}	element		input element
		 * @param	{Date}		now		current date
		 * @param	{boolean}	isMinDate	true for the minimum date
		 */
		_initDateRange: function(element, now, isMinDate) {
			var attribute = 'data-' + (isMinDate ? 'min' : 'max') + '-date';
			var value = (element.hasAttribute(attribute)) ? elAttr(element, attribute).trim() : '';
			
			if (value.match(/^(\d{4})-(\d{2})-(\d{2})$/)) {
				// YYYY-mm-dd
				value = new Date(value).getTime();
			}
			else if (value === 'now') {
				value = now.getTime();
			}
			else if (value.match(/^\d{1,3}$/)) {
				// relative time span in years
				var date = new Date(now.getTime());
				date.setFullYear(date.getFullYear() + ~~value * (isMinDate ? -1 : 1));
				
				value = date.getTime();
			}
			else if (value.match(/^datePicker-(.+)$/)) {
				// element id, e.g. `datePicker-someOtherElement`
				value = RegExp.$1;
				
				if (elById(value) === null) {
					throw new Error("Reference date picker identified by '" + value + "' does not exists (element id: '" + element.id + "').");
				}
			}
			else {
				value = new Date((isMinDate ? 1970 : 2038), 0, 1).getTime();
			}
			
			elAttr(element, attribute, value);
		},
		
		/**
		 * Sets up callbacks and event listeners.
		 */
		_setup: function() {
			if (_didInit) return;
			_didInit = true;
			
			_firstDayOfWeek = ~~Language.get('wcf.date.firstDayOfTheWeek');
			_callbackOpen = this._open.bind(this);
			
			DomChangeListener.add('WoltLab/WCF/Date/Picker', this.init.bind(this));
			UiCloseOverlay.add('WoltLab/WCF/Date/Picker', this._close.bind(this));
		},
		
		/**
		 * Opens the date picker.
		 * 
		 * @param	{object}	event		event object
		 */
		_open: function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			this._createPicker();
			
			var input = (event.currentTarget.nodeName === 'INPUT') ? event.currentTarget : event.currentTarget.previousElementSibling;
			if (input === _input) {
				return;
			}
			
			_input = input;
			var data = _data.get(_input), date, value = elData(_input, 'value');
			if (value) {
				date = new Date(+value);
				
				if (date.toString() === 'Invalid Date') {
					date = new Date();
				}
			}
			else {
				date = new Date();
			}
			
			// set min/max date
			_minDate = elData(_input, 'min-date');
			if (_minDate.match(/^datePicker-(.+)$/)) _minDate = elData(elById(RegExp.$1), 'value');
			_minDate = new Date(+_minDate);
			
			_maxDate = elData(_input, 'max-date');
			if (_maxDate.match(/^datePicker-(.+)$/)) _maxDate = elData(elById(RegExp.$1), 'value');
			_maxDate = new Date(+_maxDate);
			
			if (data.isDateTime) {
				_dateHour.value = date.getHours();
				_dateMinute.value = date.getMinutes();
				
				_datePicker.classList.add('datePickerTime');
			}
			else {
				_datePicker.classList.remove('datePickerTime');
			}
			
			this._renderPicker(date.getDate(), date.getMonth(), date.getFullYear());
			
			UiAlignment.set(_datePicker, _input);
		},
		
		/**
		 * Closes the date picker.
		 */
		_close: function() {
			if (_datePicker !== null && _datePicker.classList.contains('active')) {
				_datePicker.classList.remove('active');
				
				var data = _data.get(_input);
				if (typeof data.onClose === 'function') {
					data.onClose();
				}
				
				_input = null;
				_minDate = 0;
				_maxDate = 0;
			}
		},
		
		/**
		 * Renders the full picker on init.
		 * 
		 * @param	{int}           day
		 * @param	{int}           month
		 * @param	{int}           year
		 */
		_renderPicker: function(day, month, year) {
			this._renderGrid(day, month, year);
			
			// create options for month and year
			var years = '';
			for (var i = _minDate.getFullYear(), last = _maxDate.getFullYear(); i <= last; i++) {
				years += '<option value="' + i + '">' + i + '</option>';
			}
			_dateYear.innerHTML = years;
			_dateYear.value = year;
			
			_dateMonth.value = month;
			
			_datePicker.classList.add('active');
		},
		
		/**
		 * Updates the date grid.
		 * 
		 * @param	{int}           day
		 * @param	{int}           month
		 * @param	{int}           year
		 */
		_renderGrid: function(day, month, year) {
			var cell, hasDay = (day !== undefined), hasMonth = (month !== undefined), i;
			
			day = ~~day || ~~elData(_dateGrid, 'day');
			month = ~~month;
			year = ~~year;
			
			// rebuild cells
			if (hasMonth || year) {
				var rebuildMonths = (year !== 0);
				
				// rebuild grid
				var fragment = document.createDocumentFragment();
				fragment.appendChild(_dateGrid);
				
				if (!hasMonth) month = ~~elData(_dateGrid, 'month');
				year = year || ~~elData(_dateGrid, 'year');
				
				// check if current selection exceeds min/max date
				var date = new Date(year + '-' + ('0' + (month + 1).toString()).slice(-2) + '-' + ('0' + day.toString()).slice(-2));
				if (date < _minDate) {
					year = _minDate.getFullYear();
					month = _minDate.getMonth();
					day = _minDate.getDate();
					
					_dateMonth.value = month;
					_dateYear.value = year;
					
					rebuildMonths = true;
				}
				else if (date > _maxDate) {
					year = _maxDate.getFullYear();
					month = _maxDate.getMonth();
					day = _maxDate.getDate();
					
					_dateMonth.value = month;
					_dateYear.value = year;
					
					rebuildMonths = true;
				}
				
				date = new Date(year + '-' + ('0' + (month + 1).toString()).slice(-2) + '-01');
				
				// shift until first displayed day equals first day of week
				while (date.getDay() !== _firstDayOfWeek) {
					date.setDate(date.getDate() - 1);
				}
				
				var selectable;
				for (i = 0; i < 35; i++) {
					cell = _dateCells[i];
					
					cell.textContent = date.getDate();
					selectable = (date.getMonth() === month);
					if (selectable) {
						if (date < _minDate) selectable = false;
						else if (date > _maxDate) selectable = false;
					}
					
					cell.classList[selectable ? 'remove' : 'add']('otherMonth');
					date.setDate(date.getDate() + 1); 
				}
				
				elData(_dateGrid, 'month', month);
				elData(_dateGrid, 'year', year);
				
				_datePicker.insertBefore(fragment, _dateTime);
				
				if (!hasDay) {
					// check if date is valid
					date = new Date(year, month, day);
					if (date.getDate() !== day) {
						while (date.getMonth() !== month) {
							date.setDate(date.getDate() - 1);
						}
						
						day = date.getDate();
					}
				}
				
				if (rebuildMonths) {
					for (i = 0; i < 12; i++) {
						var currentMonth = _dateMonth.children[i];
						
						currentMonth.disabled = (year === _minDate.getFullYear() && currentMonth.value < _minDate.getMonth()) || (year === _maxDate.getFullYear() && currentMonth.value > _maxDate.getMonth());
					}
					
					var nextMonth = new Date(year + '-' + ('0' + (month + 1).toString()).slice(-2) + '-01');
					nextMonth.setMonth(nextMonth.getMonth() + 1);
					
					_dateMonthNext.classList[(nextMonth < _maxDate) ? 'add' : 'remove']('active');
					
					var previousMonth = new Date(year + '-' + ('0' + (month + 1).toString()).slice(-2) + '-01');
					previousMonth.setDate(previousMonth.getDate() - 1);
					
					_dateMonthPrevious.classList[(previousMonth > _minDate) ? 'add' : 'remove']('active');
				}
			}
			
			// update active day
			if (day) {
				for (i = 0; i < 35; i++) {
					cell = _dateCells[i];
					
					cell.classList[(!cell.classList.contains('otherMonth') && ~~cell.textContent === day) ? 'add' : 'remove']('active');
				}
				
				elData(_dateGrid, 'day', day);
			}
			
			this._formatValue();
		},
		
		/**
		 * Sets the visible and shadow value
		 */
		_formatValue: function() {
			var data = _data.get(_input), date, value, shadowValue;
			
			if (elData(_input, 'empty') === 'true') {
				return;
			}
			
			if (data.isDateTime) {
				date = new Date(
					elData(_dateGrid, 'year'),
					elData(_dateGrid, 'month'),
					elData(_dateGrid, 'day'),
					_dateHour.value,
					_dateMinute.value
				);
				
				value = DateUtil.formatDateTime(date);
				shadowValue = DateUtil.format(date, 'c');
			}
			else {
				date = new Date(
					elData(_dateGrid, 'year'),
					elData(_dateGrid, 'month'),
					elData(_dateGrid, 'day')
				);
				
				value = DateUtil.formatDate(date);
				shadowValue = DateUtil.format(date, 'Y-m-d');
			}
			
			_input.value = value;
			elData(_input, 'value', date.getTime());
			data.shadow.value = shadowValue;
		},
		
		/**
		 * Creates the date picker DOM.
		 */
		_createPicker: function() {
			if (_datePicker !== null) {
				return;
			}
			
			_datePicker = elCreate('div');
			_datePicker.className = 'datePicker';
			_datePicker.addEventListener(WCF_CLICK_EVENT, function(event) { event.stopPropagation(); });
			
			var header = elCreate('header');
			_datePicker.appendChild(header);
			
			_dateMonthPrevious = elCreate('a');
			_dateMonthPrevious.className = 'previous';
			_dateMonthPrevious.innerHTML = '<span class="icon icon16 fa-arrow-left"></span>';
			_dateMonthPrevious.addEventListener(WCF_CLICK_EVENT, this.previousMonth.bind(this));
			header.appendChild(_dateMonthPrevious);
			
			var monthYearContainer = elCreate('span');
			header.appendChild(monthYearContainer);
			
			_dateMonth = elCreate('select');
			_dateMonth.className = 'month';
			_dateMonth.addEventListener('change', this._changeMonth.bind(this));
			
			var selectWrapper = elCreate('label');
			selectWrapper.className = 'selectDropdown';
			selectWrapper.appendChild(_dateMonth);
			monthYearContainer.appendChild(selectWrapper);
			
			var i, months = '', monthNames = Language.get('__monthsShort');
			for (i = 0; i < 12; i++) {
				months += '<option value="' + i + '">' + monthNames[i] + '</option>';
			}
			_dateMonth.innerHTML = months;
			
			_dateYear = elCreate('select');
			_dateYear.className = 'year';
			_dateYear.addEventListener('change', this._changeYear.bind(this));
			
			selectWrapper = elCreate('label');
			selectWrapper.className = 'selectDropdown';
			selectWrapper.appendChild(_dateYear);
			monthYearContainer.appendChild(selectWrapper);
			
			_dateMonthNext = elCreate('a');
			_dateMonthNext.className = 'next';
			_dateMonthNext.innerHTML = '<span class="icon icon16 fa-arrow-right"></span>';
			_dateMonthNext.addEventListener(WCF_CLICK_EVENT, this.nextMonth.bind(this));
			header.appendChild(_dateMonthNext);
			
			_dateGrid = elCreate('ul');
			_datePicker.appendChild(_dateGrid);
			
			var item = elCreate('li');
			item.className = 'weekdays';
			_dateGrid.appendChild(item);
			
			var span, weekdays = Language.get('__daysShort');
			for (i = 0; i < 7; i++) {
				var day = i + _firstDayOfWeek;
				if (day > 6) day -= 7;
				
				span = elCreate('span');
				span.textContent = weekdays[day];
				item.appendChild(span);
			}
			
			// create date grid
			var callbackClick = this._click.bind(this), cell, row;
			for (i = 0; i < 5; i++) {
				row = elCreate('li');
				_dateGrid.appendChild(row);
				
				for (var j = 0; j < 7; j++) {
					cell = elCreate('a');
					cell.addEventListener(WCF_CLICK_EVENT, callbackClick);
					_dateCells.push(cell);
					
					row.appendChild(cell);
				}
			}
			
			_dateTime = elCreate('footer');
			_datePicker.appendChild(_dateTime);
			
			_dateHour = elCreate('select');
			_dateHour.className = 'hour';
			_dateHour.addEventListener('change', this._formatValue.bind(this));
			
			var tmp = '';
			var date = new Date(2000, 0, 1);
			var timeFormat = Language.get('wcf.date.timeFormat').replace(/:/, '').replace(/[isu]/g, '');
			for (i = 0; i < 24; i++) {
				date.setHours(i);
				tmp += '<option value="' + i + '">' + DateUtil.format(date, timeFormat) + "</option>";
			}
			_dateHour.innerHTML = tmp;
			
			_dateTime.appendChild(_dateHour);
			
			_dateTime.appendChild(document.createTextNode('\u00A0:\u00A0'));
			
			_dateMinute = elCreate('select');
			_dateMinute.className = 'minute';
			_dateMinute.addEventListener('change', this._formatValue.bind(this));
			
			tmp = '';
			for (i = 0; i < 60; i++) {
				tmp += '<option value="' + i + '">' + (i < 10 ? '0' + i.toString() : i) + '</option>';
			}
			_dateMinute.innerHTML = tmp;
			
			_dateTime.appendChild(_dateMinute);
			
			document.body.appendChild(_datePicker);
		},
		
		/**
		 * Shows the previous month.
		 */
		previousMonth: function() {
			if (_dateMonth.value === '0') {
				_dateMonth.value = 11;
				_dateYear.value = ~~_dateYear.value - 1;
			}
			else {
				_dateMonth.value = ~~_dateMonth.value - 1;
			}
			
			this._renderGrid(undefined, _dateMonth.value, _dateYear.value);
		},
		
		/**
		 * Shows the next month.
		 */
		nextMonth: function() {
			if (_dateMonth.value === '11') {
				_dateMonth.value = 0;
				_dateYear.value = ~~_dateYear.value + 1;
			}
			else {
				_dateMonth.value = ~~_dateMonth.value + 1;
			}
			
			this._renderGrid(undefined, _dateMonth.value, _dateYear.value);
		},
		
		/**
		 * Handles changes to the month select element.
		 * 
		 * @param	{object}	event		event object
		 */
		_changeMonth: function(event) {
			this._renderGrid(undefined, event.currentTarget.value);
		},
		
		/**
		 * Handles changes to the year select element.
		 * 
		 * @param	{object}	event		event object
		 */
		_changeYear: function(event) {
			this._renderGrid(undefined, undefined, event.currentTarget.value);
		},
		
		/**
		 * Handles clicks on an individual day.
		 * 
		 * @param	{object}	event		event object
		 */
		_click: function(event) {
			if (event.currentTarget.classList.contains('otherMonth')) {
				return;
			}
			
			elData(_input, 'empty', false);
			
			this._renderGrid(event.currentTarget.textContent);
			
			this._close();
		},
		
		/**
		 * Returns the current Date object or null.
		 * 
		 * @param	{(Element|string)}	element		input element or id
		 * @return	{?Date}			Date object or null
		 */
		getDate: function(element) {
			element = this._getElement(element);
			
			if (element.hasAttribute('data-value')) {
				return new Date(+elData(element, 'value'));
			}
			
			return null;
		},
		
		/**
		 * Sets the date of given element.
		 * 
		 * @param	{(HTMLInputElement|string)}	element		input element or id
		 * @param	{Date}			        date		Date object
		 */
		setDate: function(element, date) {
			element = this._getElement(element);
			var data = _data.get(element);
			
			elData(element, 'value', date.getTime());
			element.value = DateUtil['formatDate' + (data.isDateTime ? 'Time' : '')](date);
			
			data.shadow.value = DateUtil.format(date, (data.isDateTime ? 'c' : 'Y-m-d'));
		},
		
		/**
		 * Clears the date value of given element.
		 * 
		 * @param	{(HTMLInputElement|string)}	element		input element or id
		 */
		clear: function(element) {
			element = this._getElement(element);
			var data = _data.get(element);
			
			element.removeAttribute('data-value');
			element.value = '';
			
			data.isEmpty = true;
			data.shadow.value = '';
		},
		
		/**
		 * Reverts the date picker into a normal input field.
		 * 
		 * @param	{(HTMLInputElement|string)}	element		input element or id
		 */
		destroy: function(element) {
			element = this._getElement(element);
			var data = _data.get(element);
			
			var container = element.parentNode;
			container.parentNode.insertBefore(element, container);
			elRemove(container);
			
			elAttr(element, 'type', 'date' + (data.isDateTime ? 'time' : ''));
			element.value = data.shadow.value;
			
			element.removeAttribute('data-value');
			element.removeEventListener(WCF_CLICK_EVENT, _callbackOpen);
			elRemove(data.shadow);
			
			element.classList.remove('inputDatePicker');
			element.readOnly = false;
			_data['delete'](element);
		},
		
		/**
		 * Sets the callback invoked on picker close.
		 * 
		 * @param	{(Element|string)}	element		input element or id
		 * @param	{function}		callback	callback function
		 */
		setCloseCallback: function(element, callback) {
			element = this._getElement(element);
			_data.get(element).onClose = callback;
		},
		
		/**
		 * Validates given element or id if it represents an active date picker.
		 * 
		 * @param	{(Element|string)}	element		input element or id
		 * @return	{Element}		input element
		 */
		_getElement: function(element) {
			if (typeof element === 'string') element = elById(element);
			
			if (!(element instanceof Element) || !element.classList.contains('inputDatePicker') || !_data.has(element)) {
				throw new Error("Expected a valid date picker input element or id.");
			}
			
			return element;
		}
	};
	
	// backward-compatibility for `$.ui.datepicker` shim
	window.__wcf_bc_datePicker = DatePicker;
	
	return DatePicker;
});
