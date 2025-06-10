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

An install from the repository includes the following specimen
configuration file ,`/usr/local/etc/log.cfg`, which serves only to
log the vessel position.

```none
[INIT]
Position, POSITION, /signalk/v1/api/vessels/self/navigation/position

[RUN]
Main engine, STATE, 1
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

You can test the operation of the system by:

```bash
$> log-install run
```

You should now find a new daily log file in the *data_directory*
specified during installation. This file should contain two POSITION
records (one output by processing the `[INIT]` paragraph and
another output by processing the `[RUN]` paragraph). If you `cat`
the file it should look something likethis:

```none
2025-06-08T20:21:39Z [2025-06-08T20:21:40.000Z] Position POSITION { "latitude": 51.688263, "longitude": 5.318658 }
2025-06-08T20:21:44Z [2025-06-08T20:21:42.000Z] Position POSITION { "latitude": 51.688263, "longitude": 5.318658 }
```

Each time `log-install run` is executed another (redundant) POSITION
record will be added to the log file.  We need to ensure that new
positions are only recorded when the host vessel is moving and
detecting this depends upon data available within the vessel's
particular Signal K system.

On my ship Signal K senses the main engine ignition state.

```none
[RUN]
Main engine, STATE, /signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

An alternative is to sense the vessel's sped over ground. The following
example will log position data when the vessel speed is greater than
3.6kph.

```none
[RUN]
Speed over ground, NONZERO, /signalk/v1/api/vessels/self/navigation/speedOverGround
>Position, POSITION, /signalk/v1/api/vessels/self/navigation/position
```

You will no doubt be able to dream up other possibilities

Once the configuration file has been chaged to include appropriate
movement sensing, further execution of `log-update run` should not
change the log file - unless, of course, the host vessel is moving.

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
