# SSL Alert WP

The wordpress plugin files are all located in the plugins/ssl-alert-wp directory
Other files in parent directory are for development and docker

## Installation of Development Environment

1. install docker from official repos
2. run docker compose up -d 
3. go to http://localhost for wordpress setup
4. setup wordpress and install plugin from wordpress admin interface

All languages files are copied into wp-content/languages/plugins during build.

## Emails

If you want to test or debug email, i recommand to install SMTP Mailer + WP Mail Debugger plugins from wordpress admin interface.