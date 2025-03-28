import warnings
from PyPDF2 import PdfFileReader, PdfFileWriter, utils
import uuid
import sys

warnings.filterwarnings("ignore", category=utils.PdfReadWarning)

pdf_document = str(sys.argv[1])
zoom = int(sys.argv[2])
pdf_name = str(uuid.uuid1()) + ".pdf"

pdf = PdfFileReader(pdf_document)
if pdf.isEncrypted:
    pdf.decrypt('')

page0 = pdf.getPage(0)
if zoom == 0:
    page0.scaleTo(288, 576)

writer = PdfFileWriter()
writer.addPage(page0)

with open(pdf_name, "wb") as f:
    writer.write(f)

print(pdf_name)
