from fpdf import FPDF
import sys
import pyqrcode
import uuid
import png
import os

qr_data = str(sys.argv[1])
sku = str(sys.argv[2])
descripcion = str(sys.argv[3])
printer = str(sys.argv[4])

pdf_name = str(uuid.uuid1()) + ".pdf"
image_name = str(uuid.uuid1()) + ".png"

qr = pyqrcode.create(qr_data)
qr.png(image_name, scale=5)

pdf = FPDF('P', 'in', [4, 8]) #pdf format
pdf.add_page()
pdf.set_font('Arial', 'B', 8)

# PDF en el primer QR
pdf.image(image_name, 0.5, 0.7, 3, 3, 'png')

pdf.cell(0, 0.1, sku)
pdf.ln()
pdf.cell(0, 0.1, descripcion[0:50])

if len(descripcion) > 50:
    pdf.ln()
    pdf.cell(0, 0.1, descripcion[50:100])

# PDF en el segundo QR
pdf.image(image_name, 0.5, 4.5, 3, 3, 'png')

pdf.ln(3.6)

pdf.cell(0, 0.1, sku)
pdf.ln()
pdf.cell(0, 0.1, descripcion[0:50])

if len(descripcion) > 50:
    pdf.ln()
    pdf.cell(0, 0.1, descripcion[50:100])

pdf.output(pdf_name)

os.remove(image_name)

os.system("lp -d " + printer + " " + pdf_name + "")

os.remove(pdf_name)