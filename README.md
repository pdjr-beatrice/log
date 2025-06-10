# log

The `log` project consists of a collection of scripts which create,
update, process and render data describing the operational state of the
host vessel.

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
context of a WordPress site and for reasons of accessibility it is
assumed that these scripts will execute on some Internet host.

## Installation

Installation of either the `log` or `wordpress` collection is easily
accomplished from a clone of this repository.

To install the `log` collection on the log system host computer login
to the host and issue the following commands, replacing
*server_address* with the protocol, address and port number of your
local data source HTTP API (e.g. `http://192.168.1.1:3000`) and
replacing *data_directory* with the path of a folder where log files
should be stored (e.g. `/var/log/shipslog`).

```bash
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> log-install -s server_address data_directory
```

To install the `wordpress` collection on the WordPress host computer
login to the host and issue the following commands, replacing
*wordpress_root* with the root directory of your WordPress installation
(e.g. `/var/www/wordpress`).

```bash
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> log-install wordpress_root
```

Once installation completes, the script `/usr/local/bin/log-uninstall`
can be used to uninstall a previously installed collection.

## Finalising the installation

An install from the repository includes this simple configuration file
`/usr/local/etc/log.cfg`:

```none
[INIT]
Position, POSITION, /signalk/v1/api/vessels/self/navigation/position

[RUN]
Main engine, STATE, 1
Main engine, STATE, /signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

which serves only to log the vessel position:  the `[INIT]` paragraph
write a position entry to the daily log file when it is first created;
the `[RUN]` paragraph aims to log a position only if the host vessel is
navigating and assumes that the main engine state is a good indicator
of this (there are other indicators that might be used). Since I don't
know what data is available from Signal K on your vessel, the sample
configuration file fakes the main engine as permanently on and you will
want to change this to avoid filling your log files with redundant
POSITION records. Here are two suggestions.

Suggestion 1: sense the main engine ignition state. This is how I do it
on my ship:

```none
[RUN]
Main engine, STATE, /signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

Suggestion 2: sense vessel movement using speed over ground. The following
example will log position data when the vessel speed is greater than
3.6kph.

```none
[RUN]
Speed over ground, NONZERO, /signalk/v1/api/vessels/self/navigation/speedOverGround
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

You will no doubt be able to dream up other possibilities

## Basic testing

To test the system is working, execute the command:

```bash
$> log-update run
```

You should now find a new daily log file in the *data_directory*
specified during installation. This file should contain a single
POSITION record (output by processing the `[INIT]` paragraph) which
looks something like this:

```none
2025-06-08T20:21:39Z [2025-06-08T20:21:40.000Z] Position POSITION { "latitude": 51.688263, "longitude": 5.318658 }
```

Repeated execution of `log-update run` will only add further entries to
the log if the STATE (or NONZERO) test in the `[RUN]` paragraph succeeds.
In this way, repeated updates will only extend the log (and so record a
track) if the vessel is being navigated.

## Finalising installation

If the basic testing described above succeeds, then the execution of
`log-update run` needs to be automated by adding an appropriate line to
the host sytem's `/etc/crontab`.
The frequency of repetition of the command depends mostly on the
granularity you require on any recorded track; in my case, I choose to
run the command once a minute by adding the following line to
`/etc/crontab`.

```crontab
*/1 *   * * *   root    /usr/local/bin/log-update run >/dev/null 2>/dev/null
```

## Where next?

Check out
[CONFIGURATION.md](CONFIGURATION.md)
for information on how to extend what is logged and suggestions on how
to review and publish logged data.
