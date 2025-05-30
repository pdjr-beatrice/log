# log

This repository contains code implementing the automated ship's
log used on BEATRICE OF HULL.

The log system consist of a collection of (mostly) `bash` scripts which
pull data from a Signal K server and squirrel it away as a collection
of text files each of which constitutes the host vessel's daily log.

There is a good deal of flexibility in how the log system can be deployed.
On BEATRICE, the 

Typically the log system will be implemented
on a Raspberry Pi, but there is no requirement for exclusivity which acts as a data source. The on the vessel whose activity is being
logged and a WordPress site somewhere which is responsible for
displaying log data. 


