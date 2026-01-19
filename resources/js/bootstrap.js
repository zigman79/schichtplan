
import 'dayjs/locale/de' // load on demand
import LocalizedFormat from 'dayjs/plugin/localizedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import weekOfYear from 'dayjs/plugin/weekOfYear'
import weekday from 'dayjs/plugin/weekday'
import utc from 'dayjs/plugin/utc'

/**
 * DAYJS JS because where handling so much Dates
 * @type {dayjs | ((inp?: dayjs.MomentInput, format?: dayjs.MomentFormatSpecification, language?: string, strict?: boolean) => dayjs.Moment) | ((inp?: dayjs.MomentInput, format?: dayjs.MomentFormatSpecification, strict?: boolean) => dayjs.Moment)}
 */

window.dayjs = require('dayjs');
window.dayjs.locale('de');
window.dayjs.extend(LocalizedFormat);

dayjs.extend(customParseFormat)
dayjs.extend(utc)
dayjs.extend(isSameOrBefore)
dayjs.extend(weekOfYear)
dayjs.extend(weekday)

window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
