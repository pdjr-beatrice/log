# log

This repository contains code implementing an automated ship's log
and consists of two collections:

1. The `log` collection is a group of `bash` scripts which create,
   maintain and process an archive of ship's daily log files. These
   scripts should be installed on the host vessel, co-located on the
   LAN (or even the machine) hosting the ship's Signal K server.

2. The `wordpress` collection is a handful of `php` scripts which can
   be installed on a WordPress site to provide support for the display
   of log system data.
   BEATRICE's
   [website](https://www.pdjr.eu/)
   is an example.

## Basic installation

Installation of either collection is most easily accomplished by
logging in to the computer that will host the required collection and
cloning this repository.

```bash
$> git clone https://github.com/pdjr-beatrice/log.git
$> cd log
```

The `install` script takes an absolute directory name as its only
argument.

```bash
$> sudo ./install directory
```

If *directory* specifies a WordPress installation root directory then
the wordpress collection is installed.
For example:

```bash
$> sudo ./install /var/www/wordpress
```

Otherwise, *directory* is assumed to specify the folder which should
be used as the log system's log file archive: this folder will be
created if it doen't exist and the log collection scripts installed.
For example:

```bash
$> sudo ./install /var/log/shipslog
```

The value of *directory* is written into `log.defs` so that it becomes
available to all log system scripts.

Both log system and WordPress extension installs generate the script
`/usr/local/bin/log-uninstall` which can be used to uninstall a
previously installed collection.
