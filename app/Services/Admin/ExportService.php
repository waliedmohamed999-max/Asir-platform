<?php

namespace App\Services\Admin;

use App\Exports\AdminTableExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    public function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function xlsx(string $filename, array $headers, array $rows): BinaryFileResponse
    {
        return Excel::download(new AdminTableExport($headers, $rows), $filename);
    }

    public function pdf(
        string $filename,
        string $title,
        string $subtitle,
        array $summary,
        array $headers,
        array $rows
    ) {
        return Pdf::loadView('admin.exports.pdf', [
            'title' => $title,
            'subtitle' => $subtitle,
            'summary' => $summary,
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape')->download($filename);
    }
}
