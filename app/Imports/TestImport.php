<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TestImport implements ShouldQueue, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    public function __construct($currentPerson)
    {
        $this->currentPerson = new Collection($currentPerson->toArray());
        requestBin(['in' => 'constructor test import ']);
    }

    public function collection(Collection $collection)
    {
        requestBin(['should execute this']);
        foreach ($collection as $row) {
        }

        return $collection;
    }

    public function getProcessedRowCount(): int
    {
        return $this->row_count;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'pmi_id' => Rule::required(),
            'primary_email' => Rule::required(),
            'alternate_email' => Rule::requiredIf($em1 === null),
        ];
    }
}
