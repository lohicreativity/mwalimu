<?php
namespace App\Domain\Application\Repositories\Interfaces;

use Illuminate\Http\Request;

interface NextOfKinInterface{
	
	public function store(Request $request);

	public function update(Request $request);
}