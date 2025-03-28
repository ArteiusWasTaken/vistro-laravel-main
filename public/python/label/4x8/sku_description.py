import sys
import os
import zpl
import uuid
import json

sku = str(sys.argv[1])
desc = str(sys.argv[2])
printer = str(sys.argv[3])
qty = str(sys.argv[4])
extra = str(sys.argv[5])

sku_length = len(str(sku))
barcode_width_length = 0.7 if sku_length <= 14 else 0.5
barcode_start_cords_x = 15 if sku_length <= 14 else 25

l = zpl.Label(203.2, 101.6)

l.origin(40, 0)
l.write_text(desc[:50], char_height=8, char_width=4.8, line_width=140, orientation='B', justification="C")
l.endorigin()

l.origin(48, 0)
l.write_text(desc[50:100], char_height=8, char_width=4.8, line_width=140, orientation='B', justification="C")
l.endorigin()

if extra != "":
    l.origin(56, 0)
    l.write_text(extra[:50], char_height=8, char_width=4.8, line_width=160, orientation='B', justification="C")
    l.endorigin()

l.origin(barcode_start_cords_x - (sku_length / 2), 8)
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=8, height=3.6)
l.write_barcode(height=200, barcode_type='C', check_digit='N', orientation='B')
l.write_text(sku)
l.endorigin()

zpl = l.dumpZPL()[:-3]

zpl += "^PQ" + qty + ",0,1,Y^XZ"

file_name = str(uuid.uuid1()) + ".txt";

f = open(file_name, "w+")
f.write(zpl);
f.close()
 
os.system("lp -d " + printer + " -o raw " + file_name + "")

os.remove(file_name)
