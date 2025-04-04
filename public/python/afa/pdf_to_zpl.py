import os
import sys
from zebrafy import ZebrafyPDF

def convert_pdf_to_zpl(pdf_path, label_width=406, label_height=203, dpi=203, invert=True):
    with open(pdf_path, "rb") as pdf:
        zpl_string = ZebrafyPDF(
            pdf.read(),
            format="Z64",            # Formato Z64
            invert=invert,           # No invertir colores
            dither=False,            # Desactivar dithering
            threshold=128,           # Umbral de color
            dpi=dpi,                 # DPI a 203 (para etiquetas térmicas)
            width=label_width,       # Ancho de la etiqueta en puntos (2 pulgadas)
            height=label_height,     # Alto de la etiqueta en puntos (1 pulgada)
            pos_x=0,                 # Posición en X (ajustar según necesidad)
            pos_y=0,                 # Posición en Y (ajustar según necesidad)
            rotation=0,              # No rotar la imagen
            string_line_break=80,    # Longitud de las líneas de texto
            complete_zpl=True,       # Generar el ZPL completo
            split_pages=True,        # Dividir las páginas si el PDF tiene más de una
        ).to_zpl()

    with open("output.zpl", "w") as zpl:
        zpl.write(zpl_string)

    # Eliminar saltos de línea innecesarios
    zpl_string = zpl_string.replace('\n', '')

    return zpl_string

if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.exit("Uso: python pdf_to_zpl.py <ruta/relativa/al/pdf>")

    relative_path = sys.argv[1]
    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
    pdf_file = os.path.abspath(os.path.join(project_root, relative_path))

    if not os.path.exists(pdf_file):
        sys.exit(f"Archivo no encontrado: {pdf_file}")

    # Llama a la función y luego imprime el resultado
    zpl_code = convert_pdf_to_zpl(pdf_file, label_width=406, label_height=203, invert=False)  # Invertir colores en False
    print(zpl_code)
