<?php
namespace App\Domain\Settings\Repositories\Interfaces;

use Illuminate\Http\Request;

interface CampusInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}