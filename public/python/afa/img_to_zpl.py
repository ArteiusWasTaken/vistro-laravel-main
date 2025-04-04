import sys
from zebrafy import ZebrafyZPL
from PIL import Image

def convert_image_to_zpl(image_path, label_width=406, label_height=203, dpi=203, invert=True):
   with open(image_path, "rb") as image:
       zpl_string = ZebrafyImage(
           image.read(),
           format="Z64",
           invert=True,
           dither=False,
           threshold=128,
           dpi=dpi,
           width=label_width,
           height=label_height,
           pos_x=100,
           pos_y=100,
           rotation=90,
           string_line_break=80,
           complete_zpl=True,
       ).to_zpl()

    with open("output.zpl", "w") as zpl:
        zpl.write(zpl_string)

    zpl_string = zpl_string.replace('\n', '')

    return zpl_string

if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.exit("Uso: python pdf_to_zpl.py <ruta/relativa/al/pdf>")

    relative_path = sys.argv[1]
    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
    img_file = os.path.abspath(os.path.join(project_root, relative_path))

    if not os.path.exists(img_file):
        sys.exit(f"Archivo no encontrado: {img_file}")

    zpl_code = convert_pdf_to_zpl(img_file, label_width=406, label_height=203, invert=True)
    print(zpl_code)
