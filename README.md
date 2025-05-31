# log

This repository contains code implementing the automated ship's
log used on BEATRICE OF HULL.

## log-system

This folder contains a collection of scripts which create, maintain
and process a ship's log Log data is sourced in real-time from one or
more Signal K servers using the Signal K REST API.

The system was designed to run on a Raspberry Pi and has modest resource
requirements, happily working alongside other services and applications.

alongside other which implements the vessel's
LTE gateway (and is available 24/7), reaching out to the ship's Signal K server
which executes on a Victron Cerbo-GX. A typical log system generates around 10Mb
ofvlog files per annum.

### Installation example
```
$> cd /opt
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo ./install-logsystem
```

## log-display

Consists of a collection of `PHP` scripts which support the acquisition and rendering
of log system by WordPress.

The log system on BEATRICE emails its daily log to an Internet based WordPress
installation which renders the log data in a meaningful way.




In addition to the log system `bash` scripts, a handful of `php` scripts
are provided that can be used on the WordPress rendering system.


