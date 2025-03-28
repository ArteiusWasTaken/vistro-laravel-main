import sys
import os
import zpl
import uuid
import json
import textwrap

desc = str(sys.argv[1])
qty = str(sys.argv[2])

l = zpl.Label(50.8, 25.4)

wrapped_desc = textwrap.wrap(desc, width=35)

start_y = 5
for i, line in enumerate(wrapped_desc[:2]):
    l.origin(2, start_y + (i * 5))
    l.write_text(line, char_height=3, char_width=3, line_width=35)
    l.endorigin()

zpl = l.dumpZPL()[:-3]
zpl += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl)
