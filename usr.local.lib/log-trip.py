from haversine import haversine
import sys
import json

a = None
b = None
d = 0

for line in sys.stdin:
    if (a is None):
        a = json.loads(line)
    else:
        b = json.loads(line)

    if (a and b):
        p1 = (a['latitude'], a['longitude'])
        p2 = (b['latitude'], b['longitude'])
        d += haversine(p1,p2)
        a = b

print(round(d,1))
