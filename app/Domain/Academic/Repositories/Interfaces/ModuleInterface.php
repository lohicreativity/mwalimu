<?php

interface ModuleInterface{
	
	public function store($request);

	public function update($request)

	public function destroy($id);
}