import sys
import os
import zpl
import uuid
import textwrap

sku = str(sys.argv[1])
desc = str(sys.argv[2])
serie = str(sys.argv[3])
qty = str(sys.argv[4])
extra = str(sys.argv[5])

sku_length = len(sku)
serie_length = len(serie)

barcode_width_length = 0.2 if sku_length <= 14 else 0.1
barcode_start_cords_x = 8.5 if sku_length <= 14 else 15

serie_width_length = 0.2 if serie_length <= 14 else 0.1
serie_start_cords_x = 8.5 if serie_length <= 14 else 15

l = zpl.Label(50.8, 25.4)

wrapped_desc = textwrap.wrap(desc, width=30)

start_y = 2
for i, line in enumerate(wrapped_desc[:3]):
    l.origin(2, start_y + (i * 3))
    l.write_text(line, char_height=1.5, char_width=1, line_width=30, justification='C')
    l.endorigin()

l.origin(barcode_start_cords_x - (sku_length / 2), 7)
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=2, height=0.9)
l.barcode(height=25, barcode_type='C', code=sku, check_digit='N')  # Se agregó 'code'
l.write_text(sku)
l.endorigin()

l.origin(serie_start_cords_x - (sku_length / 2), 12)
l.barcode_field_default(module_width=serie_width_length, bar_width_ratio=2, height=0.9)
l.barcode(height=25, barcode_type='C', code=serie, check_digit='N')  # Se agregó 'code'
l.write_text(serie)
l.endorigin()

if extra != "":
    l.origin(2, 14)
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

zpl_code = l.dumpZPL()[:-3]
zpl_code += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_code)

