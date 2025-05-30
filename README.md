# log

This repository contains code implementing the automated ship's
log used on BEATRICE OF HULL.

The log system consist of a collection of (mostly) `bash` scripts which
run on the host vessel and pull data from a Signal K server, squirreling
it away as a collection of text files each of which constitutes the host
vessel's daily log.

There is a good deal of flexibility in how the log system can be configured
and deployed. On BEATRICE, the log system runs on a Raspberry Pi which
implements the vessel's LTE gateway. A Signal K server is available on the
ship's LAN (running on a Victron Cerbo-GX).

At the end of each day, a `cron` job emails the day's log and a derived
KML plot of the vessel's movement to an Internet host running WordPress
which renders the log data.

In addition to the log system `bash` scripts, a handful of `php` scripts
are provided that can be used on the WordPress rendering system.


