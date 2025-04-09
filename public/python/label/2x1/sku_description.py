label_width_pts = 406
label_height_pts = 203

# Tamaño de caracteres
char_height = 3
char_width = 2

# Función para centrar horizontalmente
def center_x(text, char_width):
    text_width = len(text) * char_width * 8
    return max(0, int((label_width_pts - text_width) / 2))

# Texto principal
wrapped_desc = textwrap.wrap(desc, width=30)
start_y = 80
for i, line in enumerate(wrapped_desc[:2]):
    x = center_x(line, char_width)
    y = start_y + (i * 25)
    l.origin(x, y)
    l.write_text(line, char_height=char_height, char_width=char_width, line_width=40)
    l.endorigin()

# Extra text
if extra:
    x = center_x(extra, char_width)
    l.origin(x, start_y + 50)
    l.write_text(extra[:50], char_height=2, char_width=1.2, line_width=40)
    l.endorigin()

# Código de barras (centrado)
barcode_width_pts = 250
barcode_x = int((label_width_pts - barcode_width_pts) / 2)

l.origin(barcode_x, 10)
l.barcode_field_default(module_width=0.25, bar_width_ratio=2, height=1.0)
l.barcode(height=70, barcode_type='C', code=sku, check_digit='N')
l.write_text(sku)
l.endorigin()
