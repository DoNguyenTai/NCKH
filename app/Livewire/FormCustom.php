<?php

namespace App\Livewire;

use App\Models\FieldForm;
use App\Models\TypeOfForm;
use Illuminate\Support\Str;
use Livewire\Component;

class FormCustom extends Component
{
    public $typeOfForm;            // danh sách loại đơn
    public $type_of_form;          // ID loại đơn đang chọn
    public $selectedForm;          // Loại đơn đang được chọn (bao gồm fieldForm)
    public $customFields = [];     // Các trường người dùng đang thêm/chỉnh sửa

    public $action = 'CREATE';     // CREATE hoặc UPDATE
    public $fieldID = null;        // ID của field đang sửa
    

    public function mount()
    {
        $this->typeOfForm = TypeOfForm::all();
    }

    public function render()
    {
        $this->selectedForm = TypeOfForm::with(['fieldForm' => function ($q) {
            $q->orderBy('order');
        }])->find( $this->type_of_form);
        return view('livewire.form-custom');
    }

    public function ChoosetypeForm($formId)
    {
        $this->type_of_form = $formId;
       
        $this->resetFieldState();
    }

    public function addCustomField($action = 'CREATE')
    {
 

        $this->action = $action;
        $this->customFields[] = [
            'uuid' => (string) Str::uuid(),
            'key' => 'text',
            'value' => ''
        ];
    }

    public function editCustomField($action, $id)
    {
        $field = FieldForm::findOrFail($id);

        $this->resetFieldState();

        $this->action = $action;
        $this->fieldID = $id;
        $this->customFields[] = [
            'uuid' => (string) Str::uuid(),
            'key' => $field->data_type,
            'value' => $field->value
        ];
    }

    public function removeCustomField($index)
    {
        unset($this->customFields[$index]);
        $this->customFields = array_values($this->customFields); // reindex
    }

    public function save()
    {
        $this->validate([
            'type_of_form' => 'required',
            'customFields' => 'array|min:1',
            'customFields.*.value' => 'required|string',
        ]);

        if ($this->action === 'CREATE') {
            $maxOrder = FieldForm::where('form_id', $this->type_of_form)->max('order') ?? 0;

            foreach ($this->customFields as $field) {
                $exists = FieldForm::where('form_id', $this->type_of_form)
                    ->where('value', $field['value'])
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'Tên trường đã tồn tại!');
                    return;
                }

                FieldForm::create([
                    'form_id' => $this->type_of_form,
                    'data_type' => $field['key'] ?? 'text',
                    'value' => $field['value'],
                    'order' => ++$maxOrder,
                ]);
            }
        }

        if ($this->action === 'UPDATE' && $this->fieldID) {
            $fieldDB = FieldForm::find($this->fieldID);

            if ($fieldDB) {
                $fieldDB->update([
                    'data_type' => $this->customFields[0]['key'] ?? 'text',
                    'value' => $this->customFields[0]['value']
                ]);
            }
        }

        $this->resetFieldState();
        $this->dispatch('$refresh'); 
    }

    public function resetFieldState()
    {
        $this->customFields = [];
        $this->action = 'CREATE';
        $this->fieldID = null;

    }

    public function deleteCustomField($id) {
        FieldForm::findOrFail($id)->delete();
    }

   
   
}
