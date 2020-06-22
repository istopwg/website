# PWG Web Site

This repository contains the [PWG web site](https://www.pwg.org) code, core web
pages, and resource files.


## Directory/File Organization

The web site repository is generally organized by workgroup or topic:

- `3d`: 3D Printing information
- `blog`: Blog articles, usually announcements or summaries of meetings.
- `chair`: PWG officers page and other general PWG information.
- `cloud`: Information about the (inactive) Cloud Imaging Model workgroup.
- `dynamo`: The PHP site code - mainly a templating engine with some management
  functions thrown in.
- `ids`: Information about the Imaging Device Security workgroup.
- `ipp`: Information about the Internet Printing Protocol workgroup.
- `pwg-logos`: Logo images for member companies.
- `sm`: Information about the (inactive) Semantic Model workgroup.
- `wims`: Information about the (inactive) Workgroup for Imaging Management
  Services.

The `index.html` and `standards.html` files are placeholders for the
corresponding PHP files in the `dynamo` directory.  All other HTML files get
the standard site template applied to them, while other files are passed to the
web browser as-is.

Changes pushed to the `dynamo` directory are updated on the web site.  Changes
to the other directories currently will not appear on the PWG web site since
they are hosted by the FTP server from the `pub/pwg/www` directory.


## Architecture

The web site uses Apache, PHP, and a SQL database (currently MariaDB).
Configuration files that can be used for testing can be found in the
`dynamo/config` directory.

The `site.cfg.tmpl` file is the template for the site configuration.  Copy this
file to `site.cfg` and edit as needed for your local setup.

An Apache 2.4.x configuration template is called `pwg-apache.conf`.  You'll need
`mod_actions`, `mod_php`, and `mod_ssl` enabled on your server.

PHP support requires a couple tweaks to the default `php.ini` file, which are
listed in the `pwg-php.ini` file.

The SQL table definitions and sample data are in `pwg.sql`, `selfcert.sql`, and
`test.sql`.  The `loadtest.sh` script can be used to setup the proper database
for testing.
