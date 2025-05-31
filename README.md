# log

This repository contains code implementing the automated ship's
log used on BEATRICE OF HULL.

## log-system

Consists of a collection of (mostly) `bash` scripts which maintain,
transform and output a ship's log, sourcing data in real-time from one
or more Signal K servers using a the Signal K REST API.

Typically the log system will run on the host vessel, but there is a good
deal of flexibility in how the log system can be configured and deployed.

On BEATRICE, the log system runs on a Raspberry Pi which implements the
vessel's LTE gateway, reaching out to the ship's Signal K server which
executes on a Victron Cerbo-GX. At the end of each day, the day log is
emailed to an Internet host running WordPress which renders the log data.

### Installation example
```
$> cd /opt
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo ./install-logsystem
```




In addition to the log system `bash` scripts, a handful of `php` scripts
are provided that can be used on the WordPress rendering system.


