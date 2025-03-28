import sys
import os
import zpl
import uuid
import json
import codecs
import textwrap

sys.stdout = codecs.getwriter("utf-8")(sys.stdout.buffer, errors="replace")

sku = str(sys.argv[1])
desc = str(sys.argv[2])
qty = str(sys.argv[3])
extra = str(sys.argv[4])

sku_length = len(sku)
barcode_width_length = 0.2 if sku_length <= 14 else 0.1

barcode_shift = 5

barcode_start_cords_x = (4 if sku_length <= 14 else 15) + barcode_shift

l = zpl.Label(50.8, 25.4)

char_height = 2
char_width = 1.2
line_width = 35

wrapped_desc = textwrap.wrap(desc, width=30)

start_y = 10
for i, line in enumerate(wrapped_desc[:3]):
    l.origin(2, start_y + (i * 3))
    l.write_text(line, char_height=char_height, char_width=char_width, line_width=line_width)
    l.endorigin()

if extra:
    l.origin(2, start_y + 9)
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

l.origin(barcode_start_cords_x - (sku_length / 2), 2)
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=2, height=0.9)
l.barcode(height=50, barcode_type='C', code=sku, check_digit='N')  # Cambio aquí: Se agregó 'code'
l.write_text(sku)
l.endorigin()

zpl_output = l.dumpZPL()[:-3]
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_output)
