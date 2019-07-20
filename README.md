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

## Log files and log system configuration

A log file is a plain text file consisting of an arbitrary number of log entries or records.
Log files are rolled over at 00:00Z and all files have a name of the form _YYYYMMDD_ which represents the date to which their content applies.

Each line in a log file is made up of a time-stamped and labelled record which stores a single Signal K data value.
Fields in each record are space separated and the general format is "_log-timestamp_ [_signalk-timestamp_] _label-1_ _label-1.1_ _value_".
A snippet from one of _Beatrice_'s recent log files looks like this.
```
2019-07-13T22:00:01Z [2019-07-13T22:00:01.293Z] TANKLEVEL FuelPS .3811
2019-07-13T22:00:01Z [2019-07-13T22:00:01.678Z] TANKLEVEL FuelSB .4134
2019-07-13T22:00:01Z [2019-07-13T22:00:02.318Z] POSITION Position { "latitude": 52.4031, "longitude": 5.6222 }
2019-07-13T22:00:01Z [2019-07-13T22:00:02.062Z] ENGINE State 0
2019-07-13T22:00:01Z [2019-07-13T22:00:02.062Z] GENERATOR State 0
```
Exactly what data is written to a log and at what frequency is determined by a log configuration file.
The configuration file consists of a prefix, body and suffix blocks, separated from one another by a blank lineeach consisting of one or more data definition lines and each separ
## Wordpress configuration

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


