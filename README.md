# log

This repository contains code implementing the automated ship's log
used on BEATRICE OF HULL.

The log system itself consists of a collection of scripts which create,
maintain and process a ship's log using data sourced in real-time from
one or more Signal K servers.

The system was designed to run on a Raspberry Pi and has modest
resource requirements allowing it to work alongside other services
and applications.

## To install
```
$> cd
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> ./install log_directory
```
Where *log_directory* specifies a folder where the log system can store
the files it generates (if the *log_directory* doesn't exist it will be
created).

By default the `install` command copies log system scripts into folders
under `/usr/local/`.
Once installation is complete the cloned repository can be deleted.

As an alternative, system scripts can be symbolically linked from a
cloned repository into the host filesystem (maybe facilitating easier
system development) using the command `./install -s log_directory`.
If you take this approach. do not remove the cloned repository! 

After installation, you will need to edit `/usr/local/bin/log.defs`
and `/usr/local/etc/log.cfg` to reflect your usage requirements.

## To uninstall
```
$> sudo log-uninstall
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


