# log - a simple log system

__log__ provides a collection of scripts which can be used to build and
interrogate a repository of daily log files which track data recovered
from arbitrary remote sources via HTTP GET requests.
The system was developed to maintain and publish a ship's log using data
available from the vessel's
[Signal K](http://www.signalk.org) Node Server.

The log system repository is simply a file system directory containing a
collection of text files (each of which we refer to as a *log file*).
Each *log file* represents a single day in the log where a day is defined
by the current local timezone.
A log file is identified by a name of the form *YYYYMMDD*.

Each *log file* contains zero or more time-sequenced records each of which
represents a time-stamped data value recovered from one or more data servers.

The log system uses a single bash(1) script to create and update the
repository.
A number of other, mostly bash(1), scripts provide mechanisms for retrieving
data from the repository, transforming it, and distributing it.

## Reference implementation

A reference implementation of the __log__ system executes on the vessel
_Beatrice of Hull_ and automatically maintains automatically publishes log files by email to the
ship's [blog](http://www.pdjr.eu/beatrice/).

The reference implementation runs on the ship's Signal K server host with
script execution automated by the system scheduler which periodically
executes two scripts.
The log-update script is executed every minute during the day and
assembles data into the current day's log file, automatically rolling over
log files at midnight.
At the end of each day, the log-email script is executed to generate an
email from the day's log which summarises operating data and includes a
KML attachment representing the ship's passage over the preceeding 24
hours. The email is posted to a dedicated email account on a public
domain mail server (current just a normal GMail account).

A cloud-based
[Wordpress](https://wordpress.org/)
installation periodically recovers emails from the dedicated account and
publishes them as a new blog post.
__log__ includes a simple filter script which can be used by Wordpress to
interpolate an
[Open Sea Map](https://www.openseamap.org/)
rendering of the KML attachment into the published blog page.

## Log files and log system configuration

A log file is a plain text file consisting of an arbitrary number of
log entries.
Each log file contains entries relating to a single day as defined by
the local time zone and log files have a name of the form _YYYYMMDD_.

Each entry in a log file is just a time-stamped, labelled, Signal K
data value. 
A snippet from one of _Beatrice_'s recent log files looks like this:

```
2019-07-13T22:00:01Z [2019-07-13T22:00:01.293Z] TANKLEVEL FuelPS .3811
2019-07-13T22:00:01Z [2019-07-13T22:00:01.678Z] TANKLEVEL FuelSB .4134
2019-07-13T22:00:01Z [2019-07-13T22:00:01.778Z] BATTERYSTATE Domestic .9223
2019-07-13T22:00:01Z [2019-07-13T22:00:02.318Z] POSITION Position { "latitude": 52.4031, "longitude": 5.6222 }
2019-07-13T22:00:01Z [2019-07-13T22:00:02.062Z] ENGINE State 0
2019-07-13T22:00:01Z [2019-07-13T22:00:02.062Z] GENERATOR State 0
```

Each log entry has the general format.

_timestamp_ __[__*signalk-timestamp*__]__ _label-1_ _label-1.1_ _value_

Where _timestamp_ is the time the log entry was made;
_signalk-timestamp_ is time Signal K associates with _value_; _label-1_
and _label-1.1_ are identifying labels for _value_ which is the
substantive Signal K data point.

Exactly what data is written to a log file is determined by a log
configuration file which consists of a collection of _enquiries_
organised into named _paragraphs_.
_Beatrice_'s log configuration file looks like this.

```
[INIT]
TANKLEVEL Wastewater http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/wasteWater/0/currentLevel
TANKLEVEL FreshwaterPS http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/freshWater/1/currentLevel
TANKLEVEL FreshwaterSB http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/freshWater/2/currentLevel
TANKLEVEL FuelPS http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/fuel/3/currentLevel
TANKLEVEL FuelSB http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/fuel/4/currentLevel
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/258/capacity/stateOfCharge
POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[RUN]
ENGINE State http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/16/16/state
>POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position
GENERATOR State http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/16/14/state

[ONCLOSE]
TANKLEVEL Wastewater http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/wasteWater/0/currentLevel
TANKLEVEL FreshwaterPS http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/freshWater/1/currentLevel
TANKLEVEL FreshwaterSB http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/freshWater/2/currentLevel
TANKLEVEL FuelPS http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/fuel/3/currentLevel
TANKLEVEL FuelSB http://192.168.1.1:3000/signalk/v1/api/vessels/self/tanks/fuel/4/currentLevel
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/258/capacity/stateOfCharge
```

Each enquiry in the log configuration file has the general format:

\[__>__|__!__\]_label-1_ _label-1.1_ _url_

where _label-1_ and _label-1.1_ serve both a documentary and identification
role and _url_ gives the path to the data value that should be stored in the log.
Whilst all the data I use derives from my Signal K server, any data source that
can supply JSON data in response to an HTTP GET request on *url* can be used.

The '>' character at the beginning of an enquiry identifies it as conditional
and it will only be processed if processing of the immediately preceeding
non-conditional enquiry obtained a value of 1 from the Signal K server.
Thus, in the configuration presented above, if executing the "ENGINE State"
enquiry returns the value "1" (saying engine running), then the ">POSITION
Position" enquiry will be processed, otherwise it will be ignored, ensuring that
real-time position data is only logged if the vessel is moving (well, strictly
if its main engine is running).

The '!' character at the beginning of an enquiry identifies it as non-recording
meaning that it will be processed normally but the result will not be saved to
the log.
This behaviour can be used in combination with subsequent conditional enquiries
to perform an invisible test. 

## Log system scripts

All log system scripts take a __-h__ option which displays the script's
comprehensive manual page: the following descriptions do not address all
available options.

## Using `log-update` to maintain the log

The `log-update` script is exclusively responsible for updating daily log files
by executing the enquiries identified in the log configuration file and writing
recovered data into the current daily log.

Each day on its first execution after midnight, the script will execute the
queries defined in the log configuration file's INIT paragraph and so create
a new log file for the upcoming day.

In normal use, `log-update` takes one or more paragraph names as its
argument(s) and processes the selected enquiries into log entries.

It usually makes sense to schedule automatic execution of the update script:
indeed, if the log system is being used to track vessel movements, then scheduling
is pretty-much mandatory and the frequency of script execution will determine
the resolution of the logged track.

The `log-update` script will only write values returned from the Signal K server
to the log file if they differ from the most recently logged value of the same
type.
In this way, log files do not fill with redundant data.

On _Beatrice_ `log-update` is executed in response to the following `crontab`
entries:

```
*/1 * * * * root /usr/local/bin/log-update run  >/dev/null 2>/dev/null
59 23 * * * root /usr/local/bin/log-update -f onclose  >/dev/null 2>/dev/null
```

## Extracting and processing log file data

All of the log file extraction and processing scripts take a _file-selector_
argument which is a full or partial log file name of the form _YYYYMMDD_,
_YYYYMM_ or _YYYY_, selecting a daily log file, all log files for a month or all
log files for a year respectively.

### log-get - return arbitrary values from the selected log

__log-get__ is just a wrapper for grep(1) which appropriately expands log system
_file-selector_ before applying _regex_.

```
log-get "BATTERYSTATE Domestic" 2019
```

The script returns the selected list of values.

### log-positions - get the positions through which the vessel passed

Returns a list of position values from the selected log file(s).
The generated output is a list of JSON records with latitude, longitude and date
fields.

### log-stops - get the start, stop and halt positions

Returns a list of stop and (optionally) halt positions from the selected log
file(s).
The generated output is a list in the same format as that produced by
`log-positions`.

### log-trip - get the distance travelled

Uses the Haversine formula to compute the distance between the positions
returned by `log-positions` and returns the sum of the computed values.

### log-runtime - get the total runtime of some device

Returns the total runtime _HH_:_MM_ for some device by filtering the selected
log files using a supplied token and then summing the intervals between State 1
and State 0 log entries. 

### log-report - populate a report template with log data values

### log-kml - returns a KML document representing position and stop data

### log-email - emails the output of `log-tabulate` and `log-kml` to one or more recipients

## Wordpress configuration

The Wordpress installation which supports publishing of _Beatrice_'s blog relies
on the
[Postie](https://wordpress.org/plugins/postie/)
plugin and, for rendering of vessel postion on a map, the
[OSM](https://wordpress.org/plugins/osm/)
plugin.  

Wordpress must be configured to allow all types of file upload by setting
`define('ALLOW_UNFILTERED_UPLOADS', true);` in the installation's
`wp-config.php` file.
Additionally, a user with administrator priveleges must be identified as the
Postie user.

Postie must be configured to process messages in the email the account to which
the log system publishes and to post the received email as the identified
Wordpress user.
The Postie option which requires user login must be set to 'yes' and that which
defines the format of received email set to 'text'.

At this stage, log system messages will appear as blog posts which display
operational data and include a link to the KML attachment describing vessel
movement.

To render the KML attachment as a map, __postie-kml-plugin.php__ must be copied
into the `wp-content/mu-plugins/` folder.
OSM requires no special configuration.

The __postie-kml-plugin.php__ script works by replacing the KML attachment link
in an email generated blog post with a Wordpress short-code which triggers the
OSM plugin.

