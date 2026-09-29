<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MenuController
{
    // All dishes (customers see unavailable ones marked as "Unavailable")
    public function list(Request $request, Response $response): Response
    {
        $items = db()->menu_items->find([], ['sort' => ['name' => 1]]);

        return json($response, cleanList($items));
    }

    // One dish
    public function show(Request $request, Response $response, array $args): Response
    {
        $item = db()->menu_items->findOne(['_id' => toObjectId($args['id'])]);

        if (!$item) {
            throw new \Exception('Dish not found', 404);
        }

        return json($response, cleanDoc($item));
    }

    // Admin: add a new dish (sent as a form so an image can be uploaded)
    public function create(Request $request, Response $response): Response
    {
        authUser($request, 'admin');

        $data = requireFields($request->getParsedBody(), ['name', 'price', 'category']);

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            throw new \Exception('Price must be a number greater than 0', 400);
        }

        $id = db()->menu_items->insertOne([
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'price' => (float) $data['price'],
            'category' => trim($data['category']),
            'imageUrl' => $this->saveImage($request),
            'isAvailable' => true,
        ])->getInsertedId();

        return json($response, ['message' => 'Dish added', 'id' => (string) $id], 201);
    }

    // Admin: mark a dish available / unavailable
    public function setAvailability(Request $request, Response $response, array $args): Response
    {
        authUser($request, 'admin');

        $data = $request->getParsedBody();
        if (!isset($data['isAvailable'])) {
            throw new \Exception('isAvailable is required', 400);
        }

        db()->menu_items->updateOne(
            ['_id' => toObjectId($args['id'])],
            ['$set' => ['isAvailable' => filter_var($data['isAvailable'], FILTER_VALIDATE_BOOLEAN)]]
        );

        return json($response, ['message' => 'Dish updated']);
    }

    // Admin: remove a dish
    public function delete(Request $request, Response $response, array $args): Response
    {
        authUser($request, 'admin');

        db()->menu_items->deleteOne(['_id' => toObjectId($args['id'])]);

        return json($response, ['message' => 'Dish deleted']);
    }

    // Save the uploaded image in public/uploads and return its web path.
    private function saveImage(Request $request): ?string
    {
        $file = $request->getUploadedFiles()['image'] ?? null;

        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \Exception('Image upload failed, try again', 400);
        }
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('Image must be smaller than 2MB', 400);
        }

        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \Exception('Image must be a jpg, png or webp file', 400);
        }

        $folder = __DIR__ . '/../../public/uploads';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $name = bin2hex(random_bytes(8)) . '.' . $extension;
        $file->moveTo("$folder/$name");

        // Make sure the file really is a picture
        if (!@getimagesize("$folder/$name")) {
            unlink("$folder/$name");
            throw new \Exception('That file is not a valid image', 400);
        }

        return "/uploads/$name";
    }
}
