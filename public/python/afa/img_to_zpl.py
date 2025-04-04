import sys
import os
from zebrafy import ZebrafyImage
from PIL import Image

def convert_image_to_zpl(image_path, label_width=406, label_height=203, dpi=203, invert=False):
    # Abre la imagen
    with Image.open(image_path) as img:
        # Redimensiona la imagen para que se ajuste al tamaño de la etiqueta
        img = img.resize((label_width, label_height), Image.ANTIALIAS)  # Redimensiona la imagen a las dimensiones de la etiqueta

        # Guarda la imagen redimensionada a un archivo temporal
        temp_image_path = "temp_resized_image.bmp"
        img.save(temp_image_path)

        # Lee la imagen redimensionada
        with open(temp_image_path, "rb") as image:
            zpl_string = ZebrafyImage(
                image.read(),
                format="Z64",            # Formato Z64
                invert=invert,           # Invertir colores
                dither=False,            # Desactivar dithering
                threshold=128,           # Umbral de color
                width=label_width,       # Ancho de la etiqueta en puntos (406 px)
                height=label_height,     # Alto de la etiqueta en puntos (203 px)
                pos_x=0,                 # Posición en X (ajustar según necesidad)
                pos_y=0,                 # Posición en Y (ajustar según necesidad)
                rotation=0,              # No rotar la imagen
                string_line_break=80,    # Longitud de las líneas de texto
                complete_zpl=True        # Generar el ZPL completo
            ).to_zpl()

        # Elimina el archivo temporal de la imagen redimensionada
        os.remove(temp_image_path)

    with open("output.zpl", "w") as zpl:
        zpl.write(zpl_string)

    zpl_string = zpl_string.replace('\n', '')  # Eliminar saltos de línea innecesarios

    return zpl_string

if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.exit("Uso: python img_to_zpl.py <ruta/relativa/al/imagen>")

    relative_path = sys.argv[1]
    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
    img_file = os.path.abspath(os.path.join(project_root, relative_path))

    if not os.path.exists(img_file):
        sys.exit(f"Archivo no encontrado: {img_file}")

    zpl_code = convert_image_to_zpl(img_file, label_width=406, label_height=203, dpi=203, invert=False)  # Invertir colores en False
    print(zpl_code)
