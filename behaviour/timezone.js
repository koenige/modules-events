/*
 * events module
 * JavaScript to display time and date for an event in local timezone
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/events
 *
 * @copyright Copyright © 2020, 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */
 

function calculateNewTime() {
	var currentDate = new Date();
	var currentTimezone = currentDate.getTimezoneOffset();
	var times = document.getElementsByTagName('time');
	var timezones = document.getElementsByClassName('timezone');
	if (timezones.length != 1) return false;
	var timezone = timezones[0].innerHTML;
	timezoneDiff = - timezone;
	timezoneDiff = timezoneDiff / 100 * 60;
	if (timezoneDiff === currentTimezone) return;

	var startDate = '';
	var endDate = '';

    for (i = 0; i < times.length; i++) {
        var date = new Date(times[i].getAttribute('datetime') + timezone);
        var options = {
        	weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
        	hour: '2-digit', minute: '2-digit', hour12: false
        };
        var formattedDate = new Intl.DateTimeFormat('en-GB', options).format(date).replace(',', '').replace(':', '.');

        if (times[i].getAttribute('itemprop') === 'startDate') {
            startDate = formattedDate;
        } else if (times[i].getAttribute('itemprop') === 'endDate') {
            endDate = formattedDate;
            if (endDate.substring(0, 14) === startDate.substring(0, 14))
                endDate = endDate.substring(17);
        }
    }

	var span = document.createElement('span');
	if (endDate) {
		var localTime = document.createTextNode(' (Your Time: ' +  startDate + '–' + endDate  + ' h)');
	} else {
		var localTime = document.createTextNode(' (Your Time: ' + startDate + ' h)');
	}
	span.appendChild(document.createElement('br'));
	span.appendChild(localTime);
	
	timezones[0].appendChild(span);
}

calculateNewTime();
