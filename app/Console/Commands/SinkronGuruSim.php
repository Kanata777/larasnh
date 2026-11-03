<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\SimPegawaiService;

#[Signature('sim:sinkron-guru')]
#[Description('Command description')]
class SinkronGuruSim extends Command
{
    protected $description = 'Sinkronkan data guru dari SIM Pegawai ke tabel wali_kelas';
    /**
     * Execute the console command.
     */
    public function handle(SimPegawaiService $sim): int
    {
        $hasil = $sim->sync();

        $hasil['success'] ? $this->info($hasil['message']) : $this->error($hasil['message']);

        return $hasil['success'] ? self::SUCCESS : self::FAILURE;
    }
}
