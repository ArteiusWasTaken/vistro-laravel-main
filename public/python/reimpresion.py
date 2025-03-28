import sys
import json
import datetime
import pytz
import os
from escpos.printer import Network

printer = sys.argv[1]
paqueteria = sys.argv[2]
guias = json.loads(sys.argv[3])

tz = pytz.timezone('America/Mexico_City')
date = str(datetime.datetime.now(tz))

try:
    manifiesto = Network(str(printer)) #Printer IP Address

    manifiesto.set(align="center")
    manifiesto.image("img/omg.png")

    manifiesto.text("\n\n")

    manifiesto.text("Manifiesto")
    manifiesto.text("\n")
    manifiesto.text("Reimpresion")
    manifiesto.text("\n")
    manifiesto.text(str(paqueteria))
    manifiesto.text("\n\n")

    manifiesto.text("Guias")
    manifiesto.text("\n")
    manifiesto.text(str(len(guias)))
    manifiesto.text("\n")
    manifiesto.text("------------------------------------------")
    manifiesto.text("\n")

    for guia in guias:
        manifiesto.text(str(guia))
        manifiesto.text("\n")

    manifiesto.text("\n\n\n\n\n\n")

    manifiesto.text("------------------------------------------")
    manifiesto.text("\n")

    manifiesto.text("Recibi")

    manifiesto.text("\n\n")
    manifiesto.text(date)

    manifiesto.text("\n\n")

    manifiesto.cut()
except Exception as e:
    quit(json.dumps({"code": 500, "message": "Ocurrió un error al conectar con la impresora"}), e)

quit(json.dumps({"code": 200, "message": "Manifiesto impreso correctamente"}))
