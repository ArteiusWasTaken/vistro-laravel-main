import sys
import json
import datetime
import pytz
from escpos.printer import Network

printer = sys.argv[1]
info = json.loads(sys.argv[2])
productos = json.loads(sys.argv[3])
seguimientos = json.loads(sys.argv[4])

tz = pytz.timezone('America/Mexico_City')
date = str(datetime.datetime.now(tz))

try:
    picking = Network(str(printer)) #Printer IP Address

    picking.set(align="left")
    picking.text("\n\n")
    picking.barcode(str(info['id']), 'CODE39', 65, 3, '', '')
    picking.text("\n")
    picking.text(info["area"] + " / " + info["marketplace"] + "\n");
    picking.text(info["empresa"] + " / " + info["almacen"] + "\n");

    picking.text("\n\n")

    picking.set(align="left")

    picking.text("Productos")
    picking.text("\n")
    picking.text("------------------------------------------------")
    picking.text("\n")

    for producto in productos:
        picking.text(str(producto["sku"]))
        picking.text("\n")
        picking.text(str(producto["descripcion"]))
        picking.text("\n")
        picking.set(width=2, height=2)
        picking.text(str(producto["cantidad"]))
        picking.set(width=1, height=1)
        picking.text("\n")
        picking.text("------------------------------------------------")
        picking.text("\n")

    picking.text("\n\n")

    picking.text("Ultimo seguimiento")
    picking.text("\n")
    picking.text("------------------------------------------------")
    picking.text("\n")

    for seguimiento in seguimientos:
        picking.text(str(seguimiento["usuario"]))
        picking.text("\n")
        picking.text(str(seguimiento["seguimiento"]))
        picking.text("\n")
        picking.text("------------------------------------------------")
        picking.text("\n")

    picking.text("\n\n")

    picking.set(align="center")
    picking.text(date)

    picking.text("\n\n")

    picking.cut()
except Exception as e:
    quit(json.dumps({"code": 500, "message": "Ocurrio un error al conectar con la impresora, mensaje de error: " + str(e) + ""}))

quit(json.dumps({"code": 200, "message": "Eticketa impresora correctamente"}))
