<?php

namespace App\Middleware;

class RequireRole
{
    public static function check(
        \Psr\Http\Message\ServerRequestInterface $request,
        array $allowedRoles
    ): void {

        $role = $request->getAttribute('role');

        if (!in_array($role, $allowedRoles, true)) {

            throw new \Exception(
                'Insufficient permissions',
                403
            );
        }
    }
}