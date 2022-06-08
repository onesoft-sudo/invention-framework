<?php

namespace OSN\Framework\Contracts;

interface Query
{
    public function table(string $table): static;

    public function selectRaw(string $start): static;

    public function insertRaw(string $start): static;

    public function updateRaw(string $start): static;

    public function deleteRaw(string $start): static;

    public function whereRaw(string $start): static;

    public function generateQuery();

    public function prepare(string $sql): bool|\PDOStatement;

    public function exec(): bool;

    public function get(): array;
}