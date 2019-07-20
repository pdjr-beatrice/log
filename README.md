# log - ship log system and integrated blog

This collection of scripts implements a system for maintaining, manipulating and publishing a simple ship's log using data derived from a
[Signal K](http://www.signalk.org) Node Server.
A reference implementation of the log system executes on the vessel _Beatrice of Hull_ and log files are published daily by email to the ship's
[Wordpress blog](http://www.pdjr.eu/).

The log system core implementation consists of a single bash(1) script responsible for creating and updating daily log files and a number of other scripts designed to interrogate these files, render the contained data in a range of formats and distribute the rendered content via email.
These scripts can, in principle, execute on any machine which has real-time access to port 80 on the Signal K server(s) which supply the raw log data.
_Beatrice_'s log system simply runs on the ship's Signal K server host.

At the end of each day, _Beatrice_ publishes the day's log to an unattended email account. The substantive content of the published material is a table of operating data and a KML attachment which represents the ship's passage over the preceeding 24 hours.
A cloud based Wordpress blog installation uses the Wordpress Postie plugin to retrieve the published email and a simple filter script written in PHP is used by Postie to interpolate an Open Sea Map rendering of the KML document into a new blog page.

# Wordpress configuration

The Wordpress installation which supports publishing of _Beatrice_'s blog relies on the
[Postie](https://wordpress.org/plugins/postie/)
plugin and, optionally, on the
[OSM](https://wordpress.org/plugins/osm/)
plugin.  


Wordpress must be configured to allow all types of file upload by setting `define('ALLOW_UNFILTERED_UPLOADS', true);` in the installation's `wp-config.php` file. Additionally, a user with administrator priveleges must be identified as the Postie user.

Postie must be configured to process messages in the email the account to which the log system publishes and to post the received email as the identified Wordpress user.  The Postie option which requires user login must be set to 'yes' and that which defines the received email format must be set to 'text'.

OSM requires no special configuration.

If no further action is taken 
