<?php
namespace App\Domain\Settings\Repositories\Interfaces;

use Illuminate\Http\Request;

interface PermissionInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}