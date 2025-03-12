=== SSL Alert WP ===
Contributors: kpitene
Donate link: https://github.com/kpitene/ssl-alert-wp
Tags: ssl, security, certificate, expiration, notification
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monitor your WordPress site's SSL certificate expiration and receive timely notifications to prevent security issues and browser warnings.

== Description ==

SSL Alert WP is a simple yet powerful plugin that helps you monitor your WordPress site's SSL certificate expiration. It automatically checks your certificate status and sends notifications before it expires, helping you avoid security issues and browser warnings that could affect your visitors' experience.

**Key Features:**

* Automatically monitors SSL certificate expiration for your WordPress site
* Configurable notification schedule (default: 14 days, 7 days, 1 day before expiration)
* Email notifications to site administrators or custom email addresses
* Daily notifications after certificate expiration (optional)
* Manual certificate check from WordPress admin panel
* Multi-language support (English and French, with more languages planned)
* Easy-to-use settings page in WordPress admin

**Why SSL Monitoring Matters:**

An expired SSL certificate can cause serious problems for your website:
* Visitors will see security warnings in their browsers
* Search engines may lower your site's ranking
* User trust in your website will decrease
* Sensitive data may be at risk

SSL Alert WP helps you stay ahead of these issues by providing timely reminders to renew your certificate.

== Frequently Asked Questions ==

= How does the plugin check my SSL certificate? =

The plugin connects to your website's URL (or a custom URL you specify) and retrieves the SSL certificate information, including the expiration date.

= Can I monitor a different URL than my WordPress site? =

Yes, you can specify a different HTTPS URL to monitor in the plugin settings.

= How often does the plugin check my certificate? =

The plugin runs a daily check of your certificate status using the WordPress cron system.

= Can I manually check my certificate status? =

Yes, you can manually check your SSL certificate status at any time by going to Settings > SSL Monitor and clicking the "Check Now" button.

= What languages does the plugin support? =

The plugin currently supports English (default) and French (fr_FR). More languages are planned for future releases.

= How do I add a new translation manually? =

To add a new translation:
1. Copy the `languages/ssl-alert-wp.fr_FR.po` file which has all strings
2. Create new `.po` and `.mo` files for your language
3. Place them in the `languages` directory
4. Compile .mo file with poeditor, gettext command line, or other tool:
   `msgfmt -c -v -o ssl-alert-wp-fr_FR.mo ssl-alert-wp-fr_FR.po`

== Screenshots ==

1. SSL Monitor settings page
2. Certificate status display
3. Email notification example

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of SSL Alert WP.

== Configuration ==

1. **URL to Monitor**: By default, the plugin monitors your WordPress site's URL. You can optionally specify a different HTTPS URL to monitor.

2. **Notification Days**: Set when you want to receive notifications before certificate expiration. Default: 14, 7, and 1 day before expiration. 

3. **Notification Emails**: Specify which email addresses should receive notifications. By default, notifications are sent to the WordPress admin email.

== Requirements ==

* WordPress 5.0 or higher
* PHP 7.2 or higher
* HTTPS enabled on the monitored site

== Support ==

For bug reports or feature requests, please use the [GitHub issue tracker](https://github.com/kpitene/ssl-alert-wp/issues).
