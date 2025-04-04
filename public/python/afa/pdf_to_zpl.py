from zebrafy import ZebrafyPDF, ZebrafyZPL

def convert_pdf_to_zpl(pdf_path):
    with open(pdf_path, "rb") as f:
        pdf_bytes = f.read()

    pdf = ZebrafyPDF(pdf_bytes)
    images = pdf.convert()
    zpl = ZebrafyZPL(images[0])
    return zpl.to_zpl()

if __name__ == "__main__":
    import sys

    if len(sys.argv) < 2:
        print("Uso: python pdf_to_zpl.py archivo.pdf")
        sys.exit(1)

    pdf_file = sys.argv[1]
    print(convert_pdf_to_zpl(pdf_file))
