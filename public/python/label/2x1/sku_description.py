import sys
import os
import zpl
import uuid
import json

# Obtener los argumentos de la línea de comandos
sku = str(sys.argv[1])
desc = str(sys.argv[2])
qty = str(sys.argv[3])
extra = str(sys.argv[4])

# Configuración para la longitud del SKU y el código de barras
sku_length = len(sku)
barcode_width_length = 0.2 if sku_length <= 14 else 0.1

# Ajusta esta variable para mover el código de barras a la izquierda o derecha
barcode_shift = 5  # Aumentar para mover a la derecha, disminuir para mover a la izquierda

barcode_start_cords_x = (4 if sku_length <= 14 else 15) + barcode_shift

# Crear una nueva etiqueta ZPL
l = zpl.Label(50.8, 25.4)  # Tamaño de la etiqueta en milímetros

# Configuración de la altura y anchura del texto y de la línea
char_height = 2
char_width = 1.2
line_width = 35

# Escribir la descripción en la etiqueta en múltiples líneas
lines_of_desc = [desc[i:i+50] for i in range(0, len(desc), 50)]
start_y = 10
for i, line in enumerate(lines_of_desc[:3]):  # Limitar a 3 líneas para un máximo de 150 caracteres
    l.origin(2, start_y + (i * 3))
    l.write_text(line, char_height=char_height, char_width=char_width, line_width=line_width)
    l.endorigin()

# Escribir texto extra si existe
if extra:
    l.origin(2, start_y + 9)  # Ajusta la posición Y según sea necesario
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

# Escribir el código de barras y el SKU
l.origin(barcode_start_cords_x - (sku_length / 2), 2)
l.barcode_field_default(module_width=barcode_width_length, bar_width_ratio=2, height=50)
l.barcode('C', '12345678987654321', height=50, check_digit='N')
l.write_text(sku)
l.endorigin()


# Finalizar la etiqueta ZPL y agregar la cantidad de impresiones
zpl_output = l.dumpZPL()[:-3]
zpl_output += "^PQ" + qty + ",0,1,Y^XZ"

# Crear un archivo con la etiqueta ZPL
file_name = str(uuid.uuid1()) + ".txt"
with open(file_name, "w+") as f:
    f.write(zpl_output)


# Eliminar el archivo temporal
os.remove(file_name)

print(zpl_output)
