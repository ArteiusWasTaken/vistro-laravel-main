import os
import sys
from zebrafy import ZebrafyPDF

def convert_pdf_to_zpl(pdf_path):
    with open("source.pdf", "rb") as pdf:
        zpl_string = ZebrafyPDF(pdf.read()).to_zpl()

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
    zpl_code = convert_pdf_to_zpl(pdf_file)
    print(zpl_code)
