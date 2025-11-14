<?php
// File: entities/MonAn.php

class MonAn
{
    private int $id_mon;
    private ?int $id_nhom;
    private string $ten_mon;
    private float $gia_tien;
    private ?string $mo_ta;
    private string $trang_thai;

    public function __construct(
        int $id_mon,
        ?int $id_nhom,
        string $ten_mon,
        float $gia_tien,
        ?string $mo_ta,
        string $trang_thai
    ) {
        $this->id_mon = $id_mon;
        $this->id_nhom = $id_nhom;
        $this->ten_mon = $ten_mon;
        $this->gia_tien = $gia_tien;
        $this->mo_ta = $mo_ta;
        $this->trang_thai = $trang_thai;
    }

    // Getters
    public function getId(): int { return $this->id_mon; }
    public function getIdNhom(): ?int { return $this->id_nhom; }
    public function getTenMon(): string { return $this->ten_mon; }
    public function getGiaTien(): float { return $this->gia_tien; }
    public function getMoTa(): ?string { return $this->mo_ta; }
    public function getTrangThai(): string { return $this->trang_thai; }

    public function toArray(): array
    {
        return [
            'id_mon' => $this->id_mon,
            'id_nhom' => $this->id_nhom,
            'ten_mon' => $this->ten_mon,
            'gia_tien' => $this->gia_tien,
            'mo_ta' => $this->mo_ta,
            'trang_thai' => $this->trang_thai
        ];
    }
}