<?php
/**
 * Comment: Export Class to facilitate Maatwebsite/Laravel-Excel
 * Created: 5/10/2019
 */

namespace App;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected $header_array;
    protected $data_array;

    public function __construct($header, $data)
    {
        $this->header_array = $header;
        $this->data_array = $data;
    }

    public function collection()
    {
        return collect($this->data_array);
    }

    public function headings(): array
    {
        return $this->header_array;
    }
}
