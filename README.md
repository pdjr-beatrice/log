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

### Installation

1. Clone this repository into a folder on the machine which will host
   the log system. The installer works by creating symbolic links for
   all scripts, so the folder into which you clone the repository needs
   to be persistent (I usually clone into `/opt/`).
   ```
   $> cd /opt
   $> sudo git clone https://github.com/pdjr-beatrice/log.git
   ```
2. Use the `Makefile` to create symbolic links into folders under
   `/usr/local/`/
   ```
   $> cd log
   $> sudo make install
   ```
3. Create a folder for your log system data.
   ```
   $> sudo mkdir /var/log/beatrice
   ```
4. Edit `/usr/local/bin/log.defs` and `/usr/local/etc/log.cfg` to reflect
   your usage requirements.

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


