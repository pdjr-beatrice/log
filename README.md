# log

This repository contains code implementing the automated ship's log
used on BEATRICE OF HULL.

## log/

This folder contains a collection of scripts which create, maintain
and process a ship's log using data sourced in real-time from one or
more Signal K servers.

The system was designed to run on a Raspberry Pi and has modest
resource requirements allowing it to work alongside other services
and applications.

### Installation example
```
$> cd /opt
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo make install
```

## wordpress/

This folder contains a collection of scripts which support the
acquisition and rendering of log system data by a WordPress
installation using the Postie plugin.

### Installation example
```
$> cd /opt
$> sudo make install-wordpress
```

## Makefile


