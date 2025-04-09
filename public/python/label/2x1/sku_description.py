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

# Tamaño en mm de una etiqueta 2x1 pulgadas
l = zpl.Label(50.8, 25.4)  # 203 DPI = 1 pulgada = 25.4 mm

label_width_pts = 812  # 203 dpi * 4" = 812 dots para 2 pulgadas
label_height_pts = 203  # 203 dpi * 1" = 203 dots

# Calcula ancho del texto centrado
def center_text_x(text_length_chars, char_width_pts):
    text_pixel_width = text_length_chars * char_width_pts * 8
    return int((label_width_pts - text_pixel_width) / 2 / 8)

# Texto
char_height = 3
char_width = 2
line_width = 40

wrapped_desc = textwrap.wrap(desc, width=30)
start_y = 80
for i, line in enumerate(wrapped_desc[:2]):
    x = center_text_x(len(line), char_width)
    y = start_y + (i * 30)
    l.origin(x, y)
    l.write_text(line, char_height=char_height, char_width=char_width, line_width=line_width)
    l.endorigin()

# Texto adicional
if extra:
    x = center_text_x(len(extra), char_width)
    l.origin(x, start_y + 70)
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

# Código de barras centrado
barcode_width = 300
barcode_height = 70
barcode_x = int((label_width_pts - barcode_width) / 8 / 2)
l.origin(barcode_x, 10)
l.barcode_field_default(module_width=0.25, bar_width_ratio=2, height=1.0)
l.barcode(height=barcode_height, barcode_type='C', code=sku, check_digit='N')
l.write_text(sku)
l.endorigin()

# Final ZPL
zpl_output = l.dumpZPL()[:-3]
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_output)
