import sys
from zebrafy import ZebrafyZPL
from PIL import Image

def convert_image_to_zpl(image_path):
    image = Image.open(image_path).convert("1")
    zpl_label = ZebrafyZPL(image)  # Aquí va directamente la imagen
    return zpl_label.to_zpl()

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python img_to_zpl.py <imagen>")
        sys.exit(1)
