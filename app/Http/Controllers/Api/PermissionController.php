<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::query()
            ->orderBy('name')
            ->get();
    }
}
