<?php

namespace App\Repositories;

use App\Models\Secret;

class SecretRepository implements SecretRepositoryInterface
{
    public function create(array $data): Secret
    {
        return Secret::create($data);
    }

    public function find(string $id): ?Secret
    {
        return Secret::find($id);
    }

    public function delete(string $id): void
    {
        Secret::destroy($id);
    }
}
