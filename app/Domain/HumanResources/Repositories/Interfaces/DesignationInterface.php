<?php
namespace App\Domain\HumanResources\Repositories\Interfaces;

use Illuminate\Http\Request;

interface DesignationInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}