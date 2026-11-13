<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Builds the formatted Excel (.xlsx) quiz-question template and turns an
 * uploaded .xlsx back into CSV text, so QuizCsvImportService validates both
 * formats with exactly the same rules.
 *
 * Uses only PHP's zip and SimpleXML extensions; no spreadsheet package.
 */
class QuizQuestionSpreadsheet
{
    public const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /** Rows beyond this are refused before anything is expanded in memory. */
    private const MAX_ROW = 1000;

    private const MAX_COLUMN = 50;

    /** Guards against zip bombs: the 2 MB upload may not inflate past this. */
    private const MAX_UNCOMPRESSED_BYTES = 30 * 1024 * 1024;

    // Cell style indexes defined in styles().
    private const STYLE_HEADER = 1;

    private const STYLE_NOTE = 2;

    private const STYLE_BODY_TEXT = 3;

    private const STYLE_BODY_CENTER = 4;

    private const STYLE_COLUMN_TEXT = 5;

    /**
     * @param  list<string>  $notes  Lines shown above the header; each must start with "#".
     * @param  list<string>  $headers
     * @param  list<list<string|int>>  $examples
     * @param  array<string, float>  $widths  Column letter => width in characters.
     */
    public function template(array $notes, array $headers, array $examples, array $widths): string
    {
        $lastColumn = $this->columnLetter(count($headers) - 1);
        $rowsXml = '';
        $merges = [];
        $rowNumber = 0;

        foreach ($notes as $note) {
            $rowNumber++;
            $cells = $this->stringCell('A'.$rowNumber, $note, self::STYLE_NOTE);
            for ($index = 1; $index < count($headers); $index++) {
                $cells .= '<c r="'.$this->columnLetter($index).$rowNumber.'" s="'.self::STYLE_NOTE.'"/>';
            }
            $rowsXml .= '<row r="'.$rowNumber.'" ht="21" customHeight="1">'.$cells.'</row>';
            $merges[] = 'A'.$rowNumber.':'.$lastColumn.$rowNumber;
        }

        $rowNumber++;
        $headerRow = $rowNumber;
        $cells = '';
        foreach ($headers as $index => $header) {
            $cells .= $this->stringCell($this->columnLetter($index).$rowNumber, $header, self::STYLE_HEADER);
        }
        $rowsXml .= '<row r="'.$rowNumber.'" ht="24" customHeight="1">'.$cells.'</row>';

        foreach ($examples as $example) {
            $rowNumber++;
            $cells = '';
            foreach (array_values($example) as $index => $value) {
                $reference = $this->columnLetter($index).$rowNumber;
                $cells .= is_int($value)
                    ? '<c r="'.$reference.'" s="'.self::STYLE_BODY_CENTER.'"><v>'.$value.'</v></c>'
                    : $this->stringCell($reference, (string) $value, self::STYLE_BODY_TEXT);
            }
            $rowsXml .= '<row r="'.$rowNumber.'">'.$cells.'</row>';
        }

        $colsXml = '';
        foreach (array_keys($headers) as $index) {
            $letter = $this->columnLetter($index);
            $column = $index + 1;
            // Text format on the answer columns keeps typed values such as
            // TRUE or 007 exactly as entered; points stays a number column.
            $style = $index === count($headers) - 1 ? '' : ' style="'.self::STYLE_COLUMN_TEXT.'"';
            $colsXml .= '<col min="'.$column.'" max="'.$column.'" width="'.($widths[$letter] ?? 14).'" customWidth="1"'.$style.'/>';
        }

        $firstDataRow = $headerRow + 1;
        $mergeXml = $merges === [] ? '' : '<mergeCells count="'.count($merges).'">'
            .implode('', array_map(fn (string $range): string => '<mergeCell ref="'.$range.'"/>', $merges))
            .'</mergeCells>';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::MAIN_NS.'" xmlns:r="'.self::REL_NS.'">'
            .'<sheetViews><sheetView workbookViewId="0">'
            .'<pane ySplit="'.$headerRow.'" topLeftCell="A'.$firstDataRow.'" activePane="bottomLeft" state="frozen"/>'
            .'<selection pane="bottomLeft" activeCell="A'.$firstDataRow.'" sqref="A'.$firstDataRow.'"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18" customHeight="1"/>'
            .'<cols>'.$colsXml.'</cols>'
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .$mergeXml
            .'<dataValidations count="1">'
            .'<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1"'
            .' promptTitle="Question type" prompt="Pick multiple_choice, true_false or identification."'
            .' errorTitle="Question type" error="Choose a type from the list."'
            .' sqref="A'.$firstDataRow.':A'.($headerRow + 500).'">'
            .'<formula1>"multiple_choice,true_false,identification"</formula1>'
            .'</dataValidation></dataValidations>'
            .'<pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            .'</worksheet>';

        return $this->zip([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="'.self::REL_NS.'/officeDocument" Target="xl/workbook.xml"/>'
                .'</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<workbook xmlns="'.self::MAIN_NS.'" xmlns:r="'.self::REL_NS.'">'
                .'<sheets><sheet name="Questions" sheetId="1" r:id="rId1"/></sheets>'
                .'</workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="'.self::REL_NS.'/worksheet" Target="worksheets/sheet1.xml"/>'
                .'<Relationship Id="rId2" Type="'.self::REL_NS.'/styles" Target="styles.xml"/>'
                .'</Relationships>',
            'xl/worksheets/sheet1.xml' => $sheet,
            'xl/styles.xml' => $this->styles(),
        ]);
    }

    /**
     * Reads the first worksheet of an .xlsx file and returns it as CSV text.
     * Empty rows are kept as blank lines so reported row numbers match the
     * row numbers the teacher sees in the spreadsheet.
     */
    public function toCsv(string $binary): string
    {
        $path = tempnam(sys_get_temp_dir(), 'quiz-xlsx-');
        if ($path === false || file_put_contents($path, $binary) === false) {
            throw $this->invalid('The Excel file could not be read.');
        }

        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($zip->open($path, ZipArchive::RDONLY) !== true) {
                throw $this->invalid('The file is not a valid Excel (.xlsx) workbook.');
            }
            $opened = true;

            $uncompressed = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $uncompressed += (int) ($zip->statIndex($index)['size'] ?? 0);
            }
            if ($uncompressed > self::MAX_UNCOMPRESSED_BYTES) {
                throw $this->invalid('The Excel file is too large to import.');
            }

            $sheetXml = $this->part($zip, $this->firstSheetPath($zip));
            if ($sheetXml === null) {
                throw $this->invalid('The Excel file has no worksheet to import.');
            }
            $sharedStrings = $this->sharedStrings($zip);
            $grid = $this->readGrid($sheetXml, $sharedStrings);
        } finally {
            if ($opened) {
                $zip->close();
            }
            @unlink($path);
        }

        if ($grid === []) {
            return '';
        }

        $lastRow = max(array_keys($grid));
        $lastColumn = max(array_map(fn (array $row): int => max(array_keys($row)), $grid));
        $stream = fopen('php://temp', 'r+');
        for ($row = 1; $row <= $lastRow; $row++) {
            $cells = $grid[$row] ?? [];
            if ($cells === []) {
                fwrite($stream, "\r\n");

                continue;
            }
            $line = [];
            for ($column = 0; $column <= $lastColumn; $column++) {
                $line[] = $cells[$column] ?? '';
            }
            fputcsv($stream, $line, ',', '"', '', "\r\n");
        }
        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    /** @return array<int, array<int, string>> row number => column index => value */
    private function readGrid(string $sheetXml, array $sharedStrings): array
    {
        $sheet = $this->xml($sheetXml);
        $grid = [];
        foreach ($sheet->sheetData->row ?? [] as $rowNode) {
            foreach ($rowNode->c as $cell) {
                $reference = (string) $cell['r'];
                if (! preg_match('/^([A-Z]{1,3})(\d+)$/', $reference, $match)) {
                    continue;
                }
                $value = $this->cellValue($cell, $sharedStrings);
                if (trim($value) === '') {
                    continue;
                }
                $row = (int) $match[2];
                $column = $this->columnIndex($match[1]);
                if ($row > self::MAX_ROW) {
                    throw $this->invalid('The Excel file has data below row '.self::MAX_ROW.'. Keep questions within the first 500 rows under the header.');
                }
                if ($column >= self::MAX_COLUMN) {
                    continue;
                }
                $grid[$row][$column] = $value;
            }
        }
        ksort($grid);

        return $grid;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $raw = isset($cell->v) ? (string) $cell->v : '';

        return match ($type) {
            's' => $sharedStrings[(int) $raw] ?? '',
            'inlineStr' => $this->richText($cell->is),
            'b' => $raw === '1' ? 'TRUE' : 'FALSE',
            'str', 'e' => $raw,
            default => $this->number($raw),
        };
    }

    private function number(string $raw): string
    {
        if (! is_numeric($raw)) {
            return $raw;
        }
        $number = (float) $raw;
        if (floor($number) === $number && abs($number) < 1e15) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(sprintf('%.15F', $number), '0'), '.');
    }

    /** @return list<string> */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $this->part($zip, 'xl/sharedStrings.xml');
        if ($xml === null) {
            return [];
        }

        $strings = [];
        foreach ($this->xml($xml)->si as $item) {
            $strings[] = $this->richText($item);
        }

        return $strings;
    }

    /** Plain <t> or rich-text runs <r><t>, skipping phonetic hints. */
    private function richText(?SimpleXMLElement $node): string
    {
        if ($node === null) {
            return '';
        }
        if (isset($node->t)) {
            return (string) $node->t;
        }
        $text = '';
        foreach ($node->r as $run) {
            $text .= (string) $run->t;
        }

        return $text;
    }

    private function firstSheetPath(ZipArchive $zip): string
    {
        $fallback = 'xl/worksheets/sheet1.xml';
        $workbookXml = $this->part($zip, 'xl/workbook.xml');
        $relsXml = $this->part($zip, 'xl/_rels/workbook.xml.rels');
        if ($workbookXml === null || $relsXml === null) {
            return $fallback;
        }

        $sheet = $this->xml($workbookXml)->sheets->sheet[0] ?? null;
        $relationId = $sheet ? (string) $sheet->attributes(self::REL_NS)['id'] : '';
        foreach ($this->xml($relsXml)->Relationship as $relation) {
            if ((string) $relation['Id'] === $relationId) {
                $target = ltrim((string) $relation['Target'], '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        return $fallback;
    }

    private function part(ZipArchive $zip, string $name): ?string
    {
        $contents = $zip->getFromName($name);

        return $contents === false ? null : $contents;
    }

    private function xml(string $contents): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if ($xml === false) {
            throw $this->invalid('The Excel file is damaged and could not be read.');
        }

        return $xml;
    }

    /** @param array<string, string> $parts */
    private function zip(array $parts): string
    {
        $path = tempnam(sys_get_temp_dir(), 'quiz-template-');
        $zip = new ZipArchive;
        if ($path === false || $zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('The Excel template could not be created.');
        }
        foreach ($parts as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $zip->close();
        $binary = (string) file_get_contents($path);
        @unlink($path);

        return $binary;
    }

    private function styles(): string
    {
        $border = '<border><left style="thin"><color rgb="FFD0D5E2"/></left><right style="thin"><color rgb="FFD0D5E2"/></right>'
            .'<top style="thin"><color rgb="FFD0D5E2"/></top><bottom style="thin"><color rgb="FFD0D5E2"/></bottom><diagonal/></border>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::MAIN_NS.'">'
            .'<fonts count="3">'
            .'<font><sz val="11"/><color rgb="FF1F2937"/><name val="Calibri"/><family val="2"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
            .'<font><i/><sz val="10"/><color rgb="FF4B5563"/><name val="Calibri"/><family val="2"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF13176B"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFF6D5"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'.$border.'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function stringCell(string $reference, string $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $letter = chr(65 + (($index - 1) % 26)).$letter;
        }

        return $letter;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function invalid(string $message): ValidationException
    {
        return ValidationException::withMessages(['csv_file' => $message]);
    }
}
