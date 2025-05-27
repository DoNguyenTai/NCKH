<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        $viewBooking = Permission::create(['name' => 'view booking']);
        $editBooking = Permission::create(['name' => 'edit booking']);

        $admin->givePermissionTo(['view booking', 'edit booking']);
        $user->givePermissionTo('view booking');
    }
}
