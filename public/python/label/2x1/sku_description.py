import sys
import os
import zpl
import uuid
import textwrap
import unicodedata

# Normalizar texto (eliminar acentos, etc.)
def clean_text(text):
    return unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode("ascii")

# Argumentos de entrada
sku = clean_text(str(sys.argv[1]))
desc = clean_text(str(sys.argv[2]))
qty = str(sys.argv[3])
extra = clean_text(str(sys.argv[4]))

# Escala para impresoras de 300 DPI
DPI_SCALE = 300 / 203

sku_length = len(sku)

# Ajustar ancho del código de barras basado en largo del SKU
barcode_width_length = 0.12 * DPI_SCALE if sku_length <= 12 else 0.099 * DPI_SCALE
barcode_start_cords_x = (6 if sku_length <= 14 else 13) * DPI_SCALE

# Crear etiqueta
l = zpl.Label(50.8 * DPI_SCALE, 25.4 * DPI_SCALE)

# Función para dividir descripción sin cortar palabras
def split_text_without_cutting_words(text, max_length):
    words = text.split()
    lines = []
    current_line = ""

    for word in words:
        if len(current_line) + len(word) + 1 > max_length:
            lines.append(current_line)
            current_line = word
        else:
            if current_line:
                current_line += " "
            current_line += word

    if current_line:
        lines.append(current_line)

    return lines

# Escribir descripción
lines_of_desc = split_text_without_cutting_words(desc, 50)[:3]
start_y = 10 * DPI_SCALE
for i, line in enumerate(lines_of_desc):
    l.origin(int(2 * DPI_SCALE), int(start_y + (i * 3 * DPI_SCALE)))
    l.write_text(line, char_height=2 * DPI_SCALE, char_width=1.1 * DPI_SCALE, line_width=35 * DPI_SCALE)
    l.endorigin()

# Escribir extra si existe
if extra:
    l.origin(int(2 * DPI_SCALE), int(start_y + 9 * DPI_SCALE))
    l.write_text(extra[:50], char_height=2 * DPI_SCALE, char_width=1.2 * DPI_SCALE, line_width=40 * DPI_SCALE)
    l.endorigin()

# Código de barras
l.origin(int(barcode_start_cords_x - (sku_length / 2) * DPI_SCALE), int(2 * DPI_SCALE))
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=2, height=50 * DPI_SCALE)
l.barcode(barcode_type='C', code=sku, height=int(50 * DPI_SCALE), check_digit='N')
l.write_text(sku)
l.endorigin()

# Generar ZPL final
zpl_output = l.dumpZPL()[:-3]
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

print(zpl_output)
