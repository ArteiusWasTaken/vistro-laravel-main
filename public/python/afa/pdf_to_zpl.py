import sys
from zebrafy.zebrafy_zpl import ZebrafyZPL
from zebrafy.utils import pdf_to_image

def convert_pdf_to_zpl(pdf_path):
    images = pdf_to_image(pdf_path)
    zpl_labels = [ZPLLabel.from_image(image) for image in images]
    return "\n".join(label.to_zpl() for label in zpl_labels)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python pdf_to_zpl.py")
        sys.exit(1)

    pdf_file = sys.argv[1]
    print(convert_pdf_to_zpl(pdf_file))
