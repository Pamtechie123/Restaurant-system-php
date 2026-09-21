<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\MenuRepository;
use MongoDB\BSON\UTCDateTime;

class OrderService
{
    private OrderRepository $orderRepo;
    private MenuRepository $menuRepo;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->menuRepo = new MenuRepository();
    }

    public function placeOrder(
        string $userId,
        string $orderType,
        ?int $tableNumber,
        array $items,
        ?string $reservationId = null,
        ?string $shippingAddress = null,
        ?string $state = null,
        ?string $city = null,
        ?string $phone = null
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Validate Order Type
        |--------------------------------------------------------------------------
        */

        $validOrderTypes = [
            'dine-in',
            'takeaway',
            'delivery'
        ];

        if (!in_array($orderType, $validOrderTypes, true)) {
            throw new \Exception(
                'Invalid order type'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Items
        |--------------------------------------------------------------------------
        */

        if (empty($items)) {
            throw new \Exception(
                'Order must contain at least one item'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Dine-In
        |--------------------------------------------------------------------------
        */

        if ($orderType === 'dine-in') {

            if (!$tableNumber || $tableNumber < 1) {
                throw new \Exception(
                    'Table number is required for dine-in'
                );
            }

            if (
                $reservationId === null ||
                trim($reservationId) === ''
            ) {
                throw new \Exception(
                    'Reservation ID is required for dine-in'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Delivery
        |--------------------------------------------------------------------------
        */

        if ($orderType === 'delivery') {

            if (
                !$shippingAddress ||
                trim($shippingAddress) === ''
            ) {
                throw new \Exception(
                    'Shipping address is required for delivery'
                );
            }

            if (
                !$state ||
                trim($state) === ''
            ) {
                throw new \Exception(
                    'State is required for delivery'
                );
            }

            if (
                !$city ||
                trim($city) === ''
            ) {
                throw new \Exception(
                    'City is required for delivery'
                );
            }

            if (
                !$phone ||
                trim($phone) === ''
            ) {
                throw new \Exception(
                    'Phone number is required for delivery'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Order Total
        |--------------------------------------------------------------------------
        */

        $orderItems = [];
        $total = 0;

        foreach ($items as $item) {

            if (
                !isset($item['menuItemId']) ||
                !isset($item['quantity'])
            ) {
                throw new \Exception(
                    'Invalid order item'
                );
            }

            $quantity = (int) $item['quantity'];

            if ($quantity < 1) {
                throw new \Exception(
                    'Item quantity must be at least 1'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Get Menu Item
            |--------------------------------------------------------------------------
            */

            $menuItem = $this->menuRepo->findById(
                $item['menuItemId']
            );

            if (!$menuItem) {
                throw new \Exception(
                    'Menu item not found: ' .
                    $item['menuItemId']
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Check Availability
            |--------------------------------------------------------------------------
            */

            if (
                isset($menuItem['isAvailable']) &&
                !$menuItem['isAvailable']
            ) {
                throw new \Exception(
                    'Item unavailable: ' .
                    $menuItem['name']
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Add Item To Order
            |--------------------------------------------------------------------------
            */

            $price = (float) $menuItem['price'];

            $orderItems[] = [
                'menuItemId' => $item['menuItemId'],
                'name' => $menuItem['name'],
                'price' => $price,
                'quantity' => $quantity
            ];

            $total += $price * $quantity;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Order
        |--------------------------------------------------------------------------
        */

        $order = [
            'userId' => $userId,

            'orderType' => $orderType,

            'reservationId' => $orderType === 'dine-in'
                ? $reservationId
                : null,

            'tableNumber' => $orderType === 'dine-in'
                ? $tableNumber
                : null,

            'shippingAddress' => $orderType === 'delivery'
                ? trim($shippingAddress)
                : null,

            'state' => $orderType === 'delivery'
                ? trim($state)
                : null,

            'city' => $orderType === 'delivery'
                ? trim($city)
                : null,

            'phone' => $orderType === 'delivery'
                ? trim($phone)
                : null,

            'items' => $orderItems,

            'total' => $total,

            'status' => 'pending',

            'createdAt' => new UTCDateTime()
        ];


        /*
        |--------------------------------------------------------------------------
        | Save Order
        |--------------------------------------------------------------------------
        */

        return (string) $this->orderRepo->create(
            $order
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Orders
    |--------------------------------------------------------------------------
    */

    public function getActiveOrders(): array
    {
        return $this->orderRepo->findActive();
    }


    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        string $id,
        string $status
    ): void {

        $validStatuses = [
            'pending',
            'preparing',
            'ready',
            'served',
            'cancelled'
        ];

        if (
            !in_array(
                $status,
                $validStatuses,
                true
            )
        ) {
            throw new \Exception(
                'Invalid status'
            );
        }

        $this->orderRepo->updateStatus(
            $id,
            $status
        );
    }
}