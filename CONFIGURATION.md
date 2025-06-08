# log

The `log` project consists of a collection of scripts which create,
update, process and render data describing the operational state of a
the host vessel.

The script collection is broken into two parts: a `log` sub-collection
concerned with generating daily log files and a `wordpress`
sub-collection concerned with rendering log file content in an
accessible way.

The `log` sub-collection consumes data from the host vessel's Signal K
server using HTTP APIs and for reasons of availabilty it is assumed
that these scripts will execute on the host vessel, perhaps co-located
with the Signal K server itself.

The `woprdpress` sub-collection consumes daily log files received by
email from the host vessel and displays this information within the
context of a WordPress site and it is assumed that these scripts will
execute on some Internet host.

## Installation

Install the `log` collection on a host computer on the vessel by
executing the `log-install` script.
```
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> log-install -s 192.168.1.1 /var/log/ships-log
```






## log-update

The `log-update` program creates and updates a collection of daily log
files in a configurable *log directory*.
Each day a new file with the name *YYYYMMDD* is created at 00:00Z, is
updated throughout the day, and is closed at 23:59:45Z.

Exactly what data `log-update` places in the daily log is specified
by rules grouped in named paragraphs in the configuration file
`/usr/local/etc/log.cfg`.
Each time `log-update` is executed, the name of the rule paragraph or
paragraphs that should be processed must be specified.
The simplest way of generating a log is to configure the host system
`/etc/crontab/` so that `log-update` is executed at appropriate time
intervals.

The following example `log.cfg` consists of three paragraphs.

The *INIT* paragraph is automatically executed before any user defined
paragraphs when a new daily log file is created.
This will result in two entries at the start of every daily log
recording the state of charge of the domestic battery bank and the
position of the vessel.

The *RUN* and *CLOSE* paragraphs group some rules which can be executed
by `log-update`.

The *RUN* paragraph logs the current engine state and if this is 1 (on)
then logs the position of the vessel.
The pragraph will only be executed when it is explicitly named as an
argument to `log-update`; repeatedly executing the command
`log-update run` will record the vessel's track when the engine is
running.
As a general point, note that `log-update` only writes an entry to the
log file when the entry differs from the most recently recorded value,
so the log will not fill with repeated entries noting engine on.

The *CLOSE* paragraph logs the domestic battery state and will only
be executed when it is explicitly named as an argument to `log-update`.

```none
[INIT]
PERCENT Battery http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[RUN]
STATE Engine http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[CLOSE]
PERCENT Battery http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
```

A typical approach to log file generation is to use the host system's
cron mechanism to automatically execute `log-update`.
The following entries in `/etc/crontab` are suitable for the log
configuration discussed above.

```cron
*/1 *    * * *   root    /usr/local/bin/log-update run >/dev/null 2>/dev/null
58  23   * * *   root    /usr/local/bin/log-update close  >/dev/null 2>/dev/null
```

The first `crontab` entry executes the *RUN* paragraph every minute
through the day.

The second `crontab` entry executes the *CLOSE* paragraph at one minute
before midnight to generate closing entries in the ship's log.

On BEATRICE, at the end of each day I use the `log-email` script to post
the day's log and a derived KML file rendering the vessel's track to my
remote WordPress site for display.
This is easily accomplished with an additional `crontab` entry.

```cron
59  23   * * *   root    /usr/local/bin/log-email  >/dev/null 2>/dev/null
```

## log.cfg

The `log.cfg` file consists of one or more named paragraphs each of
which contains a sequence of rules.

Each rule has the general format:

```none
[modifier]type label url
```

Where:

*modifier* is an optional character ('!' or '>') which modifieds how
the rule is applied (see discussion below).

*type* is a label which specifies how the data returned by *url* should
be processed before being saved to the log file (see discussion below).

*label* is a identifier for this entry in the log.

*url* is a Signal K API URL which returns a data value from Signal K
(or somewhere).

Here is an example of a rule.

```none
STATE Engine http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
```

*type* understands the following values:

| Type name | Input value | Output value |
|---        |---          |---           |
| RATIO     | FP in the range 0..1. | Rounded to four decimal places. |
| PERCENT   | FP in the range 0..1. | Multiplied by 10 and rounded to 0 decimal places. |
| POSITION  | { lat: lat, lon: lon } | lat and lon rounded to four decimal places. |
