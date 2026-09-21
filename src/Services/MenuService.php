<?php
namespace App\Services;
use App\Repositories\MenuRepository;

class MenuService
{
    private MenuRepository $repo;

    public function __construct()
    {
        $this->repo = new MenuRepository();
    }

    public function listAvailable(): array
    {
        return $this->repo->findAllAvailable();
    }

    public function getById(string $id): array
    {
        $item = $this->repo->findById($id);
        if (!$item) throw new \Exception('Menu item not found');
        return (array) $item;
    }

    public function create(array $data): string
    {
        $id = $this->repo->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => (float) $data['price'],
            'category' => $data['category'] ?? 'general',
            'imageUrl' => $data['imageUrl'] ?? '',
            'isAvailable' => true,
        ]);
        return (string) $id;
    }

    public function update(string $id, array $data): void
    {
        $this->repo->update($id, $data);
    }
    public function delete(string $id): void
    {
        $this->repo->delete($id);
    }
}


