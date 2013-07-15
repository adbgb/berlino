=== WP SlimStat Shortcodes ===
Contributors: coolmann
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=Z732JS7KQ6RRL&lc=US&item_name=WP%20SlimStat&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: chart, analytics, visitors, users, spy, shortstat, tracking, reports, seo, referers, analyze, wassup, geolocation, online users, spider, tracker, pageviews, world map, stats, maxmind, flot, stalker, statistics, google+, monitor, seo
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 2.1

== Description ==
An extension for WP SlimStat to display metrics on public-facing pages and widgets

If you have always wanted to show off a little, here's your chance: share your analytics data with your visitors, 
in just a few easy steps. Take the power of WP SlimStat to a whole new level: create a page listing the most visited posts,
or categories, or the most popular search terms, or the most recent. It's up to you, really. Need to filter results to
a specific browser? Check. Want to create a page with last year's most popular search terms from France? Check. All you
have to do is add a snippet of `code` to the page where you want the stats to be shown, and... save.

= Requirements =
* Wordpress 3.1 or higher
* [WP Slimstat 2.8+](http://wordpress.org/extend/plugins/wp-slimstat/)

== Installation ==

1. Log-in to your Wordpress admin
1. Go to Plugins > Add New
1. Search for WP SlimStat Shortcodes
1. Click on `Install Now` under WP SlimStat Shortcodes
1. Add one of the [many shortcodes available](http://wordpress.org/extend/plugins/wp-slimstat-shortcodes/faq/) to your pages

== Frequently Asked Questions ==

= What is a shortcode? =
A [shortcode](http://codex.wordpress.org/Shortcode_API) is sort of a placeholder: a special string that will be replaced by dynamic content.

= What do shortcodes look like? =

[slimstat f='FUNC' w='WHAT_COLUMN' lf='FILTERS' lc='COLUMNS' s='SEP']

where

* `f` [required] is the criteria to be used (count results, get popular or recent items)
* `w` [required] defines what dataset you want to retrieve (browser, language, resource; here below you can find a complete list of columns)
* `lf` [optional] specifies what conditions to use while retrieving the data (browser equals Firefox, country equals China); if the value starts with `WHERE:`, the string that follows will be used *verbatim* in the SQL query
* `lc` [optional, default: WHAT_COLUMN] tells the plugin what data to return; use * for all the columns (defaults to the column specified in `w`)
* `s` [optional] sets the character or string used to "separate" each piece of information in a row (defaults to `<span class='slimstat-item-separator'>,</span>`)

= Frequently used codes =

* Count all pageviews from the beginning: `[slimstat f='count-all' w='*']`
* Popular pages (this month): `[slimstat f='popular' w='resource' lf='content_type equals post' lc='post_link,count']`
* Recent searches: `[slimstat f='recent' w='searchterms']`
* Popular searches this year so far: `[slimstat f='popular' w='searchterms' lf='day equals 1|month equals 1|interval equals -1']`
* Most active visitors: `[slimstat f='popular' w='user' lc='user,count']`

= Available functions =

* `count` and `count-all` return a number
* `recent` and `popular` return a bulleted list of elements
* `custom`, used along with `filterby` to run a custom SQL query - i.e. `filterby='SELECT * FROM wp_slim_stats t1...'`; it returns a list of elements

= Available keys (WHAT_COLUMN) =

* `browser`: user agent (Firefox, Chrome, ...)
* `content_type`: post, page, *custom-post-type*, attachment, singular, post_type_archive, tag, taxonomy, category, date, author, archive, search, feed, home; please refer to the [Conditional Tags](http://codex.wordpress.org/Conditional_Tags) manual page for more information
* `country`: 2-letter code (us, ru, de, it, ...)
* `domain`: domain name of the referring page (i.e., www.google.com if a visitor came from Google)
* `language`: please refer to the [language culture names](http://msdn.microsoft.com/en-us/library/ee825488(v=cs.20).aspx) (first column) for more information
* `platform`: operating system; it accepts identifiers like win7, win98, macosx, ...; please refer to [this manual page](http://php.net/manual/en/function.get-browser.php) for more information about these codes
* `referer`: complete URL of the referring page
* `searchterms`: search term used to find your website on a search engine
* `user`: visitor's name according to the cookie set by Wordpress after s/he left a comment

= How do I filter the data returned by your plugin? =

A filter consists of three elements: the key, the condition and the value. For example, if you want to display your 20 most popular posts visited by people coming from Italy,
your key is `country`, your condition is `equals` and your value is `it` (Country code for Italy). You can combine multiple keys/values to further narrow down your results.

= Available filters =

* all the keys listed above
* `author`: Wordpress author associated to that post/page when the resource was accessed
* `browser`: user agent (Firefox, Chrome, ...)
* `version`: user agent version (9.0, 11, ...)
* `category`: ID of the category/term associated to the resource, when available
* `css_version`: what CSS standard was supported by that browser (1, 2, 3 and other integer values)
* `colordepth`: visitor's screen's color depth (8, 16, 24, ...)
* `ip`: visitor's public IP address
* `other_ip`: visitor's private IP address, if available
* `resolution`: viewport width and height (1024x768, 800x600, ...)
* `resource`: URL accessed on your site
* `type`: (browser) 1 = search engine crawler, 2 = mobile device, 3 = syndication reader, 0 = all others
* `direction`: asc or desc (lowercase)
* `limit_results`: max number of results returned
* `starting`: return results starting from a given offset

= Can I use date ranges? =
Yes you can! By default shortcodes return data related to the current month, but you can specify a different start date and the interval in your filters (along with the `equals` operator):

* `day` (default: 1)
* `month` (default: current month)
* `year` (default: current year)
* `strtotime`, use it if you want the plugin to use PHP's [strtotime](http://php.net/manual/en/function.strtotime.php) function to calculate a specific date for you (yesteday, this month, last year, 3 weeks ago)
* `interval`, to fix the number of days from the start date (for example, ten days starting from January 14th); please make sure to define `interval` *after* the start date!
 * if both `day` or `strtotime` are set, the default value is zero
 * if neither `day` nor `strtotime` are set, the default value is the number of days in the current month
 * you can use `-1` to indicate the number of days between the start date and today (in other words, to describe date ranges like 'Year to date', you can set `day equals 1,month equals 1,interval equals -1`)

= What conditional operators are available? (note: words are separated by underscores, not blank spaces!) =

* `equals`
* `is_not_equal_to`
* `contains`
* `does_not_contain`
* `starts_with`
* `ends_with`
* `sounds_like`
* `is_greater_than`
* `is_less_than`
* `is_empty` (followed by a blank space and the # sign: searchterms is_empty #|month equals 5)
* `is_not_empty` (followed by a blank space and the # sign: searchterms is_not_empty #|browser contains fire)

= How do I combine keys and values to create filters? =
In order to simplify things, WP SlimStat Shortcodes implements a 'natural language' approach: define filters combining the various parameters, separating groups with the 'pipe' ( | ) character.
For example, let's say you want to obtain your blog's 5 most popular posts. This is what the shortcode will look like:

`[slimstat f='popular' w='resource' lf='content_type equals post|limit_results equals 5' lc='post_link,count']`

Curious about what your visitors where searching for, before landing on your blog?

`[slimstat f='recent' w='searchterms']`

Do you want to target a specific language? What about listing the 20 most recent Chinese-speaking IP addresses who accessed just your homepage:

`[slimstat f='recent' w='ip' lf='content_type equals home|language equals zh-cn|limit_results equals 20']`

Do you need just to count how many pageviews have been generated during the current month? (by default all shortcodes use the current month as date range)

`[slimstat f='count' w='*']`

Maybe this year?

`[slimstat f='count' w='*' lf='strtotime equals 1 January|interval equals -1']`

The first 5 days of January?
 
`[slimstat f='count' w='*' lf='day equals 1|month equals 1|interval equals 5']`

From the beginning (all pageviews recorded so far)

`[slimstat f='count-all' w='*']`

Unique IPs (unique visitors):

`[slimstat f='count' w='ip']`

Unique Visits this month:

`[slimstat f='count' w='visit_id']`

Things can easily get fancy

* `[slimstat f='popular' w='searchterms' lf='searchterms contains pizza dough' lc='searchterms,count']`

= Available columns (lc) =

* all the keys listed here above, separated by commas
* `count`, available only when using `popular`, represents the number of pageviews matching your query
* `dt`, combined with `recent`, can be used to display date and time of each pageview (it uses your Wordpress settings for date and time format)
* `hostname`, to convert IP addresses into hostnames, using PHP's [gethostbyaddr](http://www.php.net/manual/en/function.gethostbyaddr.php)
* `post_link`, returns post titles linked to their corresponding permalinks

== Changelog ==

= 2.1 =
* Added: shortcode to count all pageviews recorded so far (not just the current month, thank you [Zeb](http://wordpress.org/support/topic/total-pageviews))
* Fixed: minor bugs in interpreting certain values

= 2.0 =
* Code has been almost completely rewritten
* New filters have been added
* Syntax has been simplified