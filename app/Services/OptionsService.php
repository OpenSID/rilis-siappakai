<?php

namespace App\Services;

use App\Enums\JenisTema;
use App\Enums\Logo;
use App\Enums\NilaiKebenaran;
use App\Enums\Opensid;
use App\Enums\Pendaftaran;
use App\Enums\Warna;
use App\Enums\StyleOption;
use App\Enums\WidgetOption;

class OptionsService
{
    public function pilihLogo()
    {
        return collect(Logo::cases())->map(fn($case) => [
            'value' => $case->value,
            'logo' => $case->label()
        ]);
    }

    public function pilihWarna()
    {
        return collect(Warna::cases())->map(fn($case) => [
            'value' => $case->value,
            'color' => $case->label()
        ]);
    }

    public function pilihStyle()
    {
        return collect(StyleOption::cases())->map(fn($case) => [
            'value' => $case->value,
            'style' => $case->label()
        ]);
    }

    public function pilihNilaiKebenaran()
    {
        return collect(NilaiKebenaran::cases())->map(fn($case) => [
            'value' => $case->value,
            'nilai' => $case->label()
        ]);
    }

    public function pilihOpenSid()
    {
        return collect(Opensid::cases())
            ->filter(function ($case) {
                return $case->value < 3;
            })
            ->map(fn($case) => [
                'value' => $case->value,
                'label' => $case->label()
            ]);
    }

    public function pilihWidget()
    {
        return collect(WidgetOption::cases())->map(fn($case) => [
            'value' => $case->value,
            'widget' => $case->label()
        ]);
    }

    public function pilihJenisTema()
    {
        return collect(JenisTema::cases())->map(fn($case) => [
            'key' => $case->value,
            'value' => $case->label()
        ]);
    }

    public function pilihPendaftaran($sebutandesa, $sebutankab, $namakabupaten)
    {
        return collect(Pendaftaran::cases())

            ->map(fn($case) => [
                'key' => $case->value,
                'value' => $case->options($sebutandesa, $sebutankab, $namakabupaten)
            ]);
    }

    public function getAll()
    {
        return  [
            'logo' => $this->pilihLogo(),
            'warna' => $this->pilihWarna(),
            'style' => $this->pilihStyle(),
            'nilaiKebenaran' => $this->pilihNilaiKebenaran(),
            'widget' => $this->pilihWidget(),
            'jenisTema' => $this->pilihJenisTema(),
            'pendaftaran' => $this->pilihPendaftaran('Desa', 'Kabupaten', 'Kabupaten'),
        ];
    }
}
