import os
import sys
from zebrafy import ZebrafyPDF, ZebrafyZPL

def convert_pdf_to_zpl(pdf_path):
    with open(pdf_path, "rb") as f:
        pdf_bytes = f.read()

    pdf = ZebrafyPDF(pdf_bytes)
    images = pdf.convert()

    zpl = ZebrafyZPL(images[0])
    return zpl.to_zpl()

if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.exit("Uso: python pdf_to_zpl.py <archivo.pdf>")

    relative_path = sys.argv[1]
    script_dir = os.path.dirname(os.path.abspath(__file__))
    pdf_file = os.path.abspath(os.path.join(script_dir, "..", "..", relative_path))

    if not os.path.exists(pdf_file):
        sys.exit(f"Archivo no encontrado: {pdf_file}")

    print(convert_pdf_to_zpl(pdf_file))
