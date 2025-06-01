# log

This repository contains code implementing the automated ship's log
used on BEATRICE OF HULL.

## Log system

The log system itself consists of a collection of `bash` scripts which
create, maintain and process a ship's log using data sourced in
real-time from one or more Signal K servers.

The production system should be run on a device on the host vessel,
co-located on the LAN (or even the machine) hosting the ship's Signal K
server.
The system is designed to run on a Raspberry Pi and has modest resource
requirements allowing it to work alongside other services and
applications.

### Basic installation
Login to the computer that will host the log system.
```
$> cd
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo ./install log_directory
```
Where *log_directory* specifies a folder where the log system can store
the files it generates (if the *log_directory* doesn't exist it will be
created).

The `install` command copies log system scripts into folders under
`/usr/local/`.
Once installation is complete the cloned repository can be deleted.

### Development installation
As an alternative, system scripts can be symbolically linked from a
cloned repository into the host filesystem (maybe facilitating easier
system development).

Login to the computer that will host the log system.
```
$> cd /opt
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo ./install log_directory
```
If you take this approach. do not remove the cloned repository! 

### System configuration
After installation, you will need to edit `/usr/local/bin/log.defs`
and `/usr/local/etc/log.cfg` to reflect your usage requirements.

### Uninstalling the log system
```
$> sudo log-uninstall
```
All installed script files will be deleted (or unlinked).  

## Wordpress support

A small collection of scripts support the acquisition and rendering of
log system data by a WordPress installation using the Postie plugin.

### To install
Login to the computer that hosts the WordPress site that will be used
to display the ship's log.
```
$> cd
$> sudo git clone https://github.com/pdjr-beatrice/log.git
$> cd log
$> sudo install wordpress_folder
```
Where *wordpress_folder* is the root directory of the WordPress site
(the directory containing `wp-config.php`).

### To uninstall
```
$> sudo log-uninstall
```
All installed script files will be deleted (or unlinked).  


