# log system

The log system creates, maintains and processes a collection of daily
log files in the directory specified by *LOGDIR*.
Each day a new file with the name *YYYYMMDD* is created at 00:00Z,
updated throughout the day, and closed at 23:59:45Z.
Typically, this work is undertaken by the `/usr/local/bin/log-update`.

Exactly what data `log-update` places in the daily log is specified
by rules grouped in named paragraphs in the configuration file
`/usr/local/etc/log.cfg`.
Each time `log-update` is executed, the name of the rule paragraph 
to be processed must be specified by an argument.
The simplest way of generating a log is to configure the host system
`/etc/crontab/` so that `log-update` is executed at appropriate
time intervals.

The following example `log.cfg` consists of three paragraphs.

The *INIT* paragraph is automatically executed before any user defined
paragraphs when a new daily log file is created.
This will result in two entries at the start of every daily log
recording the state of charge of the domestic battery bank and the
position of the vessel.

*RUN* and *CLOSE* are user-defined names for paragraphs which will only
be executed when their names are passed as arguments to `log-update`.

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
```
[INIT]
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[RUN]
ENGINE State http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[CLOSE]
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
```

A typical approach to log file generation is to use the host system's
cron mechanism to automatically execute `log-update`.
The following entries in `/etc/crontab` are suitable for the log
configuration discussed above.
```
*/1 *    * * *   root    /usr/local/bin/log-update run >/dev/null 2>/dev/null
59  23   * * *   root    /usr/local/bin/log-update close  >/dev/null 2>/dev/null
```


