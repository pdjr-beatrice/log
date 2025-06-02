# NAME

log-atrest - check whether a log show's the vessel was at rest.

## SYNOPIS

```bash
$> log-atrest -h
$> log-atrest [-v] [yyyymmdd]
```

## DESCRIPTION

`log-atrest` examines the log file specified by *yyyymmdd* (or the most
recent log file in the log archive if not specified) and sets its exit
value to 0 if position information in the log shows the vessel did not
navigate on the selected day or to 1 if position information indicates
that the vessel moved.

The `-v` option writes the value 'yes' to standard output if the vessel
moved, otherwise the value 'no'.

The `-h` option write a usage message to standard output.
