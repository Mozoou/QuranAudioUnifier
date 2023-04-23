<?php

namespace App\Services\AudioFetcher;

class ResourceBuilder
{
    public function createResource(string $resourceFqcn, array $data): object
    {
        $resource = new $resourceFqcn();

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($resource, $method)) {
                $resource->{$method}($value);
            }
        }

        return $resource;
    }
}
