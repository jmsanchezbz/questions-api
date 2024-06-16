# Importación de preguntas

## Configuración script python para la importación de preguntas desde los archivos txt


  Este script genera un fichero con los inserts de las preguntas generado de los archivos de texto en la carpeta **path** que deben ser de la misma **administración** y **grupo** definido.

    def create_inserts_file():
        path = '/path/where/files/are/' # Path where the files are
        administration="caib"   #Convocatoria caib,local...
        group="A1"  #A1, A2, B1, B2...

## Ejecución script python para la importación de preguntas
Una vez configurada el path, administración y grup del script se debe ejecutar el siguiente comando para obtener el fichero definido también en el script *resultsql="test.sql"*.

    $ python3 import_questions.py

## Conversión de archivos a UTF-8

    #!/bin/bash

    # Function to convert a single file from ISO-8859-15 to UTF-8
    convert_file() {
      local file="$1"
      local dir
      dir=$(dirname "$file")
      local base
      base=$(basename "$file")
      local filename="${base%.*}"
      local extension="${base##*.}"
      local converted_file="$dir/${filename}_converted.$extension"

      # Convert the file and save to the new file
      iconv -f ISO-8859-15 -t UTF-8 "$file" > "$converted_file"

      if [ $? -eq 0 ]; then
        echo "Converted $file to $converted_file"
      else
        echo "Failed to convert $file"
      fi
    }

    # Export the function to be used by find
    export -f convert_file

    # Find all files in the current directory and its subdirectories
    # and convert them
    find . -type f -exec bash -c 'convert_file "$0"' {} \;

    echo "Conversion complete."

