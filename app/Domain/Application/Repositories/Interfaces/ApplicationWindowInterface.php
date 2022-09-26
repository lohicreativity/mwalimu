<?php
namespace App\Domain\Application\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ApplicationWindowInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}