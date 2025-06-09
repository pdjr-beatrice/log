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

```
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> log-install -s server_address data_directory
```

To install the `wordpress` collection on the WordPress host computer
login to the host and issue the following commands, replacing
*wordpress_root* with the root directory of your WordPress installation
(e.g. `/var/www/wordpress`).

```
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> log-install wordpress_root
```

Once installation completes, the script `/usr/local/bin/log-uninstall`
can be used to uninstall a previously installed collection.

## Scripts in the `log` collection

|:--- |:--- |
| `log.cfg`  | Daily log configuration file. |
| `log.defs` | System global configuration variables and functions. |
| `log-install` | System install script. |
| `log-uninstall` | System uninstall script. |
| `log-update`    | Update today's log file by processing `log.cfg`. |
