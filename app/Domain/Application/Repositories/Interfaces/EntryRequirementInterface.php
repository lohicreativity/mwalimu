<?php
namespace App\Domain\Application\Repositories\Interfaces;

use Illuminate\Http\Request;

interface EntryRequirementInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}