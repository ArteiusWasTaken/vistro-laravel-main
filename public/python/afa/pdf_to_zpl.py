import os
import sys
from zebrafy import ZebrafyPDF

def convert_pdf_to_zpl(pdf_path):
    with open(pdf_path, "rb") as pdf:
        zpl_string = ZebrafyPDF(
            pdf.read(),
            format="Z64",
            invert=True,
            dither=False,
            threshold=128,
            dpi=203,
            width=406,
            height=203,
            pos_x=100,
            pos_y=100,
            rotation=90,
            string_line_break=80,
            complete_zpl=True,
            split_pages=True,
        ).to_zpl()

    # Eliminar saltos de línea innecesarios
    zpl_string = zpl_string.replace('\n', '')

    # Retorna el código ZPL
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
    zpl_code =_
