import fitz  # PyMuPDF
import json
import os
import argparse

def cortar_pdf(input_path):
    """
    Recorta una región rectangular de una página PDF y guarda el resultado.
    """

    resultado = {
        "error": True,
        "message": "",
        "path": ""
    }
    try:

        # Validar que el archivo exista
        if not os.path.exists(input_path):
            resultado["message"] = "El archivo '{input_path}' no existe."
            resultado

        # Abrir el PDF original
        pdf = fitz.open(input_path)
        pagina = pdf[0]
        ancho, alto = pagina.rect.width, pagina.rect.height
        x1, y1, x2, y2 = 0, 440, 500, 780

        # Validar dimensiones
        if ancho != 802.0 or alto != 555.0:
            resultado["message"] = f"Error: Dimensiones incorrectas. Esperado: 802x555, Obtenido: {ancho}x{alto}"
            return resultado

        # Generar ruta de salida automática
        output_path = os.path.join(
            os.path.dirname(input_path),
            os.path.splitext(os.path.basename(input_path))[0] + "_recortado.pdf"
        )
        resultado["output_path"] = os.path.abspath(output_path)
        
        # Definir el rectángulo a recortar (las coordenadas son como en un sistema de imágenes)
        rectangulo = fitz.Rect(x1, y1, x2, y2)
        
        # Recortar la página
        pagina.set_cropbox(rectangulo)
        
        # Guardar solo la página recortada (o todas si prefieres)
        pdf_pagina_recortada = fitz.open()  # Nuevo PDF
        pdf_pagina_recortada.insert_pdf(pdf, from_page=0, to_page=0)
        
        # Guardar el resultado
        pdf_pagina_recortada.save(output_path)

        # Éxito
        resultado.update({
            "success": True,
            "message": "PDF cortado verticalmente (mitad izquierda).",
            "output_path": output_path
        })

    except Exception as e:
        resultado["message"] = f"Error inesperado: {str(e)}"

    return resultado

if __name__ == "__main__":
    # Configurar argumentos de línea de comandos
    parser = argparse.ArgumentParser(description="Corta un PDF para su impresión en termico.")
    parser.add_argument("--input", required=True, help="Ruta del PDF de entrada.")
    args = parser.parse_args()

    response = cortar_pdf(args.input)
    print(json.dumps(response, indent=2))