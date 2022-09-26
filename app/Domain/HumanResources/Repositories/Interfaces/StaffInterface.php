<?php
namespace App\Domain\HumanResources\Repositories\Interfaces;

use Illuminate\Http\Request;

interface StaffInterface{
	
	public function store(Request $request);

	public function update(Request $request);

	public function updateDetails(Request $request);
}