<?php

namespace App\Services;

use App\Models\Order;
use RuntimeException;
use Throwable;
use ZipArchive;

class FinanceReportXlsxExporter
{
    /**
     * @param  array{from: string, to: string, status: string}  $filters
     * @param  array{statuses: list<array{0: string, 1: int, 2: int}>, reconciliation: list<array{0: string, 1: int}>}  $summary
     * @param  iterable<Order>  $orders
     */
    public function create(array $filters, array $summary, iterable $orders): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekspor Excel memerlukan ekstensi PHP zip.');
        }

        $workbookPath = tempnam(sys_get_temp_dir(), 'finance-report-');
        $worksheetPath = tempnam(sys_get_temp_dir(), 'finance-sheet-');

        if ($workbookPath === false || $worksheetPath === false) {
            throw new RuntimeException('File sementara untuk laporan Excel tidak dapat dibuat.');
        }

        try {
            $this->writeWorksheet($worksheetPath, $filters, $summary, $orders);

            $archive = new ZipArchive;
            $opened = $archive->open($workbookPath, ZipArchive::OVERWRITE);

            if ($opened !== true) {
                throw new RuntimeException('Arsip laporan Excel tidak dapat dibuat.');
            }

            $archive->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $archive->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $archive->addFromString('xl/workbook.xml', $this->workbookXml());
            $archive->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $archive->addFromString('xl/styles.xml', $this->stylesXml());
            $archive->addFile($worksheetPath, 'xl/worksheets/sheet1.xml');

            if (! $archive->close()) {
                throw new RuntimeException('File laporan Excel tidak dapat diselesaikan.');
            }
        } catch (Throwable $exception) {
            @unlink($workbookPath);
            @unlink($worksheetPath);

            throw $exception;
        }

        @unlink($worksheetPath);

        return $workbookPath;
    }

    /**
     * @param  array{from: string, to: string, status: string}  $filters
     * @param  array{statuses: list<array{0: string, 1: int, 2: int}>, reconciliation: list<array{0: string, 1: int}>}  $summary
     * @param  iterable<Order>  $orders
     */
    private function writeWorksheet(string $path, array $filters, array $summary, iterable $orders): void
    {
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            throw new RuntimeException('Worksheet laporan Excel tidak dapat ditulis.');
        }

        fwrite($stream, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
        fwrite($stream, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        fwrite($stream, '<sheetViews><sheetView workbookViewId="0"><pane ySplit="13" topLeftCell="A14" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>');
        fwrite($stream, '<cols>');

        foreach ([20, 22, 28, 20, 22, 13, 18, 18, 20, 18, 18, 18, 18, 18] as $index => $width) {
            $column = $index + 1;
            fwrite($stream, '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>');
        }

        fwrite($stream, '</cols><sheetData>');
        $rowNumber = 1;
        $this->writeRow($stream, $rowNumber++, [['Laporan Keuangan Mamikhevanshop', 'string', 3]]);
        $this->writeRow($stream, $rowNumber++, [['Periode', 'string', 1], [$filters['from'].' sampai '.$filters['to']]]);
        $statusLabel = $filters['status'] === '' ? 'Semua status' : (Order::STATUS_LABELS[$filters['status']] ?? $filters['status']);
        $this->writeRow($stream, $rowNumber++, [['Filter status', 'string', 1], [$statusLabel]]);
        $this->writeRow($stream, $rowNumber++, []);
        $this->writeRow($stream, $rowNumber++, [['Ringkasan Pesanan', 'string', 3], [''], [''], [''], ['Rekonsiliasi Pesanan Selesai', 'string', 3]]);
        $this->writeRow($stream, $rowNumber++, [['Status', 'string', 1], ['Jumlah transaksi', 'string', 1], ['Nilai (Rp)', 'string', 1], [''], ['Komponen', 'string', 1], ['Nilai (Rp)', 'string', 1]]);

        for ($index = 0; $index < max(count($summary['statuses']), count($summary['reconciliation'])); $index++) {
            $status = $summary['statuses'][$index] ?? null;
            $reconciliation = $summary['reconciliation'][$index] ?? null;
            $this->writeRow($stream, $rowNumber++, [
                [$status[0] ?? ''],
                [$status[1] ?? '', 'number'],
                [$status[2] ?? '', 'number', 2],
                [''],
                [$reconciliation[0] ?? ''],
                [$reconciliation[1] ?? '', 'number', 2],
            ]);
        }

        $this->writeRow($stream, $rowNumber++, []);
        $this->writeRow($stream, $rowNumber++, [['Rincian Transaksi', 'string', 3]]);
        $tableHeaderRow = $rowNumber;
        $headers = ['Tanggal', 'Kode Pesanan', 'Pelanggan', 'Status', 'Pembayaran', 'Jumlah Item', 'Subtotal Produk', 'Diskon Produk', 'Penjualan Produk Bersih', 'Ongkir', 'Diskon Pengiriman', 'Ongkir Bersih', 'Kode Unik Transfer', 'Total Transaksi'];
        $this->writeRow($stream, $rowNumber++, array_map(fn (string $header): array => [$header, 'string', 1], $headers));

        foreach ($orders as $order) {
            $this->writeRow($stream, $rowNumber++, [
                [$order->created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? ''],
                [$order->code],
                [$order->customer_name],
                [$order->statusLabel()],
                [$order->paymentLabel()],
                [$order->items_count, 'number'],
                [$order->subtotal, 'number', 2],
                [$order->promotion_discount, 'number', 2],
                [$order->subtotal - $order->promotion_discount, 'number', 2],
                [$order->shipping_cost, 'number', 2],
                [$order->shipping_discount, 'number', 2],
                [$order->shipping_cost - $order->shipping_discount, 'number', 2],
                [$order->payment_unique_code ?? 0, 'number', 2],
                [$order->total, 'number', 2],
            ]);
        }

        fwrite($stream, '</sheetData><autoFilter ref="A'.$tableHeaderRow.':N'.max($tableHeaderRow, $rowNumber - 1).'"/></worksheet>');
        fclose($stream);
    }

    /**
     * @param  resource  $stream
     * @param  list<array{0: mixed, 1?: string, 2?: int}>  $cells
     */
    private function writeRow($stream, int $rowNumber, array $cells): void
    {
        fwrite($stream, '<row r="'.$rowNumber.'">');

        foreach ($cells as $index => $cell) {
            $reference = $this->columnName($index + 1).$rowNumber;
            $value = $cell[0] ?? '';
            $type = $cell[1] ?? 'string';
            $style = $cell[2] ?? 0;

            if ($type === 'number' && $value !== '') {
                fwrite($stream, '<c r="'.$reference.'"'.($style ? ' s="'.$style.'"' : '').' t="n"><v>'.(int) $value.'</v></c>');

                continue;
            }

            $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            fwrite($stream, '<c r="'.$reference.'"'.($style ? ' s="'.$style.'"' : '').' t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>');
        }

        fwrite($stream, '</row>');
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder).$name;
            $column = intdiv($column - 1, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
            '<Default Extension="xml" ContentType="application/xml"/>'.
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'.
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'.
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.
            '</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'.
            '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<sheets><sheet name="Laporan Keuangan" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'.
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'.
            '</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
            '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0;[Red](&quot;Rp&quot; #,##0)"/></numFmts>'.
            '<fonts count="3"><font><name val="Calibri"/><sz val="11"/></font><font><name val="Calibri"/><b/><color rgb="FFFFFFFF"/><sz val="11"/></font><font><name val="Calibri"/><b/><color rgb="FF292524"/><sz val="16"/></font></fonts>'.
            '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF59E0B"/><bgColor indexed="64"/></patternFill></fill></fills>'.
            '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'.
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'.
            '<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf></cellXfs>'.
            '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'.
            '</styleSheet>';
    }
}
