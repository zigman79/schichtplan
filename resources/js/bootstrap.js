import axios from 'axios'
import dayjs from 'dayjs'
import lodash from 'lodash'

import 'dayjs/locale/de' // load on demand
import LocalizedFormat from 'dayjs/plugin/localizedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import weekOfYear from 'dayjs/plugin/weekOfYear'
import weekday from 'dayjs/plugin/weekday'
import utc from 'dayjs/plugin/utc'

/**
 * DAYJS JS because where handling so much Dates
 */

dayjs.locale('de')
dayjs.extend(LocalizedFormat)
dayjs.extend(customParseFormat)
dayjs.extend(utc)
dayjs.extend(isSameOrBefore)
dayjs.extend(weekOfYear)
dayjs.extend(weekday)

window.dayjs = dayjs
window._ = lodash

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default dayjs
