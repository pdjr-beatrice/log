# log - ship log system and integrated blog

This collection of scripts implements a system for maintaining, manipulating and publishing a simple ship's log using data derived from a
[Signal K](http://www.signalk.org) Node Server.
A reference implementation of the __log__ system executes on the vessel _Beatrice of Hull_ and log files are published daily by email to the ship's
[Wordpress blog](http://www.pdjr.eu/).

The log system core implementation consists of a single 'bash' script responsible for creating and updating daily log files and a number of other scripts designed to interrogate these files, render the contained data in a range of formats and distribute the rendered content via email.
These scripts can, in principle, execute on any machine which has real-time access to port 80 on the Signal K server(s)  supplying the raw log data.
_Beatrice_'s log system simply runs on the ship's Signal K server host with script execution automated by 'cron'.

At the end of each day, _Beatrice_ publishes the day's log to an unattended email account. The substantive content of the published material is a table of operating data and a KML attachment which represents the ship's passage over the preceeding 24 hours.
A cloud-based Wordpress blog installation uses the Wordpress _Postie_ plugin to retrieve the published email and a simple filter script written in PHP is used by _Postie_ to interpolate an _Open Sea Map_ rendering of the KML document into a new blog page.

# Wordpress configuration

The Wordpress installation which supports publishing of _Beatrice_'s blog relies on the
[Postie](https://wordpress.org/plugins/postie/)
plugin and, for rendering of vessel postion on a map, the
[OSM](https://wordpress.org/plugins/osm/)
plugin.  

Wordpress must be configured to allow all types of file upload by setting `define('ALLOW_UNFILTERED_UPLOADS', true);` in the installation's `wp-config.php` file. Additionally, a user with administrator priveleges must be identified as the Postie user.

Postie must be configured to process messages in the email the account to which the log system publishes and to post the received email as the identified Wordpress user.  The Postie option which requires user login must be set to 'yes' and that which defines the format of received email set to 'text'.

At this stage, log system messages will appear as blog posts which display operational data and include a link to the KML attachment describing vessel movement.

To render the KML attachment as a map, the log system __postie-kml-plugin.php__ must be copied into the `wp-content/mu-plugins/` folder.  OSM requires no special configuration.

The __postie-kml-plugin.php__ script works by replacing the KML attachment link in an email generated blog post with a Wordpress short-code which triggers the OSM plugin.

# Log system configuration


