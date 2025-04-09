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

# Para centrar el código de barras, calculamos el centro de la etiqueta
label_width = 50.8  # Ancho de la etiqueta en mm
barcode_start_cords_x = (label_width - (sku_length * 0.2)) / 2  # Centrar el código de barras

l = zpl.Label(label_width, 25.4)

# Tamaño del texto
char_height = 2
char_width = 1.2
line_width = 35

# Envolvemos la descripción para que no se salga de la etiqueta
wrapped_desc = textwrap.wrap(desc, width=30)

# Posición inicial de la descripción (debajo del código de barras)
start_y = 30  # Empezamos más abajo para dejar espacio para el código de barras

# Escribimos la descripción
for i, line in enumerate(wrapped_desc[:3]):
    l.origin(2, start_y + (i * 3))  # Colocamos el texto empezando desde `start_y`
    l.write_text(line, char_height=char_height, char_width=char_width, line_width=line_width)
    l.endorigin()

# Si hay texto extra, lo agregamos abajo de la descripción
if extra:
    l.origin(2, start_y + (len(wrapped_desc) * 3) + 3)  # Ajustamos la posición de `extra` debajo de la descripción
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

# Código de barras en el centro de la etiqueta
l.origin(barcode_start_cords_x, 2)  # Centrado horizontalmente
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=2, height=0.9)
l.barcode(height=50, barcode_type='C', code=sku, check_digit='N')
l.write_text(sku)
l.endorigin()

# Generar la salida ZPL
zpl_output = l.dumpZPL()[:-3]
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_output)
