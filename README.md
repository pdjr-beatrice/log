# log

This repository contains code implementing the automated ship's
log used on BEATRICE OF HULL.

## log-system

This folder contains a collection of scripts which create, maintain
and process a ship's log using data sourced in real-time from one or
more Signal K servers.

The system was designed to run on a Raspberry Pi and has modest resource
requirements, happily working alongside other services and applications.

### Installation example
```
$> cd /opt
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo ./install-logsystem
```

## log-display

Consists of a collection of `PHP` scripts which support the acquisition and rendering
of log system data by a WordPress installation using the Postie plugin.

### Installation example
```
$> cd <i>root_of_wordpress_installation</i>
$> sudo ./install-displaysystem
```


