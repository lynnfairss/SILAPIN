<?php

namespace App\Services;

use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Paragraph;
use PhpOffice\PhpWord\IOFactory;

class DocxParserService
{
    public function parse(string $filePath): array
    {
        libxml_use_internal_errors(true);
        $phpWord = IOFactory::load($filePath);
        libxml_clear_errors();

        $section = $phpWord->getSections()[0] ?? null;
        if (!$section) {
            return [];
        }

        $elements = $section->getElements();
        $result = [];

        $this->parseHalTable($elements, $result);
        $this->parseKepada($elements, $result);
        $this->parsePembuka($elements, $result);
        $this->parseSayaYang($elements, $result);
        $this->parseIdentitasTable($elements, $result);
        $this->parseBermaksud($elements, $result);
        $this->parseIsi($elements, $result);
        $this->parseRencana($elements, $result);
        $this->parseJadwalTable($elements, $result);
        $this->parsePenutup($elements, $result);
        $this->parseTtdTable($elements, $result);

        return $result;
    }

    private function getCellText($cell): string
    {
        $text = '';
        foreach ($cell->getElements() as $el) {
            $text .= $this->extractText($el);
        }
        return trim($text);
    }

    private function getCellTexts($cell): array
    {
        $texts = [];
        foreach ($cell->getElements() as $el) {
            if ($el instanceof Text) {
                $texts[] = $el->getText();
            } elseif ($el instanceof TextRun) {
                foreach ($el->getElements() as $run) {
                    if ($run instanceof Text) {
                        $texts[] = $run->getText();
                    }
                }
            } elseif ($el instanceof Paragraph) {
                $paraText = $this->extractText($el);
                if (trim($paraText) !== '') {
                    $texts[] = $paraText;
                }
            }
        }
        return $texts;
    }

    private function extractText($el): string
    {
        $text = '';
        if ($el instanceof Text) {
            $text .= $el->getText();
        } elseif ($el instanceof TextRun) {
            foreach ($el->getElements() as $run) {
                $text .= $this->extractText($run);
            }
        } elseif ($el instanceof Paragraph) {
            foreach ($el->getElements() as $pEl) {
                $text .= $this->extractText($pEl);
            }
        } elseif ($el instanceof Table) {
            foreach ($el->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $text .= $this->getCellText($cell);
                }
            }
        }
        return $text;
    }

    private function getTableAtIndex(array $elements, int $index): ?Table
    {
        $count = 0;
        foreach ($elements as $el) {
            if ($el instanceof Table) {
                if ($count === $index) {
                    return $el;
                }
                $count++;
            }
        }
        return null;
    }

    private function getNonTableTextAtIndex(array $elements, int $index): string
    {
        $count = 0;
        foreach ($elements as $el) {
            if ($el instanceof Table) {
                continue;
            }
            if ($count === $index) {
                return trim($this->extractText($el));
            }
            $count++;
        }
        return '';
    }

    private function parseHalTable(array $elements, array &$result): void
    {
        $table = $this->getTableAtIndex($elements, 1);
        if (!$table) return;

        $rows = $table->getRows();
        if (empty($rows)) return;

        $cells = $rows[0]->getCells();
        if (count($cells) < 2) return;

        $halText = $this->getCellText($cells[0]);
        $halText = preg_replace('/^Hal\s+:\s*/', '', $halText);
        $result['hal'] = trim($halText);
    }

    private function parseKepada(array $elements, array &$result): void
    {
        $result['kepada_yth'] = $this->getNonTableTextAtIndex($elements, 3);
        $result['kepada_kab'] = $this->getNonTableTextAtIndex($elements, 4);
        $result['kepada_tempat'] = $this->getNonTableTextAtIndex($elements, 5);
    }

    private function parsePembuka(array $elements, array &$result): void
    {
        $result['pembuka'] = $this->getNonTableTextAtIndex($elements, 7);
    }

    private function parseSayaYang(array $elements, array &$result): void
    {
        $result['saya_yang'] = $this->getNonTableTextAtIndex($elements, 9);
    }

    private function parseIdentitasTable(array $elements, array &$result): void
    {
        $table = $this->getTableAtIndex($elements, 2);
        if (!$table) return;

        $rows = $table->getRows();
        if (count($rows) < 3) return;

        if (isset($rows[0]->getCells()[2])) {
            $result['nama_peminjam'] = $this->getCellText($rows[0]->getCells()[2]);
        }
        if (isset($rows[1]->getCells()[2])) {
            $result['nik'] = $this->getCellText($rows[1]->getCells()[2]);
        }
        if (isset($rows[2]->getCells()[2])) {
            $result['jabatan'] = $this->getCellText($rows[2]->getCells()[2]);
        }
    }

    private function parseBermaksud(array $elements, array &$result): void
    {
        $result['bermaksud'] = $this->getNonTableTextAtIndex($elements, 11);
    }

    private function parseIsi(array $elements, array &$result): void
    {
        $result['isi'] = $this->getNonTableTextAtIndex($elements, 14);
    }

    private function parseRencana(array $elements, array &$result): void
    {
        $result['rencana'] = $this->getNonTableTextAtIndex($elements, 17);
    }

    private function parseJadwalTable(array $elements, array &$result): void
    {
        $table = $this->getTableAtIndex($elements, 4);
        if (!$table) return;

        $rows = $table->getRows();
        if (count($rows) < 3) return;

        foreach ($rows as $i => $row) {
            $cells = $row->getCells();
            if (count($cells) < 4) continue;

            $label = $this->getCellText($cells[1]);
            $value = preg_replace('/^:\s*/', '', $this->getCellText($cells[3]));

            switch ($i) {
                case 0:
                    $result['hari_label'] = $label;
                    break;
                case 1:
                    $result['tanggal_label'] = $label;
                    break;
                case 2:
                    $result['tempat_label'] = $label;
                    $result['instansi'] = trim($value);
                    break;
            }
        }
    }

    private function parsePenutup(array $elements, array &$result): void
    {
        $raw = $this->getNonTableTextAtIndex($elements, 19);

        if (str_contains($raw, 'Atas perhatian')) {
            $parts = explode('Atas perhatian', $raw, 2);
            $result['penutup'] = rtrim($parts[0]);
            $result['terima_kasih'] = 'Atas perhatian' . $parts[1];
        } else {
            $result['penutup'] = $raw;
            $result['terima_kasih'] = '';
        }
    }

    private function parseTtdTable(array $elements, array &$result): void
    {
        $table = $this->getTableAtIndex($elements, 5);
        if (!$table) return;

        $rows = $table->getRows();
        if (count($rows) < 2) return;

        $row0Cells = $rows[0]->getCells();
        if (count($row0Cells) >= 4) {
            $result['ttd_kiri_label'] = $this->getCellText($row0Cells[1]);
            $result['ttd_kanan_label'] = $this->getCellText($row0Cells[3]);
        }

        $row1Cells = $rows[1]->getCells();
        if (count($row1Cells) >= 4) {
            $leftTexts = $this->getCellTexts($row1Cells[1]);
            $leftTexts = array_values(array_filter($leftTexts, fn($t) => trim($t) !== ''));

            if (count($leftTexts) >= 1) {
                $result['ttd_kiri_nama'] = $leftTexts[0];
            }
            if (count($leftTexts) >= 2) {
                $nrpText = $leftTexts[1];
                $result['ttd_kiri_nrp'] = preg_replace('/^NRP\.\s*/', '', $nrpText);
            }
            if (count($leftTexts) >= 3) {
                $result['ttd_kiri_jabatan'] = $leftTexts[2];
            }

            $rightTexts = $this->getCellTexts($row1Cells[3]);
            $rightTexts = array_values(array_filter($rightTexts, fn($t) => trim($t) !== ''));

            if (count($rightTexts) >= 1) {
                $result['ttd_kanan_nama'] = $rightTexts[0];
            }
            if (count($rightTexts) >= 2) {
                $nrpText = $rightTexts[1];
                $result['ttd_kanan_nrp'] = preg_replace('/^NRP\.\s*/', '', $nrpText);
            }
            if (count($rightTexts) >= 3) {
                $result['ttd_kanan_jabatan'] = $rightTexts[2];
            }
        }
    }
}
