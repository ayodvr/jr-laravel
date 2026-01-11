<?php

namespace App\Repositories;

use App\Models\Secret;

interface SecretRepositoryInterface
{
    public function create(array $data): Secret;
    public function find(string $id): ?Secret;
    public function delete(string $id): void;
}
