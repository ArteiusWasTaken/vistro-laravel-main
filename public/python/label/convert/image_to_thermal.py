import img2pdf
import uuid
import sys
import os
from PIL import Image
import PyPDF2
import warnings

img = str(sys.argv[1])
zoom = int(sys.argv[2])
img_name = str(uuid.uuid1()) + ".png"
pdf_name = str(uuid.uuid1()) + ".pdf"
pdf_name_resized = str(uuid.uuid1()) + ".pdf"

image = Image.open(img).convert("L")

new_img = Image.new("RGB", (image.size[0], image.size[1]), (255, 0, 255)).convert("L")
cmp_img = Image.composite(image, new_img, image).quantize(colors=256, method=2)

cmp_img.save(img_name)

with open(pdf_name, "wb") as f:
    f.write(img2pdf.convert(img_name))

with warnings.catch_warnings():
    warnings.filterwarnings("ignore", category=PyPDF2.utils.PdfReadWarning)
    pdf = PyPDF2.PdfFileReader(pdf_name)
    page0 = pdf.getPage(0)
    if zoom == 0:
        page0.scaleTo(288, 576)

    writer = PyPDF2.PdfFileWriter()
    writer.addPage(page0)
    with open(pdf_name_resized, "wb+") as f:
        writer.write(f)

os.remove(img_name)
os.remove(pdf_name)

quit(pdf_name_resized)
