# SSL Alert WP

Monitor your WordPress site's SSL certificate expiration and receive timely notifications to prevent security issues and browser warnings.

## Features

- Automatically monitors SSL certificate expiration for your WordPress site
- Configurable notification schedule (default: 14 days, 7 days, 1 day before expiration)
- Email notifications to site administrators or custom email addresses
- Daily notifications after certificate expiration (optional)
- Manual certificate check from WordPress admin panel
- Multi-language support (English and French and more later)
- Easy-to-use settings page in WordPress admin

## Manual Installation

If not installed from recommanded process of using wordpress admin interface :
1. Upload the `plugins/ssl-alert-wp` folder to the `/wp-content/plugins/` directory
2. Copy plugins/ssl-alert-wp/languages/ files in wordpress wp-content/languages/plugins directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > SSL Monitor to configure notification settings

## Configuration

1. **URL to Monitor**: By default, the plugin monitors your WordPress site's URL. You can optionally specify a different HTTPS URL to monitor.

2. **Notification Days**: Set when you want to receive notifications before certificate expiration. Default: 14, 7, and 1 day before expiration. 

3. **Notification Emails**: Specify which email addresses should receive notifications. By default, notifications are sent to the WordPress admin email.


## Manual Check

You can manually check your SSL certificate status at any time:

1. Go to Settings > SSL Monitor
2. Click the "Check Now" button
3. View the immediate results on the page


### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- HTTPS enabled on the monitored site

### Translations

The plugin currently supports:
- English (default)
- French (fr_FR)

To add a new translation:
1. Copy the `languages/ssl-alert-wp.fr_FR.po` file which has all strings
2. Create new `.po` and `.mo` files for your language
3. Place them in the `languages` directory
4. compile .mo file with poeditor, gettext command line, or other tool : 
msgfmt -c -v -o ssl-alert-wp-fr_FR.mo ssl-alert-wp-fr_FR.po 


## License

This plugin is licensed under the GPL v2 or later.

See [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html) for more information.

## Support

For bug reports or feature requests, please use the [GitHub issue tracker](https://github.com/kpitene/ssl-alert-wp/issues).
