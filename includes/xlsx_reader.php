<?php
/**
 * includes/xlsx_reader.php
 * Lector y generador nativo de archivos Microsoft Excel (.xlsx) OpenXML.
 * No requiere librerías externas pesadas (utiliza ZipArchive y SimpleXML).
 */

if (!function_exists('leer_archivo_xlsx')) {
    /**
     * Parsea un archivo .xlsx y retorna una matriz de filas con columnas normalizadas.
     *
     * @param string $ruta_archivo Ruta física al archivo .xlsx
     * @param int $total_columnas Número mínimo de columnas a normalizar por fila (por defecto 6)
     * @return array|false Matriz de filas o false en caso de error
     */
    function leer_archivo_xlsx(string $ruta_archivo, int $total_columnas = 6) {
        if (!file_exists($ruta_archivo)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta_archivo) !== true) {
            return false;
        }

        // 1. Cargar strings compartidos (xl/sharedStrings.xml)
        $sharedStrings = [];
        $ssXmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXmlContent !== false) {
            $ssXml = @simplexml_load_string($ssXmlContent);
            if ($ssXml && isset($ssXml->si)) {
                foreach ($ssXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $textoRich = '';
                        foreach ($si->r as $r) {
                            $textoRich .= (string)$r->t;
                        }
                        $sharedStrings[] = $textoRich;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Localizar la primera hoja de trabajo
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXmlContent === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $stat['name'])) {
                    $sheetXmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        if ($sheetXmlContent === false) {
            $zip->close();
            return false;
        }

        $sheetXml = @simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            $zip->close();
            return [];
        }

        $filas = [];

        // 3. Procesar filas y celdas
        foreach ($sheetXml->sheetData->row as $rowNode) {
            $celdasFila = [];

            foreach ($rowNode->c as $cell) {
                $cellRef = (string)$cell['r'];
                preg_match('/^([A-Z]+)(\d+)$/i', $cellRef, $matches);
                $colLetters = strtoupper($matches[1] ?? 'A');

                // Convertir letras de columna (A, B, C...) a índice base 0
                $colIdx = 0;
                $len = strlen($colLetters);
                for ($k = 0; $k < $len; $k++) {
                    $colIdx = $colIdx * 26 + (ord($colLetters[$k]) - 64);
                }
                $colIdx -= 1;

                $type = (string)$cell['t'];
                $val = isset($cell->v) ? (string)$cell->v : '';

                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $val = (string)$cell->is->t;
                }

                $celdasFila[$colIdx] = $val;
            }

            // Normalizar la fila con las columnas requeridas
            if (!empty($celdasFila)) {
                $filaNormalizada = [];
                for ($c = 0; $c < $total_columnas; $c++) {
                    $filaNormalizada[$c] = trim((string)($celdasFila[$c] ?? ''));
                }
                $filas[] = $filaNormalizada;
            }
        }

        $zip->close();
        return $filas;
    }
}

if (!function_exists('crear_archivo_xlsx')) {
    /**
     * Genera un archivo .xlsx válido a partir de una matriz de filas.
     *
     * @param string $ruta_destino Ruta donde se guardará el archivo generado
     * @param array $datos Matriz de filas y columnas
     * @param string $nombre_hoja Nombre de la hoja de cálculo
     * @return bool True si se generó con éxito
     */
    function crear_archivo_xlsx(string $ruta_destino, array $datos, string $nombre_hoja = 'Aspirantes'): bool {
        $zip = new ZipArchive();
        if ($zip->open($ruta_destino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="' . htmlspecialchars($nombre_hoja, ENT_XML1, 'UTF-8') . '" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';

        $sharedStrings = [];
        $ssMap = [];
        $sheetRowsXml = '';

        $obtenerLetraColumna = function($colIdx) {
            $letter = '';
            $colIdx += 1;
            while ($colIdx > 0) {
                $rem = ($colIdx - 1) % 26;
                $letter = chr(65 + $rem) . $letter;
                $colIdx = intdiv($colIdx - 1, 26);
            }
            return $letter;
        };

        $rNum = 1;
        foreach ($datos as $row) {
            $sheetRowsXml .= '<row r="' . $rNum . '">';
            $cNum = 0;
            foreach ($row as $cellVal) {
                $colLetter = $obtenerLetraColumna($cNum);
                $cellRef = $colLetter . $rNum;
                $strVal = (string)$cellVal;
                if (!isset($ssMap[$strVal])) {
                    $ssIndex = count($sharedStrings);
                    $sharedStrings[] = $strVal;
                    $ssMap[$strVal] = $ssIndex;
                } else {
                    $ssIndex = $ssMap[$strVal];
                }
                $sheetRowsXml .= '<c r="' . $cellRef . '" t="s"><v>' . $ssIndex . '</v></c>';
                $cNum++;
            }
            $sheetRowsXml .= '</row>';
            $rNum++;
        }

        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $str) {
            $sstXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $sstXml .= '</sst>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>' . $sheetRowsXml . '</sheetData>
</worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        return true;
    }
}

