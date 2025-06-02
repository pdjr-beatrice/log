# log-atrest

Check whether a day's log show's that the vessel was at rest.

## SYNOPIS

```bash
$> log-atrest -h
$> log-atrest [-v] [yyyymmdd]
```

## DESCRIPTION

`log-atrest` examines the log file specified by *yyyymmdd* (or today's
log file if *yyyymmdd* is absent) and sets its exit value to 0 if
position information shows that the vessel did not navigate on the
selected day or to 1 if position information indicates that the vessel
moved.

The `-v` option writes the value 'yes' to standard output if the vessel
moved, otherwise the value 'no'.

The `-h` option write a usage message to standard output.
