<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Generator dokumen Word (.docx) murni berbasis PHP ZipArchive (OOXML)
 * untuk template & surat resmi Tata Naskah Dinas Kecamatan Tamalate.
 */
class DocxTemplateBuilder
{
    private array $blocks = [];

    public function addKopSurat(
        string $instansi = 'PEMERINTAH KOTA MAKASSAR',
        string $unit = 'KECAMATAN TAMALATE',
        string $alamat = 'Jalan Danau Tanjung Bunga No. 1, Makassar 90224 · Pos-el: kecamatantamalate@makassarkota.go.id'
    ): self {
        $this->blocks[] = $this->p($instansi, ['align' => 'center', 'bold' => true, 'size' => 26, 'spaceAfter' => 20]);
        $this->blocks[] = $this->p($unit, ['align' => 'center', 'bold' => true, 'size' => 30, 'spaceAfter' => 40]);
        $this->blocks[] = $this->p($alamat, ['align' => 'center', 'size' => 18, 'spaceAfter' => 120, 'bottomBorder' => true]);

        return $this;
    }

    public function addTitle(string $title, ?string $subtitle = null): self
    {
        $this->blocks[] = $this->p($title, [
            'align' => 'center',
            'bold' => true,
            'underline' => true,
            'size' => 24,
            'spaceBefore' => 120,
            'spaceAfter' => $subtitle ? 40 : 140,
        ]);

        if ($subtitle !== null && $subtitle !== '') {
            $this->blocks[] = $this->p($subtitle, [
                'align' => 'center',
                'bold' => true,
                'size' => 22,
                'spaceAfter' => 160,
            ]);
        }

        return $this;
    }

    public function addParagraph(string $text, array $opts = []): self
    {
        $this->blocks[] = $this->p($text, $opts);

        return $this;
    }

    /**
     * Tabel key-value 3 kolom (Label | : | Nilai) tanpa atau dengan border.
     *
     * @param array<int, array{0:string, 1:string}> $rows
     */
    public function addKeyValueTable(array $rows, bool $bordered = false): self
    {
        $tblRows = '';
        foreach ($rows as [$label, $value]) {
            $tblRows .= '<w:tr>'
                . $this->cell($label, 2800, ['bold' => false], $bordered)
                . $this->cell(':', 320, ['align' => 'center'], $bordered)
                . $this->cell($value, 6180, [], $bordered)
                . '</w:tr>';
        }

        $this->blocks[] = $this->wrapTable($tblRows, $bordered);

        return $this;
    }

    /**
     * Tabel grid umum dengan lebar kolom proporsional (total 9300 dxa ~ A4 content width).
     *
     * @param array<int, string>|null $headers
     * @param array<int, array<int, string>> $rows
     * @param array<int, int> $colWidths
     */
    public function addGridTable(?array $headers, array $rows, array $colWidths, bool $bordered = true): self
    {
        $tblRows = '';
        if ($headers !== null) {
            $tblRows .= '<w:tr>';
            foreach ($headers as $idx => $h) {
                $tblRows .= $this->cell($h, $colWidths[$idx] ?? 2000, ['bold' => true, 'fill' => 'E5E7EB'], $bordered);
            }
            $tblRows .= '</w:tr>';
        }

        foreach ($rows as $row) {
            $tblRows .= '<w:tr>';
            foreach ($row as $idx => $val) {
                $tblRows .= $this->cell((string) $val, $colWidths[$idx] ?? 2000, [], $bordered);
            }
            $tblRows .= '</w:tr>';
        }

        $this->blocks[] = $this->wrapTable($tblRows, $bordered);

        return $this;
    }

    /**
     * Blok dua kolom tanda tangan / catatan.
     *
     * @param array<int, string> $leftLines
     * @param array<int, string> $rightLines
     */
    public function addSignatureColumns(array $leftLines, array $rightLines, bool $bordered = false): self
    {
        $leftXml = '';
        foreach ($leftLines as $line) {
            $leftXml .= $this->p($line, ['align' => $bordered ? 'left' : 'center', 'spaceAfter' => 40, 'size' => 21]);
        }

        $rightXml = '';
        foreach ($rightLines as $line) {
            $rightXml .= $this->p($line, ['align' => 'center', 'spaceAfter' => 40, 'size' => 21]);
        }

        $borderXml = $bordered ? $this->borderProps(true) : $this->borderProps(false);
        $this->blocks[] = '<w:tbl><w:tblPr><w:tblW w:w="9300" w:type="dxa"/>' . $borderXml . '</w:tblPr>'
            . '<w:tr>'
            . '<w:tc><w:tcPr><w:tcW w:w="4650" w:type="dxa"/>' . $this->cellMargins() . '</w:tcPr>' . $leftXml . '</w:tc>'
            . '<w:tc><w:tcPr><w:tcW w:w="4650" w:type="dxa"/>' . $this->cellMargins() . '</w:tcPr>' . $rightXml . '</w:tc>'
            . '</w:tr></w:tbl>';

        return $this;
    }

    public function buildBinary(): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'tamalate_docx_');
        if ($tmpPath === false) {
            throw new RuntimeException('Gagal membuat file sementara untuk DOCX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpPath);
            throw new RuntimeException('Gagal membuka arsip DOCX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelsXml());
        $zip->addFromString('word/styles.xml', $this->stylesXml());
        $zip->addFromString('word/document.xml', $this->documentXml());
        $zip->close();

        $binary = file_get_contents($tmpPath);
        @unlink($tmpPath);

        if ($binary === false) {
            throw new RuntimeException('Gagal membaca hasil DOCX.');
        }

        return $binary;
    }

    private function p(string $text, array $opts = []): string
    {
        $align = $opts['align'] ?? 'left';
        $bold = ! empty($opts['bold']);
        $underline = ! empty($opts['underline']);
        $italic = ! empty($opts['italic']);
        $size = (int) ($opts['size'] ?? 22); // half-points: 22 = 11pt
        $spaceBefore = (int) ($opts['spaceBefore'] ?? 0);
        $spaceAfter = (int) ($opts['spaceAfter'] ?? 80);
        $bottomBorder = ! empty($opts['bottomBorder']);

        $pPr = '<w:pPr>';
        $pPr .= '<w:spacing w:before="' . $spaceBefore . '" w:after="' . $spaceAfter . '" w:line="264" w:lineRule="auto"/>';
        $pPr .= '<w:jc w:val="' . $align . '"/>';
        if ($bottomBorder) {
            $pPr .= '<w:pBdr><w:bottom w:val="double" w:sz="8" w:space="4" w:color="111827"/></w:pBdr>';
        }
        $pPr .= '</w:pPr>';

        $rPr = '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
        $rPr .= '<w:sz w:val="' . $size . '"/><w:szCs w:val="' . $size . '"/>';
        if ($bold) {
            $rPr .= '<w:b/>';
        }
        if ($italic) {
            $rPr .= '<w:i/>';
        }
        if ($underline) {
            $rPr .= '<w:u w:val="single"/>';
        }
        $rPr .= '</w:rPr>';

        $lines = explode("\n", $text);
        $runs = '';
        foreach ($lines as $i => $line) {
            if ($i > 0) {
                $runs .= '<w:r>' . $rPr . '<w:br/></w:r>';
            }
            $runs .= '<w:r>' . $rPr . '<w:t xml:space="preserve">' . $this->esc($line) . '</w:t></w:r>';
        }

        return '<w:p>' . $pPr . $runs . '</w:p>';
    }

    private function cell(string $text, int $widthDxa, array $opts = [], bool $bordered = true): string
    {
        $fillXml = isset($opts['fill'])
            ? '<w:shd w:val="clear" w:color="auto" w:fill="' . $opts['fill'] . '"/>'
            : '';
        $align = $opts['align'] ?? 'left';
        $bold = ! empty($opts['bold']);

        return '<w:tc><w:tcPr><w:tcW w:w="' . $widthDxa . '" w:type="dxa"/>'
            . $this->cellMargins($bordered ? 80 : 40)
            . $fillXml
            . '</w:tcPr>'
            . $this->p($text, ['align' => $align, 'bold' => $bold, 'size' => 21, 'spaceAfter' => 20])
            . '</w:tc>';
    }

    private function wrapTable(string $rowsXml, bool $bordered): string
    {
        return '<w:tbl><w:tblPr>'
            . '<w:tblW w:w="9300" w:type="dxa"/>'
            . $this->borderProps($bordered)
            . '</w:tblPr>'
            . $rowsXml
            . '</w:tbl>'
            . '<w:p><w:pPr><w:spacing w:after="100"/></w:pPr></w:p>';
    }

    private function borderProps(bool $bordered): string
    {
        if (! $bordered) {
            return '<w:tblBorders>'
                . '<w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/>'
                . '<w:right w:val="none"/><w:insideH w:val="none"/><w:insideV w:val="none"/>'
                . '</w:tblBorders>';
        }

        return '<w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:left w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:right w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="64748B"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="64748B"/>'
            . '</w:tblBorders>';
    }

    private function cellMargins(int $tb = 60): string
    {
        return '<w:tcMar>'
            . '<w:top w:w="' . $tb . '" w:type="dxa"/>'
            . '<w:left w:w="100" w:type="dxa"/>'
            . '<w:bottom w:w="' . $tb . '" w:type="dxa"/>'
            . '<w:right w:w="100" w:type="dxa"/>'
            . '</w:tcMar>';
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function documentXml(): string
    {
        $body = implode("\n", $this->blocks);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . $body
            . '<w:sectPr>'
            . '<w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1417" w:header="708" w:footer="708" w:gutter="0"/>'
            . '</w:sectPr>'
            . '</w:body></w:document>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
            . '<w:sz w:val="22"/><w:szCs w:val="22"/>'
            . '</w:rPr></w:rPrDefault></w:docDefaults>'
            . '</w:styles>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    private function documentRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }
}
