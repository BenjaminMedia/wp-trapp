# WP TRAPP Plugin
Send content to the TRAPP translation service.

## Installation

As composer is very optional in WordPress community there are two ways to install this plugin.

### Composer

**If the project is loading the main composer autoload file.**

Install plugin:

`composer create-project benjaminmedia/wp-trapp 1.*`

Install plugin as working dev/master version:

`composer create-project benjaminmedia/wp-trapp --stability dev --prefer-dist`

Install plugin as a working dev/master version with all vcs files:

`composer create-project benjaminmedia/wp-trapp --stability dev --prefer-source --keep-vcs`

### WP Plugin

**If the plugin should not rely on anything other than its own composer functionality.**

Install plugin:

`composer require benjaminmedia/wp-trapp 1.*`

Install plugin as working dev/master version:

`composer require benjaminmedia/wp-trapp dev-master --prefer-dist`

Install plugin as a working dev/master version with all vcs files:

`composer require benjaminmedia/wp-trapp dev-master --prefer-source`
