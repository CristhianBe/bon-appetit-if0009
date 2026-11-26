<?php

namespace App\Http\Requests;

use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Foundation\Http\FormRequest;

class ReservaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:120'],
            'personas' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'zona' => ['nullable', 'string', 'max:40'],
            'fecha' => ['sometimes', 'required', 'date_format:Y-m-d', 'after_or_equal:'.ReservaService::hoy()],
            'hora' => ['sometimes', 'required', 'date_format:H:i'],
            'mesa_id' => ['nullable', 'integer', 'exists:mesas,id'],
            'estado' => ['sometimes', 'required', 'in:'.implode(',', Reserva::ESTADOS)],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la reserva es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'personas.min' => 'Debe asistir al menos una persona.',
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'fecha.after_or_equal' => 'La fecha de la reserva no puede ser anterior a hoy.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'mesa_id.exists' => 'La mesa indicada no existe.',
            'estado.in' => 'El estado de la reserva no es válido.',
        ];
    }
}
