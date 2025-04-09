import sys
import os
import zpl
import codecs
import textwrap

# Configura salida UTF-8
sys.stdout = codecs.getwriter("utf-8")(sys.stdout.buffer, errors="replace")

# Parámetros desde consola
sku = str(sys.argv[1])
desc = str(sys.argv[2])
qty = str(sys.argv[3])
extra = str(sys.argv[4])

# Crear etiqueta: 2x1 pulgadas (50.8mm x 25.4mm)
l = zpl.Label(50.8, 25.4)  # ZPL usa mm

# Resolución 203 DPI: 2 pulgadas = 406 dots de ancho
label_width_dots = 406
label_height_dots = 203

# Tamaño de caracteres
char_height = 3
char_width = 2

# Centrado horizontal en puntos
def center_x(text, char_width_factor):
    text_width = len(text) * char_width_factor * 8  # 1 char ≈ 8 dots
    return max(0, int((label_width_dots - text_width) / 8))  # convertir a mm (1mm ≈ 8 dots)

# Descripción (hasta 2 líneas)
wrapped_desc = textwrap.wrap(desc, width=30)
start_y = 90
for i, line in enumerate(wrapped_desc[:2]):
    x = center_x(line, char_width)
    y = start_y + (i * 30)
    l.origin(x, y)
    l.write_text(line, char_height=char_height, char_width=char_width)
    l.endorigin()

# Texto adicional
if extra:
    x = center_x(extra, 1.2)
    l.origin(x, start_y + 60)
    l.write_text(extra[:50], char_height=2, char_width=1.2)
    l.endorigin()

# Código de barras centrado
barcode_module_width = 0.25  # más grande que antes
barcode_height = 70

# Ancho estimado del código de barras en dots = sku_length * module_width * 203
barcode_width_dots = len(sku) * barcode_module_width * 203
barcode_x = int((label_width_dots - barcode_width_dots) / 8)  # a mm

l.origin(barcode_x, 10)
l.barcode_field_default(module_width=barcode_module_width, bar_width_ratio=2, height=1.0)
l.barcode(height=barcode_height, barcode_type='C', code=sku, check_digit='N')
l.write_text(sku)
l.endorigin()

# Generar ZPL y agregar cantidad
zpl_output = l.dumpZPL()[:-3]  # quitar ^XZ
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_output)
