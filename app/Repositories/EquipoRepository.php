<?php

namespace App\Repositories;

use App\Models\Equipo;

class EquipoRepository
{
    protected $model;

    public function __construct(Equipo $equipo)
    {
        $this->model = $equipo;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getEstadisticas()
    {
        return [
            "total" => $this->model->count(),
            "activos" => $this->model->where("estado", "Activo")->count()
        ];
    }
}