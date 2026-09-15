<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DispensasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private Collection $dispensasi;

    public function __construct(Collection $dispensasi)
    {
        $this->dispensasi = $dispensasi;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->dispensasi;
    }

    /**
     * Header kolom di Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'No. Surat',
            'Tanggal Pengajuan',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Kategori',
            'Alasan',
            'Tujuan',
            'Jam Keluar',
            'Jam Kembali',
            'Status',
            'Guru Piket',
        ];
    }

    /**
     * Mapping data dari collection ke kolom Excel
     */
    public function map($row): array
    {
        static $no = 1;
        
        return [
            $no++,
            $row->nomor_surat,
            $row->created_at->format('d-m-Y H:i'),
            $row->siswa->user->nis_nip ?? '-',
            $row->siswa->nama_lengkap,
            $row->siswa->kelas?->nama_kelas ?? '-',
            $row->siswa->kelas?->jurusan?->nama_jurusan ?? '-',
            ucfirst(str_replace('_', ' ', $row->kategori)),
            $row->alasan,
            $row->tujuan,
            $row->jam_keluar,
            $row->jam_kembali,
            ucfirst($row->status),
            $row->guru?->nama_lengkap ?? '-',
        ];
    }

    /**
     * Styling sederhana untuk header Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0']
                ]
            ],
        ];
    }
}