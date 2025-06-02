# log system

## Operation

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

```
[OPEN]
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[MINUTE]
ENGINE State http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/switches/bank/16/16/state
>POSITION Position http://192.168.1.1:3000/signalk/v1/api/vessels/self/navigation/position

[CLOSE]
BATTERYSTATE Domestic http://192.168.1.1:3000/signalk/v1/api/vessels/self/electrical/batteries/278/capacity/stateOfCharge
```

### Daily log file  