import sys
from zebrafy.zpl import ZPLLabel
from PIL import Image

def convert_image_to_zpl(image_path):
    image = Image.open(image_path).convert("1")
    zpl_label = ZPLLabel.from_image(image)
    return zpl_label.to_zpl()

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python img_to_zpl.py")
        sys.exit(1)

    image_file = sys.argv[1]
    print(convert_image_to_zpl(image_file))
