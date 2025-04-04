import os
import sys
from zebrafy import ZebrafyPDF

def convert_pdf_to_zpl(pdf_path, label_width=406, label_height=203, dpi=203, invert=False):
    # Leer el PDF y convertirlo a ZPL
    with open(pdf_path, "rb") as pdf:
        zpl_string = ZebrafyPDF(pdf.read(), invert=invert).to_zpl()

    # Obtener las dimensiones del PDF en puntos (1 punto = 1/72 pulgadas)
    pdf_width, pdf_height = 8.5 * 72, 11 * 72  # Ajusta esto si el PDF tiene otro tamaño

    # Calcular la escala para ajustar al ancho de la etiqueta
    scale_factor = label_width / pdf_width if pdf_width > label_width else 1

    # Modificar la escala en ZPL (esto es solo un ejemplo)
    zpl_string = zpl_string.replace("^FO100,100", f"^FO100,100^GB{int(label_width*scale_factor)},{int(label_height*scale_factor)},100")

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
